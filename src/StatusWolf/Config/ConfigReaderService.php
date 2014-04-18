<?php
/**
 * ConfigReaderService
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 5 March 2014
 *
 */

namespace StatusWolf\Config;

use Silex\Application;

class ConfigReaderService {

    private $_config_reader;

    public function __construct($reader_type = 'json') {

        if ($reader_type === "json") {
            $this->_config_reader = new JsonConfigReader();
        } else {
            throw new \InvalidArgumentException(sprintf("%s is not a valid config type", $reader_type));
        }
    }

    private function _merge_config($sw_config, array $config) {
        foreach ($config as $key => $value) {
            if (isset($sw_config[$key]) && is_array($value)) {
                $sw_config[$key] = $this->_merge_config_recursive($sw_config[$key], $value);
            } else {
                $sw_config[$key] = $value;
            }
        }

        return $sw_config;
    }

    private function _merge_config_recursive(array $current_value, array $new_value) {
        foreach ($new_value as $key => $value) {
            if (is_array($value) && isset($current_value[$key])) {
                $current_value[$key] = $this->_merge_config_recursive($current_value[$key], $value);
            } else {
                $current_value[$key] = $value;
            }
        }
        return $current_value;
    }

    public function read_config_file(Application $sw, $filename = false) {

        if (!$filename) {
            throw new \RuntimeException('A valid config file name must be provided.');
        }

        if (!file_exists($filename)) {
            throw new \InvalidArgumentException(
                sprintf("Config file %s does not exist.", $filename)
            );
        }

        if ($this->_config_reader->understands($filename)) {
            $config = $this->_config_reader->read($filename);
            $sw['sw_config.config'] = $this->_merge_config($sw['sw_config.config'], $config);
        } else {
            throw new \InvalidArgumentException(
                sprintf("Config file %s is invalid.", $filename)
            );
        }

    }

}
