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
      <link href="<?php echo URL; ?>app/css/nav.css?v=1" rel="stylesheet">
      <div class="brand"><a href="<?php echo URL; ?>"><h4>StatusWolf</h4></a></div>
      <div class="version"><p><?php $ver = SWConfig::read_values('statuswolf.version'); echo $ver; ?></p></div>
      <div id="menu-placeholder"></div>
      <div class="user-menu dropdown menu-btn" id="user-menu">
        <form id="logout" method="post" action="<?php echo URL; ?>">
          <input type="hidden" name="action" value="logout">
          <span class="iconic iconic-user" style="padding-left: 10px;"></span><a data-toggle="dropdown"><span class="menu-label"><?php echo $this->usersession['friendly_name']; ?></span></a>
          <ul class="dropdown-menu menu-left" id="user-menu-options" role="menu" aria-labelled-by="dLabel">
            <li><a href="<?php echo URL; ?>account/my_settings"><span>My Settings</span></a></li>
            <li onClick="$('#logout').submit()"><span>Logout</span></li>
          </ul>
        </form>
      </div>
    </div>