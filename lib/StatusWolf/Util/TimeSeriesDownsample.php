<?php
/**
 * TimeSeriesDownsample
 *
 * Takes raw time series data and downsamples it to even intervals
 * of 1 minute or greater, as defined by the $_sample_interval
 *
 * Authors: Mark Troyer <disco@box.com>, Jeff Queisser <jeff@box.com>
 * Created: 4 April 2013
 *
 * @package Cumulus.Utility
 */

/*
 * Downsample time series data
 *
 * @package Cumulus.utility
 */
class TimeSeriesDownsample
{

	/**
	 * The calling time series object, used to track the
	 * start and end times of all series
	 *
	 * @var object
	 */
	public $ts_object;

	/*
	 * The interval to downsample to, in minutes
	 *
	 * @var int
	 */
	private $_sample_interval;

	/*
	 * The downsampling method, defaults to sum
	 *
	 * @var string;
	 */
	private $_downsample_method;

	/*
	 * Allowed downsampling methods
	 *
	 * @var array()
	 */
	private $_methods = array('sum', 'avg', 'min', 'max', 'dev');

	/*
	 * __construct
	 *
	 * Build the TimeSeriesData object
	 *
	 * @param int $sample_interval
	 * @param string $method
	 * @return void
	 */
	public function __construct($sample_interval = 1, $method = 'sum')
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

    $this->loggy->logDebug($this->log_tag . 'Downsampler init, interval: ' . $sample_interval . ' method: ' . $method);
		$this->_sample_interval = $sample_interval * 60;
		if (in_array($method, $this->_methods))
		{
			$this->_downsample_method = $method;
		}
		else
		{
			return "Error: unknown downsample method";
		}
	}

	/*
	 * downsample
	 *
	 * Master function to downsample time series data
	 *
	 * @param array $time_series
	 * @return array
	 */
	public function downsample($time_series = array(), $start, $end)
	{
		if (empty($time_series))
		{
			return null;
		}

		$start_check = getdate($start);
		$start_differential = $start_check['seconds'];

		$start_min = $start + ($this->_sample_interval - $start_differential);
		$next_min = $start_min + $this->_sample_interval;

		$master_start = $this->ts_object->start_time;
		if (!$master_start)
		{
			$this->ts_object->start_time = $next_min;
		}
//		$end_stamp = end($time_series);
//		$end_stamp = $end_stamp['timestamp'];
//    reset($time_series);
		$end_check = getdate($end);
		$end_differential = $end_check['seconds'];
		$end_min = $end - $end_differential;
		$master_end = $this->ts_object->end_time;
		if (!$master_end)
		{
			$this->ts_object->end_time = $end_min;
		}

		$downsample_buckets = array();
    $downsample_buckets[$end_min] = array();

		foreach ($time_series as $ts_entry)
		{
			if (($ts_entry['timestamp'] <= $start_min) || ($ts_entry['timestamp'] > $end_min))
			{
				continue;
			}
			else if ($ts_entry['timestamp'] === $end_min)
			{
				$downsample_buckets[$next_min][] = $ts_entry['value'];
			}
			else if ($ts_entry['timestamp'] <= $next_min)
			{
				$downsample_buckets[$next_min][] = $ts_entry['value'];
			}
			else
			{
				while ($ts_entry['timestamp'] > $next_min)
				{
					$next_min += $this->_sample_interval;
					$downsample_buckets[$next_min] = array();
				}
				$downsample_buckets[$next_min][] = $ts_entry['value'];
			}
		}

		$downsample_points = array();
    ksort($downsample_buckets);

    $this->loggy->logDebug($this->log_tag . 'Downsampling data to interval ' . $this->_sample_interval . ' using method ' . $this->_downsample_method);
		switch ($this->_downsample_method)
		{
			case 'avg':
				foreach ($downsample_buckets as $timestamp => $values)
				{
					if (empty($values))
					{
						$downsample_points[] = array('timestamp' => $timestamp, 'value' => null);
					}
					else
					{
            $value_agg = array_sum($values) / count($values);
            if ($value_agg < 0)
            {
              $value_agg = 0;
            }
						$downsample_points[] = array('timestamp' => $timestamp, 'value' => $value_agg);
					}
				}
				break;
			case 'sum':
				foreach ($downsample_buckets as $timestamp => $values)
				{
					if (empty($values))
					{
						$downsample_points[] = array('timestamp' => $timestamp, 'value' => null);
					}
					else
					{
            $value_agg = array_sum($values);
            if ($value_agg < 0)
            {
              $value_agg = 0;
            }
						$downsample_points[] = array('timestamp' => $timestamp, 'value' => $value_agg);
					}
				}
				break;
			case 'min':
				foreach ($downsample_buckets as $timestamp => $values)
				{
					if (empty($values))
					{
						$downsample_points[] = array('timestamp' => $timestamp, 'value' => null);
					}
					else
					{
            $value_agg = min($values);
            if ($value_agg < 0)
            {
              $value_agg = 0;
            }
						$downsample_points[] = array('timestamp' => $timestamp, 'value' => +$value_agg);
					}
				}
				break;
			case 'max':
				foreach ($downsample_buckets as $timestamp => $values)
				{
					if (empty($values))
					{
						$downsample_points[] = array('timestamp' => $timestamp, 'value' => null);
					}
					else
					{
            $value_agg = max($values);
            if ($value_agg < 0)
            {
              $value_agg = 0;
            }
						$downsample_points[] = array('timestamp' => $timestamp, 'value' => +$value_agg);
					}
				}
				break;
			case 'dev':
				foreach ($downsample_buckets as $timestamp => $values)
				{
					if (empty($values))
					{
						$downsample_points[] = array('timestamp' => $timestamp, 'value' => null);
					}
					else
					{
						$amount = count($values);
						$mean = array_sum($values) / $amount;

						foreach ($values as $value)
						{
							$difference[] = pow($value - $mean, 2);
						}
            $value_agg = pow(array_sum($difference) / $amount, 0.5);
            if ($value_agg < 0)
            {
              $value_agg = 0;
            }
						$downsample_points[] = array('timestamp' => $timestamp, 'value' => $value_agg);
					}
				}
				break;
		}

		return $downsample_points;
	}

}
