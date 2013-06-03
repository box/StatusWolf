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
    if ($_SESSION['authenticated'])
    {
      include 'navbar.php';
    }
    include 'home.php';
    include 'footer.php';
  }

}
