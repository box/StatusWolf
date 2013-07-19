<?php
/**
 * GraphWidgetController
 *
 * Describe your class here
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 15 July 2013
 *
 * @package StatusWolf.Widets.GraphWidget
 */
class GraphWidgetController extends SWController
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

    $this->loggy->logDebug($this->log_tag . 'Looking for Widget function ' . $url_path[0]);
    if (!empty($url_path[0]))
    {
      $widget_action = array_shift($url_path);
      switch($widget_action)
      {
        case 'api':
          if (!empty($url_path[0]))
          {
            $_api_function = array_shift($url_path);
            $this->loggy->logDebug($this->log_tag . 'calling function ' . $_api_function);
            $this->$_api_function($url_path);
          }
          else
          {
            throw new SWException ('No API function specified');
          }
      }
    }
    else
    {
      throw new SWException ('No action specified for widget');
    }
  }

  protected function datasource_form($form)
  {
    if (!empty($form) && $form[0])
    {
      $this->loggy->logDebug($this->log_tag . 'Loading data source form for ' . $form[0]);
      ob_start();
      include WIDGETS . 'GraphWidget' . DS . 'Views' . DS . $form[0] . '.php';
      $raw_form = ob_get_contents();
      $form_data = array('form_source' => $raw_form);
      ob_end_clean();
      echo json_encode($form_data);
    }
    else
    {
      throw new SWException('No datasource form found');
    }
  }

}
