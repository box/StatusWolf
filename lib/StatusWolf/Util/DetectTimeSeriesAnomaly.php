<?php
/**
 * DetectTimeSeriesAnomaly
 *
 * Very much based on the anomaly detection system in Kale,
 * (http://codeascraft.com/2013/06/11/introducing-kale/)
 * but implemented in PHP (obviously) over stored metrics rather
 * than a live stream.
 *
 * Pulls a configurable period of metric data from the time immediately
 * before the graph period (default: 86400 seconds/1 day) and runs each
 * metric point for the graph period through five different anomaly
 * detection checks. Each check returns a yes or no vote on whether
 * or not the point is anomalous with the preceding data. If the number
 * of checks voting yes is equal to or greater than the consensus
 * threshold, the point is flagged as anomalous. Default is to flag if
 * three of the five checks vote yes. These levels can be changed in
 * the statuswolf.conf file.
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 15 July 2013
 *
 * @package StatusWolf.Util
 */

require_once "Math/Stats.php";
require_once "Math/Histogram.php";

class DetectTimeSeriesAnomaly {

  // Values for these variables are pulled from the config file

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
  private $_anomaly_algorithms = [];

  /**
   * The number of checks which must vote yes in order for a
   * data point to be flagged as anomalous. Config default
   * is 4
   *
   * @var int
   */
  private $_consensus_threshold = 4;

  /**
   * Several of the checks check the data point against the
   * standard deviation of the series - this configures how
   * many standard deviations away the point must be in order
   * to get a yes vote. Config default is 3.
   *
   * @var int
   */
  private $_std_dev_threshold = 3;

  public function __construct() {

    // Init logging for the class
    if(SWConfig::read_values('statuswolf.debug'))
    {
      $this->loggy = new KLogger(ROOT . 'app/log/', KLogger::DEBUG);
    }
    else
    {
      $this->loggy = new KLogger(ROOT . 'app/log/', KLogger::INFO);
    }
    $this->log_tag = '(' . $_SESSION['_sw_authsession']['username'] . '|' . $_SESSION['_sw_authsession']['sessionip'] . ') ';

    $this->anomaly_config = SWConfig::read_values('statuswolf.anomalies');

    if (array_key_exists('pre_anomaly_period', $this->anomaly_config))
    {
        $this->_pre_anomaly_period = $this->anomaly_config['pre_anomaly_period'];
    }

    if (array_key_exists('algorithms', $this->anomaly_config))
    {
      $this->_anomaly_algorithms = $this->anomaly_config['algorithms'];
    }
    else
    {
      throw new SWException('No anomaly algorithms configured');
    }

    if (array_key_exists('anomaly_consensus', $this->anomaly_config))
    {
      $this->_consensus_threshold = $this->anomaly_config['anomaly_consensus'];
    }

    if (array_key_exists('std_dev_threshold', $this->anomaly_config))
    {
      $this->_std_dev_threshold = $this->anomaly_config['std_dev_threshold'];
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
    set_time_limit(300);
  }
  /**
   * DetectTimeSeriesAnomaly::detect_anomaly()
   *
   * Takes the current series of graph data and pairs it with the
   * data from the pre anomaly period to determine whether a point
   * is anomalous.
   *
   * @param array $query_bits - [metric_name, data_cache, pre-period_data_cache]
   * @return array
   */
  public function detect_anomaly($query_bits)
  {

    $this->loggy->logDebug($this->log_tag . "Anomaly detection initiated");
    $this->loggy->logDebug($this->log_tag . "Consensus threshold: " . $this->_consensus_threshold);
    $this->loggy->logDebug($this->log_tag . "Std Dev threshold: " . $this->_std_dev_threshold);
    $anomaly_detect_start = time();
    $rolling_metric_data = [];

    $metric_name = $query_bits['metric'];
    $current_cache = $query_bits['cache'];
    $pre_period_cache = $query_bits['pre_cache'];

    $current_cache_data = file_get_contents($current_cache);
    $current_cache_data = unserialize($current_cache_data);
    $pre_period_cache_data = file_get_contents($pre_period_cache);
    $pre_period_cache_data = unserialize($pre_period_cache_data);

    $keys = array_keys($current_cache_data);
    foreach ($keys as $key)
    {
      if (preg_match("/^$metric_name/", $key))
      {
        $metric_key = $key;
        break;
      }
    }

    $graph_data = [];
    foreach ($current_cache_data[$metric_key] as $series_entry)
    {
      if (!empty($series_entry['value']))
      {
        $graph_data[] = $series_entry;
      }
    }
    $pre_anomaly_period_data = [];
    foreach ($pre_period_cache_data[$metric_key] as $pre_series_entry)
    {
      if (!empty($pre_series_entry['value']))
      {
        $pre_anomaly_period_data[] = $pre_series_entry;
      }
    }
    unset($current_cache_data);
    unset($pre_period_cache_data);

    foreach ($pre_anomaly_period_data as $pre_period_values)
    {
      $rolling_metric_data[] = array($pre_period_values['timestamp'], $pre_period_values['value']);
    }

    $violations = [];
    $in_violation = false;
    $start_violation = null;
    $consensus_votes = [];
    foreach ($graph_data as $current_values)
    {
      $anomaly_consensus = [];
      array_shift($rolling_metric_data);
      array_push($rolling_metric_data, array($current_values['timestamp'], $current_values['value']));
      foreach ($this->_anomaly_algorithms as $anomaly_algorithm)
      {
        $vote = $this->$anomaly_algorithm($rolling_metric_data);
        $consensus_votes[$anomaly_algorithm] += $vote;
        $anomaly_consensus[] = $vote;
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

    $anomaly_detect_end = time();
    $detection_time = $anomaly_detect_end - $anomaly_detect_start;
    $this->loggy->logDebug($this->log_tag . 'Voting results:');
    foreach ($this->_anomaly_algorithms as $voter)
    {
      if ($consensus_votes[$voter] > 0)
      {
        $this->loggy->logDebug($this->log_tag . $voter . " voted Yes " . $consensus_votes[$voter] . " times");
      }
      else
      {
        $this->loggy->logDebug($this->log_tag . $voter . " voted No " . $consensus_votes[$voter] . " times");
      }
    }
    $this->loggy->logInfo("Anomaly detection completed in " . $detection_time . " seconds");

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
    $current_timestamp = time();
    $first_hour_offset = $this->_pre_anomaly_period - 3600;
    $first_hour_threshold = $current_timestamp - $first_hour_offset;
    $series_in_range = true;
    $first_hour_series = [];
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
    $series_values = [];
    foreach ($time_series as $metric_data_entry)
    {
      $series_values[] = $metric_data_entry[1];
    }
    $series_mean = $this->stats->mean($series_values);
    $adjusted_series = [];
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
    $series_values = [];
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
    $errors_series = [];
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
    $series_values = [];
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
