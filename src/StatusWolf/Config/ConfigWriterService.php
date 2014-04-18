<?php
/**
 * ConfigWriterService
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 13 March 2014
 *
 */

namespace StatusWolf\Config;

use Silex\Application;
use StatusWolf\Exception\ConfigWriterException;

class ConfigWriterService {

    private $_config_writer;
    private $_config_path;

    public function __construct($writer_type = 'json', $config_path = false) {

        if ($writer_type === "json") {
            $this->_config_path = $config_path ?: __DIR__ . '/../../../conf/';
            $this->_config_writer = new JsonConfigWriter();
        } else {
            throw new \InvalidArgumentException(sprintf("%s is not a valid config type", $writer_type));
        }
    }

    public function write_config_file(Application $sw, $filename = false) {

        if (!$filename) {
            throw new \RuntimeException('A valid config file name must be provided');
        }

        if ($filename === "sw_config.json") {
            $config_sections = array('sw_app', 'db_options', 'auth_config', 'loggin', 'graphing');
        } elseif ($filename === "sw_datasource.json") {
            $config_sections = array('datasource');
        } else {
            throw new \InvalidArgumentException(sprintf("Unknown config file %s", $filename));
        }

        $config_parts = array();
        foreach ($config_sections as $section) {
            $config_parts[$section] = $sw['sw_config.config'][$section];
        }

        try {
            $this->_config_writer->write($this->_config_path . $filename, $config_parts);
        } catch(ConfigWriterException $e) {
            throw $e;
        }

    }

}
