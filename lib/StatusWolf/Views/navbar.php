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
  <div class="brand dropdown menu-btn" id="nav-menu">
    <span class="flexy" id="statuswolf-menu-content" data-toggle="dropdown">
      <h4 class="menu-label" id="statuswolf-menu-content">StatusWolf</h4>
      <span class="version"><p><?php $ver = SWConfig::read_values('statuswolf.version'); echo $ver ?></p></span>
    </span>
    <ul class="dropdown-menu" id="nav-menu-content" role="menu">
      <li id="nav-home"><a href="<?php echo URL; ?>"><span class="iconic iconic-home"><span class="font-reset"> Home</span></span></a></li>
      <li id="nav-adhoc"><a href="<?php echo URL; ?>adhoc/"><span>Ad-Hoc Search</span></a></li>
      <li id="nav-dashboard"><a href="<?php echo URL; ?>dashboard/"><span>Dashboard</span></a></li>
    </ul>
  </div><div id="menu-placeholder"></div>
  <div class="user-menu dropdown menu-btn" id="user-menu">
    <form id="logout" method="post" action="<?php echo URL; ?>">
      <input type="hidden" name="action" value="logout">
      <span class="iconic iconic-user" style="padding-left: 10px;"></span><a data-toggle="dropdown"><span class="menu-label"><?php echo $this->usersession['friendly_name']; ?></span></a>
      <ul class="dropdown-menu menu-left" id="user-menu-options" role="menu" aria-labelled-by="dLabel">
        <li><a href="<?php echo URL; ?>account/my_settings"><span>My Settings</span></a></li>
        <?php if (!array_key_exists('auto_login', $_SESSION[SWConfig::read_values('auth.sessionName')]['data']) || $_SESSION[SWConfig::read_values('auth.sessionName')]['data']['auto_login'] !== "true")
              {
                echo '<li onClick="$(\'#logout\').submit()"><span>Logout</span></li>';
              }
        ?>
      </ul>
    </form>
  </div>
</div>