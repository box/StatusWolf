<?php
/**
 * OpenTSDB
 *
 * Describe your class here
 *
 * Authors: Mark Troyer <disco@box.com>, Jeff Queisser <jeff@box.com>
 * Date Created: 6 June 2013
 *
 * @package StatusWolf.Model.Datasource
 */

class OpenTSDB extends TimeSeriesData {

  /**
   * Format for dates in OpenTSDB API queries
   *
   * @const
   */
  const OPENTSDB_DATE_FORMAT = 'Y/m/d-H:i:s';

  /**
   * Valid return types for OpenTSDB API queries
   *
   * @var array
   */
  public static $OPENTSDB_RETURN_TYPES = array('ascii', 'json');

  /**
   * The OpenTSDB host to query
   *
   * @var string
   */
  private $_host = null;

  /**
   * Key string for the OpenTSDB API query
   *
   * @var string
   */
  private $_key = null;

  /**
   * Base URL template for OpenTSDB API queries
   *
   * @var string
   */
  private $_tsdb_base_url = null;

  /**
   * API query start time as OPENTSDB_DATE_FORMAT
   *
   * @var string
   */
  private $_query_start = null;

  /**
   * API query end time as OPENTSDB_DATE_FORMAT
   * @var string
   */
  private $_query_end = null;

  /**
   * The generated OpenTSDB API query URL
   *
   * @var string
   */
  public $tsdb_query_url = null;

  /**
   * Time in seconds to trim off the query end stamp to account for
   * lag in OpenTSDB population.
   *
   * @var int
   */
  public $tsdb_query_trim = 0;

  /**
   * OpenTSDB::__construct()
   *
   * OpenTSDB time series data object constructor
   *
   * @param string $host
   * @throws SWException
   */
  public function __construct($host = null)
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

    // Check for an OpenTSDB host provided to the constructor
    if ($host)
    {
      $this->_host = $host;
    }
    // Host not provided, find it in the datasource configuration. If there
    // is more than one possible host, choose one randomly
    else
    {
      if ($host_config = SWConfig::read_values('datasource.OpenTSDB.url'))
      {
        if (is_array($host_config))
        {
          $this->_host = $host_config[array_rand($host_config)];
        }
        else
        {
          $this->_host = $host_config;
        }
      }
      else
      {
        throw new SWException('No OpenTSDB Host configured');
      }
    }

    // Default trim is 0, check the datasource config for a value
    if ($trim = SWConfig::read_values('datasource.OpenTSDB.trim'))
    {
      $this->tsdb_query_trim = $trim;
    }

    $this->loggy->logDebug($this->log_tag . "OpenTSDB object created");
    // The template URL with the configured OpenTSDB host included
    $this->_tsdb_base_url = 'http://' . $this->_host . '/q?start=%s&end=%s%s&%s';

  }

  /**
   * OpenTSDB::_build_url()
   *
   * Function to build the OpenTSDB API query URI. Requires a string for
   * the search key. If start and end times are not provided will default
   * to a 4-hour span ending at the present minute.
   *
   * @param array $query_bits
   *    Required: $query_bits['key']
   *    Optional:
   *    $query_bits['start_time']
   *    $query_bits['end_time']
   * @return null|string
   */
  protected function _build_url(array $query_bits)
  {
    if ($query_bits['key'])
    {
      $this->_key = $query_bits['key'];
    }
    else
    {
      // If no key string is provided bail out immediately
      return null;
    }

    if (isset($query_bits['end_time']))
    {
      $this->_end_timestamp = $query_bits['end_time'];
    }
    else
    {
      $this->_end_timestamp = time() - $this->tsdb_query_trim;
    }
    $this->_end_timestamp = $this->_end_timestamp - $this->tsdb_query_trim;
    $this->_query_end = date(self::OPENTSDB_DATE_FORMAT, $this->_end_timestamp);

    if (isset($query_bits['start_time']))
    {
      $this->_start_timestamp = $query_bits['start_time'];
    }
    else
    {
      $this->_start_timestamp = $this->_end_timestamp - (HOUR * 4);
    }
    $this->_query_start = date(self::OPENTSDB_DATE_FORMAT, $this->_start_timestamp);

    $this->tsdb_query_url = sprintf($this->_tsdb_base_url, $this->_query_start, $this->_query_end, $this->_key, 'ascii');
    return $this->tsdb_query_url;
  }

  /**
   * OpenTSDB::get_raw_data()
   *
   * Function to query OpenTSDB API and store the returned data for
   * later use.
   *
   * @param array $query_bits
   *    Required:
   *    $query_bits['metric'] - metric name to search on
   *    Optional:
   *    $query_bits['tags'] - tags to filter the metrics on
   * @throws SWException
   */
  public function get_raw_data(array $query_bits)
  {
    $search_metrics = Array();
    $downsample_interval = Array();
    $downsample_type = Array();

    // Make sure we were passed query building blocks
    if (empty($query_bits))
    {
      $this->loggy->logDebug($this->log_tag . "No query data found");
      throw new SWException('No query found to search on');
    }
    else
    {

      // Large datasets can suck up extra memory and take longer to return
      ini_set('memory_limit', '2G');
      set_time_limit(300);

      // Make sure we have a metric name to search on and build the metric
      // string with aggregator, rate & interpolation info
      if (array_key_exists('metrics', $query_bits))
      {
        $query_bits['key'] = '';
        foreach($query_bits['metrics'] as $search_key => $metric)
        {
          $qkey = '&m=';
          if (array_key_exists('agg_type', $metric))
          {
            $this->aggregation_type = $metric['agg_type'];
          }
          $qkey .= $this->aggregation_type . ':';

          if (array_key_exists('rate', $metric) && $metric['rate'])
          {
            $qkey .= 'rate:';
          }

          if (!array_key_exists('lerp', $metric) || (!$metric['lerp']))
          {
            $qkey .= 'nointerpolation:';
          }
          $qkey .= $metric['name'];

          $search_metrics[$metric['name']] = Array();

          if (array_key_exists('tags', $metric) && is_array($metric['tags']))
          {
            $search_metrics[$metric['name']] = $metric['tags'];
            $qkey .= '{';
            foreach ($metric['tags'] as $tag)
            {
              $qkey .= $tag . ',';
            }
            $qkey = rtrim($qkey, ',');
            $qkey .= '}';
          }
          $metric_keys[$qkey] = $metric['name'];
          $query_bits['key'] = $query_bits['key'] . $qkey;

          // Every metric should have an associated downsample type and
          // interval, but check just in case and set to the default
          // if not found
          if (array_key_exists('ds_interval', $metric))
          {
            $downsample_interval[$metric['name']] = $metric['ds_interval'];
          }
          else
          {
            $downsample_interval[$metric['name']] = $this->downsample_interval;
          }

          if (array_key_exists('ds_type', $metric))
          {
            $downsample_type[$metric['name']] = $metric['ds_type'];
          }
          else
          {
            $downsample_type[$metric['name']] = $this->downsample_type;
          }

        }
      }
      else
      {
        throw new SWException('No query found to search on');
      }

    }

    // Fetch the metric data from the OpenTSDB API via Curl
    $query_url = $this->_build_url($query_bits);
    $curl = new Curl($query_url);

    $data_pull_start = time();
    try
    {
      $raw_data = $curl->request();
    }
    catch(SWException $e)
    {
      $this->loggy->logError($this->log_tag . "Failed to retrieve metrics from OpenTSDB, start time was: $this->_query_start");
      $this->loggy->logError($this->log_tag . substr($e->getMessage(), 0, 256));
      $raw_error = explode("\n", $e->getMessage());
      $error_message = array_slice($raw_error, 2);
      $error_message[1] = substr($error_message[1], 15);
      $this->ts_data = array('error', $error_message);
      return null;
    }
    $data_pull_end = time();
    $pull_time = $data_pull_end - $data_pull_start;
    $this->loggy->logInfo($this->log_tag . "Retrieved metrics from OpenTSDB, total execution time: $pull_time seconds");
    $data = explode("\n", $raw_data);

    $this->num_points = count($data);
    $graph_data = array();

    foreach ($data as $line)
    {
      // Break up each returned line into its component parts to extract
      // the metric name, the timestamp, any tags and the metric value
      $fields = explode(' ', $line);
      $metric = array_shift($fields);
      $timestamp = array_shift($fields);
      $value = array_shift($fields);
      if ($query_bits['history_graph'] === "no")
      {
        if (!empty($fields))
        {
          $tag_key = implode(' ', $fields);
        }
        else
        {
          $tag_key = '';
        }
      }
      else
      {
        // Patched identified by sreynolds since we are using php 5.3 
        // $search_key = (array_keys($query_bits['metrics'])[0]);
        $bits_keys = array_keys($query_bits['metrics'])
        $search_key = $bits_keys[0];
        // end patch

        if (array_key_exists('tags', $query_bits['metrics'][$search_key]))
        {
          $tag_key = implode(' ', $query_bits['metrics'][$search_key]['tags']);
        }
        else
        {
          $tag_key = '';
        }
      }
      $series_key = $metric . ' ' . $tag_key;
      // OpenTSDB returns more data than is actually requested, so trim off
      // any points that are outside the requested range
      if (($timestamp < $this->_start_timestamp) || ($timestamp > $this->_end_timestamp))
      {
        continue;
      }
      if (!isset($graph_data[$series_key]))
      {
        $graph_data[$series_key] = array();
      }
      $graph_data[$series_key][] = array('timestamp' => $timestamp, 'value' => $value);
    }

    $legend = $this->_normalize_legend(array_keys($graph_data), $search_metrics);

    foreach ($graph_data as $series => $data)
    {
      $series_parts = explode(' ', $series);
      $series_metric = $series_parts[0];
      foreach ($data as $key => $row)
      {
        $timestamp[$key] = $row['timestamp'];
        $value[$key] = $row['value'];
      }
      // Sort the data to make sure it's all in timestamp order
      $this->loggy->logDebug($this->log_tag . 'sorting data, ' . count($timestamp) . ' timestamps, ' . count($value) . ' values');
      array_multisort($timestamp, SORT_ASC, $value, SORT_ASC, $data);
      // Downsample the data
      $this->loggy->logDebug($this->log_tag . 'Calling downsampler, interval: ' . $downsample_interval[$series_metric] . ' method: ' . $downsample_type[$series_metric]);
      $downsampler = new TimeSeriesDownsample($downsample_interval[$series_metric], $downsample_type[$series_metric]);
      $downsampler->ts_object = @$this;
      $this->loggy->logDebug($this->log_tag . 'Downsampling data, start: ' . $this->_start_timestamp . ', end: ' . $this->_end_timestamp);
      $ds_timer_start = time();
      $graph_data[$series] = $downsampler->downsample($data, $this->_start_timestamp, $this->_end_timestamp);
      $ds_timer_end = time();
      $ds_total_time = $ds_timer_end - $ds_timer_start;
      $this->loggy->logDebug($this->log_tag . 'Downsampling completed in ' . $ds_total_time . ' seconds');
    }

    $this->ts_data = $graph_data;
    $this->ts_data['query_url'] = $this->tsdb_query_url;
    $this->ts_data['start'] = $this->start_time;
    $this->ts_data['end'] = $this->end_time;
    $this->ts_data['legend'] = $legend;

    return;

  }

  /**
   * OpenTSDB::_normalize_legend()
   *
   * Reduces the series names to make them more meaningful and readable on the resulting graphs
   *
   * @param array $graph_data
   */
  protected function _normalize_legend($raw_legends, $query_metrics)
  {
    $save_metric_names = false;
    $metrics = Array();
    $legend_pool = Array();

    $this->loggy->logDebug($this->log_tag . "Normalizing legend");
    $this->loggy->logDebug($this->log_tag . implode(', ', $raw_legends));

    $query_metrics_tags = Array();
    foreach($query_metrics as $metric => $metric_tags)
    {
      $query_metrics_tags[$metric] = Array();
      foreach ($metric_tags as $mtag)
      {
        $bits = explode('=', $mtag);
        array_push($query_metrics_tags[$metric], $bits[0]);
      }
    }

    foreach ($raw_legends as $raw_bits)
    {
      $legend_pool[$raw_bits] = explode(' ', $raw_bits);
    }

    $this->loggy->logDebug($this->log_tag . "Metrics: " . implode(', ', array_keys($query_metrics)) . " (" . count($query_metrics) . " metrics)");
    $this->loggy->logDebug($this->log_tag . "All the legend bits: " . json_encode($legend_pool));
    $this->loggy->logDebug($this->log_tag . "Query tags: " . json_encode($query_metrics_tags));

    foreach ($legend_pool as $series => $legend_bits)
    {
      $legend_string = '';
      $metric_name = array_shift($legend_bits);
      $legend_string = $metric_name . ' ';
      foreach ($legend_bits as $tag)
      {
        $tag_bits = explode('=', $tag);
        if (in_array($tag_bits[0], $query_metrics_tags[$metric_name]))
        {
          $legend_string = $legend_string . $tag . ' ';
        }
      }

      $legends[$series] = trim($legend_string);
    }

    return $legends;

  }

  /**
   * OpenTSDB::flush_data()
   *
   * Clears the array of gathered data
   */
  public function flush_data()
  {
    unset($this->ts_data);
    $this->ts_data = array();
    $this->_start_timestamp = null;
    $this->_end_timestamp = null;
  }

}
