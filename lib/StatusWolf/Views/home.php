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

<div class="home-container">
  <div class="home-logo"></div>
  <div class="home-menu">
    <div class="home-menu-box-wrapper">
      <div class="home-menu-box" id="ad-hoc-box">
        <div class="home-menu-item" id="adhoc">
          <span>Ad-Hoc Search</span>
        </div>
        <div class="home-descriptions" id="ad-hoc-descriptions">
          <p>Search against your configured data sources on a single graph</p>
        </div>
      </div>
      <div class="home-menu-box" id="dashboards-box">
        <div class="home-menu-item" id="dashboard">
          <span>Dashboard</span>
        </div>
        <div class="home-descriptions" id="dashboards-description">
          <p>Create, save and reload dashboards</p>
        </div>
      </div>
      <div class="home-menu-box" id="my-settings-box">
        <div class="home-menu-item" id="my-settings">
          <span>My Settings</span>
        </div>
        <div class="home-descriptions" id="my-settings-description">
          <p>Edit your saved searches and saved dashboards</p>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/jquery.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/bootstrap.js"></script>

<script type="text/javascript">

  $('div.home-menu-item').click(function() {
    if ($(this).attr('id') === "my-settings")
    {
      var new_loc = 'account/my_settings';
    }
    else
    {
      var new_loc = $(this).attr('id');
    }

    window.location.href = '<?php echo URL; ?>' + new_loc;
  });

</script>