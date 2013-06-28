<?php
/**
 * ApiController
 *
 * Controller for backend calls to retrieve data, etc.
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 02 June 2013
 *
 * @package StatusWolf.Controller
 */


class ApiController extends SWController
{

  public function __construct($url_path)
  {

    // Init app logging for the class
    if(SWConfig::read_values('statuswolf.debug'))
    {
      $this->loggy = new KLogger(ROOT . 'app/log/', KLogger::DEBUG);
    }
    else
    {
      $this->loggy = new KLogger(ROOT . 'app/log/', KLogger::INFO);
    }
    $this->log_tag = '(' . $_SESSION['_sw_authsession']['username'] . '|' . $_SESSION['_sw_authsession']['sessionip'] . ') ';

    parent::__construct();

    // Determine the API function to call and pass on any remaining URL bits
    if (!empty($url_path[0]))
    {
      $_api_function = array_shift($url_path);
      $this->$_api_function($url_path);
    }
    else
    {
      throw new SWException('No API function found');
    }
  }

  /**
   * Load the form view for the chosen Ad-Hoc datasource
   *
   * @param string $form - the name of the datasource form to load,
   *                       will have '.php' appended
   * @throws SWException
   */
  protected function datasource_form($form)
  {
    if (!empty($form) && $form[0])
    {
      ob_start();
      include VIEWS . $form[0] . '.php';
      $raw_form = ob_get_contents();
      $form_data = array('form_source' => $raw_form);
      ob_end_clean();
      echo json_encode($form_data);
    }
    else
    {
      throw new SWException('No datasource form found');
    }
  }

  /**
   * Connects to the OpenTSDB suggest API to populate the autocomplete menu
   * for the OpenTSDB Ad-Hoc search form
   *
   * @param string $query_bits - metric to look for in form 'q=string'
   */
  protected function tsdb_metric_list($query_bits) {
    list($q, $query) = explode('=', $query_bits[0]);
    $query_url = 'http://opentsdb.ve.box.net:4242/suggest?type=metrics&q=';
    $curl = new Curl($query_url . $query);
    try
    {
      $ret = json_decode($curl->request());
    }
    catch(SWException $e)
    {
      $this->loggy->logError($this->log_tag . "Failed to retrieve metric suggestion for $query from OpenTSDB");
      $this->loggy->logError($this->log_tag . substr($e->getMessage(), 0, 256));
      return null;
    }
    $data = array();
    $data['query'] = $query;
    if (count($ret) > 20) {
      $ret = array_slice($ret, 0, 20);
    }
    $data['suggestions'] = $ret;
    echo json_encode($data);
  }

  /**
   * Builds an OpenTSDBAnomalyModel object to generate model data for
   * the given metric, used to project what current data should be and
   * to check for anomalies between the projection and current data.
   * Output is the path and filename of the model data cache.
   */
  protected function opentsdb_anomaly_model()
  {
    $query_bits = $_POST;

    $build_start = time();
    $anomaly = new OpenTSDBAnomalyModel();
    $anomaly->generate($query_bits);
    $anomaly_data = $anomaly->get_cache_file();

    $this->loggy->logDebug($this->log_tag . 'Anomaly model build complete, returning cache file location:');
    $this->loggy->logDebug($this->log_tag . $anomaly_data);
    $build_end = time();
    $build_time = $build_end - $build_start;
    $this->loggy->logInfo($this->log_tag . "Total execution time for anomaly build: $build_time seconds");

    echo json_encode($anomaly_data);

  }

  /**
   * Function that takes saved OpenTSDB metric anomaly model data and compares
   * that with current metric data to generate a projection of what the
   * current data should look like and to find any anomalies from that
   * projection. Output is JSON encoded array of the start and end times of
   * the anomaly periods.
   *
   * @return null
   */
  protected function time_series_projection()
  {
    $data = $_POST;

    $projection_start = time();
    $series = $data['key'];

    // Load the cached model data
    if (file_exists($data['model_cache']))
    {
      $this->loggy->logDebug($this->log_tag . 'Loading cached model data');
      $model_data = file_get_contents($data['model_cache']);
      $model_data = unserialize($model_data);
      $data['model'] = $model_data['model'];
      unset($model_data);
    }
    else
    {
      $this->loggy->logCrit($this->log_tag . 'No cached model data found');
      return null;
    }

    // Load the cached current query data
    if (file_exists($data['query_cache']))
    {
      $this->loggy->logDebug($this->log_tag . 'Loading current data');
      $current_data = file_get_contents($data['query_cache']);
      $current_data = unserialize($current_data);
      $data['actual'] = $current_data[$series];
    }
    else
    {
      $this->loggy->logDebug($this->log_tag . 'No query cache data found');
      return null;
    }

    $this->loggy->logDebug($this->log_tag . 'Building projection');

    $anomaly_graph = new TimeSeriesProjection();
    $anomaly_graph->build_series($data['actual'], $data['model']);
    $anomaly_data = array('projection' => $anomaly_graph->read());

    $this->loggy->logDebug($this->log_tag . 'Detecting anomolies in current metric data');

    $anomaly_finder = new DetectTimeSeriesAnomaly();
    $anomaly_data['anomalies'] = array();
    $anomaly_data['anomalies'] = $anomaly_finder->detect_anomaly($anomaly_data['projection'], $anomaly_graph->get_accuracy_margin());
    $projection_end = time();
    $projection_time = $projection_end - $projection_start;
    $this->loggy->logInfo($this->log_tag . "Projection and anomaly detection complete, total execution time: $projection_time seconds");

    echo json_encode($anomaly_data);
  }

  /**
   * Function to pull random quote data from configured source(s) for
   * display during long-running operations (like building anomaly models)
   *
   * @param array $path - Config options are pulled from the URL passed in
   */
  protected function fortune($path)
  {
    $arguments = array();
    if (!empty($path[0]))
    {
      $arguments['source'] = $path[0];
    }
    if (!empty($path[1]))
    {
      $arguments['category'] = $path[1];
    }

    $fortune = new Fortune();
    $my_fortune = $fortune->get_fortune($arguments);
    echo json_encode($my_fortune);

  }

}
