<?php
/**
 * OpenTSDBAnomalyModel
 *
 * AKA QuAD - Queisser Anomaly Detection
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

//  private $_model_start = 1362985200;
  private $_model_start = 1364799600;

  private $_model_weeks = 6;

  public $reference_model = array();

  public function generate(array $query_bits)
  {

    if (isset($query_bits['start_time']))
    {
      $this->_model_start = $query_bits['start_time'];
      unset($query_bits['start_time']);
    }

    if (isset($query_bits['weeks']))
    {
      $this->_model_weeks = $query_bits['weeks'];
      unset($query_bits['weeks']);
    }

    if (date('N H:i:s', $this->_model_start) != '1 00:00:00')
    {
      throw new SWException('FATAL - model period does not begin on a Monday at 00:00:00');
    }

    $training_data = new OpenTSDB();
    $all_weeks = array();
    $week_heads = array();

    for ($i = $this->_model_start, $j = 0; $j < $this->_model_weeks; $i += WEEK, $j++)
    {
      $query_bits['start_time'] = $i;
      $query_bits['end_time'] = $i + WEEK - 1;
      $training_data->get_raw_data($query_bits);
      $week_heads[$j] = strtolower(date('M_j', $i));
      $all_weeks[$j] = $training_data->read();
      $training_data->flush_data();
    }
    for ($w = 0; $w < $this->_model_weeks; $w++)
    {
      foreach($all_weeks[$w] as $week_data)
      {
        unset($week_data['query_url']);
        unset($week_data['start_time']);
        unset($week_data['end_time']);

        if (!isset($series))
        {
          $series = key($week_data);
        }
        $all_weeks[$w] = $week_data[$series];
      }
    }

    for ($i = 0; $i < (WEEK / 60); $i++)
    {
      for ($j = 0; $j < $this->_model_weeks; $j++)
      {
        $this->reference_model[$week_heads][$j][$i] = $all_weeks[$j][$i]['value'];
      }
    }

    $this->reference_model['model'] = $this->_calculate_reference($all_weeks);

    $cache_file = CACHE . 'anomaly_model' . DS . md5($series) . '.model';
    file_put_contents($cache_file, serialize($this->reference_model));

  }

  private function _calculate_reference(array $all_weeks)
  {

    $model = array();

    for ($i = 0; $i < (WEEK / 60); $i++)
    {
      $minute_line = array();
      for ($j = 0; $j < $this->_model_weeks; $j++)
      {
        $minute_line[] = $all_weeks[$j][$i]['value'];
      }
      $points = $this->_get_points($minute_line);
      $points = $this->_moving_average($points, 50);
      $model[$i] = $points;
    }

    return $model;

  }

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

}
