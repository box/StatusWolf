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

# Load the app constants
require(dirname(dirname(__FILE__)) . '/conf/constants.php');
//require APPLIB . 'SWConfig.php';
//require APPLIB . 'Error/SWException.php';

if (function_exists('ini_set'))
{
  ini_set('include_path', APPLIB . PATH_SEPARATOR . APPLIB . 'Error' . PATH_SEPARATOR . ini_get('include_path'));
//  print ini_get('include_path') . "\n";
}
spl_autoload_register();
spl_autoload_extensions('.php');

if (!include(APPLIB . 'bootstrap.php'))
{
  $bootstrap = false;
}


//if (!$bootstrap)
//{
//  print "Unable to bootstrap StatusWolf!\n";
//}
//else
//{
//  print "App bootstrap complete\n";
//}