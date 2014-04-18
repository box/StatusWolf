<?php
/**
 * ConfigWriterInterface
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 13 March 2014
 *
 */

namespace StatusWolf\Config;

interface ConfigWriterInterface {
    function write($config_file, $config_parts);
}
