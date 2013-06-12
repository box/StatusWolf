<?php
/**
 * TimeSeriesProjection
 *
 * A component of QuAD - Queisser Anomaly Detection
 *
 * Authors: Jeff Queisser <jeff@box.com>, Mark Troyer <disco@box.com>
 * Date Created: 11 June 2013
 *
 * @package StatusWolf.Util
 */
class TimeSeriesProjection {

  private $_model_point_offset = 0;

  private $_accuracy_margin;

  private $_anomaly_data;

  public function build_series(array $actual, array $model, $accuracy_margin = 0.15)
  {

    $this->_accuracy_margin = (float) $accuracy_margin;

    if(empty($actual) || empty($model))
    {
      throw new SWException ("Projection build failed, not all data provided");
    }

    $start_time = $actual[0]['timestamp'];
    $this->_model_point_offset = $this->_get_model_point($start_time);

    $loggy = fopen('/tmp/sw_log.txt', "a");
    fwrite($loggy, "Generating coefficients for projection\n");
    bcscale(10);
    $coefficients = $this->_regression($actual, $model);
    fwrite ($loggy, "Coefficents are: " . json_encode($coefficients) . "\n");
    $base_coefficient = round(floatval($coefficients[0]), 4);
    $model_coefficient = round(floatval($coefficients[1]), 4);
    fwrite($loggy, "Base: " . $base_coefficient . ", Model: ". $model_coefficient . "\n");
    fclose($loggy);

    $projected = $this->_projection(count($actual), $model, $base_coefficient, $model_coefficient);

    for ($i = 0; $i < count($actual); $i++)
    {
      $low_value = (float) $projected[$i] * (1 - $this->_accuracy_margin);
      $high_value = (float) $projected[$i] * (1 + $this->_accuracy_margin);
      $this->_anomaly_data[$i] = array('timestamp' => $actual[$i]['timestamp'], 'value' => array(array($low_value, $projected[$i], $high_value), array(null, (float) $actual[$i]['value'], null)));
    }
  }

  private function _regression($actual, $model)
  {

    $regression = new PolynomialRegression(2);

    $loggy = fopen('/tmp/sw_log.txt', "a");
    for ($i = 0; $i < count($actual); $i++)
    {
      $model_point = $i + $this->_model_point_offset;
      if ($model_point >= 10080)
      {
        $model_point -= 10080;
      }
      fwrite($loggy, "model point: " . $model[$model_point] . ", actual point: " . $actual[$i]['value'] . "\n");
      $regression->addData($model[$model_point], $actual[$i]['value']);
    }
    fclose($loggy);

    return $regression->getCoefficients();
  }

  private function _projection($entries, $model, $base_coefficient, $model_coefficient)
  {

    $projected = array();

    for ($i = 0; $i < $entries; $i++)
    {
      $model_point = $i + $this->_model_point_offset;
      if ($model_point >= 10080)
      {
        $model_point -= 10080;
      }
      $projected[$i] = ($model_coefficient * $model[$model_point]) + $base_coefficient;
    }

    return $projected;
  }

  private function _get_model_point($start_time)
  {
    $start_dow = date('w', $start_time);
    if ($start_dow == 1)
    {
      $start_of_series_week = strtotime('midnight today', $start_time);
    }
    else
    {
      $start_of_series_week = strtotime('last Monday', $start_time);
    }

    $minute_of_week = (($start_time - $start_of_series_week) / 60);
    $minute_of_week = $minute_of_week % (WEEK / 60);

    return $minute_of_week;
  }

  public function set_accuracy_margin($band)
  {
    if ($band)
    {
      $this->_accuracy_margin = (float) $band;
    }
  }

  public function get_accuracy_margin()
  {
    return $this->_accuracy_margin;
  }

  public function read()
  {
    if (!empty($this->_anomaly_data))
    {
      return $this->_anomaly_data;
    }
    else
    {
      return null;
    }
  }

}
