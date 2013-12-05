<?php
/**
 * TimeSeriesAnomalies
 *
 * Very much based on the anomaly detection system in Kale,
 * (http://codeascraft.com/2013/06/11/introducing-kale/)
 * but implemented in PHP (obviously) over stored metrics rather
 * than a live stream.
 *
 * Expects an array of the current time series data, and an array of
 * data from a period prior to the current data. Runs each current
 * metric point for the graph period through five different anomaly
 * detection checks. Each check returns a yes or no vote on whether
 * or not the point is anomalous with the preceding data. If the number
 * of checks voting yes is equal to or greater than the consensus
 * threshold, the point is flagged as anomalous. Default is to flag if
 * three of the five checks vote yes.
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 28 October 2013
 *
 * @package Arugula
 */

require_once "Math/Stats.php";
require_once "Math/Histogram.php";

class TimeSeriesAnomalies {

  /**
   * The number of seconds worth of data to pull in order to
   * build the anomaly check data
   *
   * @var int
   */
  private $_pre_anomaly_period = 86400;

  /**
   * The list of anomaly detection algorithms to check each
   * point against.
   *
   * @var array
   */
  private $_anomaly_algorithms = array('first_hour_average' => 'enabled',
                                       'mean_subtraction_cumulation' => 'enabled',
                                       'simple_stddev_from_moving_average' => 'enabled',
                                       'least_squares' => 'enabled',
                                       'histogram_bins' => 'enabled');

  /**
   * The number of checks which must vote yes in order for a
   * data point to be flagged as anomalous. Config default
   * is 4
   *
   * @var int
   */
  private $_consensus_threshold = 3;

  /**
   * Several of the checks check the data point against the
   * standard deviation of the series - this configures how
   * many standard deviations away the point must be in order
   * to get a yes vote. Config default is 3.
   *
   * @var int
   */
  private $_std_dev_threshold = 3;

  /**
   * Anomaly detection, especially for larger datasets, can use
   * more memory than PHP is normally given. Bump up the limit for
   * the detection process
   *
   * @var string
   *
   */
  private $_php_memory_limit = '512M';

  private $_return_votes = false;

  private $_arugula_options = array('PRE' => '_pre_anomaly_period',
                                    'CONSENSUS' => '_consensus_threshold',
                                    'STDDEV' => '_std_dev_threshold',
                                    'MEM' => '_php_memory_limit',
                                    'VOTES' => '_return_votes');

  public function __construct($options) {

    // Check for default option overrides
    if (!empty($options))
    {
      if (array_key_exists('PRE', $options))
      {
        $this->_pre_anomaly_period = $options['PRE'];
      }
      if (array_key_exists('CONSENSUS', $options))
      {
        $this->_consensus_threshold = $options['CONSENSUS'];
      }
      if (array_key_exists('STDDEV', $options))
      {
        $this->_std_dev_threshold = $options['STDDEV'];
      }
      if (array_key_exists('MEM', $options))
      {
        $this->_php_memory_limit = $options['MEM'];
      }
      if (array_key_exists('VOTES', $options))
      {
        $this->_return_votes = $options['VOTES'];
      }
    }

    if ($this->_consensus_threshold > count($this->_anomaly_algorithms))
    {
      throw new SWException('Consensus level is greater than the number of voters');
    }

    $this->stats = new Math_Stats();

    /*
     * Anomaly detection can take some time... Bump up the PHP timeout to
     * allow for that. This may need to be even higher for large datasets.
     * If you're seeing Internal Server Error messages from the Calculating
     * Anomalies step, check the apache error log for PHP time out errors.
    */
    set_time_limit(600);
    ini_set('memory_limit', $this->_php_memory_limit);

  }

  /**
   * Return JSON blob with name and status of the detection algorithms
   *
   * @return string
   */
  public function algorithms()
  {
    return json_encode($this->_anomaly_algorithms);
  }

  /**
   * @param array $algorithm
   * @return bool|null
   */
  public function enable_algorithm($algorithm)
  {
    if (!empty($algorithm))
    {
      foreach ($algorithm as $a)
      {
        if (array_key_exists($a, $this->_anomaly_algorithms))
        {
          $this->_anomaly_algorithms[$a] = 'enabled';
        }
      }

      return true;
    }
    else
    {
      return null;
    }
  }

  /**
   * @param array $algorithm
   * @return bool|null
   */
  public function disable_algorithm($algorithm)
  {
    if (!empty($algorith))
    {
      foreach ($algorithm as $a)
      {
        if (array_key_exists($a, $this->_anomaly_algorithms))
        {
          $this->_anomaly_algorithms[$a] = 'disabled';
        }
      }
      return true;
    }
    else
    {
      return null;
    }
  }

  /**
   * @param array $option
   * @return bool|null
   */
  public function set_option($option)
  {
    if (!empty($option))
    {
      $option_key = key($option);
      if (array_key_exists($option_key, $this->_arugula_options))
      {
        $arugula_option = $this->_arugula_options[$option_key];
        $this->$arugula_option = $option[$option_key];
        echo json_encode(array($arugula_option => $this->$arugula_option));
      }
      else
      {
        return null;
      }
    }
  }

  /**
   * TimeSeriesAnomalies::detect_anomaly()
   *
   * Takes the current series of graph data and pairs it with the
   * data from the pre anomaly period to determine whether a point
   * is anomalous.
   *
   * @param array $data - [current_data, pre-period_data]
   * @param array $options
   * @return array
   */
  public function detect_anomaly($data)
  {

    $rolling_metric_data = array();

    $current_data = $data['current_data'];
    $pre_period_data = $data['pre-period_data'];

    foreach ($pre_period_data as $pre_series_entry)
    {
      if (!empty($pre_series_entry['value']))
      {
        $rolling_metric_data[] = array($pre_series_entry['timestamp'], $pre_series_entry['value']);
      }
    }
    unset($pre_period_data);

    $violations = array();
    $in_violation = false;
    $start_violation = null;
    $consensus_votes = array();
    foreach ($current_data as $current_values)
    {
      $anomaly_consensus = array();
      array_shift($rolling_metric_data);
      array_push($rolling_metric_data, array($current_values['timestamp'], $current_values['value']));
      foreach ($this->_anomaly_algorithms as $anomaly_algorithm => $status)
      {
        if ($status === "enabled")
        {
          $vote = $this->$anomaly_algorithm($rolling_metric_data);
          $consensus_votes[$anomaly_algorithm] += $vote;
          $anomaly_consensus[] = $vote;
        }
      }
      if (array_sum($anomaly_consensus) >= $this->_consensus_threshold)
      {
        if (!$in_violation)
        {
          $start_violation = $current_values['timestamp'];
          $in_violation = true;
        }
      }
      else
      {
        if ($in_violation)
        {
          $violations[] = array('start' => $start_violation, 'end' => $current_values['timestamp']);
          $start_violation = null;
          $in_violation = false;
        }
      }
    }
    if ($in_violation)
    {
      $violations[] = array('start' => $start_violation, 'end' => $graph_data[count($graph_data) - 1]['timestamp']);
    }

    if ($this->_return_votes)
    {
      $violations['votes'] = array();
      foreach ($this->_anomaly_algorithms as $voter => $status)
      {
        if ($status === "enabled")
        {
          if ($consensus_votes[$voter] > 0)
          {
            $violations['votes'][] = $voter . ' voted yes ' . $consensus_votes[$voter] . ' times';
          }
          else
          {
            $violations['votes'][] = $voter . ' voted no ' . $consensus_votes[$voter] . ' times';
          }
        }
      }
    }

    return $violations;
  }

  /**
   * Calculates the average of the last three datapoints in the series to use
   * as the anomaly measure. Reduces noise, but also reduces sensitivity and
   * increases the delay to detection.
   *
   * @param array $time_series
   * @return float
   */
  protected function tail_average($time_series)
  {
    $tail_slice = array_slice($time_series, -3);
    $this->stats->setData($tail_slice);
    $tail_average = $this->stats->mean();
    return($tail_average);
  }

  /**
   * Calculate the simple average over one hour of data, $this->_pre_anomaly_period
   * seconds ago. Will vote yes if the average of the last three data points of the
   * current data are outside of $this->_std_dev_threshold standard deviations
   * of this value.
   *
   * @param array $time_series
   * @return bool
   */
  protected function first_hour_average($time_series)
  {
    $current_entry = array_slice($time_series, -1);
    $current_timestamp = $current_entry[0][0];
    $first_hour_offset = $this->_pre_anomaly_period - 3600;
    $first_hour_threshold = $current_timestamp - $first_hour_offset;
    $series_in_range = true;
    $first_hour_series = array();
    while ($series_in_range)
    {
      foreach ($time_series as $metric_data_entry)
      {
        if ($metric_data_entry[0] < $first_hour_threshold)
        {
          $first_hour_series[] = $metric_data_entry[1];
        }
        else
        {
          $series_in_range = false;
        }
      }
    }
    $this->stats->setData($first_hour_series);
    $series_mean = $this->stats->mean();
    $series_std_dev = $this->stats->stDev();
    $series_tail_average = $this->tail_average($first_hour_series);

    return abs($series_tail_average - $series_mean) > $series_std_dev * $this->_std_dev_threshold;
  }

  /**
   * Votes yes if the value of the current data point is farther than
   * $this->_std_deviation_threshold standard deviations out in
   * cumulative terms after subtracting the mean from each data point.
   *
   * @param array $time_series
   * @return bool
   */
  protected function mean_subtraction_cumulation($time_series)
  {
    $series_values = array();
    foreach ($time_series as $metric_data_entry)
    {
      $series_values[] = $metric_data_entry[1];
    }
    $series_mean = $this->stats->mean($series_values);
    $adjusted_series = array();
    foreach ($series_values as $value)
    {
      $adjusted_series[] = $value - $series_mean;
    }
    $this->stats->setData($adjusted_series);
    $adjust_series_std_dev = $this->stats->stDev();

    return abs($adjusted_series[count($adjusted_series) - 1]) > $adjust_series_std_dev * $this->_std_dev_threshold;
  }

  /**
   * Votes yes if the absolute value of the average of the last three
   * data points minus the moving average is greater than
   * $this->_std_dev_threshold standard deviations of the average.
   * Does not exponentially weight the moving average making it
   * better for detecting anomalies over an entire series
   *
   * @param array $time_series
   * @return bool
   */
  protected function simple_stddev_from_moving_average($time_series)
  {
    $series_values = array();
    foreach ($time_series as $metric_data_entry)
    {
      $series_values[] = $metric_data_entry[1];
    }
    $this->stats->setData($series_values);
    $series_mean = $this->stats->mean();
    $series_std_dev = $this->stats->stDev();
    $series_tail_average = $this->tail_average($series_values);

    return abs($series_tail_average - $series_mean) > $series_std_dev * $this->_std_dev_threshold;
  }

  /**
   * Votes yes if the average of the last three data points on
   * a projected least squares model is greater than
   * $this->_std_dev_threshold standard deviations
   *
   * @param array $time_series
   * @return bool
   */
  protected function least_squares($time_series)
  {
    bcscale(10);
    $x_range = range(1, count($time_series));
    $regression = new PolynomialRegression(2);
    for ($i = 0; $i < count($time_series); $i++)
    {
      $regression->addData($x_range[$i], $time_series[$i][1]);
    }
    $coefficients = $regression->getCoefficients();
    $model_coefficient = round(floatval($coefficients[0]), 4);
    $base_coefficient = round(floatval($coefficients[1]), 4);
    $errors_series = array();
    for ($i = 0; $i < count($time_series); $i++)
    {
      $projected = $model_coefficient * $x_range[$i][1] + $base_coefficient;
      $error_check = $time_series[$i][1] - $projected;
      $errors_series[$i] = $error_check;
    }
    $this->stats->setData($errors_series);
    $error_std_dev = $this->stats->stDev();
    $this->stats->setData(array_slice($errors_series, -3));
    $error_tail_average = $this->stats->mean();
    $vote = false;
    if ((round($error_std_dev) != 0) && (round($error_tail_average) != 0))
    {
      $vote = abs($error_tail_average) > $error_std_dev * $this->_std_dev_threshold;
    }
    return $vote;
  }

  /**
   * Votes yes if the average of the last three data points falls into a
   * histogram bin with less than 20 other data points.
   *
   * @param $time_series
   * @return bool
   */
  protected function histogram_bins($time_series)
  {
    $series_values = array();
    foreach ($time_series as $metric_data_entry)
    {
      $series_values[] = $metric_data_entry[1];
    }
    $series_tail_average = $this->tail_average($series_values);
    $histogram = new Math_Histogram();
    $histogram->setBinOptions(15);
    $histogram->setData($series_values);
    $histogram->calculate();
    $bins = $histogram->getBins();
    $anomalous = false;
    foreach ($bins as $bin)
    {
      if ($bin['count'] <= 20)
      {
        if($series_tail_average > $bin['low'] && $series_tail_average < $bin['high'])
        {
          $anomalous = true;
          break;
        }
      }
    }

    return $anomalous;
  }

}
