<?php
/**
 * home.php
 *
 * Default home page for new sessions
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 28 May 2013
 *
 * @package StatusWolf.Views
 */
?>

    <div class="container">
      <div class="left-column">
        <div class="home-logo"></div>
      </div>
      <div class="home-menu">
      	<div class="home-menu-item btn" id="ad-hoc">
          <a href="<?php echo URL; ?>adhoc/">Ad-Hoc Search</a>
        </div>
        <div class="home-menu-item btn hidden" id="shared-dashboards">
          <a href="<?php echo URL; ?>dashboard/shared/">Shared Dashboards</a>
        </div>
        <div class="home-menu-item btn hidden" id="my-dashboards">
          <a href="<?php echo URL; ?>dashboard/my/">My Dashboards</a>
        </div>
      </div>
      <div class="right-column"></div>
    </div>

    <script type="text/javascript" src="<?php echo URL; ?>app/js/lib/jquery.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>app/js/lib/bootstrap.js"></script>

