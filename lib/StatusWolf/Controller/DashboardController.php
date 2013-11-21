<?php
/**
 * DashboardController
 *
 * Controller for custom dashboard interface
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 10 July 2013
 *
 * @package StatusWolf.Controller
 */

class DashboardController extends SWController
{
  public function __construct($url_parts)
  {
    parent::__construct();

    // Init logging for the class
    if(SWConfig::read_values('statuswolf.debug'))
    {
      $this->loggy = new KLogger(ROOT . 'app/log/', KLogger::DEBUG);
    }
    else
    {
      $this->loggy = new KLogger(ROOT . 'app/log/', KLogger::INFO);
    }
    $this->log_tag = '(' . $_SESSION['_sw_authsession']['username'] . '|' . $_SESSION['_sw_authsession']['sessionip'] . ') ';
    $url_path = $url_parts['url_path'];

    if (!empty($url_path[0]))
    {
      $this->loggy->logDebug($this->log_tag . 'Setting dashboard id to ' . $url_path[0]);
      $_SESSION['_sw_authsession']['data']['dashboard_id'] = $url_path[0];
      if (array_key_exists('query', $url_parts))
      {
        $_SESSION['_sw_authsession']['data']['dashboard_tags'] = explode('&', $url_parts['query']);
      }
      else if (array_key_exists('dashboard_tags', $_SESSION['_sw_authsession']['data']))
      {
        unset($_SESSION['_sw_authsession']['data']['dashboard_tags']);
      }
    }
    else
    {
      $this->loggy->logDebug($this->log_tag . 'Clearing dashboard id');
      unset($_SESSION['_sw_authsession']['data']['dashboard_id']);
    }

    include 'header.php';
    if ($_SESSION['authenticated'])
    {
      include 'navbar.php';
    }
    include 'dash.php';
    include 'footer.php';

  }
}
