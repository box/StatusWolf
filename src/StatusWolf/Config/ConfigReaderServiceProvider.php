<?php
/**
 * ConfigServiceProvider
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 27 February 2014
 *
 */

namespace StatusWolf\Config;

use Silex\Application;
use Silex\ServiceProviderInterface;
use StatusWolf\Config\ConfigReaderService;

class ConfigReaderServiceProvider implements ServiceProviderInterface {

    public function register(Application $sw) {

        $sw['sw_config.config'] = array();

        $sw['sw_config'] = $sw->share(function($sw) {
            return new ConfigReaderService();
        });

    }

    public function boot(Application $sw) {}

}
