<?php
/**
 * ConfigWriterServiceProvider
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 13 March 2014
 *
 */

namespace StatusWolf\Config;

use Silex\Application;
use Silex\ServiceProviderInterface;
use StatusWolf\Config\ConfigWriterService;

class ConfigWriterServiceProvider implements ServiceProviderInterface {

    public function register(Application $sw) {

        $sw['sw_config.writer'] = $sw->share(function($sw) {
            return new ConfigWriterService();
        });
    }

    public function boot(Application $sw) {}
}
