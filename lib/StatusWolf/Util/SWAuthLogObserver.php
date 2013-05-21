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
    $this->auth_log_messages[] = $event;
  }
}
