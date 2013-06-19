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
    if(SWConfig::read_values('statuswolf.debug'))
    {
      $this->loggy = new KLogger(ROOT . 'app/log/', KLogger::DEBUG);
    }
    else
    {
      $this->loggy = new KLogger(ROOT . 'app/log/', KLogger::INFO);
    }

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
    // Make sure we were passed query building blocks
    if (empty($query_bits))
    {
      throw new SWException('No query found to search on');
    }
    else
    {
      ini_set('memory_limit', '2G');
      set_time_limit(120);

      // Make sure we have a metric name to search on and build the metric
      // string with downsampler, aggregator, rate & interpolation info
      if (array_key_exists('metrics', $query_bits))
      {
        $query_bits['key'] = '';
        foreach($query_bits['metrics'] as $metric)
        {
          $qkey = '&m=';
          if (array_key_exists('agg_type', $metric))
          {
            $this->aggregation_type = $metric['agg_type'];
          }
          $qkey .= $this->aggregation_type . ':';

          if (array_key_exists('ds_interval', $metric))
          {
            $this->downsample_interval = $metric['ds_interval'];
          }
          $qkey .= $this->downsample_interval . 'm-';

          if (array_key_exists('ds_type', $metric))
          {
            $this->downsample_type = $metric['ds_type'];
          }
          $qkey .= $this->downsample_type . ':';

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

          $query_bits['key'] = $query_bits['key'] . $qkey;
        }
      }
      else
      {
        throw new SWException('No query found to search on');
      }
    }



    $query_url = $this->_build_url($query_bits);
    $curl = new Curl($query_url);

    $data_pull_start = time();
    try
    {
      $raw_data = $curl->request();
    }
    catch(SWException $e)
    {
//      throw new SWException('Failed to retrieve metrics from OpenTSDB: ' . $e->getMessage());
      $this->loggy->logError("Failed to retrieve metrics from OpenTSDB, start time was: $this->_query_start");
      $this->loggy->logError(substr($e->getMessage(), 0, 256));
      return null;
    }
    $data_pull_end = time();
    $pull_time = $data_pull_end - $data_pull_start;
    $this->loggy->logInfo("Retrieved metrics from OpenTSDB, total execution time: $pull_time seconds");
    $data = explode("\n", $raw_data);

    $this->num_points = count($data);
    $graph_data = array();

    foreach ($data as $line)
    {
      $fields = explode(' ', $line);
      $metric = array_shift($fields);
      $timestamp = array_shift($fields);
      $value = array_shift($fields);
      if ($query_bits['history-graph'] === "no")
      {
        if (!empty($fields))
        {
          $tag_key = implode(' ', $fields);
        }
        else
        {
          $tag_key = 'NONE';
        }
      }
      else
      {
        if (array_key_exists('tags', $query_bits['metrics'][0]))
        {
          $tag_key = implode(' ', $query_bits['metrics'][0]['tags']);
        }
        else
        {
          $tag_key = 'NONE';
        }
      }
      $series_key = $metric . ' ' . $tag_key;
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

    foreach ($graph_data as $series => $data)
    {
      foreach ($data as $key => $row)
      {
        $timestamp[$key] = $row['timestamp'];
        $value[$key] = $row['value'];
      }
      array_multisort($timestamp, SORT_ASC, $value, SORT_ASC, $data);
      $downsampler = new TimeSeriesDownsample($this->downsample_interval, $this->downsample_type);
      $downsampler->ts_object = @$this;
      $graph_data[$series] = $downsampler->downsample($data, $this->_start_timestamp, $this->_end_timestamp);
    }

    $this->ts_data = $graph_data;
    $this->ts_data['query_url'] = $this->tsdb_query_url;
    $this->ts_data['start'] = $this->start_time;
    $this->ts_data['end'] = $this->end_time;

  }

  public function flush_data()
  {
    unset($this->ts_data);
    $this->ts_data = array();
    $this->_start_timestamp = null;
    $this->_end_timestamp = null;
  }

}
