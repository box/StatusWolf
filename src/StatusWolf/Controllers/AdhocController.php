<?php
/**
 * AdhocController
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 29 April 2014
 *
 */

namespace StatusWolf\Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use StatusWolf\Security\User\SWUser;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\HttpFoundation\Request;

class AdhocController implements ControllerProviderInterface {

    public function connect(Application $sw) {
        $controllers = $sw['controllers_factory'];

        $controllers->get('/', function(Application $sw) {
            return $sw->redirect('/adhoc/OpenTSDB');
        })->bind('adhoc_default');

        $controllers->get('/saved/{id}', function(Application $sw, $id) {
            return $sw->redirect('/adhoc/OpenTSDB/saved/' . $id);
        })->bind('adhoc_saved_redirect');

        $controllers->get('/shared/{id}', function(Application $sw, $id) {
            return $sw->redirect('/adhoc/OpenTSDB/shared/' . $id);
        })->bind('adhoc_shared_redirect');

        $controllers->get('/{datasource}', function(Application $sw, $datasource) {
            $user_token = $sw['security']->getToken();
            $user = $user_token->getUser();
            $username = $user instanceof SWUser ? $user->getUsername() : $user;
            $fullname = $user instanceof SWUser ? $user->getFullName() : '';
            $user_id = $user instanceof SWUser ? $user->getId() : '0';
            $widgets = $sw['get_widgets'];
            $widget_datasources = array();
            foreach ($widgets as $type => $config) {
                $widget_datasources[] = $config['datasource'];
            }
            $datasource_key = $datasource . 'Widget';
            if (array_key_exists($datasource_key, $widgets)) {
                $adhoc_widget = Array();
                $adhoc_widget[$datasource_key] = $widgets[$datasource_key];
                $adhoc_widget_json = json_encode($adhoc_widget);
                return $sw['twig']->render('adhoc.html', array(
                    'username'           => $username,
                    'fullname'           => $fullname,
                    'user_id'            => $user_id,
                    'd3'                 => $sw['sw_config.config']['graphing']['d3_location'],
                    'datasource'         => $datasource,
                    'datasource_options' => json_encode($widget_datasources),
                    'widget_type'        => $datasource_key,
                    'adhoc_widget'       => $adhoc_widget,
                    'adhoc_widget_json'  => $adhoc_widget_json,
                    'extra_css'          => array('adhoc.css',),
                    'extra_js'           => array(
                        'sw_lib.js',
                        'lib/jquery-ui.js',
                        'lib/date.js',
                        'lib/md5.js',
                        'status_wolf_colors.js',
                        'lib/jquery.autocomplete.js',
                        'lib/jquery.dataTables.min.js',
                        'lib/bootstrap-datetimepicker.js'
                    ),
                    'sw_debug' => $sw['sw_config.config']['sw_app']['debug'] ? 1 : 0,
                ));
            }
        })->bind('adhoc_datasource');

        $controllers->get('/{datasource}/saved/{id}', function(Application $sw, $datasource, $id) {
            $user_token = $sw['security']->getToken();
            $user = $user_token->getUser();
            $username = $user instanceof SWUser ? $user->getUsername() : $user;
            $fullname = $user instanceof SWUser ? $user->getFullName() : '';
            $user_id = $user instanceof SWUser ? $user->getId() : '0';
            $widgets = $sw['get_widgets'];
            $widget_datasources = array();
            foreach ($widgets as $type => $config) {
                $widget_datasources[] = $config['datasource'];
            }
            $datasource_key = $datasource . 'Widget';
            if (array_key_exists($datasource_key, $widgets)) {
                $adhoc_widget = Array();
                $adhoc_widget[$datasource_key] = $widgets[$datasource_key];
                $adhoc_widget_json = json_encode($adhoc_widget);
                return $sw['twig']->render('adhoc.html', array(
                    'username'           => $username,
                    'fullname'           => $fullname,
                    'user_id'            => $user_id,
                    'd3'                 => $sw['sw_config.config']['graphing']['d3_location'],
                    'datasource'         => $datasource,
                    'datasource_options' => json_encode($widget_datasources),
                    'widget_type'        => $datasource_key,
                    'adhoc_widget'       => $adhoc_widget,
                    'adhoc_widget_json'  => $adhoc_widget_json,
                    'search_type'        => 'saved',
                    'search_id'          => $id,
                    'extra_css'          => array('adhoc.css',),
                    'extra_js'           => array(
                        'sw_lib.js',
                        'status_wolf_colors.js',
                        'lib/jquery-ui.js',
                        'lib/date.js',
                        'lib/md5.js',
                        'lib/jquery.autocomplete.js',
                        'lib/jquery.dataTables.min.js',
                        'lib/bootstrap-datetimepicker.js'
                    ),
                    'sw_debug' => $sw['sw_config.config']['sw_app']['debug'] ? 1 : 0,
                ));
            }
        })->bind('adhoc_saved_search');

        $controllers->get('/{datasource}/shared/{id}', function(Application $sw, $datasource, $id) {
            $user_token = $sw['security']->getToken();
            $user = $user_token->getUser();
            $username = $user instanceof SWUser ? $user->getUsername() : $user;
            $fullname = $user instanceof SWUser ? $user->getFullName() : '';
            $user_id = $user instanceof SWUser ? $user->getId() : '0';
            $widgets = $sw['get_widgets'];
            $widget_datasources = array();
            foreach ($widgets as $type => $config) {
                $widget_datasources[] = $config['datasource'];
            }
            $datasource_key = $datasource . 'Widget';
            if (array_key_exists($datasource_key, $widgets)) {
                $adhoc_widget = Array();
                $adhoc_widget[$datasource_key] = $widgets[$datasource_key];
                $adhoc_widget_json = json_encode($adhoc_widget);
                return $sw['twig']->render('adhoc.html', array(
                    'username'           => $username,
                    'fullname'           => $fullname,
                    'user_id'            => $user_id,
                    'd3'                 => $sw['sw_config.config']['graphing']['d3_location'],
                    'datasource'         => $datasource,
                    'datasource_options' => json_encode($widget_datasources),
                    'widget_type'        => $datasource_key,
                    'adhoc_widget'       => $adhoc_widget,
                    'adhoc_widget_json'  => $adhoc_widget_json,
                    'search_type'        => 'shared',
                    'search_id'          => $id,
                    'extra_css'          => array('adhoc.css',),
                    'extra_js'           => array(
                        'sw_lib.js',
                        'status_wolf_colors.js',
                        'lib/jquery-ui.js',
                        'lib/date.js',
                        'lib/md5.js',
                        'lib/jquery.autocomplete.js',
                        'lib/jquery.dataTables.min.js',
                        'lib/bootstrap-datetimepicker.js'
                    ),
                    'sw_debug' => $sw['sw_config.config']['sw_app']['debug'] ? 1 : 0,
                ));
            }
        })->bind('adhoc_shared_search');

        return $controllers;
    }

}
