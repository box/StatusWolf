<?php
/**
 * JsonConfigWriter
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 13 March 2014
 *
 */

namespace StatusWolf\Config;

use StatusWolf\Exception\ConfigWriterException;

class JsonConfigWriter implements ConfigWriterInterface {

    public function write($config_file, $config_parts) {

        if(file_put_contents($config_file . '.new', json_encode($config_parts, JSON_PRETTY_PRINT) . "\n", LOCK_EX) === false) {
            throw new ConfigWriterException(sprintf("Unable to write config file %s", $config_file));
        }

        if (!copy($config_file, $config_file . '.previous')) {
            throw new ConfigWriterException(sprintf("Unable to backup existing config file %s", $config_file));
        }

        if (!rename($config_file . '.new', $config_file)) {
            throw new ConfigWriterException(sprintf("Unable to activate new config %s", $config_file));
        }

        chmod($config_file, 0660);
        chmod($config_file . '.previous', 0660);

    }

}
