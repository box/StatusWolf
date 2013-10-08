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

    if ($host_config = SWConfig::read_values('datasource.OpenTSDB.url'))
    {
      if (is_array($host_config))
      {
        $tsdb_host = $host_config[array_rand($host_config)];
      }
      else
      {
        $tsdb_host = $host_config;
      }
    }
    else
    {
      throw new SWException('No OpenTSDB Host configured');
    }

    $query_url = 'http://' . $tsdb_host . '/suggest?type=metrics&q=';
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
   * Hands off time series data to the anomaly detection system, returns
   * an array of start/stop times where there were anomalies detected
   * in the series
   */
  protected function detect_timeseries_anomalies()
  {
    $query_bits = $_POST;
    $anomaly = new DetectTimeSeriesAnomaly();
    $anomaly_data = $anomaly->detect_anomaly($query_bits);
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
    $datasource = mysqli_real_escape_string($shared_search_db, $data['datasource']);
    $search_data = mysqli_real_escape_string($shared_search_db, serialize($data));
    $save_shared_search = sprintf("REPLACE INTO shared_searches VALUES('%s', '%s', '%s', '%s')", $search_key, $datasource, $search_data, time());
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
  protected function save_adhoc_search($url_path)
  {
    if (!empty($url_path))
    {
      $search_id = array_shift($url_path);
      if (!is_numeric($search_id))
      {
        unset($search_id);
      }
      if ($confirm = array_shift($url_path))
      {
        if($confirm === "Confirm")
        {
          $confirm_save = true;
        }
        else
        {
          $confirm_save = false;
        }
      }
    }

    $this->loggy->logDebug($this->log_tag . 'API call, saving adhoc search');
    $search_parameters = $_POST;
    $this->loggy->logDebug(json_encode($search_parameters));
    if ($search_parameters['save_span'] == 1)
    {
      if (array_key_exists('time_span', $search_parameters))
      {
        unset($search_parameters['time_span']);
      }
    }
    else
    {
      unset($search_parameters['start_time']);
      unset($search_parameters['end_time']);
    }
    $app_config = SWConfig::read_values('statuswolf.session_handler');
    $saved_search_db = new mysqli($app_config['db_host'], $app_config['db_user'], $app_config['db_password'], $app_config['database']);
    if (mysqli_connect_error())
    {
      throw new SWException('Shared search database connection error: ' . mysqli_connect_errno() . ' ' . mysqli_connect_error());
    }
    $sanitized_title = mysqli_real_escape_string($saved_search_db, $search_parameters['title']);
    $sanitized_datasource = mysqli_real_escape_string($saved_search_db, $search_parameters['datasource']);
    $sanitized_user_id = mysqli_real_escape_string($saved_search_db, $search_parameters['user_id']);
    $sanitized_private = mysqli_real_escape_string($saved_search_db, $search_parameters['private']);
    $search_data = mysqli_real_escape_string($saved_search_db, serialize($search_parameters));
    if (!$confirm_save)
    {
      $this->loggy->logDebug($this->log_tag . "Checking search title against saved searches");
      $check_search_title = sprintf("SELECT id, title FROM saved_searches WHERE title='%s' AND user_id='%s'", $sanitized_title, $sanitized_user_id);
      if ($check_title_result = $saved_search_db->query($check_search_title))
      {
        if ($check_title_result->num_rows && $check_title_result->num_rows > 0)
        {
          $raw_query_data = $check_title_result->fetch_assoc();
          echo json_encode(array("query_result" => "Error", "query_info" => "Title", "search_id" => $raw_query_data['id']));
          $saved_search_db->close();
          return;
        }
      }
    }

    if (!empty($search_id))
    {
      $saved_search_query = sprintf("UPDATE saved_searches SET title='%s', private='%s', search_params='%s' WHERE id='%s'", $sanitized_title, $sanitized_private, $search_data, $search_id);
    }
    else
    {
      $saved_search_query = sprintf("INSERT INTO saved_searches VALUES('', '%s', '%s', '%s', '%s', '%s')", $sanitized_title, $sanitized_user_id, $sanitized_private, $search_data, $sanitized_datasource);
    }
    $save_result = $saved_search_db->query($saved_search_query);
    $search_id = $saved_search_db->insert_id;
    if (mysqli_error($saved_search_db))
    {
      throw new SWException('Error saving search: ' . mysqli_errno($saved_search_db) . ' ' . mysqli_error($saved_search_db));
    }
    else
    {
      $_session_data = $_SESSION[SWConfig::read_values('auth.sessionName')];
      if ($search_parameters['private'] > 0)
      {
        if (array_key_exists('user_searches', $_session_data['data']))
        {
          array_push($_session_data['data']['user_searches'], array('id' => $search_id, 'title' => $search_parameters['title']));
        }
        else
        {
          $_session_data['data']['user_searches'] = array();
          array_push($_session_data['data']['user_searches'], array('id' => $search_id, 'title' => $search_parameters['title']));
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
          array_push($_session_data['data']['public_searches'], array('id' => $search_id, 'title' => $search_parameters['title'], 'username' => $usermap[$search_parameters['user_id']]));
        }
        else
        {
          $_session_data['data']['public_searches'] = array();
          array_push($_session_data['data']['public_searches'], array('id' => $search_id, 'title' => $search_parameters['title'], 'username' => $usermap[$search_parameters['user_id']]));
        }
      }
      $this->loggy->logDebug($this->log_tag . json_encode($_session_data));
      echo json_encode($_session_data);
    }
  }

  protected function delete_saved_searches()
  {
    $data = $_POST;
    $search_ids = array();

    $sw_db = new mysqli($this->_app_config['session_handler']['db_host'], $this->_app_config['session_handler']['db_user'], $this->_app_config['session_handler']['db_password'], $this->_app_config['session_handler']['database']);
    if (mysqli_connect_error())
    {
      throw new SWException('Saved searches database connect error: ' . mysqli_connect_errno() . ' ' . mysqli_connect_error());
    }
    foreach ($data as $search_title => $search_id)
    {
      array_push($search_ids, mysqli_real_escape_string($sw_db, $search_id));
    }
    $this->loggy->logDebug($this->log_tag . "API call, deleting saved searches " . implode("','", $search_ids));
    $search_ids_string = implode("','", $search_ids);
    $delete_searches_query = sprintf("DELETE FROM saved_searches WHERE id in ('%s')", $search_ids_string);
    $this->loggy->logDebug($this->log_tag . $delete_searches_query);
    $sw_db->query($delete_searches_query);
    if (mysqli_error($sw_db))
    {
      throw new SWException('Session open error: ' . mysqli_errno($sw_db) . ' ' . mysqli_error($sw_db));
    }
    else
    {
      echo json_encode($search_ids);
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
    $search_user = mysqli_real_escape_string($sw_db, $data['user_id']);
    $saved_searches_query = sprintf("SELECT * FROM saved_searches where user_id='%s' AND private=1", $search_user);
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

  function search($url_path)
  {
    if ($_adhoc_datasource = array_shift($url_path))
    {
      $_search_object = new $_adhoc_datasource();
      print_r($_POST);
      $_search_object->get_raw_data($_POST);
      $raw_data = $_search_object->read();
      echo json_encode($raw_data);
    }
    else
    {
      throw new SWException ('No datasource specified for Ad-Hoc search');
    }
  }

  function load_saved_search($query_bits)
  {

    $db_conf = $this->_app_config['session_handler'];

    $sw_db = new mysqli($db_conf['db_host'], $db_conf['db_user'], $db_conf['db_password'], $db_conf['database']);
    if (mysqli_connect_error())
    {
      throw new SWException('Unable to connect to shared search database: ' . mysqli_connect_errno() . ' ' . mysqli_connect_error());
    }
    $search_id = mysqli_real_escape_string($sw_db, array_shift($query_bits));
    $this->loggy->logDebug($this->log_tag . "Loading saved search id #" . $search_id);
    $saved_search_query = sprintf("SELECT * FROM saved_searches WHERE id='%s'", $search_id);
    if ($result = $sw_db->query($saved_search_query))
    {
      if ($result->num_rows && $result->num_rows > 0)
      {
        $raw_query_data = $result->fetch_assoc();
        if ($raw_query_data['private'] == 1 && $raw_query_data['user_id'] != $this->_session_data['user_id'])
        {
          $this->loggy->logDebug($this->log_tag . 'Access violation, user id ' . $this->_session_data['user_id'] . ' trying to view private search owned by user id ' . $raw_query_data['user_id']);
          $incoming_query_data = 'Not Allowed';
        }
        else {
          $serialized_query = $raw_query_data['search_params'];
          $incoming_query_data = unserialize($serialized_query);
        }
      }
      else
      {
        $incoming_query_data = 'Not Found';
      }
    }
    else
    {
      throw new SWException('Database read error: ' . mysqli_errno($sw_db) . ' ' . mysqli_error($sw_db));
    }

    echo json_encode($incoming_query_data);

  }

  function query_param_cache()
  {
    $query_data = $_POST;
    $_SESSION[SWConfig::read_values('auth.sessionName')]['data']['current_query'] = serialize($query_data);
  }

  function config($url_path)
  {
    if ($config_item = array_shift($url_path))
    {
      echo json_encode(SWConfig::read_values($config_item));
    }
    else {
      echo json_encode(SWConfig::read_values());
    }
  }

  function save_dashboard($url_path)
  {
    $dashboard_id = array_shift($url_path);
    if ($confirm = array_shift($url_path))
    {
      if($confirm === "Confirm")
      {
        $confirm_save = true;
        $new_dashboard = false;
      }
      else
      {
        $confirm_save = false;
      }
    }
    $this->loggy->logDebug($this->log_tag . 'API call, saving dashboard id: ' . $dashboard_id);
    $dashboard_config = $_POST;
    $this->loggy->logDebug($this->log_tag . json_encode($dashboard_config));
    $app_config = SWConfig::read_values('statuswolf.session_handler');
    $saved_dashboard_db = new mysqli($app_config['db_host'], $app_config['db_user'], $app_config['db_password'], $app_config['database']);
    if (mysqli_connect_error())
    {
      throw new SWException('Dashboard database connection error: ' . mysqli_connect_errno() . ' ' . mysqli_connect_error());
    }
    $dashboard_config['title'] = mysqli_real_escape_string($saved_dashboard_db, $dashboard_config['title']);
    $dashboard_config['user_id'] = mysqli_real_escape_string($saved_dashboard_db, $dashboard_config['user_id']);
    $dashboard_config['shared'] = mysqli_real_escape_string($saved_dashboard_db, $dashboard_config['shared']);
    $widgets_string = mysqli_real_escape_string($saved_dashboard_db, serialize($dashboard_config['widgets']));
    if (!$confirm_save)
    {
      $this->loggy->logDebug($this->log_tag . "Checking dashboard title against saved dashboards");
      $check_dashboard_title = sprintf("SELECT id, title FROM saved_dashboards WHERE title='%s' AND user_id='%s'", $dashboard_config['title'], $dashboard_config['user_id']);
      if ($check_title_result = $saved_dashboard_db->query($check_dashboard_title))
      {
        if ($check_title_result->num_rows && $check_title_result->num_rows > 0)
        {
          $raw_query_data = $check_title_result->fetch_assoc();
          echo json_encode(array("query_result" => "Error", "query_info" => "Title", "dashboard_id" => $raw_query_data['id']));
          $saved_dashboard_db->close();
          return;
        }
        else
        {
          $new_dashboard = true;
        }
      }
    }
    $save_dashboard_query = sprintf("REPLACE INTO saved_dashboards VALUES('%s', '%s', '%s', '%s', '%s')", $dashboard_id, $dashboard_config['title'], $dashboard_config['user_id'], $dashboard_config['shared'], $widgets_string);
    $add_dashboard_rank_query = sprintf("INSERT INTO dashboard_rank VALUES('%s','0')", $dashboard_id);
    $this->loggy->logDebug($this->log_tag . $save_dashboard_query);
    $save_result = $saved_dashboard_db->query($save_dashboard_query);
    $transaction_id = $saved_dashboard_db->insert_id;
    if (mysqli_error($saved_dashboard_db))
    {
      throw new SWException('Error saving search: ' . mysqli_errno($saved_dashboard_db) . ' ' . mysqli_error($saved_dashboard_db));
    }
    if ($new_dashboard)
    {
      $rank_result = $saved_dashboard_db->query($add_dashboard_rank_query);
      $rank_transaction_id = $saved_dashboard_db->insert_id;
      if (mysqli_error($saved_dashboard_db))
      {
        throw new SWException('Error adding dashboard to rank table: ' . mysqli_errno($saved_dashboard_db) . ' ' . mysqli_error($saved_dashboard_db) );
      }
    }

    echo json_encode(array("query_result", "Success"));

    $saved_dashboard_db->close();

  }

  protected function get_saved_dashboards()
  {
    $data = $_POST;
    $_saved_dashboards = array();

    $dashboard_db = new mysqli($this->_app_config['session_handler']['db_host'], $this->_app_config['session_handler']['db_user'], $this->_app_config['session_handler']['db_password'], $this->_app_config['session_handler']['database']);
    if (mysqli_connect_error())
    {
      throw new SWException('Saved searches database connect error: ' . mysqli_connect_errno() . ' ' . mysqli_connect_error());
    }
    $data['user_id'] = mysqli_real_escape_string($dashboard_db, $data['user_id']);
    $dashboard_query = sprintf("SELECT * FROM saved_dashboards where user_id='%s' AND shared=0", $data['user_id']);
    $user_dashboards_result = $dashboard_db->query($dashboard_query);
    if ($user_dashboards_result->num_rows && $user_dashboards_result->num_rows > 0)
    {
      $_saved_dashboards['user_dashboards'] = array();
      while($user_dashboards = $user_dashboards_result->fetch_assoc())
      {
        array_push($_saved_dashboards['user_dashboards'], array('id' => $user_dashboards['id'], 'title' => $user_dashboards['title']));
      }
    }
    $usermap = array();
    $usermap_result = $dashboard_db->query("SELECT * FROM user_map");
    if ($usermap_result->num_rows && $usermap_result->num_rows > 0)
    {
      while ($usermap_entry = $usermap_result->fetch_assoc())
      {
        $usermap[$usermap_entry['id']] = $usermap_entry['username'];
      }
    }
    $shared_dashboards_query = sprintf("SELECT dr.count, sd.* FROM dashboard_rank dr, saved_dashboards sd WHERE sd.id = dr.id AND shared=1 ORDER BY dr.count DESC");
    $shared_dashboards_result = $dashboard_db->query($shared_dashboards_query);
    if ($shared_dashboards_result->num_rows && $shared_dashboards_result->num_rows > 0)
    {
      $_saved_dashboards['shared_dashboards'] = array();
      while($shared_dashboards = $shared_dashboards_result->fetch_assoc())
      {
        array_push($_saved_dashboards['shared_dashboards'], array('id' => $shared_dashboards['id'], 'user_id' => $shared_dashboards['user_id'], 'username' => $usermap[$shared_dashboards['user_id']], 'title' => $shared_dashboards['title']));
      }
    }

    echo json_encode($_saved_dashboards);

  }

  protected function load_saved_dashboard($query_bits)
  {
    $db_conf = $this->_app_config['session_handler'];

    $sw_db = new mysqli($db_conf['db_host'], $db_conf['db_user'], $db_conf['db_password'], $db_conf['database']);
    if (mysqli_connect_error())
    {
      throw new SWException('Unable to connect to shared dashboard database: ' . mysqli_connect_errno() . ' ' . mysqli_connect_error());
    }
    $dash_id = mysqli_real_escape_string($sw_db, array_shift($query_bits));
    $this->loggy->logDebug($this->log_tag . "Loading saved dashboard id " . $dash_id);
    $dashboard_query = sprintf("SELECT * FROM saved_dashboards WHERE id='%s'", $dash_id);
    if ($dashboard_result = $sw_db->query($dashboard_query))
    {
      if ($dashboard_result->num_rows && $dashboard_result->num_rows > 0)
      {
        $dashboard_data = $dashboard_result->fetch_assoc();
        if ($dashboard_data['shared'] == 0 && $dashboard_data['user_id'] != $this->_session_data['user_id'])
        {
          $this->loggy->logDebug($this->log_tag . 'Access violation, user id ' . $this->_session_data['user_id'] . ' trying to view private dashboard owned by user id ' . $dashboard_data['user_id']);
          $dashboard_config['error'] = 'Not Allowed';
        }
        else
        {
          $this->loggy->logDebug($this->log_tag . 'Dashboard config found, loading');
          $dashboard_config = $dashboard_data;
          $dashboard_config['widgets'] = unserialize($dashboard_data['widgets']);
          $this->loggy->logDebug($this->log_tag . 'Updating dashboard rank');
          $update_rank_query = sprintf("UPDATE dashboard_rank SET count=count+1 WHERE id='%s'", $dash_id);
          $rank_result = $sw_db->query($update_rank_query);
          $transaction_id = $sw_db->insert_id;
          if (mysqli_error($sw_db))
          {
            throw new SWException('Error updating dashboard rank: ' . mysqli_errno($sw_db) . ' ' . mysqli_error($sw_db));
          }
        }
      }
      else
      {
        $this->loggy->logDebug($this->log_tag . 'Dashboard id ' . $dash_id . ' was not found');
        $dashboard_config['error'] = 'Not Found';
      }
    }
    else
    {
      throw new SWException('Database read error: ' . mysqli_errno($sw_db) . ' ' . mysqli_error($sw_db));
    }

    echo json_encode($dashboard_config);

  }
  protected function delete_saved_dashboards()
  {
    $data = $_POST;
    $dashboard_ids = array();

    $sw_db = new mysqli($this->_app_config['session_handler']['db_host'], $this->_app_config['session_handler']['db_user'], $this->_app_config['session_handler']['db_password'], $this->_app_config['session_handler']['database']);
    if (mysqli_connect_error())
    {
      throw new SWException('Saved dashboards database connect error: ' . mysqli_connect_errno() . ' ' . mysqli_connect_error());
    }
    foreach ($data as $dashboard_title => $dashboard_id)
    {
      array_push($dashboard_ids, mysqli_real_escape_string($sw_db, $dashboard_id));
    }
    $this->loggy->logDebug($this->log_tag . "API call, deleting saved dashboards " . implode(',', $dashboard_ids));
    $dashboard_ids_string = implode("','", $dashboard_ids);
    $delete_dashboards_query = sprintf("DELETE FROM saved_dashboards WHERE id in ('%s')", $dashboard_ids_string);
    $delete_rank_query = sprintf("DELETE FROM dashboard_rank WHERE id in ('%s')", $dashboard_ids_string);
    $this->loggy->logDebug($this->log_tag . $delete_dashboards_query);
    $sw_db->query($delete_dashboards_query);
    if (mysqli_error($sw_db))
    {
      throw new SWException('Session open error: ' . mysqli_errno($sw_db) . ' ' . mysqli_error($sw_db));
    }
    else
    {
      echo json_encode($dashboard_ids);
    }
    $sw_db->query($delete_rank_query);
    if (mysqli_error($sw_db))
    {
      throw new SWException('Session open error: ' . mysqli_errno($sw_db) . ' ' . mysqli_error($sw_db));
    }

    $sw_db->close();

  }

}
