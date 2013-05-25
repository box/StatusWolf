<?php
/**
 * SWController
 *
 * Base controller for StatusWolf
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 22 May 2013
 *
 * @package StatusWolf.Controller
 */
class SWController
{

  protected $usersession;

  public function __construct()
  {
    if ($_SESSION['authenticated'])
    {
      $this->usersession = &$_SESSION[SWConfig::read_values('auth.sessionName')];
    }
    else
    {
      $this->usersession = &$_SESSION['_sw_session'];
    }
  }

}
