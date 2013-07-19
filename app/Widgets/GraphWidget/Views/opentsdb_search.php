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

<div class="graph-widget-form-row" id="row1">
  <div id="auto-update">
    <div class="push-button">
      <input type="checkbox" id="auto-update-button" name="auto-update">
      <label id="auto-update-label" for="auto-update-button">
        <span class="iconic iconic-x-alt red"></span><span> Auto Update</span>
      </label>
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

  // Load the handler for the toggle-buttons
  loadScript("<?php echo URL; ?>app/js/toggle-buttons.js", function(){});
  // Load the handler for the on-off push buttons
  loadScript("<?php echo URL; ?>app/js/push-button.js", function(){});

</script>