<?php

$sw_conf = SWConfig::read_values('statuswolf');
$db_conf = $sw_conf['session_handler'];
$_session_data = $_SESSION[SWConfig::read_values('auth.sessionName')];
$sw_db = new mysqli($db_conf['db_host'], $db_conf['db_user'], $db_conf['db_password'], $db_conf['database']);
if (mysqli_connect_error())
{
  throw new SWException('Unable to connect to shared search database: ' . mysqli_connect_errno() . ' ' . mysqli_connect_error());
}

if (array_key_exists('saved_search_key', $_SESSION))
{
  $this->loggy->logDebug($this->log_tag . 'Looking for saved search id ' . $_SESSION['saved_search_key']);
  $saved_search_query = sprintf("SELECT * FROM saved_searches WHERE id='%s'", $_SESSION['saved_search_key']);
  if ($result = $sw_db->query($saved_search_query))
  {
    if ($result->num_rows && $result->num_rows > 0)
    {
      $raw_query_data = $result->fetch_assoc();
      if ($raw_query_data['private'] == 1 && $raw_query_data['user_id'] != $_session_data['user_id'])
      {
        $this->loggy->logDebug($this->log_tag . 'Access violation, user id ' . $_session_data['user_id'] . ' trying to view private search owned by user id ' . $raw_query_data['user_id']);
        $incoming_query_data = 'Not Allowed';
      }
      else {
        $serialized_query = $raw_query_data['search_params'];
        $incoming_query_data = unserialize($serialized_query);
      }
    }
    else
    {
      $incoming_query_data = 'Not Found';
    }
  }
  else
  {
    throw new SWException('Database read error: ' . mysqli_errno($sw_db) . ' ' . mysqli_error($sw_db));
  }
}
else if (array_key_exists('shared_search_key', $_SESSION))
{
  $shared_search_query = sprintf("SELECT * FROM shared_searches WHERE search_id='%s'", $_SESSION['shared_search_key']);
  if ($result = $sw_db->query($shared_search_query))
  {
    if ($result->num_rows && $result->num_rows > 0)
    {
      $raw_query_data = $result->fetch_assoc();
      $serialized_query = $raw_query_data['search_params'];
      $incoming_query_data = unserialize($serialized_query);
    }
    else
    {
      $incoming_query_data = "Expired";
    }
  }
  else
  {
    throw new SWException('Database read error: ' . mysqli_errno($sw_db) . ' ' . mysqli_error($sw_db));
  }
}
else
{
  $incoming_query_data = null;
}

?>

<div class="graph-widget-form-row row1">
  <div class="flexy date-span-toggle">
    <div class="toggle-button-group">
      <div class="toggle-button toggle-on">
        <label>
          <input type="radio" class="section-toggle date-search" name="date-span" value="date-search" checked="checked" data-target="graph-widget-dates">
          <span>Date Range</span>
        </label>
      </div><div class="toggle-button">
        <label>
          <input type="radio" class="section-toggle span-search" name="date-span" value="span-search" data-target="graph-widget-time-span">
          <span>Time Span</span>
        </label>
      </div>
    </div>
  </div>
  <div class="glue4">
    <div class="flexy section section-on graph-widget-dates" data-name="graph-widget-dates">
      <div class="graph-widget-form-item menu-label" data-name="start-time">
        <h4>Start</h4>
        <input type="text" class="input input-append date-input" data-format="yyyy/MM/dd hh:mm:ss" name="start-time"><span class="input-addon-btn">
          <span class="iconic iconic-calendar-alt"></span>
        </span>
      </div>
      <div class="graph-widget-form-item menu-label" data-name="end-time">
        <h4>End</h4>
        <input type="text" class="input input-append date-input" data-format="yyyy/MM/dd hh:mm:ss" name="end-time"><span class="input-addon-btn">
          <span class="iconic iconic-calendar-alt"></span>
        </span>
      </div>
    </div>
    <div class="flexy section section-off graph-widget-time-span" data-name="graph-widget-time-span">
      <div class="graph-widget-form-item menu-label" style="margin-right: 0">
        <h4>Show Me The Past</h4>
      </div>
      <div class="dropdown graph-widget-button">
        <span class="flexy" data-toggle="dropdown">
          <div class="graph-widget-button-label" data-name="time-span" data-ms="<?php echo (HOUR * 4) ?>">4 Hours</div>
          <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
        </span>
        <ul class="dropdown-menu menu-left" data-name="time-span-options" role="menu" aria-labelledby="dLabel">
          <li><span data-ms="<?php echo (MINUTE * 10); ?>">10 minutes</span></li>
          <li><span data-ms="<?php echo (MINUTE * 30); ?>">30 Minutes</span></li>
          <li><span data-ms="<?php echo HOUR; ?>">1 hour</span></li>
          <li><span data-ms="<?php echo (HOUR * 2); ?>">2 hours</span></li>
          <li><span data-ms="<?php echo (HOUR * 4); ?>">4 hours</span></li>
          <li><span data-ms="<?php echo (HOUR * 8); ?>">8 hours</span></li>
          <li><span data-ms="<?php echo (HOUR * 12); ?>">12 hours</span></li>
          <li><span data-ms="<?php echo DAY; ?>">1 day</span></li>
          <li><span data-ms="<?php echo WEEK; ?>">1 week</span></li>
          <li><span data-ms="<?php echo (WEEK * 2); ?>">2 weeks</span></li>
          <li><span data-ms="<?php echo MONTH; ?>">1 month</span></li>
        </ul>
      </div>
    </div>
  </div>
</div>
<div class="graph-widget-form-row row2">
  <div id="auto-update">
    <div class="push-button">
      <input type="checkbox" data-name="auto-update-button" name="auto-update">
      <label for="auto-update-button">
        <span class="iconic iconic-x-alt red"></span><span> Auto Update</span>
      </label>
    </div>
  </div>
  <div class="flexy history-toggle">
    <div class="toggle-button-group">
      <div class="toggle-button toggle-on">
        <label>
          <input type="radio" class="section-toggle history-no" name="history-graph" checked="checked" data-target="history-no" value="no">
          <span>No History</span>
        </label>
      </div><div class="toggle-button">
        <label>
          <input type="radio" class="section-toggle history-anomaly" name="history-graph" data-target="history-anomaly" value="anomaly">
          <span>Anomaly</span>
        </label>
      </div><div class="toggle-button">
        <label>
          <input type="radio" class="section-toggle history-wow" name="history-graph" data-target="history-wow" value="wow">
          <span>Week-Over-Week</span>
        </label>
      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="<?php echo URL; ?>app/css/datetimepicker.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/toggle-buttons.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/push-button.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/table.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/loader.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/tooltip.css">
<link rel="stylesheet" href="<?php echo URL . WIDGETS_URL; ?>GraphWidget/css/custom.graph_widget.css">

<script type="text/javascript">

  var sw_conf = eval('(<?php echo json_encode($sw_conf); ?>)');
  var query_data = {};
  var query_url = '';
  var incoming_query_data = '<?php if ($incoming_query_data) { echo json_encode($incoming_query_data); } ?>';

  // Load the handler for the toggle-buttons
  loadScript("<?php echo URL; ?>app/js/toggle-buttons.js", function(){});
  // Load the handler for the on-off push buttons
  loadScript("<?php echo URL; ?>app/js/push-button.js", function(){});

  // Add the handler for the date/time picker and init the form objects
  loadScript("<?php echo URL; ?>app/js/lib/bootstrap-datetimepicker.js", function() {
    $('.widget-form-item[data-name="start-time"]').datetimepicker({collapse: false});
    $('.widget-form-item[data-name="end-time"]').datetimepicker({collapse: false});
  });

  // If anomaly or week-over week displays are chosen update the
  // time span menu to limit the search to 1 week or less
  $('.section-toggle').click(function() {
    console.log('clicked on: ');
    console.log($(this));
    $('.' + $(this).attr('data-target')).removeClass('section-off').addClass('section-on').siblings('.section').addClass('section-off').removeClass('section-on');
    if ($(this).attr('data-target') == 'history-anomaly' || $(this).attr('data-target') == 'history-wow')
    {
      $('.optional-metric').removeClass('section-on');
      $('.optional-metric').addClass('section-off');
      $('.dropdown-menu[data-name="time-span-options"]').html('<li><span data-ms="<?php echo (MINUTE * 10); ?>">10 minutes</span></li>')
          .append('<li><span data-ms="<?php echo (MINUTE * 30); ?>">30 Minutes</span></li>')
          .append('<li><span data-ms="<?php echo HOUR ?>">1 hour</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 2) ?>">2 hours</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 4) ?>">4 hours</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 8) ?>">8 hours</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 12) ?>">12 hours</span></li>')
          .append('<li><span data-ms="<?php echo DAY ?>">1 day</span></li>')
          .append('<li><span data-ms="<?php echo WEEK ?>">1 week</span></li>');
      if ($('.widget-button-label[data-name="time-span"]').attr('data-ms') > <?php echo WEEK; ?>)
      {
        $('.widget-button-label[data-name="time-span"]').text('1 week').attr('data-ms', <?php echo WEEK; ?>);
      }
    }
    else if ($(this).attr('data-target') == 'history-no')
    {
      $('.optional-metric').removeClass('section-off');
      $('.optional-metric').addClass('section-on');
      $('.widget-button-label[data-name="time-span"]').html('<li><span data-ms="<?php echo (MINUTE * 10); ?>">10 minutes</span></li>')
          .append('<li><span data-ms="<?php echo (MINUTE * 30); ?>">30 Minutes</span></li>')
          .append('<li><span data-ms="<?php echo HOUR ?>">1 hour</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 2) ?>">2 hours</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 4) ?>">4 hours</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 8) ?>">8 hours</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 12) ?>">12 hours</span></li>')
          .append('<li><span data-ms="<?php echo DAY ?>">1 day</span></li>')
          .append('<li><span data-ms="<?php echo WEEK ?>">1 week</span></li>')
          .append('<li><span data-ms="<?php echo (WEEK * 2) ?>">2 weeks</span></li>')
          .append('<li><span data-ms="<?php echo MONTH ?>">1 month</span></li>');
    }
  });



</script>