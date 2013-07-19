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

    include 'header.php';
    if ($_SESSION['authenticated'])
    {
      include 'navbar.php';
    }
    include 'dash.php';
    include 'footer.php';
  }
}
