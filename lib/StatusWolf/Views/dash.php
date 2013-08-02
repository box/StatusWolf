<?php
/**
 * dash.php
 *
 * View for custom dashboards
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 10 July 2013
 *
 * @package StatusWolf.Views
 */

$_session_data = $_SESSION[SWConfig::read_values('auth.sessionName')];
$_sw_conf = SWConfig::read_values('statuswolf');

// Register available widgets

$widget_main = WIDGETS;
$widget_dir_iterator = new DirectoryIterator($widget_main);
$widgets = array();
$widget_list = array();
foreach($widget_dir_iterator as $fileinfo)
{
  if ($fileinfo->isDot()) { continue; }
  if ($fileinfo->isDir())
  {
    $widgets[] = $fileinfo->getFilename();
  }
}
foreach($widgets as $widget_key)
{
  $widget_info = file_get_contents($widget_main . DS . $widget_key . DS . $widget_key . '.json');
  $widget_info = implode('', explode("\n", $widget_info));
  $widget_list[$widget_key] = json_decode($widget_info, true);
}

?>

<link rel="stylesheet" href="<?php echo URL; ?>app/css/widget_base.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/dash.css">
<div class="container">
  <div id="dash-container"></div>
</div>

<script type="text/javascript" src="<?php echo URL; ?>app/js/sw_lib.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/jquery.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/bootstrap.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/date.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/md5.js"></script>

<script type="text/javascript">

<!--  document._session_data = eval('(--><?php //echo json_encode($_session_data); ?><!--)');-->
  var _session_data = '<?php echo json_encode($_session_data); ?>';
  if (typeof(_session_data) == "string")
  {
    document._session_data = eval('(' + _session_data + ')');
  }
  else
  {
    document._session_data = _session_data
  }
  var _sw_conf = '<?php echo json_encode($_sw_conf); ?>';
  if (typeof(_sw_conf) == "string")
  {
    document._sw_conf = eval('(' + _sw_conf + ')');
  }
  else
  {
    document._sw_conf = _sw_conf;
  }

  console.log(document._session_data);
  console.log(document._sw_conf);

  $(document).ready(function() {
    var widgets = eval('(<?php echo json_encode($widget_list); ?>)');
    this.sw_url = '<?php echo URL; ?>';

    $('#menu-placeholder').replaceWith('<div class="dashboard-menu dropdown menu-btn" id="dashboard-menu">');
    $('#dashboard-menu').append('<span class="flexy" id="dashboard-menu-content" data-toggle="dropdown">')
    $('#dashboard-menu-content').append('<span class="menu-label" id="dashboard-menu-label">Dashboard <span class="iconic iconic-play rotate-90">');
    $('#dashboard-menu').append('<ul class="dropdown-menu sub-menu-item" id="dashboard-menu-options" role="menu" aria-labelledby="dLabel">');
    $('#dashboard-menu-options').append('<li class="flexy dropdown" id="add-widget-menu-item"><span>Add Widget</span><span class="glue1"></span><span class="iconic iconic-play"></span></li>');
    $('#add-widget-menu-item').append('<ul class="dropdown-menu sub-menu" id="add-widget-menu-options">');
    $.each(widgets, function(widget_index, widget_data) {
      var widget_url = '<?php echo URL . WIDGETS_URL; ?>';
      var widget_script_url = widget_url + widget_index + '/js/' + widget_data.name + '.js';
      console.log('loading ' + widget_index + ' widget from ' + widget_script);
      var widget_script = widget_script_url.split('/');
      widget_script = widget_script.pop();
      var widget_type = widget_script.split('.');
      loadScript(widget_script_url, function() {});
      $('#add-widget-menu-options').append('<li onClick="add_widget(\'' + widget_type[1] + '\')"><span>' + widget_data.title + '</span></li>');
    });
  });

  function add_widget(widget_type)
  {
    var username = "<?php echo $_session_data['username'] ?>";
    var widget_id = "widget" + md5(username + new Date.now().getTime());
    var widget;
    $('#dash-container').append('<div class="widget-container" id="' + widget_id + '">');
    if (widget_type === "graphwidget")
    {
      widget = $('#' + widget_id).graphwidget({sw_url: '<?php echo URL; ?>'});
    }
    setTimeout(function() {
      $('#' + widget_id).removeClass('transparent');
    }, 100);
  }

</script>