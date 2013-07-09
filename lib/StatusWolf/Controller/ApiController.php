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

  private $_session_data;
  private $_app_config;

  public function __construct($url_path)
  {

    $this->_session_data = $_SESSION[SWConfig::read_values('auth.sessionName')];
    $this->_app_config = SWConfig::read_values('statuswolf');
    // Init app logging for the class
    if(SWConfig::read_values('statuswolf.debug'))
    {
      $this->loggy = new KLogger(ROOT . 'app/log/', KLogger::DEBUG);
    }
    else
    {
      $this->loggy = new KLogger(ROOT . 'app/log/', KLogger::INFO);
    }
    $this->log_tag = '(' . $this->_session_data['username'] . '|' . $this->_session_data['sessionip'] . ') ';

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
   * Function to generate an MD5 key for an ad-hoc search and save the
   * key and query data to the database to facilitate sharing of searches.
   * Returns the search key for use in building the URL of the shared search.
   *
   * @throws SWException
   */
  protected function get_shared_search()
  {
    $this->loggy->logDebug($this->log_tag . 'API call, return shared search key');
    $data = $_POST;
    $search_key = md5(json_encode($data));
    $app_config = SWConfig::read_values('statuswolf.session_handler');
    $shared_search_db = new mysqli($app_config['db_host'], $app_config['db_user'], $app_config['db_password'], $app_config['database']);
    if (mysqli_connect_error())
    {
      throw new SWException('Shared search database connection error: ' . mysqli_connect_errno() . ' ' . mysqli_connect_error());
    }
    $save_shared_search = sprintf("REPLACE INTO shared_searches VALUES('%s', '%s', '%s', '%s')", $search_key, $data['datasource'], serialize($data), time());
    $this->loggy->logDebug($this->log_tag . 'Saved search query: ' . $save_shared_search);
    $write_result = $shared_search_db->query($save_shared_search);
    if (mysqli_error($shared_search_db))
    {
      throw new SWException('Shared search save error: ' . mysqli_errno($shared_search_db) . ' ' . mysqli_error($shared_search_db));
    }
    else
    {
      $this->loggy->logDebug($this->log_tag . 'Shared search key: ' . $search_key);
      echo json_encode(array('search_id' => $search_key));
    }

    // while we have a db connection, expire any shared searches more than 24 hours old
    $expiration = time() - DAY;
    $expiry_query = sprintf("DELETE FROM shared_searches where timestamp < '%s'", $expiration);
    $this->loggy->logInfo($this->log_tag . "Expiring shared searches older than " . date('Y/m/d H:i:s', $expiration));
    $this->loggy->logDebug($this->log_tag . "Expiration query: " . $expiry_query);
    if ($shared_search_db->query($expiry_query) === TRUE)
    {
      $expired_rows = $shared_search_db->affected_rows;
      if ($expired_rows > 0)
      {
        $this->loggy->logInfo($this->log_tag . ' ' . $expired_rows . ' ' . "shared searches expired");
      }
      else
      {
        $this->loggy->logInfo($this->log_tag . ' ' . "No expired shared searches found");
      }
    }

    $shared_search_db->close();
  }

  /**
   * Function to save query data for adhoc searches.
   * Saved searches can be private (viewable by creating user only),
   * or public (viewable by all users).
   *
   * @return bool
   * @throws SWException
   */
  protected function save_adhoc_search()
  {
    $this->loggy->logDebug($this->log_tag . 'API call, saving adhoc search');
    $data = $_POST;
    if ($data['save_span'] == 1)
    {
      if (array_key_exists('time_span', $data))
      {
        unset($data['time_span']);
      }
    }
    else
    {
      unset($data['start_time']);
      unset($data['end_time']);
    }
    $app_config = SWConfig::read_values('statuswolf.session_handler');
    $saved_search_db = new mysqli($app_config['db_host'], $app_config['db_user'], $app_config['db_password'], $app_config['database']);
    if (mysqli_connect_error())
    {
      throw new SWException('Shared search database connection error: ' . mysqli_connect_errno() . ' ' . mysqli_connect_error());
    }
    $saved_search_query = sprintf("INSERT INTO saved_searches VALUES('', '%s', '%s', '%s', '%s', '%s')", $data['title'], $data['user_id'], $data['private'], serialize($data), $data['datasource']);
    $save_result = $saved_search_db->query($saved_search_query);
    $search_id = $saved_search_db->insert_id;
    if (mysqli_error($saved_search_db))
    {
      throw new SWException('Error saving search: ' . mysqli_errno($saved_search_db) . ' ' . mysqli_error($saved_search_db));
    }
    else
    {
      $_session_data = $_SESSION[SWConfig::read_values('auth.sessionName')];
      if ($data['private'] < 1)
      {
        if (array_key_exists('user_searches', $_session_data['data']))
        {
          array_push($_session_data['data']['user_searches'], array('id' => $data['id'], 'title' => $data['title']));
        }
        else
        {
          $_session_data['data']['user_searches'] = array();
          array_push($_session_data['data']['user_searches'], array('id' => $data['id'], 'title' => $data['title']));
        }
      }
      else
      {
        $usermap = array();
        $usermap_result = $saved_search_db->query("SELECT * FROM user_map");
        if ($usermap_result->num_rows && $usermap_result->num_rows > 0)
        {
          while ($usermap_entry = $usermap_result->fetch_assoc())
          {
            $usermap[$usermap_entry['id']] = $usermap_entry['username'];
          }
        }
        if (array_key_exists('public_searches', $_session_data['data']))
        {
          array_push($_session_data['data']['public_searches'], array('id' => $data['id'], 'title' => $data['title'], 'username' => $usermap[$data['user_id']]));
        }
        else
        {
          $_session_data['data']['public_searches'] = array();
          array_push($_session_data['data']['public_searches'], array('id' => $data['id'], 'title' => $data['title'], 'username' => $usermap[$data['user_id']]));
        }
      }
      return true;
    }
  }

  /**
   * Function to find all saved searches for a user, and all public searches.
   *
   * @throws SWException
   */
  protected function get_saved_searches()
  {
    $data = $_POST;
    $_saved_searches = array();

    $sw_db = new mysqli($this->_app_config['session_handler']['db_host'], $this->_app_config['session_handler']['db_user'], $this->_app_config['session_handler']['db_password'], $this->_app_config['session_handler']['database']);
    if (mysqli_connect_error())
    {
      throw new SWException('Saved searches database connect error: ' . mysqli_connect_errno() . ' ' . mysqli_connect_error());
    }
    $saved_searches_query = sprintf("SELECT * FROM saved_searches where user_id='%s' AND private=1", $data['user_id']);
    $user_searches_result = $sw_db->query($saved_searches_query);
    if ($user_searches_result->num_rows && $user_searches_result->num_rows > 0)
    {
      $_saved_searches['user_searches'] = array();
      while($user_searches = $user_searches_result->fetch_assoc())
      {
        array_push($_saved_searches['user_searches'], array('id' => $user_searches['id'], 'title' => $user_searches['title'], 'datasource' => $user_searches['data_source']));
      }
    }
    $usermap = array();
    $usermap_result = $sw_db->query("SELECT * FROM user_map");
    if ($usermap_result->num_rows && $usermap_result->num_rows > 0)
    {
      while ($usermap_entry = $usermap_result->fetch_assoc())
      {
        $usermap[$usermap_entry['id']] = $usermap_entry['username'];
      }
    }
    $public_searches_query = sprintf("SELECT * FROM saved_searches WHERE private=0");
    $public_searches_result = $sw_db->query($public_searches_query);
    if ($public_searches_result->num_rows && $public_searches_result->num_rows > 0)
    {
      $_saved_searches['public_searches'] = array();
      while($public_searches = $public_searches_result->fetch_assoc())
      {
        array_push($_saved_searches['public_searches'], array('id' => $public_searches['id'], 'user_id' => $public_searches['user_id'], 'username' => $usermap[$public_searches['user_id']], 'title' => $public_searches['title'], 'datasource' => $public_searches['data_source']));
      }
    }

    echo json_encode($_saved_searches);

  }

}
