<?php
/**
 * bootstrap.php
 *
 * Bootstraps the application - loads the datasource config and checks for an authenticated user session
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 20 May 2013
 */

// Load the PEAR::Auth module for authenticated sessions
require_once "Auth.php";

// Load datasource config and authentication config
SWConfig::load_config('datasource.conf');
SWConfig::load_config('auth.conf');

// Auth method - which backend are we using for authentication?
$auth_method = SWConfig::read_values('auth.method');
// Load the options for the auth backend
$auth_options = SWConfig::read_values('auth.' . $auth_method);
// Set the name used for session management, defaults to '_sw_authsession' to avoid conflicts
// with other apps on the server that may be using the default Auth session naming
if (! $auth_options['sessionName'] = SWConfig::read_values('auth.sessionName'))
{
  $auth_options['sessionName'] = '_sw_authsession';
}

// Base login function, prints the login form if no active auth session exists
function login($username = null, $status = null, &$auth = null)
{
  echo "<form method=\"post\" action=\"index.php\">";
  echo "<input type=\"text\" name=\"username\">";
  echo "<input type=\"password\" name=\"password\">";
  echo "<input type=\"submit\">";
  echo "</form>";
}

// Function to deal with failed user login attempts
function login_failed($user = null, &$auth = null)
{
  print "User " . $user . " failed to login: " . $auth->getStatus() . "\n";
}

// Create authentication object
$sw_auth = new Auth($auth_method, $auth_options, 'login');

if (array_key_exists('enableLogging', $auth_options) && $auth_options['enableLogging'])
{
  require_once "Log.php";
  require_once "Log/observer.php";
  $debug_log_observer = new SWAuthLogObserver(PEAR_LOG_DEBUG);
  $sw_auth->attachLogObserver($debug_log_observer);
}

// Set the function to use on failed login attempts
$sw_auth->setFailedLoginCallback('login_failed');

// Start the new auth session
$sw_auth->start();

// Logout the user, restart the session and present a login form
if (array_key_exists('action', $_GET))
{
  if ($_GET['action'] == "logout" && $sw_auth->checkAuth())
  {
    $sw_auth->logout();
    $sw_auth->start();
  }
}

// Check for a logged in session
if ($sw_auth->checkAuth())
{
  $usersession = &$_SESSION[$auth_options['sessionName']];
  print "User " . $usersession['username'] . " is logged in!\n";

}

if (array_key_exists('enableLogging', $auth_options) && $auth_options['enableLogging'])
{
  print "<h3>Auth Log:\n</h3>\n";
  foreach ($debug_log_observer->auth_log_messages as $debug_event)
  {
    print $debug_event['priority'] . ": " . $debug_event['message'] . "<br>\n";
  }
  print "<pre>\n";
  print_r($_SESSION);
  print "</pre>";
}

$bootstrap = true;
