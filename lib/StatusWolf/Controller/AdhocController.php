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
  public function __construct($url_path)
  {
    parent::__construct();

    if (array_key_exists('shared_search_key', $_SESSION))
    {
      unset($_SESSION['shared_search_key']);
    }

    if (!empty($url_path[0]))
    {
      $_adhoc_function = array_shift($url_path);
      if ($_adhoc_function === "search")
      {
        if ($_adhoc_datasource = array_shift($url_path))
        {
          $_search_object = new $_adhoc_datasource();
          $_search_object->get_raw_data($_POST);
          $raw_data = $_search_object->read();
          echo json_encode($raw_data);
        }
        else
        {
          throw new SWException ('No datasource specified for Ad-Hoc search');
        }
      }
      else if ($_adhoc_function === "shared")
      {
        if ($_shared_search_key = array_shift($url_path))
        {
          include 'header.php';
          $_SESSION['shared_search_key'] = $_shared_search_key;
          if ($_SESSION['authenticated'])
          {
            include 'navbar.php';
          }
          include 'adhoc.php';
          include 'footer.php';
        }
        else
        {
          throw new SWException('No shared search provided, no search to display');
        }
      }
      else
      {
        throw new SWException ('Unknown Ad-Hoc search function: ' . $_adhoc_function);
      }
    }
    else
    {
      include 'header.php';
      if ($_SESSION['authenticated'])
      {
        include 'navbar.php';
      }
      include 'adhoc.php';
      include 'footer.php';
    }
  }
}
