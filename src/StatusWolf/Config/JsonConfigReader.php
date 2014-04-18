<?php
/**
 * JsonConfigReader
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 27 February 2014
 *
 */

namespace StatusWolf\Config;

class JsonConfigReader implements ConfigReaderInterface {

    public function read($config_file) {
        $config = $this->parse_json($config_file);

        if (JSON_ERROR_NONE !== json_last_error()) {
            $json_read_error = $this->get_json_error(json_last_error());
            throw new \RuntimeException(
                sprintf("Invalid JSON format in config %s: %s", $config_file, $json_read_error)
            );
        }

        return $config ?: array();
    }

    public function understands($config_file) {
        return (bool) preg_match('#\.json?$#', $config_file);
    }

    private function parse_json($config_file) {
        $json = file_get_contents($config_file);
        return json_decode($json, true);
    }

    private function get_json_error($error_code) {
        $errors = array(
            JSON_ERROR_DEPTH          => 'The maximum stack depth has been exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
            JSON_ERROR_CTRL_CHAR      => 'Control character error, check encoding',
            JSON_ERROR_SYNTAX         => 'JSON syntax error',
            JSON_ERROR_UTF8           => 'Malformed UTF-8, check encoding'
        );

        return isset($errors[$error_code]) ? $errors[$error_code] : 'Unknown';
    }
}
