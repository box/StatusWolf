<?php
/**
 * HomeController
 *
 * Controller for the default home page of the app
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 22 May 2013
 *
 * @package StatusWolf.Controller
 */

class HomeController extends SWController {

  public function __construct()
  {
    parent::__construct();
    include 'header.php';
    include 'navbar.php';
    print '<div class="container">' . "\n";
    print "<pre>\n";
    print "User " . $this->usersession['friendly_name'] . ' (' . $this->usersession['username'] . ") logged in\n";
    print "App bootstrap complete\n";
    print $_SERVER['SCRIPT_NAME'] . "\n";
    print "session id: " . session_id() . "\n";
    print_r($this->usersession);
    print "</pre>\n";
    print "</div>\n";
    include 'footer.php';
  }

}
