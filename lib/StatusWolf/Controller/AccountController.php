<?php
/**
 * AccountController
 *
 * Controller for actions related to a user's account, editing saved searches, changing prefs, etc.
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 7 August 2013
 *
 * @package
 */
class AccountController extends SWController {

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
      $_account_function = array_shift($url_path);
      if ($_account_function === "my_settings")
      {
        include 'header.php';
        if ($_SESSION['authenticated'])
        {
          include 'navbar.php';
        }
        include 'my_settings.php';
        include 'footer.php';
      }
    }

  }

}