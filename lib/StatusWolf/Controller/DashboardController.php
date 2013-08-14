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
  public function __construct($url_path)
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

    if (!empty($url_path[0]))
    {
      $this->loggy->logDebug($this->log_tag . 'Setting dashboard id to ' . $url_path[0]);
      $_SESSION['_sw_authsession']['data']['dashboard_id'] = $url_path[0];
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
