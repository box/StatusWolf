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
    if (array_key_exists('shared_search_key', $_SESSION['_sw_authsession']))
    {
      unset($_SESSION['_sw_authsession']['shared_search_key']);
    }
    if (array_key_exists('saved_search_key', $_SESSION['_sw_authsession']))
    {
      unset($_SESSION['_sw_authsession']['saved_search_key']);
    }

    $url_path = $url_parts['url_path'];

    if (!empty($url_path[0]))
    {
      $_adhoc_function = array_shift($url_path);
      if ($_adhoc_function === "search")
      {
        $this->loggy->logDebug($this->log_tag . "AdHoc search specified.");
        if ($_adhoc_datasource = array_shift($url_path))
        {
          $this->loggy->logDebug($this->log_tag . "Search datasource: " . $_adhoc_datasource);
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
          $_SESSION['_sw_authsession']['shared_search_key'] = $_shared_search_key;
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
      else if ($_adhoc_function === "saved")
      {
        $this->loggy->logDebug($this->log_tag . 'Saved search requested');
        if ($_saved_search_key = array_shift($url_path))
        {
          $this->loggy->logDebug($this->log_tag . 'Saved search id: ' . $_saved_search_key);
          include 'header.php';
          $_SESSION['_sw_authsession']['saved_search_key'] = $_saved_search_key;
          if (array_key_exists('query', $url_parts))
          {
            $this->loggy->logDebug($this->log_tag . 'Search options given: ' . $url_parts['query']);
            $_SESSION['_sw_authsession']['saved_search_options'] = explode('&', $url_parts['query']);
          }
          if ($_SESSION['authenticated'])
          {
            include 'navbar.php';
          }
          include 'adhoc.php';
          include 'footer.php';
        }
        else
        {
          throw new SWException('No saved search provided, no search to display');
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
