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

  /**
   * Offset of the current metric point from 0, which is 00:00:00 on Monday
   *
   * @var int
   */
  private $_model_point_offset = 0;

  /**
   * The accuracy margin for the projection
   *
   * @var float
   */
  private $_accuracy_margin;

  /**
   * The generated projection data
   *
   * @var array
   */
  private $_projection_data;

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
   * TimeSeriesProjection::build_series()
   *
   * Uses the current metric data, the model data for the metric and
   * an accuracy margin to build a projection curve for the metric
   *
   * @param array $actual
   * @param array $model
   * @param float $accuracy_margin
   * @throws SWException
   */
  public function build_series(array $actual, array $model, $accuracy_margin = 0.15)
  {

    $this->_accuracy_margin = (float) $accuracy_margin;

    if(empty($actual) || empty($model))
    {
      throw new SWException ("Projection build failed, not all data provided");
    }

    $start_time = $actual[0]['timestamp'];
    $this->_model_point_offset = $this->_get_model_point($start_time);

    $this->loggy->logDebug($this->log_tag . "Generating coefficients for projection");
    bcscale(10);
    $coefficients = $this->_regression($actual, $model);
    $base_coefficient = round(floatval($coefficients[0]), 4);
    $model_coefficient = round(floatval($coefficients[1]), 4);

    $projected = $this->_projection(count($actual), $model, $base_coefficient, $model_coefficient);

    for ($i = 0; $i < count($actual); $i++)
    {
      $low_value = (float) $projected[$i] * (1 - $this->_accuracy_margin);
      $high_value = (float) $projected[$i] * (1 + $this->_accuracy_margin);
      $this->_projection_data[$i] = array('timestamp' => $actual[$i]['timestamp'], 'value' => array(array($low_value, $projected[$i], $high_value), array(null, (float) $actual[$i]['value'], null)));
    }
  }

  /**
   * Determine the regression for each point of the current metric data
   *
   * @param array $actual
   * @param array $model
   * @return array
   */
  private function _regression($actual, $model)
  {

    $regression = new PolynomialRegression(2);

    for ($i = 0; $i < count($actual); $i++)
    {
      $model_point = $i + $this->_model_point_offset;
      if ($model_point >= 10080)
      {
        $model_point -= 10080;
      }
      $regression->addData($model[$model_point], $actual[$i]['value']);
    }

    return $regression->getCoefficients();
  }

  /**
   * Find the actual projected data for the series
   *
   * @param array $entries
   * @param array $model
   * @param float $base_coefficient
   * @param float $model_coefficient
   * @return array
   */
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

  /**
   * Find the minute of the week that corresponds to the start time of the
   * current metric data
   *
   * @param int $start_time
   * @return int
   */
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

  /**
   * Set the accuracy margin for the projection
   *
   * @param float $band
   */
  public function set_accuracy_margin($band)
  {
    if ($band)
    {
      $this->_accuracy_margin = (float) $band;
    }
  }

  /**
   * Return the currently set accuracy margin
   * @return float
   */
  public function get_accuracy_margin()
  {
    return $this->_accuracy_margin;
  }

  /**
   * Return the projection data
   *
   * @return array|null
   */
  public function read()
  {
    if (!empty($this->_projection_data))
    {
      return $this->_projection_data;
    }
    else
    {
      return null;
    }
  }

}
