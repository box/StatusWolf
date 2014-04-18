<?php
/**
 * TimeSeriesDownsample
 *
 * Downsamples raw time series data to even intervals of 1 minute or
 * greater, as defined by $_sample_interval
 *
 * Authors: Mark Troyer <disco@box.com>, Jeff Queisser <jeff@box.com>
 * Created: 7 March 2014
 *
 */

 namespace StatusWolf\Model;

 use Silex\Application;
 use StatusWolf\Model\OpenTSDBDataSource;

 class TimeSeriesDownsample {

     public $ts_object;

     private $_sample_interval;

     private $_downample_method;

     private $_allowed_methods = array('sum', 'avg', 'min', 'max', 'dev');

     private $_null_as_zero;

     public function __construct(Application $sw, OpenTSDBDataSource $ts_object, $sample_interval = 1, $method = 'sum', $null_as_zero = false) {

         $this->sw = $sw;
         $this->ts_object = $ts_object;
         $this->_null_as_zero = $null_as_zero;
         $this->_sample_interval = $sample_interval * 60;

         if (in_array($method, $this->_allowed_methods)) {
             $this->_downample_method = $method;
         } else {
             throw new \RunTimeException(sprintf("Unknown downsampling method %s", $method));
         }

     }

    public function downsample($time_series = array(), $start, $end) {

        $start_min = $start;

        if (empty($time_series)) {
            $this->sw['logger']->addError('Nothing to downsample!');
            throw new \InvalidArgumentException("No time series data found");
        }

        $start_check = getdate($start);
        $start_differential = $start_check['seconds'];

        if ($start_differential > 0) {
            $start_min = $start + (60 - $start_differential);
        }

        $next_min = $start_min + $this->_sample_interval;
        if (isset($this->ts_object->start_time)) {
            $master_start = $this->ts_object->start_time;
        } else {
            $master_start = $this->ts_object->start_time = $next_min;
        }

        $end_check = getdate($end);
        $end_differential = $end_check['seconds'];
        $end_min = $end - $end_differential;
        if ($this->_sample_interval > 60) {
            $end_offset = ($end_min - $start_min) % $this->_sample_interval;
            if ($end_offset > 0) {
                $end_min = $end_min - $end_offset;
            }
        }
        if (isset($this->ts_object->end_time)) {
            $master_end = $this->ts_object->end_time;
        } else {
            $master_end = $this->ts_object->end_time = $end_min;
        }

        $this->sw['logger']->addDebug(sprintf("start_min: %s, end_min: %s", $start_min, $end_min));

        $downsample_buckets = array();
        $downsample_buckets[$end_min] = array();

        foreach ($time_series as $time_series_entry) {
            if (($time_series_entry['timestamp'] <= $start_min) || ($time_series_entry['timestamp'] > $end_min)) {
                continue;
            } elseif ($time_series_entry['timestamp'] === $end_min) {
                $downsample_buckets[$next_min][] = $time_series_entry['value'];
            } elseif ($time_series_entry['timestamp'] <= $next_min) {
                $downsample_buckets[$next_min][] = $time_series_entry['value'];
            } else {
                while ($time_series_entry['timestamp'] > $next_min) {
                    $next_min += $this->_sample_interval;
                    $downsample_buckets[$next_min] = array();
                }
                $downsample_buckets[$next_min][] = $time_series_entry['value'];
            }
        }

        $downsample_points = array();
        ksort($downsample_buckets);

        $this->sw['logger']->addDebug(sprintf("Downsampling data to interval %s using method %s", $this->_sample_interval, $this->_downample_method));

        switch ($this->_downample_method) {
            case 'avg':
                foreach ($downsample_buckets as $timestamp => $values) {
                    if (empty($values)) {
                        if ($this->_null_as_zero) {
                            $downsample_points[] = array('timestamp' => $timestamp, 'value' => 0);
                        } else {
                            $downsample_points[] = array('timestamp' => $timestamp, 'value' => null);
                        }
                    } else {
                        $value_agg = array_sum($values) / count($values);
                        if ($value_agg < 0) {
                            $value_agg = 0;
                        }
                        $downsample_points[] = array('timestamp' => $timestamp, 'value' => $value_agg);
                    }
                }
                break;
            case 'sum':
                foreach ($downsample_buckets as $timestamp => $values) {
                    if (empty($values)) {
                        if ($this->_null_as_zero) {
                            $downsample_points[] = array('timestamp' => $timestamp, 'value' => 0);
                        } else {
                            $downsample_points[] = array('timestamp' => $timestamp, 'value' => null);
                        }
                    } else {
                        $value_agg = array_sum($values);
                        if ($value_agg < 0) {
                            $value_agg = 0;
                        }
                        $downsample_points[] = array('timestamp' => $timestamp, 'value' => $value_agg);
                    }
                }
                break;
            case 'min':
                foreach ($downsample_buckets as $timestamp => $values) {
                    if (empty($values)) {
                        if ($this->_null_as_zero) {
                            $downsample_points[] = array('tiemstamp' => $timestamp, 'value' => 0);
                        } else {
                            $downsample_points[] = array('timestamp' => $timestamp, 'value' => null);
                        }
                    } else {
                        $value_agg = min($values);
                        if ($value_agg < 0) {
                            $value_agg = 0;
                        }
                        $downsample_points[] = array('timestamp' => $timestamp, 'value' => $value_agg);
                    }
                }
                break;
            case 'max':
                foreach ($downsample_buckets as $timestamp => $values) {
                    if (empty($values)) {
                        if ($this->_null_as_zero) {
                            $downsample_points[] = array('timestamp' => $timestamp, 'value' => 0);
                        } else {
                            $downsample_points[] = array('timestamp' => $timestamp, 'value' => null);
                        }
                    } else {
                        $value_agg = max($values);
                        if ($value_agg < 0) {
                            $value_agg = 0;
                        }
                        $downsample_points[] = array('timestamp' => $timestamp, 'value' => $value_agg);
                    }
                }
                break;
            case 'dev':
                foreach ($downsample_buckets as $timestamp => $values) {
                    if (empty($values)) {
                        if ($this->_null_as_zero) {
                            $downsample_points[] = array('timestamp' => $timestamp, 'value' => 0);
                        } else {
                            $downsample_points[] = array('timestamp' => $timestamp, 'value' => null);
                        }
                    } else {
                        $amount = count($values);
                        $mean = array_sum($values) / $amount;
                        foreach ($values as $value) {
                            $difference[] = pow($value - $mean, 2);
                        }
                        $value_agg = pow(array_sum($difference) / $amount, 0.5);
                        if ($value_agg < 0) {
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
