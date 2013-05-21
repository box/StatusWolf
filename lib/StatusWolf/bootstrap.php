<?php
/**
 * bootstrap.php
 *
 * Bootstraps the application - loads the datasource config and checks for an authenticated user session
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 20 May 2013
 */

require_once "Auth.php";
require_once "Log.php";
require_once "Log/observer.php";

SWConfig::load_config('datasource.conf');
SWConfig::load_config('auth.conf');

$auth_method = SWConfig::read_values('auth.method');
if ($auth_method === 'LDAP')
{
  $auth_options = SWConfig::read_values('auth.LDAP');
  $auth_options['sessionName'] = '_sw_authsession';
//  $auth_options['enableLogging'] = true;
}
else if ($auth_method === 'MDB2')
{
  $db_options = SWConfig::read_values('auth.MDB2');
  $auth_options = array(
    'sessionName' => '_sw_authsession'
//    ,'enableLogging' => true
    ,'dsn' => array (
      'phptype' => $db_options['type']
      ,'username' => $db_options['user']
      ,'password' => $db_options['password']
      ,'hostspec' => $db_options['host']
      ,'database' => $db_options['authdb']
    )
  );
}

function login($username = null, $status = null, &$auth = null)
{
  echo "<form method=\"post\" action=\"index.php\">";
  echo "<input type=\"text\" name=\"username\">";
  echo "<input type=\"password\" name=\"password\">";
  echo "<input type=\"submit\">";
  echo "</form>";
}

function login_failed($user = null, &$auth = null)
{
  print "User " . $user . " failed to login: " . $auth->getStatus() . "\n";
}

class Auth_Log_Observer extends Log_observer
{
  var $messages = array();
  function notify($event)
  {
    $this->messages[] = $event;
  }
}

$sw_auth = new Auth($auth_method, $auth_options, 'login');
if (array_key_exists('enableLogging', $auth_options))
{
  $debug_log_observer = new Auth_Log_Observer(PEAR_LOG_DEBUG);
  $sw_auth->attachLogObserver($debug_log_observer);
}
$sw_auth->setFailedLoginCallback('login_failed');
$sw_auth->start();
if (array_key_exists('action', $_GET))
{
  if ($_GET['action'] == "logout" && $sw_auth->checkAuth())
  {
    $sw_auth->logout();
    $sw_auth->start();
  }
}

if ($sw_auth->checkAuth())
{
  $usersession = &$_SESSION[$auth_options['sessionName']];
  print "User " . $usersession['username'] . " is logged in!\n";

}

//print "<h3>Auth Log:\n</h3>\n";
//foreach ($debug_log_observer->messages as $debug_event)
//{
//  print $debug_event['priority'] . ": " . $debug_event['message'] . "<br>\n";
//}
//
//print "<pre>\n";
//print_r($usersession);
//print "</pre>";

$bootstrap = true;
