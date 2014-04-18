<?php
/**
 * ApiSessionController
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 4 March 2014
 *
 */

namespace StatusWolf\Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class ApiSessionController implements ControllerProviderInterface {

    public function connect(Application $sw) {

        $controllers = $sw['controllers_factory'];

        $controllers->put('/', function(Application $sw, Request $request) {
            $sw['logger']->addDebug('Request: ' . json_encode($request->request->all()));
            $session_data_key = $request->get('session_data_key');
            $session_data_value = $request->get('session_data_value');
            $sw['session']->set($session_data_key, $session_data_value);
            $sw['logger']->addDebug($session_data_key . ' = ' . $sw['session']->get($session_data_key));
            return true;
        })->bind('api_session_put');

        return $controllers;
    }

}
