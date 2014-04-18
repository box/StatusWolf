<?php
/**
 * ApiAnomalyController
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 17 March 2014
 *
 */

namespace StatusWolf\Controllers;

use Arugula\TimeSeries\AnomalyDetector;
use Silex\Application;
use Silex\ControllerProviderInterface;
use StatusWolf\Model\OpenTSDBDataSource;
use Symfony\Component\HttpFoundation\Request;

class ApiAnomalyController implements ControllerProviderInterface {

    public function connect(Application $sw) {

        $controllers = $sw['controllers_factory'];

        $controllers->post('/time-series', function(Application $sw, Request $request) {
            ini_set('max_input_vars', 1440);

            $anomaly_query = $request->request->all();
            $time_series_data = $anomaly_query['ts_data'];
            $series_key = key($time_series_data);
            $anomaly_query['query_data']['end_time'] = $time_series_data[$series_key][0]['timestamp'] - 1;
            $anomaly_query['query_data']['start_time'] = $anomaly_query['query_data']['end_time'] - WEEK;
            $sw['logger']->addDebug(sprintf(
                "Setting pre-period to %s - %s",
                $anomaly_query['query_data']['start_time'],
                $anomaly_query['query_data']['end_time']
            ));
            $sw['logger']->addDebug("Initiating pre-period query");
            $sw['logger']->addDebug("Query: " . json_encode($anomaly_query['query_data']));
            $opentsdb = new OpenTSDBDataSource($sw);
            $opentsdb->get_metric_data($anomaly_query['query_data']);
            $pre_period_data = $opentsdb->read();
            $sw['logger']->addDebug("Pre anomaly period data fetch successful, creating anomaly detection object");
            $anomaly_detector = new AnomalyDetector();
            $anomaly_data = $anomaly_detector->detect_anomalies(array('current_data' => $time_series_data[$series_key], 'previous_data' => $pre_period_data[$series_key]));
            $sw['logger']->addDebug("Anomaly detection complete");

            return json_encode($anomaly_data);

        });

        return $controllers;
    }
}
