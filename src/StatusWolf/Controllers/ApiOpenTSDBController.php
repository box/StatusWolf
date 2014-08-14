<?php
/**
 * ApiOpenTSDBController
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 5 March 2014
 *
 */

namespace StatusWolf\Controllers;

use PDO;
use Silex\Application;
use Silex\ControllerProviderInterface;
use StatusWolf\Exception\ApiNetworkFetchException;
use StatusWolf\Exception\InvalidConfigurationException;
use StatusWolf\Model\OpenTSDBDataSource;
use StatusWolf\Network\Curl;
use StatusWolf\Security\User\SWUser;
use Symfony\Component\HttpFoundation\Request;

class ApiOpenTSDBController implements ControllerProviderInterface {

    public function connect(Application $sw) {

        $controllers = $sw['controllers_factory'];

        $controllers->get('/suggest_metrics', function(Application $sw, Request $request) {
            $metric_stub = $request->get('query');
            $opentsdb_config = $sw['sw_config.config']['datasource']['OpenTSDB'];

            if (in_array('url', $opentsdb_config) && is_array($opentsdb_config['url'])) {
                $tsdb_host = $opentsdb_config['url'][array_rand($opentsdb_config['url'])];
            } else {
                throw new InvalidConfigurationException('No OpenTSDB Host found in the datasource config');
            }

            $suggestion_url = 'http://' . $tsdb_host . '/suggest?type=metrics&q=';

            $curl = new Curl($sw, $suggestion_url . $metric_stub, $opentsdb_config['proxy'], $opentsdb_config['proxy_url']);

            try {
                $response_data = json_decode($curl->request());
            } catch (ApiNetworkFetchException $e) {
                $sw['logger']->addError(sprintf("Failed to retrieve metric name suggestion for %s", $metric_stub));
                $sw['logger']->addError($e->getMessage());
                $response_data = array();
            }

            $suggestion = array();
            $suggestion['query'] = $metric_stub;
            if (count($response_data) > 20) {
                $response_data = array_slice($response_data, 0, 20);
            }
            $suggestion['suggestions'] = $response_data;

            return json_encode($suggestion);

        });

        $controllers->get('/suggest_tags', function(Application $sw, Request $request) {
            $metric = $request->get('metric');
            $opentsdb_config = $sw['sw_config.config']['datasource']['OpenTSDB'];
            if (in_array('api_version', $opentsdb_config)) {
                $api_version = $opentsdb_config['api_version'];
            } else {
                $api_version = 1;
            }

            if (in_array('url', $opentsdb_config) && is_array($opentsdb_config['url'])) {
                $tsdb_host = $opentsdb_config['url'][array_rand($opentsdb_config['url'])];
            } else {
                throw new InvalidConfigurationException('No OpenTSDB host list found in the datasource config');
            }

            if ($api_version == 2) {
                $suggestion_url = 'http://' . $tsdb_host . '/api/query?start=1h-ago&m=sum:rate:' . $metric;
            } else {
                $suggestion_url = 'http://' . $tsdb_host . '/q?start=1h-ago&m=sum:rate:' . $metric . '&json';
            }
            $curl = new Curl($sw, $suggestion_url, $opentsdb_config['proxy'], $opentsdb_config['proxy_url']);

            try {
                $response_data = json_decode($curl->request());
            } catch(ApiNetworkFetchException $e) {
                $sw['logger']->addError(sprintf("Failed to retrieve tag suggestions for %s", $metric));
                $sw['logger']->addError($e->getMessage());
                $response_data = array();
            }

            $suggestion = array();
            $suggestion['metric'] = $metric;
            $suggestion['suggestions'] = array();
            if (is_object($response_data)) {
                if (property_exists($response_data, 'etags')) {
                    $suggestion['suggestions'] = $response_data->etags[0];
                }
            }

            return json_encode($suggestion);

        });

        $controllers->post('/search', function(Application $sw, Request $request) {
            $query_data = $request->request->get('query_data');
            $sw['logger']->addDebug(sprintf("Query: %s", json_encode($query_data)));
            $opentsdb = new OpenTSDBDataSource($sw);
            $opentsdb->get_metric_data($query_data);
            $opentsdb_data = $opentsdb->read();
            return json_encode($opentsdb_data);
        });

        return $controllers;
    }
}
