<?php
/**
 * Defines constant values for StatusWolf
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 4 April 2013
 *
 */

/**
 * Filesystem defines
 */
if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

if (!defined('ROOT'))
{
	define('ROOT', dirname(dirname(dirname(__FILE__))) . DS);
}

if (!defined('LIB'))
{
	define('LIB', ROOT . 'lib' . DS);
}

if (!defined('APPLIB'))
{
	define('APPLIB', LIB . 'StatusWolf' . DS);
}

if (!defined('VIEWS'))
{
  define('VIEWS', APPLIB . 'Views' . DS);
}

if (!defined('CFG'))
{
	define('CFG', ROOT . 'conf' . DS);
}

if (!defined('CACHE'))
{
	define('CACHE', ROOT . 'cache' . DS);
}

if (!defined('URL'))
{
  if (isset($_SERVER['HTTPS']))
  {
    $proto = 'https://';
  }
  else
  {
    $proto = 'http://';
  }
  $dir = null;
  if (strpos($_SERVER['SCRIPT_NAME'], '/') !== false)
  {
    $dir_parts = explode('/', $_SERVER['SCRIPT_NAME']);
    // Drop the script file from the url
    array_pop($dir_parts);
    if ($dir_parts[count($dir_parts)-1] === "app")
    {
      array_pop($dir_parts);
    }
    if (count($dir_parts) > 1)
    {
      $dir = implode('/', $dir_parts);
    }
  }
  define('URL', $proto . $_SERVER['HTTP_HOST'] . $dir);
}

/**
 * Time defines
 */
if (!defined('SECOND'))
{
	define('SECOND', 1);
}

if (!defined('MINUTE'))
{
	define('MINUTE', 60);
}

if (!defined('HOUR'))
{
	define('HOUR', 3600);
}

if (!defined('DAY'))
{
	define('DAY', 86400);
}

if (!defined('WEEK'))
{
	define('WEEK', 604800);
}

if (!defined('MONTH'))
{
	define('MONTH', 2592000);
}

if (!defined('YEAR'))
{
	define('YEAR', 31536000);
}
