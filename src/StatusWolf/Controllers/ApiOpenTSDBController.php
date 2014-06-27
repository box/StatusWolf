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

            if (in_array('url', $opentsdb_config) && is_array($opentsdb_config['url'])) {
                $tsdb_host = $opentsdb_config['url'][array_rand($opentsdb_config['url'])];
            } else {
                throw new InvalidConfigurationException('No OpenTSDB host list found in the datasource config');
            }

            $suggestion_url = 'http://' . $tsdb_host . '/q?start=1h-ago&m=sum:rate:' . $metric . '&json';
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

        $controllers->get('/saved_searches/{user_id}', function(Application $sw, Request $request, $user_id) {
            $_saved_searches = array();

            $sql = "SELECT * FROM saved_searches WHERE user_id = ? AND data_source='OpenTSDB' AND shared=0";
            $user_search_query = $sw['db']->prepare($sql);
            $user_search_query->bindValue(1, $user_id);
            $user_search_query->execute();
            $_saved_searches['user_searches'] = array();
            while($user_searches = $user_search_query->fetch()) {
                array_push($_saved_searches['user_searches'], array('id' => $user_searches['id'], 'title' => $user_searches['title']));
            }

            $sql = "SELECT ss.*, u.username FROM saved_searches ss, users u WHERE ss.user_id=u.id AND ss.shared=1";
            $shared_search_query = $sw['db']->prepare($sql);
            $shared_search_query->execute();
            $_saved_searches['shared_searches'] = array();
            while($shared_searches = $shared_search_query->fetch()) {
                array_push($_saved_searches['shared_searches'], array('id' => $shared_searches['id'], 'user_id' => $shared_searches['user_id'], 'username' => $shared_searches['username'], 'title' => $shared_searches['title']));
            }

            return json_encode($_saved_searches);

        });

        $controllers->get('/saved/{search_id}', function(Application $sw, $search_id) {

            $user_token = $sw['security']->getToken();
            $user = $user_token->getUser();
            $user_id = $user instanceof SWUser ? $user->getId() : '0';

            $sql = "SELECT * FROM saved_searches WHERE id = ? AND data_source='OpenTSDB'";
            $saved_search_query = $sw['db']->prepare($sql);
            $saved_search_query->bindValue(1, $search_id);
            $saved_search_query->execute();
            if (!$saved_search = $saved_search_query->fetch(PDO::FETCH_ASSOC)) {
                $saved_search_config = 'Not Found';
            } elseif ($saved_search['shared'] === 0 && $user_id !== $saved_search['user_id']) {
                $saved_search_config = 'Not Allowed';
            } else {
                $serialized_search = $saved_search['search_params'];
                $saved_search_config = unserialize($serialized_search);
            }

            return json_encode($saved_search_config);

        });

        $controllers->get('/shared/{search_id}', function(Application $sw, $search_id) {

            $expiration = time() - 86400;

            $sql = "SELECT * FROM shared_searches WHERE search_id = ? AND data_source='OpenTSDB'";
            $shared_search_query = $sw['db']->prepare($sql);
            $shared_search_query->bindValue(1, $search_id);
            $shared_search_query->execute();
            if (!$shared_search = $shared_search_query->fetch(PDO::FETCH_ASSOC)) {
                $shared_search_config = 'Not Found';
            } else if ($shared_search['timestamp'] < $expiration) {
                $expiry_query = $sw['db']->prepare("DELETE FROM shared_searches WHERE search_id = ? AND data_source='OpenTSDB'");
                $expiry_query->bindValue(1, $search_id);
                $expiry_query->execute();
                $shared_search_config = 'Expired';
            } else {
                $serialized_search = $shared_search['search_params'];
                $shared_search_config = unserialize($serialized_search);
            }

            return json_encode($shared_search_config);

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
