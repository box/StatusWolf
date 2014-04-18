<?php
/**
 * ApiDatasourceController
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 5 March 2014
 *
 */

namespace StatusWolf\Controllers;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class ApiDatasourceController implements ControllerProviderInterface {

    public function connect(Application $sw) {

        $sw->mount('/api/datasource/opentsdb', new ApiOpenTSDBController());

        $controllers = $sw['controllers_factory'];

        return $controllers;
    }
}
