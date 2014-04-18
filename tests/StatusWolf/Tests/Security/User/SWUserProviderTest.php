<?php
/**
 * SWUserProviderTest
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 15 April 2014
 *
 */

namespace StatusWolf\Tests\Security;

use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use StatusWolf\Security\User\SWUserProvider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use StatusWolf\Config\ConfigReaderServiceProvider;

class SWUserProviderTest extends \PHPUnit_Framework_TestCase {

    private $auth_config = array(
        'with_csrf' => true,
        'users' => array(
            'auto_create' => false,
            'default_auth_type' => 'mysql',
            'default_role' => 'ROLE_USER'
        )
    );

    public function test_supportsClass() {

        $sw = new Application();
        $sw->register(new ConfigReaderServiceProvider());
        $sw['sw_config']->read_config_file($sw, __DIR__ . '/../../../../../conf/sw_config.json');
        $sw->register(new DoctrineServiceProvider(), array(
            'db.options' => $sw['sw_config.config']['db_options'],
        ));
        $sw['user_provider'] = $sw->share(function() use($sw) {
            return new SWUserProvider($sw['db'], $this->auth_config);
        });

        $this->assertTrue($sw['user_provider']->supportsClass('StatusWolf\Security\User\SWUser'));
        $this->assertFalse($sw['user_provider']->supportsClass('NotStatusWolf\User'));

    }

    public function test_loadUserByUsername_exists() {

        $sw = new Application();
        $sw->register(new ConfigReaderServiceProvider());
        $sw['sw_config']->read_config_file($sw, __DIR__ . '/../../../../../conf/sw_config.json');
        $sw->register(new DoctrineServiceProvider(), array(
            'db.options' => $sw['sw_config.config']['db_options'],
        ));
        $sw['user_provider'] = $sw->share(function() use($sw) {
            return new SWUserProvider($sw['db'], $this->auth_config);
        });

        $this->assertInstanceOf('StatusWolf\Security\User\SWUser', $sw['user_provider']->loadUserByUsername('swadmin'));

    }

    /**
     * @test
     * @expectedException Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     * @expectedExceptionMessage User test not found
     */
    public function test_loadUserByUsername_not_exists() {

        $sw = new Application();
        $sw->register(new ConfigReaderServiceProvider());
        $sw['sw_config']->read_config_file($sw, __DIR__ . '/../../../../../conf/sw_config.json');
        $sw->register(new DoctrineServiceProvider(), array(
            'db.options' => $sw['sw_config.config']['db_options'],
        ));
        $sw['user_provider'] = $sw->share(function() use($sw) {
            return new SWUserProvider($sw['db'], $this->auth_config);
        });

        $sw['user_provider']->loadUserByUsername('test');

    }

}
