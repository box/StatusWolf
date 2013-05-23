<?php
/**
 * index.php
 *
 * Entry point to the application, calls the app bootstrap and hands off to
 * the Router for directing to the proper controller
 *
 * Author: Mark Troyer <disco@box.com>
 * Created: 20 May 2013
 */

// Load the app constants
require(dirname(dirname(__FILE__)) . '/lib/StatusWolf/constants.php');
require(dirname(dirname(__FILE__)) . '/lib/StatusWolf/Util/SWAutoLoader.php');
require(dirname(dirname(__FILE__)) . '/lib/StatusWolf/SWConfig.php');

if (function_exists('ini_set'))
{
  ini_set('include_path', VIEWS . PATH_SEPARATOR . ini_get('include_path'));
}

// Bootstrap the app
if (!include(APPLIB . 'bootstrap.php'))
{
  $bootstrap = false;
}


if ($bootstrap)
{
  $router = new SWRouter($_SERVER['REQUEST_URI']);
}