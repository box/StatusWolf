<?php
/**
 * TimeSeriesData
 *
 * Base class definition for time series models. Data returned may contain
 * multiple series of data, sorted by key. Regardless of data source, time
 * series objects will return data in the form of array
 * $ts_data(<series_key>[] = ('timestamp' => <timestamp>, 'value' => <value>))
 * and must also be able to return data as JSON or CSV. Data can be
 * returned whole or by individual series_key. By default time series data
 * will be downsampled to 1 minute intervals.
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 6 June 2013
 *
 * @package StatusWolf.Model
 */

class TimeSeriesData {

  /**
   * Default container for returned data
   *
   * @var array
   */
  public $ts_data = array();

  /**
   * Number of datapoints returned from the search
   *
   * @var int
   */
  public $num_points = null;

  /**
   * Downsampling algorithm to use, default is sum
   *
   * @var string
   */
  public $downsample_type = 'sum';

  /**
   * The downsampling algorithms to choose from
   *
   * @var array
   */
  private $_downsamplers = array('sum','avg','min','max');

  /**
   * The interval (in minutes) to which we downsample the data
   * @var int
   */
  public $downsample_interval = 1;

  /**
   * The aggregation algorithm to use, default is sum
   *
   * @var string
   */
  public $aggregation_type = 'sum';

  /**
   * The aggregation algorithms to choose from
   *
   * @var array
   */
  private $_aggregators = array('sum','avg','min','max','dev');

  /**
   * Holds the first timestamp collected in a series of data
   * @var int
   */
  protected $_start_timestamp;

  /**
   * Holds the last timestamp collected in a series of data
   *
   * @var int
   */
  protected $_end_timestamp;

  /**
   * Query start time as timestamp, post-downsampling
   *
   * @var int
   */
  public $start_time = null;

  /**
   * Query end time as timestamp, post-downsampling
   *
   * @var int
   */
  public $end_time = null;

  /**
   * Cache file to store the returned query data in
   *
   * @var string
   */
  protected $_query_cache = null;


  /**
   * TimeSeriesData::read()
   *
   * Return the time series data as an array
   *
   * @param string $key
   * @return array|null
   */
  public function read($key = null)
  {
    if (!empty($this->ts_data))
    {
      if ($key)
      {
        return $this->ts_data[$key];
      }
      else
      {
        return $this->ts_data;
      }
    }
    else
    {
      return null;
    }
  }

  /**
   * TimeSeriesData::read_json()
   *
   * Return the time series data in JSON format
   *
   * @param string $key
   * @return null|string
   */
  public function read_json($key = null)
  {
    if (!empty($this->ts_data))
    {
      if ($key)
      {
        return json_encode($this->ts_data[$key]);
      }
      else
      {
        return json_encode($this->ts_data);
      }
    }
    else
    {
      return null;
    }
  }

  /**
   * TimeSeriesData::read_csv()
   *
   * Return the time series data in CSV format
   *
   * @param string $key
   * @return null
   */
  public function read_csv($key = null)
  {
    if (!empty($this->ts_data))
    {
      if ($key)
      {
        $output = "timestamp,value\n";
        foreach ($this->ts_data[$key] as $data)
        {
          $output .= $data['timestamp'] . ',' . $data['value'] . "\n";
        }
      }
      else
      {
        $output = "key,timestamp,value\n";
        foreach ($this->ts_data as $key => $raw_data)
        {
          foreach ($raw_data as $data)
          {
            $output .= $key . ',' . $data['timestamp'] . ',' . $data['value'] . "\n";
          }
        }
      }
      return $output;
    }
    else
    {
      return null;
    }
  }

  /**
   * TimeSeriesData::get_cache_file()
   *
   * Get the name of the file where the query results are cached
   *
   * @return null|string
   */
  public function get_cache_file()
  {
    if (!empty($this->_query_cache))
    {
      return $this->_query_cache;
    }
    else
    {
      return null;
    }
  }

  /**
   * TimeSeriesData::get_start()
   *
   * Get the first timestamp in the series
   *
   * @return int|null
   */
  public function get_start()
  {
    if (!empty($this->_start_timestamp))
    {
      return $this->_start_timestamp;
    }
    else
    {
      return null;
    }
  }

  /**
   * TimeSeriesData::set_start()
   *
   * Update the first timestamp found in the series
   *
   * @param int|string $timestamp
   * @return bool
   */
  public function set_start($timestamp = null)
  {
    if ($timestamp)
    {
      $this->_start_timestamp = (int) $timestamp;
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * TimeSeriesData::get_end()
   *
   * Get the last timestamp in the series
   *
   * @return int|null
   */
  public function get_end()
  {
    if (!empty($this->_end_timestamp))
    {
      return $this->_end_timestamp;
    }
    else
    {
      return null;
    }
  }

  /**
   * TimeSeriesData::set_end()
   *
   * Update the last timestamp found in the series
   *
   * @param int|string $timestamp
   * @return bool
   */
  public function set_end($timestamp = null)
  {
    if ($timestamp)
    {
      $this->_end_timestamp = (int) $timestamp;
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * TimeSeriesData::get_downsample_type()
   *
   * Get the downsampling algorithm used for this series
   *
   * @return null|string
   */
  public function get_downsample_type()
  {
    if (!empty($this->downsample_type))
    {
      return $this->downsample_type;
    }
    else
    {
      return null;
    }
  }

  /**
   * TimeSeriesData::set_downsample_type()
   *
   * Update the downsampling algorithm used for the series
   *
   * @param string $downsampler
   * @return bool
   */
  public function set_downsample_type($downsampler = null)
  {
    if (array_key_exists($downsampler, $this->_downsamplers))
    {
      $this->downsample_type = $downsampler;
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * TimeSeriesData::get_downsample_interval()
   *
   * Get the current downsampling interval (in minutes)
   *
   * @return int|null
   */
  public function get_downsample_interval()
  {
    if (!empty($this->downsample_interval))
    {
      return $this->downsample_interval;
    }
    else
    {
      return null;
    }
  }

  /**
   * TimeSeriesData::set_downsample_interval()
   *
   * Update the downsampling interval
   *
   * @param int $interval
   * @return bool
   */
  public function set_downsample_interval($interval = null)
  {
    if ($interval && $interval > 0)
    {
      $this-> downsample_interval = (int) $interval;
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * TimeSeriesData::get_aggregation_type()
   *
   * Get the aggregation algorithm used for the series
   *
   * @return bool|string
   */
  public function get_aggregation_type()
  {
    if (!empty($this->aggregation_type))
    {
      return $this->aggregation_type;
    }
    else
    {
      return false;
    }
  }

  /**
   * TimeSeriesData::set_aggregation_type()
   *
   * Update the aggregation algorithm used for the series
   *
   * @param string $aggregator
   * @return bool
   */
  public function set_aggregation_type($aggregator = null)
  {
    if (array_key_exists($aggregator, $this->_aggregators))
    {
      $this->aggregation_type = $aggregator;
      return true;
    }
    else
    {
      return false;
    }
  }

  public function get_raw_data(array $query_bits)
  {
  }

}
