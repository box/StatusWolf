<?php
/**
 * DashboardController
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 3 March 2014
 *
 */

namespace StatusWolf\Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use StatusWolf\Security\User\SWUser;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\HttpFoundation\Request;

class DashboardController implements ControllerProviderInterface {

    public function connect(Application $sw) {
        $controllers = $sw['controllers_factory'];

        $controllers->get('/', function(Application $sw) {
            $user_token = $sw['security']->getToken();
            $user = $user_token->getUser();
            $username = $user instanceof SWUser ? $user->getUsername() : $user;
            $fullname = $user instanceof SWUser ? $user->getFullName() : '';
            $user_id = $user instanceof SWUser ? $user->getId() : '0';
            $user_authenticated = $user instanceof SWUser ? true : false;
            $widgets = $sw['get_widgets'];
            $widgets_json = json_encode($widgets);
            return $sw['twig']->render('dashboard.html', array(
                'username' => $username,
                'fullname' => $fullname,
                'user_id' => $user_id,
                'user_authenticated' => $user_authenticated,
                'extra_css' => array('dashboard.css',),
                'extra_js' => array(
                    'sw_lib.js',
                    'lib/jquery-ui.js',
                    'lib/date.js',
                    'lib/md5.js',
                    'status_wolf_colors.js',
                    'lib/jquery.autocomplete.js',
                    'lib/jquery.dataTables.min.js',
                    'lib/bootstrap-datetimepicker.js'
                ),
                'd3' => $sw['sw_config.config']['graphing']['d3_location'],
                'widgets' => $widgets,
                'widgets_json' => $widgets_json,
                'dashboard_id' => false,
                'sw_debug' => $sw['sw_config.config']['sw_app']['debug'] ? 1 : 0,
            ));
        })->bind('dashboard_home');

        $controllers->get('/{id}', function(Application $sw, Request $request, $id) {
            $user_token = $sw['security']->getToken();
            $user = $user_token->getUser();
            $username = $user instanceof SWUser ? $user->getUsername() : $user;
            $fullname = $user instanceof SWUser ? $user->getFullName() : '';
            $user_id = $user instanceof SWUser ? $user->getId() : '0';
            $user_authenticated = $user instanceof SWUser ? true : false;
            $widgets = $sw['get_widgets'];
            $widgets_json = json_encode($widgets);
            $query_data = $request->query->all();
            // @TODO: genericize this - should not be datasource specific
            if (count($query_data) > 0) {
                $opentsdb_tags = json_encode($query_data);
            } else {
                $opentsdb_tags = null;
            }
            return $sw['twig']->render('dashboard.html', array(
                'username' => $username,
                'fullname' => $fullname,
                'user_id' => $user_id,
                'user_authenticated' => $user_authenticated,
                'extra_css' => array('dashboard.css',),
                'extra_js' => array(
                    'sw_lib.js',
                    'lib/jquery-ui.js',
                    'lib/date.js',
                    'lib/md5.js',
                    'status_wolf_colors.js',
                    'lib/jquery.autocomplete.js',
                    'lib/jquery.dataTables.min.js',
                    'lib/bootstrap-datetimepicker.js'
                ),
                'd3' => $sw['sw_config.config']['graphing']['d3_location'],
                'widgets' => $widgets,
                'widgets_json' => $widgets_json,
                'dashboard_id' => $id,
                'sw_debug' => $sw['sw_config.config']['sw_app']['debug'] ? 1 : 0,
                'opentsdb_tags' => $opentsdb_tags,
            ));
        })->bind('dashboard_load');

        return $controllers;
    }

}
