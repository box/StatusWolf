<?php
/**
 * SWAuthLogObserver
 *
 * Creates an observer for Auth events, used when 'enableLogging' is
 * set to true in the auth options
 *
 * Author: Mark Troyer
 * Date Created: 21 May 2013
 *
 * @package StatusWolf.Util
 */
class SWAuthLogObserver extends Log_observer {

  var $auth_log_messages = array();
  function notify($event)
  {
    // Init logging for the class
    if(SWConfig::read_values('statuswolf.debug'))
    {
      $this->loggy = new KLogger(ROOT . 'app/log/', KLogger::DEBUG);
    }
    else
    {
      $this->loggy = new KLogger(ROOT . 'app/log/', KLogger::INFO);
    }

    $this->auth_log_messages[] = $event;

    $this->loggy->logDebug(json_encode($this->auth_log_messages));
  }
}
