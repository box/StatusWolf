<?php
/**
 * WidgetsController
 *
 * Controller to find and load the appropriate Widget Controller
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 15 July 2013
 *
 * @package StatusWolf.Controller
 */
class WidgetsController extends SWController
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
      $widget = array_shift($url_path);
      $widget_controller = ucfirst($widget) . 'Controller';
      include_once (WIDGETS . $widget . DS . 'Controller' . DS .$widget_controller . '.php');
      $widget_controller_object = new $widget_controller($url_path);
    }
    else
    {
      throw new SWException ('No Widget specified');
    }

  }
}
