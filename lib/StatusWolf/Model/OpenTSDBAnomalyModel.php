<?php
/**
 * OpenTSDBAnomalyModel
 *
 * A component of QuAD - Queisser Anomaly Detection
 *
 * Pulls time series data from OpenTSDB and returns a data model
 * that can be used for anomaly detection. Data is stored in an
 * array of the form "Month_day => array(minute data)" where each
 * "Month_day array represents the Monday of the week and the
 * minute data is an entry for each minute of each day for that
 * week (10080 entries total for each week). It also includes
 * a final array of "model => array(minute data)" for the point
 * data that has been run through the generate model process.
 *
 * Authors: Jeff Queisser <jeff@box.com>, Mark Troyer <disco@box.com>
 * Date Created: 10 June 2013
 *
 * @package StatusWolf.Model
 */

class OpenTSDBAnomalyModel {

  /**
   * Number of weeks worth of data to collect in order to build
   * the anomaly model, default is 6
   *
   * @var int
   */
  private $_model_weeks = 6;

  /**
   * The name of the cache file for model data
   *
   * @var string
   */
  private $_model_cache;

  /**
   * Container for the anomaly model data
   *
   * @var array
   */
  public $reference_model = array();

  public function __construct()
  {
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
  }

  /**
   * OpenTSDBAnomalyModel::generate()
   *
   * Checks for the existence of a cached anomaly model and returns it
   * if found, otherwise starts two weeks in the past and then moves
   * backward, week-by-week, until it collects either six weeks worth
   * of data or hits 10 weeks where it wasn't able to get good data. It then
   * determines if there were at least four weeks of good data gathered, which
   * are then used to build the anomaly model.
   *
   * @param array $query_bits
   * @throws SWException
   */
  public function generate(array $query_bits)
  {

    if (empty($query_bits))
    {
      throw new SWException('No query data found');
    }

    // Build the query key string in the form of
    // agg:downsample:rate:nointerpolation:metric_name{tags}
    if (array_key_exists('metrics', $query_bits))
    {
      $qkey = '';
      $metric = $query_bits['metrics'][0];

      if (array_key_exists('agg_type', $metric))
      {
        $qkey .= $metric['agg_type'] . ':';
      }

      if (array_key_exists('ds_interval', $metric))
      {
        $qkey .= $metric['ds_interval'] . 'm-';
      }

      if (array_key_exists('ds_type', $metric))
      {
        $qkey .= $metric['ds_type'] . ':';
      }

      if (array_key_exists('rate', $metric) && $metric['rate'])
      {
        $qkey .= 'rate:';
      }

      if (!array_key_exists('lerp', $metric) || (!$metric['lerp']))
      {
        $qkey .= 'nointerpolation:';
      }

      $qkey .= $metric['name'];

      if (array_key_exists('tags', $metric) && is_array($metric['tags']))
      {
        $qkey .= '{';
        foreach ($metric['tags'] as $tag)
        {
          $qkey .= $tag . ',';
        }
        $qkey = rtrim($qkey, ',');
        $qkey .= '}';
      }

    }

    $this->_model_cache = CACHE . 'anomaly_model' . DS . md5($qkey) . '.model';
    if (file_exists($this->_model_cache))
    {
      $this->loggy->logDebug($this->log_tag . "Cached model data found, loading");
      $anomaly_data = file_get_contents($this->_model_cache);
      $anomaly_data = unserialize($anomaly_data);
      $this->reference_model = $anomaly_data;
    }
    else
    {
      $training_data = new OpenTSDB;
      $all_weeks = array();
      $week_heads = array();
      $start_date = new DateTime();

      // Find midnight on Monday from the previous week
      while($start_date->format('D') != 'Mon')
      {
        $start_date->modify('-1 day');
      }
      $start_date->modify('-1 week');
      $start_date->setTime(0, 0, 0);
      if ($start_date->format('N H:i:s') != '1 00:00:00')
      {
        throw new SWException('Missed it by that much: ' . $start_date->format('Y/m/d H:i:s'));
      }
      $this->loggy->logDebug($this->log_tag . "Starting build of anomaly model for $qkey");

      // Track the number of weeks of model data found, and also
      // the number of weeks where it was not possible to build model
      // data - this prevents an infinite series of queries moving back
      // into the past in the case of metric data that doesn't have six
      // weeks of good data in OpenTSDB
      $weeks_modelled = 0;
      $bad_weeks = 0;
      while($weeks_modelled < $this->_model_weeks)
      {
        if($bad_weeks >= 10)
        {
          $this->loggy->logDebug($this->log_tag . "Too many bad weeks encountered, bailing out");
          $weeks_modelled = 6;
        }
        else
        {
          $query_bits['start_time'] = $start_date->format('U');
          $query_bits['end_time'] = $query_bits['start_time'] + WEEK;
          $this->loggy->logDebug($this->log_tag . "Calling opentsdb model to fetch data for week beginning " . $start_date->format('Y/m/d-H:i:s'));
          $training_data->get_raw_data($query_bits);
          $start_date->modify('-1 week');
          if($week_data = $training_data->read())
          {
            $training_data->flush_data();
            unset($week_data['query_url']);
            unset($week_data['start']);
            unset($week_data['end']);
            if ($series = key($week_data))
            {
              if (count($week_data[$series]) < 1000)
              {
                $this->loggy->logDebug($this->log_tag . "Sparse data found (count($week_data[$series]) records), skipping week");
                $training_data->flush_data();
                $bad_weeks++;
                continue;
              }
              else
              {
                $week_heads[$weeks_modelled] = strtolower($start_date->format('M_j'));
                $all_weeks[$weeks_modelled] = $week_data[$series];
                $weeks_modelled++;
                $this->loggy->logDebug($this->log_tag . "Weeks currently collected for modelling: $weeks_modelled");
              }
            }
            else
            {
              $this->loggy->logDebug($this->log_tag . "No data collected, skipping week");
              $training_data->flush_data();
              $bad_weeks++;
              continue;
            }
          }
          else
          {
            $training_data->flush_data();
            $bad_weeks++;
            continue;
          }
        }
      }

      // If there are four weeks of good data build the model
      if (count($all_weeks) >= 4)
      {
        $this->loggy->logDebug($this->log_tag . "Calculating reference model");
        $this->reference_model['key'] = $qkey;
        $this->reference_model['model'] = $this->_calculate_reference($all_weeks);
        $this->loggy->logDebug($this->log_tag . "Saving reference model to cache file $this->_model_cache");
        file_put_contents($this->_model_cache, serialize($this->reference_model));
      }
      else
      {
        $this->loggy->logDebug($this->log_tag . "Not enough data was gathered, can't build anomaly model");
        $this->_model_cache = array("error", array("0", "Unable to build anomaly model for this metric, not enough past data in OpenTSDB"));
        $this->reference_model = null;
      }
    }
  }

  /**
   * OpenTSDBAnomalyModel::_calculate_reference()
   *
   * Uses the data gathered for all weeks and generates a reference model
   * for each minute of the week (1 week == 10080 minutes)
   *
   * @param array $all_weeks
   * @return array
   */
  private function _calculate_reference(array $all_weeks)
  {

    $model = array();

    for ($i = 0; $i < (WEEK / 60); $i++)
    {
      $minute_line = array();
      for ($j = 0; $j < count($all_weeks); $j++)
      {
        if (array_key_exists($i, $all_weeks[$j]))
        {
          $minute_line[] = $all_weeks[$j][$i]['value'];
        }
      }
      if (count($minute_line) > 0)
      {
        $points = $this->_get_points($minute_line);
        $points = $this->_moving_average($points, 50);
        $model[$i] = $points;
      }
      else
      {
        $model[$i] = null;
      }
    }

    return $model;

  }

  /**
   * OpenTSDBAnomalyModel::_get_points()
   *
   * Takes the data gathered in per-minute increments and generates the
   * reference point for that minute
   *
   * @param $minute_line - metric data for a particular minute from each
   *                       week of gathered data
   * @return float|int
   */
  private function _get_points($minute_line)
  {

    $z_score_threshold = 1;
    $standard_deviation = $this->_standard_deviation($minute_line);

    $average = array_sum($minute_line) / count($minute_line);
    $final_datapoints = array();

    foreach ($minute_line as $point)
    {
      $absolute_z_score = abs(($point / $average) / $standard_deviation);
      if ($absolute_z_score <= $z_score_threshold)
      {
        $final_datapoints[] = $point;
      }
      else
      {
        continue;
      }
    }

    if (empty($final_datapoints))
    {
      return -1;
    }
    else
    {
      return array_sum($final_datapoints) / count($final_datapoints);
    }

  }

  /**
   * OpenTSDBAnomalyModel::_standard_deviation()
   *
   * Returns the standard deviation for the set of data points of a
   * particular minute
   *
   * @param $set
   * @return number
   */
  private function _standard_deviation($set)
  {

    $difference = array();
    $amount = count($set);
    $mean = array_sum($set) / $amount;

    foreach ($set as $value)
    {
      $difference[] = pow($value - $mean, 2);
    }

    return pow(array_sum($difference) / $amount, 0.5);
  }

  /**
   * OpenTSDBAnomalyModel::_moving_average()
   *
   * Find the moving average for a model data point
   *
   * @param $point
   * @param $window_size
   * @return float
   */
  private function _moving_average($point, $window_size)
  {

    static $previous_values = array();

    if (count($previous_values) >= $window_size)
    {
      array_shift($previous_values);
    }

    $previous_values[] = $point;

    return array_sum($previous_values) / count($previous_values);

  }

  /**
   * OpenTSDBAnomalyModel::read()
   *
   * Returns the full generated model data
   *
   * @return array|null
   */
  public function read()
  {

    if (!empty($this->reference_model))
    {
      return $this->reference_model;
    }
    else
    {
      return null;
    }

  }

  /**
   * OpenTSDBAnomalyModel::get_cache_file()
   *
   * Returns the name of the cache file where the model data is saved
   *
   * @return null|string
   */
  public function get_cache_file()
  {
    if (!empty($this->_model_cache))
    {
      return($this->_model_cache);
    }
    else
    {
      return null;
    }
  }

}
