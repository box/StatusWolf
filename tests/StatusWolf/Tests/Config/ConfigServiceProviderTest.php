<?php
/**
 * ConfigServiceProviderTest
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 27 February 2014
 *
 */

namespace StatusWolf\Tests;

use Silex\Application;
use StatusWolf\Config\ConfigReaderServiceProvider;

class ConfigServiceProviderTest extends \PHPUnit_Framework_TestCase {

    private $configs = array(
        'good' => '/Fixtures/sw_config_good.json',
        'broken' => '/Fixtures/sw_config_broken.json',
        'empty' => '/Fixtures/sw_config_empty.json',
        'missing' => '/Fixtures/sw_config_missing.json'
    );

    public function test_register() {

        $sw = new Application();
        $sw->register(new ConfigReaderServiceProvider());
        $sw['sw_config']->read_config_file($sw, __DIR__ . $this->configs['good']);
        $this->assertSame(true, $sw['sw_config.config']['sw_app']['debug']);
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage does not exist
     */
    public function test_missing_config() {
        $sw = new Application();
        $sw->register(new ConfigReaderServiceProvider());
        $sw['sw_config']->read_config_file($sw, __DIR__ . $this->configs['missing']);
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage JSON syntax error
     */
    public function test_invalid_json_exception() {
        $sw = new Application();
        $sw->register(new ConfigReaderServiceProvider());
        $sw['sw_config']->read_config_file($sw, __DIR__ . $this->configs['broken']);
    }

    public function test_empty_config() {
        $sw = new Application();
        $sw->register(new ConfigReaderServiceProvider());
        $sw['sw_config']->read_config_file($sw, __DIR__ . $this->configs['empty']);
        $this->assertEquals(array(), $sw['sw_config.config']);
    }

}
