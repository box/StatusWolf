<?php
/**
 * AdhocController
 *
 * Controller for Ad-Hoc search interface
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 01 June 2013
 *
 * @package StatusWolf.Controller
 */

class AdhocController extends SWController
{
  public function __construct()
  {
    parent::__construct();
    include 'header.php';
    if ($_SESSION['authenticated'])
    {
      include 'navbar.php';
    }
    include 'adhoc.php';
    include 'footer.php';
  }
}
