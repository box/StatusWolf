<?php
/**
 * ApiSearchController
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 28 June 2014
 *
 */

namespace StatusWolf\Controllers;

use PDO;
use Silex\Application;
use Silex\ControllerProviderInterface;
use StatusWolf\Security\User\SWUser;
use Symfony\Component\HttpFoundation\Request;

class ApiSearchController implements ControllerProviderInterface {

    public function connect(Application $sw) {
        $controllers = $sw['controllers_factory'];

        $controllers->get('/saved', function(Application $sw) {
            $user_token = $sw['security']->getToken();
            $user = $user_token->getUser();
            $user_id = $user instanceof SWUser ? $user->getId() : '0';

            $_saved_searches = array();

            $sql = "SELECT * FROM saved_searches WHERE user_id='$user_id' AND shared=0";
            $sw['logger']->addDebug(sprintf('Loading saved searches for user_id %s', $user_id));
            $user_search_query = $sw['db']->prepare($sql);
            $user_search_query->execute();
            $_saved_searches['user_searches'] = array();
            while($user_searches = $user_search_query->fetch()) {
                array_push($_saved_searches['user_searches'], array(
                    'id'         => $user_searches['id'],
                    'title'      => $user_searches['title'],
                    'datasource' => $user_searches['data_source']
                ));
            }

            $sql = "SELECT ss.*, u.username FROM saved_searches ss, users u WHERE ss.user_id=u.id AND shared=1";
            $shared_search_query = $sw['db']->prepare($sql);
            $shared_search_query->execute();
            $_saved_searches['shared_searches'] = array();
            while($shared_search = $shared_search_query->fetch()) {
                array_push($_saved_searches['shared_searches'], array(
                    'id'         => $shared_search['id'],
                    'user_id'    => $shared_search['user_id'],
                    'username'   => $shared_search['username'],
                    'title'      => $shared_search['title'],
                    'datasource' => $shared_search['data_source']
                ));
            }

            return json_encode($_saved_searches);

        });

        $controllers->get('/saved/user', function(Application $sw) {
            $user_token = $sw['security']->getToken();
            $user = $user_token->getUser();
            $user_id = $user instanceof SWUser ? $user->getId() : '0';
            $_user_saved_searches = array();

            $sql = "SELECT * FROM saved_searches WHERE user_id='$user_id'";
            $user_search_query = $sw['db']->prepare($sql);
            $user_search_query->execute();
            while($search_result = $user_search_query->fetch()) {
                array_push($_user_saved_searches, array(
                    'id'         => $search_result['id'],
                    'title'      => $search_result['title'],
                    'datasource' => $search_result['data_source'],
                    'shared'     => $search_result['shared']
                ));
            }

            return json_encode($_user_saved_searches);
        });

        $controllers->get('/saved/{datasource}', function(Application $sw, $datasource) {
            $user_token = $sw['security']->getToken();
            $user = $user_token->getUser();
            $user_id = $user instanceof SWUser ? $user->getId() : '0';
            $_saved_searches = array();

            $sql = "SELECT * FROM saved_searches WHERE user_id='$user_id' AND data_source='$datasource' AND shared=0";
            $user_search_query = $sw['db']->prepare($sql);
            $user_search_query->execute();
            $_saved_searches['user_searches'] = array();
            while($user_searches = $user_search_query->fetch()) {
                array_push($_saved_searches['user_searches'], array(
                    'id'    => $user_searches['id'],
                    'title' => $user_searches['title']));
            }

            $sql = "SELECT ss.*, u.username FROM saved_searches ss, users u WHERE data_source='$datasource' AND ss.user_id=u.id AND ss.shared=1";
            $shared_search_query = $sw['db']->prepare($sql);
            $shared_search_query->execute();
            $_saved_searches['shared_searches'] = array();
            while($shared_searches = $shared_search_query->fetch()) {
                array_push($_saved_searches['shared_searches'], array(
                    'id'       => $shared_searches['id'],
                    'user_id'  => $shared_searches['user_id'],
                    'username' => $shared_searches['username'],
                    'title'    => $shared_searches['title']));
            }

            return json_encode($_saved_searches);

        });

        $controllers->get('/saved/{datasource}/{search_id}', function(Application $sw, $datasource, $search_id) {

            $user_token = $sw['security']->getToken();
            $user = $user_token->getUser();
            $user_id = $user instanceof SWUser ? $user->getId() : '0';

            $sql = "SELECT * FROM saved_searches WHERE id='$search_id' AND data_source='$datasource'";
            $saved_search_query = $sw['db']->prepare($sql);
            $saved_search_query->execute();
            if (!$saved_search = $saved_search_query->fetch(PDO::FETCH_ASSOC)) {
                $saved_search_config = 'Not Found';
            } elseif ($saved_search['shared'] === 0 && $user_id !== $saved_search['user_id']) {
                $saved_search_config = 'Not Allowed';
            } else {
                $serialized_search = $saved_search['search_params'];
                $saved_search_config = unserialize($serialized_search);
            }

            return json_encode(array('search_owner' => $saved_search['user_id'], 'search_config' => $saved_search_config));

        });

        $controllers->post('/saved/{datasource}/{search_id}', function(Application $sw, Request $request, $datasource, $search_id) {

            $query_data = $request->request->get('search_config');
            if ($query_data['save_search_times'] == 1) {
                if (array_key_exists('time_span', $query_data['search'])) {
                    unset($query_data['search']['time_span']);
                }
            } else {
                unset($query_data['search']['start_time']);
                unset($query_data['search']['end_time']);
            }
            $search_string = serialize($query_data['search']);
            $sw['logger']->addDebug(json_encode($query_data));
            $sw['logger']->addDebug('confirmation? ' . $query_data['confirmation']);
            if ($query_data['confirmation'] === "false") {
                $sw['logger']->addDebug('checking for existing search with that title');
                $check_title_sql = 'SELECT * FROM saved_searches WHERE title=? AND user_id=?';
                $title_query = $sw['db']->prepare($check_title_sql);
                $title_query->bindValue(1, $query_data['search']['title']);
                $title_query->bindValue(2, $query_data['user_id']);
                $title_query->execute();
                if ($title_result = $title_query->fetch()) {
                    $sw['logger']->addDebug('Search title already exists');
                    return $sw->json(array('query_result' => 'Error', 'query_info' => 'Title', 'query_data' => $title_result['id']));
                }

                $sw['logger']->addDebug('Checking for existing search with ID ' . $search_id . ', data_source ' . $datasource . ' and user_id ' . $query_data['user_id']);
                $check_id_sql = 'SELECT * FROM saved_searches WHERE id=? AND data_source=? AND user_id=?';
                $id_query = $sw['db']->prepare($check_id_sql);
                $id_query->bindValue(1, $search_id);
                $id_query->bindValue(2, $datasource);
                $id_query->bindValue(3, $query_data['user_id']);
                $id_query->execute();
                if ($id_result = $id_query->fetchAll()) {
                    $sw['logger']->addDebug('Search ID already exists');
                    return $sw->json(array('query_result' => 'Error', 'query_info' => 'ID'));
                }

            }

            try {
                $sw['logger']->addDebug('search parameters: id = ' . $search_id . ', title = ' . $query_data['search']['title'] . ', user_id = ' . $query_data['user_id'] . ', shared = ' . $query_data['shared'] . ', datasource = ' . $datasource);
                $sw['logger']->addDebug($search_string);
                $row_count = $sw['db']->executeUpdate('REPLACE INTO saved_searches SET id = ?, title = ?, user_id = ?, shared = ?, search_params = ?, data_source = ?',
                    array(
                        $search_id,
                        $query_data['search']['title'],
                        $query_data['user_id'],
                        $query_data['shared'],
                        $search_string,
                        $datasource
                    )
                );
                $sw['logger']->addDebug('Update complete: ' . $row_count . ' rows updated');
            } catch(\PDOException $e) {
                $sw['logger']->addInfo(sprintf('Failed to save search %s (%s)', $search_id, $query_data['search']['title']));
                $sw['logger']->addDebug($e->getMessage());
                return $sw->json(array('query_result' => 'Error', 'query_info' => $e->getMessage()));
            }
            return $sw->json(array('query_result' => 'Success'));

        });

        $controllers->get('/shared/{datasource}/{search_id}', function(Application $sw, $datasource, $search_id) {

            $expiration = time() - 86400;

            $sql = "SELECT * FROM shared_searches WHERE search_id = ? AND data_source='?'";
            $shared_search_query = $sw['db']->prepare($sql);
            $shared_search_query->bindValue(1, $search_id);
            $shared_search_query->bindValue(2, $datasource);
            $shared_search_query->execute();
            if (!$shared_search = $shared_search_query->fetch(PDO::FETCH_ASSOC)) {
                $shared_search_config = 'Not Found';
            } else if ($shared_search['timestamp'] < $expiration) {
                $expiry_query = $sw['db']->prepare("DELETE FROM shared_searches WHERE search_id = ? AND data_source='$datasource'");
                $expiry_query->bindValue(1, $search_id);
                $expiry_query->execute();
                $shared_search_config = 'Expired';
            } else {
                $serialized_search = $shared_search['search_params'];
                $shared_search_config = unserialize($serialized_search);
            }

            return json_encode($shared_search_config);

        });

        return $controllers;
    }

}
