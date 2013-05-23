<?php
/**
 * navbar.php
 *
 * Default navigation bar for StatusWolf
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 22 May 2013
 *
 * @package StatusWolf.Views
 */
?>

    <div class="navbar">
      <div class="brand"><h4>StatusWolf</h4></div>
      <div class="user-menu dropdown menu-btn">
        <span class="iconic iconic-user" style="padding-left: 10px;"></span><span class="menu-label"><?php echo $this->usersession['friendly_name']; ?></span>
      </div>
    </div>