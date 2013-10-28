<?php
/**
 * DetectTimeSeriesAnomaly
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 15 July 2013
 *
 * @package StatusWolf.Util
 */

class DetectTimeSeriesAnomaly {

  private $_anomaly_config = array();

  private $_detection_lib = '';

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

    $this->_anomaly_config = SWConfig::read_values('statuswolf.anomalies');
    if (!array_key_exists('options', $this->_anomaly_config))
    {
      $this->_anomaly_config['options'] = array();
    }

    if (array_key_exists('detection_lib', $this->_anomaly_config))
    {
      $this->_detection_lib = $this->_anomaly_config['detection_lib'];
    }
    else
    {
      throw new SWException('No anomaly detection lib configured');
    }

    ini_set('max_input_vars', 1440);

  }
  /**
   * DetectTimeSeriesAnomaly::detect_anomaly()
   *
   * Takes the current series of graph data and pairs it with the
   * data from the pre anomaly period to determine whether a point
   * is anomalous.
   *
   * @param array $query_bits
   * @return array
   */
  public function detect_anomaly($query_bits)
  {

    $anomaly_data = array();

    $this->loggy->logDebug($this->log_tag . json_encode($query_bits['query_data']));
    if ($this->_detection_lib === "Arugula")
    {
      if (!array_key_exists('pre_period', $this->_anomaly_config))
      {
        $this->_anomaly_config['pre_period'] = 86400;
      }

      $series = key($query_bits['data']);
      $query_bits['query_data']['end_time'] = ($query_bits['data'][$series][0]['timestamp'] - 1) - WEEK;
      $query_bits['query_data']['start_time'] = $query_bits['query_data']['end_time'] - $this->_anomaly_config['pre_period'];
      $this->loggy->logDebug($this->log_tag . "Setting pre period start: " . $query_bits['query_data']['start_time'] . ', end: ' . $query_bits['query_data']['end_time']);
      $this->loggy->logDebug('Initiating query for pre-period data');
      $_search_object = new $query_bits['data_source'];
      $_search_object->get_raw_data($query_bits['query_data']);
      $pre_period_data = $_search_object->read();
      $this->loggy->logDebug($this->log_tag . "Pre-period data fetched, creating anomaly detection object");
      $_anomalies = new TimeSeriesAnomalies($this->_anomaly_config['options']);
      $this->loggy->logDebug($this->log_tag . "Anomaly detection initiated");
      $pre_period_key = key($pre_period_data);
      $this->loggy->logDebug($this->log_tag . "Series for pre-period data: " . $pre_period_key);
      $anomaly_data = $_anomalies->detect_anomaly(array('current_data' => $query_bits['data'][$series], 'pre-period_data' => $pre_period_data[$pre_period_key]));
      $this->loggy->logDebug($this->log_tag . "Anomaly detection complete");
    }

    return $anomaly_data;

  }

}
