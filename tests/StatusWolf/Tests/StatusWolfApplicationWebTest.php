<?php
/**
 * StatusWolfApplicationTest
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 15 April 2014
 *
 */

namespace StatusWolf\Tests;

use Silex\WebTestCase;

class StatusWolfApplicationWebTest extends WebTestCase {

    public function createApplication() {
        $_SERVER['HTTP_HOST'] = 'localhost';
        $_SERVER['SCRIPT_FILENAME'] = '/Users/mtroyer/code/StatusWolf/app/sw.php';
        $sw = require __DIR__ . '/../../../app/sw.php';
        $sw['session.test'] = true;

        return $sw;
    }

    public function test_home_redirect() {

        $client = $this->createClient();
        $client->followRedirects(false);
        $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isRedirect('/dashboard'));
    }

}
