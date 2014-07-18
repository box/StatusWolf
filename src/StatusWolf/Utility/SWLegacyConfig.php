<?php
/**
 * SWLegacyConfig
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 07 April 2014
 *
 */

namespace StatusWolf\Utility;

use Silex\Application;

class SWLegacyConfig {

    protected $_config_values = array();

    public function __construct(Application $sw) {
        $this->_logger = $sw['logger'];
    }

    public function load_legacy_config($config_file, $category = null) {
        $file = CFG . $config_file;
        $this->_logger->addDebug("Loading config from " . $config_file);
        $contents = parse_ini_file($file, true);
        if (empty($category)) {
            $file_bits = explode('.', $config_file);
            $category = $file_bits[0];
        }
        $this->write_values($category, $contents);

    }

    public function read_values() {
        return $this->_config_values;
    }

    public function write_values($category, $config, $config_value = null) {
        if (!is_array($config)) {
            $this->_config_values[$category][$config] = $config_value;
        } else {
            foreach ($config as $name => $value) {
                $this->_config_values[$category][$name] = $value;
            }
        }
    }
}
