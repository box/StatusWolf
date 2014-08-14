<?php
/**
 * OpenTSDBDataSource
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 7 March 2014
 *
 */

namespace StatusWolf\Model;

use Silex\Application;
use StatusWolf\Model\TimeSeriesDataInterface;
use StatusWolf\Exception\ApiNetworkFetchException;
use StatusWolf\Exception\InvalidConfigurationException;
use StatusWolf\Network\Curl;

class OpenTSDBDataSource implements TimeSeriesDataInterface {

    /**
     * Format for dates in OpenTSDB API queries
     */
    const OPENTSDB_DATE_FORMAT = 'Y/m/d-H:i:s';

    /**
     * Valid return types for OpenTSDB API queries
     * @var array
     */
    public static $OPENTSDB_RETURN_TYPES = array('ascii', 'json');

    /**
     * The OpenTSDB datasource config from conf/sw_datasource.json,
     * found in $sw['sw_config.config']['datasource']['OpenTSDB']
     *
     * @var array
     */
    private $_opentsdb_config;

    /**
     * The OpenTSDB server to query against
     * @var string
     */
    private $_opentsdb_host;

    /**
     * The search string for the OpenTSDB API query
     * e.g. '&m=sum:proc.stat.cpu_used{host=server1.example.com}
     * @var string
     */
    private $_search_key;

    /**
     * The URL for the OpenTSDB v1 API query including a printf-format
     * template string.
     * e.g. http://opentsdb.example.com:4242/q?start=%s&end=%s%s&%s
     * @var string
     */
    private $_opentsdb_base_url_v1;

    /**
     * The URL for the OpenTSDB v2 API query including a printf-format
     * template string.
     * e.g. http://opentsdb.example.com:4242/api/query?start=%s&end=%s%s
     */
    private $_opentsdb_base_url_v2;

    /**
     * OpenTSDB API query start time in OPENTSDB_DATE_FORMAT
     * @var string
     */
    private $_query_start;

    /**
     * OpenTSDB API query end time in OPENTSDB_DATE_FORMAT
     * @var string
     */
    private $_query_end;

    /**
     * The generated OpenTSDB API query URL
     *
     * @var string
     */
    public $opentsdb_query_url;

    /**
     * Number of seconds to trim off the query end stamp to adjust
     * for the lag in OpenTSDB data population.
     *
     * @var int
     */
    public $opentsdb_query_trim = 0;

    /**
     * Container for the data returned by OpenTSDB
     *
     * @var array
     */
    public $opentsdb_data = array();

    /**
     * Downsampling alogrithm to use, defaults to 'sum'
     *
     * @var string
     */
    public $downsample_type = 'sum';

    /**
     * The allowed downsampling algorithms
     * sum = Sum
     * avg = Average
     * min = Minimum Value
     * max = Maximum Value
     * dev = Standard Deviation
     *
     * @var array
     */
    private $_downsamplers = array('sum', 'avg', 'min', 'max', 'dev');

    /**
     * The intervale in minutes to which the data will be downsampled
     *
     * @var int
     */
    public $downsample_interval = 1;

    /**
     * Aggregation algorithm to use, defaults to 'sum'
     *
     * @var string
     */
    public $aggregation_type = 'sum';

    /**
     * The allowed aggregation algorithms
     * sum = Sum
     * avg = Average
     * min = Minimum Value
     * max = Maximum Value
     * dev = Standard Deviation
     *
     * @var array
     */
    private $_aggregators = array('sum', 'avg', 'min', 'max', 'dev');

    /**
     * Holds the first timestamp received in the data returned from OpenTSDB
     *
     * @var string
     */
    protected $start_timestamp;

    /**
     * Holds the last timestamp received in the data returned from OpenTSDB
     *
     * @var string
     */
    protected $end_timestamp;

    /**
     * Data start time, post-downsampling
     *
     * @var string
     */
    public $start_time;

    /**
     * Data end time, post-downsampling
     *
     * @var string
     */
    public $end_time;

    public function __construct(Application $sw, $_opentsdb_host = null) {

        $this->_opentsdb_config = $sw['sw_config.config']['datasource']['OpenTSDB'];
        $this->sw = $sw;

        // If no OpenTSDB host is passed to the constructor,
        // look for one in the config
        if ($_opentsdb_host) {
            $this->_opentsdb_host = $_opentsdb_host;
        } elseif (in_array('url', $this->_opentsdb_config) && is_array($this->_opentsdb_config['url'])) {
            $this->_opentsdb_host = $this->_opentsdb_config['url'][array_rand($this->_opentsdb_config['url'])];
        } else {
            throw new InvalidConfigurationException('No OpenTSDB Host found in the datasource config');
        }

        // Check config for the trim setting, the time to be trimmed from the
        // end of the query time range to account for the data population lag
        // in OpenTSDB
        if (in_array('trim', $this->_opentsdb_config)) {
            $this->opentsdb_query_trim = $this->_opentsdb_config['trim'];
        }

        $this->_opentsdb_base_url_v1 = 'http://' . $this->_opentsdb_host . '/q?start=%s&end=%s%s&%s';
        $this->_opentsdb_base_url_v2 = 'http://' . $this->_opentsdb_host . '/api/query?start=%s&end=%s%s';

    }

    /**
     * Builds the OpenTSDB API query URL. If start and end times are
     * not provided it will default to a 4-hour span ending at the
     * present minute.
     *
     * @param   array   $query_bits     The query parameters
     * @return  string                  Generated OpenTSDB API URL
     * @throws  \InvalidArgumentException
     */
    protected function _build_url($query_key, $start_time = false, $end_time = false) {

        $this->_search_key = $query_key;
        if (in_array('api_version', $this->_opentsdb_config)) {
            $api_version = $this->_opentsdb_config['api_version'];
        } else {
            $api_version = 1;
        }

        if ($end_time) {
            $this->end_timestamp = $end_time - $this->opentsdb_query_trim;
        } else {
            $this->end_timestamp = time() - $this->opentsdb_query_trim;
        }
        $this->_query_end = date(self::OPENTSDB_DATE_FORMAT, $this->end_timestamp);

        if ($start_time) {
            $this->start_timestamp = $start_time;
        } else {
            $this->start_timestamp = $this->end_timestamp - (HOUR * 4);
        }
        $this->_query_start = date(self::OPENTSDB_DATE_FORMAT, $this->start_timestamp);

        if ($api_version == 2) {
            $this->opentsdb_query_url = sprintf($this->_opentsdb_base_url_v2, $this->_query_start, $this->_query_end, $this->_search_key);
        } else {
            $this->opentsdb_query_url = sprintf($this->_opentsdb_base_url_v1, $this->_query_start, $this->_query_end, $this->_search_key, 'ascii');
        }

        return $this->opentsdb_query_url;
    }

    /**
     * Parses the incoming query parameters and performs the query
     * against the OpenTSDB API, stores the returned data in
     * $this->opentsdb_data.
     *
     * @param   array   $query_bits     The query parameters
     * @return  null
     * @throws \InvalidArgumentException
     */
    public function get_metric_data($query_bits = array()) {

        $search_metrics = array();
        $metric_keys = array();
        $null_as_zero = array();

        if (in_array('api_version', $this->_opentsdb_config)) {
            $api_version = $this->_opentsdb_config['api_version'];
        } else {
            $api_version = 1;
        }
        $this->sw['logger']->addDebug("OpenTSDB API version set to " . $api_version);

        if (empty($query_bits)) {
            $this->sw['logger']->addDebug("No query data found");
            throw new \InvalidArgumentException('No query found to search on');
        } else {
            // Large datasets suck up extra memory and take longer to return...
            ini_set('memory_limit', '8G');
            set_time_limit(300);
        }

        if (array_key_exists('metrics', $query_bits)) {
            $query_bits['key'] = '';
            foreach ($query_bits['metrics'] as $metric_key => $metric) {
                $query_key = '&m=';

                $this->sw['logger']->addDebug(sprintf("Parsing options: %s", json_encode($metric)));

                // Aggregation type
                if (array_key_exists('agg_type', $metric)) {
                    $this->aggregation_type = $metric['agg_type'];
                }
                $query_key .= $this->aggregation_type . ':';

                // Is this metric's data a rate?
                if (array_key_exists('rate', $metric) && $metric['rate']) {
                    $query_key .= 'rate:';
                }

                // Turn off interpolation in OpenTSDB (available as a patch
                // to OpenTSDB)
                if (array_key_exists('lerp', $metric) && (!$metric['lerp'] || $metric['lerp'] === "false")) {
                    $query_key .= 'nointerpolation:';
                }

                $query_key .= $metric['name'];

                $search_metrics[$metric['name']] = array();

                // The search tags that go with the metric, if given
                if (array_key_exists('tags', $metric) && is_array($metric['tags'])) {
                    $search_metrics[$metric['name']] = $metric['tags'];
                    $query_key .= '{';
                    foreach($metric['tags'] as $tag) {
                        $query_key .= $tag . ',';
                    }
                    $query_key = rtrim($query_key, ',');
                    $query_key .= '}';
                }

                $metric_keys[$query_key] = $metric['name'];
                $query_bits['key'] = $query_bits['key'] . $query_key;

                // Treat null as zero setting
                if (array_key_exists('null_zero', $metric)) {
                    $null_as_zero[$metric['name']] = $metric['null_zero'];
                } else {
                    $null_as_zero[$metric['name']] = false;
                }
            }
        } else {
            throw new \InvalidArgumentException('No search metrics found in query');
        }

        $this->sw['logger']->addDebug(sprintf("Query key build: %s", $query_bits['key']));
        $opentsdb_query_url = $this->_build_url($query_bits['key'], $query_bits['start_time'], $query_bits['end_time']);
        $this->sw['logger']->addDebug("Built OpenTSDB URL as: " . $opentsdb_query_url);

        $curl = new Curl($this->sw, $opentsdb_query_url, $this->_opentsdb_config['proxy'], $this->_opentsdb_config['proxy_url']);

        $data_pull_start = time();

        try {
            $raw_time_series_data = $curl->request();
        } catch (ApiNetworkFetchException $e) {
            $this->sw['logger']->addError(sprintf("Failed to retrieve metrics from OpenTSDB, start time was %s", $this->_query_start));
            $this->sw['logger']->addError(substr($e->getMessage(), 0, 256));
            $raw_error_message = explode("\n", $e->getMessage());
            $error_message = array_slice($raw_error_message, 2);
            $error_message[1] = substr($error_message[1], 15);
            $this->opentsdb_data = array('error', $error_message);
            return;
        }

        $data_pull_end = time();
        $pull_time = $data_pull_end - $data_pull_start;
        $this->sw['logger']->addInfo(sprintf("Retrieved metrics from OpenTSDB, total execution time: %d seconds", $pull_time));
        if ($api_version == 2) {
            $opentsdb_data = json_decode($raw_time_series_data, true);
        } else {
            $opentsdb_data = explode("\n", $raw_time_series_data);
        }

        $graph_data = array();

        $query_bits_keys = array_keys($query_bits['metrics']);
        $search_key = $query_bits_keys[0];
        $this->sw['logger']->addDebug("default search key: " . $search_key);

        if (array_key_exists('tags', $query_bits['metrics'][$search_key])) {
            $metric_tag_key = implode(' ', $query_bits['metrics'][$search_key]['tags']);
        } else {
            $metric_tag_key = '';
        }

        if ($api_version == 2) {
            foreach ($opentsdb_data as $response) {
                $metric_name = $response['metric'];
                if ($query_bits['history_graph'] === "no") {
                    $metric_tag_key = '';
                    if (!empty($response['tags'])) {
                        $key_string = '';
                        foreach ($response['tags'] as $tag => $tag_value) {
                            $key_string .= $tag . '=' . $tag_value . ' ';
                        }
                        $metric_tag_key = trim($key_string);
                    }
                }
                $series_key = $metric_name . ' ' . $metric_tag_key;
                $this->sw['logger']->addDebug(sprintf("Found new series: %s", $series_key));
                $graph_data[$series_key] = array();
                foreach ($response['dps'] as $timestamp => $value) {
                    if (($timestamp < $this->start_timestamp) || ($timestamp > $this->end_timestamp)) {
                        continue;
                    }
                    $graph_data[$series_key][] = array('timestamp' => $timestamp, 'value' => $value);
                }
            }
        } else {
            foreach ($opentsdb_data as $line) {
                if (strlen($line) === 0) {
                    continue;
                }
                $data_fields = explode(' ', $line);
                $metric_name = array_shift($data_fields);
                $timestamp = array_shift($data_fields);
                $value = array_shift($data_fields);
                if ($query_bits['history_graph'] === "no") {
                    if (!empty($data_fields)) {
                        $metric_tag_key = implode(' ', $data_fields);
                    }
                    else {
                        $metric_tag_key = '';
                    }
                }

                $series_key = $metric_name . ' ' . $metric_tag_key;

                if (($timestamp < $this->start_timestamp) || ($timestamp > $this->end_timestamp)) {
                    continue;
                }

                if (!isset($graph_data[$series_key])) {
                    $this->sw['logger']->addDebug(sprintf("Found new series: %s", $series_key));
                    $graph_data[$series_key] = array();
                }
                $graph_data[$series_key][] = array('timestamp' => $timestamp, 'value' => $value);
            }
        }

        $graph_legend = $this->_normalize_legend(array_keys($graph_data), $search_metrics);

        foreach ($graph_data as $series => $incoming_data) {
            $series_timestamps = array();
            $series_values = array();
            $series_parts = explode(' ', $series);
            $series_metric = $series_parts[0];
            foreach ($incoming_data as $data_key => $data_row) {
                $series_timestamps[$data_key] = $data_row['timestamp'];
                $series_values[$data_key] = $data_row['value'];
            }
            $this->sw['logger']->addDebug(sprintf(
                "Sorting data for %s, %d timestamps, %d values",
                $series,
                count($series_timestamps),
                count($series_values)
            ));
            array_multisort($series_timestamps, SORT_ASC, $series_values, SORT_ASC, $incoming_data);

            $graph_data[$series] = $incoming_data;
        }

        $this->opentsdb_data = $graph_data;
        $this->opentsdb_data['query_url'] = $this->opentsdb_query_url;
        $this->opentsdb_data['start'] = $this->start_time;
        $this->opentsdb_data['end'] = $this->end_time;
        $this->opentsdb_data['legend'] = $graph_legend;

        return;
    }

    protected function _normalize_legend($raw_legends, $query_metrics) {

        $save_metric_names = false;
        $metrics = array();
        $legend_pool = array();

        $this->sw['logger']->addDebug("Normalizing legend");
        $this->sw['logger']->addDebug(implode(', ', $raw_legends));

        $query_metrics_tags = array();

        foreach ($query_metrics as $metric => $metric_tags) {
            $query_metrics_tags[$metric] = array();
            foreach ($metric_tags as $mtag) {
                $tag_bits = explode('=', $mtag);
                array_push($query_metrics_tags[$metric], $tag_bits[0]);
            }
        }

        foreach ($raw_legends as $raw_bits) {
            $legend_pool[$raw_bits] = explode(' ', $raw_bits);
        }

        $this->sw['logger']->addDebug(sprintf("Metrics: %s (%d metrics)", implode(', ', array_keys($query_metrics)), count($query_metrics)));
        $this->sw['logger']->addDebug(sprintf("All the legend bits: %s", json_encode($legend_pool)));
        $this->sw['logger']->addDebug(sprintf("Query tags: %s", json_encode($query_metrics_tags)));

        foreach ($legend_pool as $series => $legend_bits) {
            $legend_string = '';
            $legend_metric_name = array_shift($legend_bits);
            $legend_string = $legend_metric_name . ' ';
            foreach ($legend_bits as $tag) {
                $tag_bits = explode('=', $tag);
                if (in_array($tag_bits[0], $query_metrics_tags[$legend_metric_name])) {
                    $legend_string = $legend_string . $tag . ' ';
                }
            }

            $legends[$series] = trim($legend_string);
        }

        return $legends;

    }

    public function read($query_key = null) {

        if (!empty($this->opentsdb_data)) {
            if (isset($query_key)) {
                return $this->opentsdb_data[$query_key];
            } else {
                return $this->opentsdb_data;
            }
        } else {
            return null;
        }

    }

    public function read_json($query_key = null) {

        if (!empty($this->opentsdb_data)) {
            if (isset($query_key)) {
                return json_encode($this->opentsdb_data[$query_key]);
            } else {
                return json_encode($this->opentsdb_data);
            }
        } else {
            return null;
        }

    }

    public function read_csv($query_key = null) {

        if (!empty($this->opentsdb_data)) {
            if (isset($query_key)) {
                $output = "timestamp,value\n";
                foreach ($this->opentsdb_data[$query_key] as $data) {
                    $output .= $data['timestamp'] . ',' . $data['value'] . "\n";
                }
            } else {
                $output = "key,timestamp,value\n";
                foreach ($this->opentsdb_data as $key => $all_data) {
                    foreach ($all_data as $data) {
                        $output .= $key . ',' . $data['timestamp'] . ',' . $data['value'] . "\n";
                    }
                }
            }
            return $output;
        } else {
            return null;
        }

    }

    public function get_start() {

        if (!empty($this->start_timestamp)) {
            return $this->start_timestamp;
        } else {
            return null;
        }

    }

    public function get_end() {

        if (!empty($this->end_timestamp)) {
            return $this->end_timestamp;
        } else {
            return null;
        }

    }

}
