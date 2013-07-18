<?php
/**
 * header.php
 *
 * Stub html header for StatusWolf
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 21 May 2013
 *
 * @package StatusWolf.Views
 */

  if(SWConfig::read_values('statuswolf.debug'))
  {
    $this->loggy = new KLogger(ROOT . 'app/log/', KLogger::DEBUG);
  }
  else
  {
    $this->loggy = new KLogger(ROOT . 'app/log/', KLogger::INFO);
  }
  $this->log_tag = '(' . $_SESSION['_sw_authsession']['username'] . '|' . $_SESSION['_sw_authsession']['sessionip'] . ') ';
?>


<!DOCTYPE html>
<html lang="en">

  <head>

    <meta charset="utf-8">

    <title>StatusWolf</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="Mark Troyer <disco@box.com">

    <link href="<?php echo URL; ?>app/css/statuswolf_base.css?v=1" rel="stylesheet">
    <link rel="icon" href="<?php echo URL; ?>app/img/favicon-96.png">

  </head>

  <body>