<?php
/**
 * StatusWolfApplicationTest
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 15 April 2014
 *
 */

namespace StatusWolf\Tests;

class StatusWolfApplicationTest extends \PHPUnit_Framework_TestCase {

    public function test_get_widgets() {

        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SCRIPT_FILENAME'] = '/Users/mtroyer/code/StatusWolf/app/sw.php';
        $sw = require_once __DIR__ . '/../../../app/sw.php';

        $widgets = $sw['get_widgets'];
        $this->assertTrue(array_key_exists('OpenTSDBWidget', $widgets));

    }

}
