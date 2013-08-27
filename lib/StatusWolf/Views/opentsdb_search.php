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

<div class="ad-hoc-form-row" id="row1">
  <table class="general-options-table" id="graph-search-general">
    <tr>
      <td>
        <div id="auto-update" class="auto-update">
          <div class="push-button">
            <input type="checkbox" id="auto-update-button" name="auto-update"><label id="auto-update-label" for="auto-update-button"><span class="iconic iconic-x-alt red"></span><span> Auto Update</span></label>
          </div>
        </div>
      </td>
      <td>
        <div class="toggle-button-group">
          <div class="toggle-button toggle-on">
            <label><input type="radio" class='section-toggle' id="history-no" name="history-graph" checked="checked" data-target="ad-hoc-no" value="no"><span>No History</span></label>
          </div><div class="toggle-button">
            <label><input type="radio" class='section-toggle' id="history-anomaly" name="history-graph" data-target="ad-hoc-anomaly" value="anomaly"><span>Anomaly</span></label>
          </div><div class="toggle-button">
            <label><input type="radio" class='section-toggle' id="history-wow" name="history-graph" data-target="ad-hoc-wow" value="wow"><span>Week-Over-Week</span></label>
          </div>
        </div>
      </td>
      <td>
        <div class="toggle-button-group">
          <div class="toggle-button toggle-on">
            <label><input type="radio" class='section-toggle' id="date-search" name="date-span" value="date-search" checked="checked" data-target="ad-hoc-dates"><span>Date Range</span></label>
          </div><div class="toggle-button">
            <label><input type="radio" class='section-toggle' id="span-search" name="date-span" value="span-search" data-target="ad-hoc-time-span"><span>Time Span</span></label>
          </div>
        </div>
      </td>
      <td width="50%">
        <div class="section section-on" id="ad-hoc-dates">
          <div class="ad-hoc-form-item menu-label" id="start-time">
            <h4>Start</h4>
            <input type="text" class="input input-append date-input" data-format="yyyy/MM/dd hh:mm:ss" name="start-time"><span class="input-addon-btn"><span class="iconic iconic-calendar-alt"></span></span>
          </div>
          <div class="ad-hoc-form-item menu-label" id="end-time">
            <h4>End</h4>
            <input type="text" class="input input-append date-input" data-format="yyyy/MM/dd hh:mm:ss" name="end-time"><span class="input-addon-btn"><span class="iconic iconic-calendar-alt"></span></span>
          </div>
        </div>
        <div class="section section-off" id="ad-hoc-time-span">
          <div class="ad-hoc-form-item menu-label" style="margin-right: 0;">
            <h4>Show Me The Past</h4>
          </div>
          <div class="dropdown ad-hoc-button">
            <!--        <input type="text" class="input input-append" name="time-span" value="4 hours"><span data-toggle="dropdown" class="input-addon-btn"><span class="iconic iconic-play rotate-90"></span></span>-->
            <span data-toggle="dropdown">
              <div class="ad-hoc-button-label" id="time-span" data-ms="<?php echo (HOUR * 4) ?>">4 hours</div>
              <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
            </span>
            <ul class="dropdown-menu menu-left" id="time-span-options" role="menu" aria-labelledby="dLabel">
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
      </td>
    </tr>
  </table>
</div>
<div class="ad-hoc-form-row" id="row2">
  <div class="ad-hoc-form-item menu-label" id="metric-search-table">
    <table class="table">
      <tr>
        <th>Metric</th>
        <th>Aggregation</th>
        <th colspan="2"><span class="info-tooltip" title="Downsampling is performed exclusively by StatusWolf, OpenTSDB downsampling can be idiosyncratic and has been shown to return unexpected graphs.">Downsampling <span class="iconic-info"></span></span></th>
        <th>Interpolation</th>
        <th>Rate</th>
        <th>Right Axis</th>
      </tr>
      <tr>
        <td width="40%">
          <div class="metric-input-textbox" id="metric1-input-textbox">
            <input type="text" class="metric-autocomplete" name="metric1">
          </div>
        </td>
        <td width="10%">
          <div class="dropdown ad-hoc-button">
            <span class="flexy" data-toggle="dropdown">
             <div class="ad-hoc-button-label" id="active-aggregation-type1">Sum</div>
             <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
            </span>
            <ul class="dropdown-menu" id="aggregation-type-options1" role="menu" aria-labelledby="dLabel">
              <li><span>Sum</span></li>
              <li><span>Average</span></li>
              <li><span>Minimum Value</span></li>
              <li><span>Maximum Value</span></li>
              <li><span>Standard Deviation</span></li>
            </ul>
          </div>
        </td>
        <td width="10%" style="padding-right: 0;">
          <div class="dropdown ad-hoc-button">
            <span class="flexy" data-toggle="dropdown">
              <div class="ad-hoc-button-label" id="active-downsample-type1">Maximum Value</div>
              <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
            </span>
            <ul class="dropdown-menu" id="downsample-type-options1" role="menu" aria-labelledby="dLabel">
              <li><span>Sum</span></li>
              <li><span>Average</span></li>
              <li><span>Minimum Value</span></li>
              <li><span>Maximum Value</span></li>
            </ul>
          </div>
        </td>
        <td width="7.5%" style="padding-left: 0;">
          <div class="dropdown ad-hoc-button" id="downsample-interval-button1">
            <span class="flexy" data-toggle="dropdown">
              <div class="ad-hoc-button-label ds-interval" id="active-downsample-interval1" data-value="1">1 minute</div>
              <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
            </span>
            <ul class="dropdown-menu ds-values" id="downsample-interval-options1" role="menu" aria-labelledby="dLabel">
             <li><span data-value="1">1 minute</span></li>
              <li><span data-value="10">10 minutes</span></li>
              <li><span data-value="30">30 minutes</span></li>
              <li><span data-value="60">1 hour</span></li>
              <li><span data-value="240">4 hours</span></li>
              <li><span data-value="720">12 hours</span></li>
             <li><span data-value="1440">1 day</span></li>
            </ul>
          </div>
        </td>
        <td width="7.5%">
          <div class="push-button binary pushed">
            <input type="checkbox" id="lerp-button1" name="lerp1" checked><label for="lerp-button1"><span class="iconic iconic-check-alt green"></span><span class="binary-label"> Yes</span></label>
          </div>
        </td>
        <td width="7.5%">
          <div class="push-button binary">
            <input type="checkbox" id="rate-button1" name="rate1"><label for="rate-button1"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> No</span></label>
          </div>
        </td>
        <td width="7.55%">
          <div class="push-button binary">
            <input type="checkbox" id="y2-button1" name="y2-1"><label for="y2-button1"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> No</span></label>
          </div>
        </td>
      </tr>
      <?php
      for ($i = 2; $i <= 6; $i++)
      {
        print '<tr class="optional-metric" id="metric-row' . $i . '">' . "\n";
        print "<td>\n";
        print '<div class="metric-input-textbox" id="metric' . $i . "-input-textbox\">\n";
        print '<input type="text" class="metric-autocomplete" name="metric' . $i . "\">\n";
        print "</div>\n</td>\n<td>\n<div class=\"dropdown ad-hoc-button\">\n<span class=\"flexy\" data-toggle=\"dropdown\">\n";
        print '<div class="ad-hoc-button-label" id="active-aggregation-type' . $i . "\">Sum</div>\n";
        print '<span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>' . "\n</span>\n</span>";
        print '<ul class="dropdown-menu" id="aggregation-type-options' . $i . '" role="menu" aria-labelledby="dLabel">' . "\n";
        print "<li><span>Sum</span></li>\n<li><span>Average</span></li>\n<li><span>Minimum Value</span></li>\n";
        print "<li><span>Maximum Value</span></li>\n<li><span>Standard Deviation</span></li>\n</ul></div></td>";
        print '<td style="padding-right: 0;">' . "\n";
        print '<div class="dropdown ad-hoc-button">' . "\n" . '<span class="flexy" data-toggle="dropdown">' . "\n";
        print '<div class="ad-hoc-button-label" id="active-downsample-type' . $i . '">Maximum Value</div>' . "\n";
        print '<span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>' . "\n</span>\n</span>";
        print '<ul class="dropdown-menu" id="downsample-type-options' . $i . '" role="menu" aria-labelledby="dLabel">' . "\n";
        print "<li><span>Sum</span></li>\n<li><span>Average</span></li>\n";
        print "<li><span>Minimum Value</span></li>\n<li><span>Maximum Value</span></li>\n</ul>\n</div>\n</td>\n";
        print '<td style="padding-left: 0;">' . "\n";
        print '<div class="dropdown ad-hoc-button" id="downsample-interval-button' . $i . '">' . "\n";
        print '<span class="flexy" data-toggle="dropdown">' . "\n";
        print '<div class="ad-hoc-button-label ds-interval" id="active-downsample-interval' . $i . '" data-value="1">1 minute</div>' . "\n";
        print '<span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>' . "\n</span>\n</span>";
        print '<ul class="dropdown-menu ds-values" id="downsample-interval-options' . $i . '" role="menu" aria-labelledby="dLabel">' . "\n";
        print '<li><span data-value="1">1 minute</span></li>' . "\n";
        print '<li><span data-value="10">10 minutes</span></li>' . "\n";
        print '<li><span data-value="30">30 minutes</span></li>' . "\n";
        print '<li><span data-value="60">1 hour</span></li>' . "\n";
        print '<li><span data-value="240">4 hours</span></li>' . "\n";
        print '<li><span data-value="720">12 hours</span></li>' . "\n";
        print '<li><span data-value="1440">1 day</span></li>' . "\n</ul>\n</div>\n</td>\n";
        print '<td>' . "\n" . '<div class="push-button binary pushed">' . "\n";
        print '<input type="checkbox" id="lerp-button' . $i . '" name="lerp' . $i . '" checked>';
        print '<label for="lerp-button' . $i . '"><span class="iconic iconic-check-alt green"></span>';
        print '<span class="binary-label"> Yes</span></label>' . "\n</div>\n</td>\n";
        print '<td>' . "\n" . '<div class="push-button binary">' . "\n";
        print '<input type="checkbox" id="rate-button' . $i . '" name="rate' . $i . '">';
        print '<label for="rate-button' . $i . '"><span class="iconic iconic-x-alt red"></span>';
        print '<span class="binary-label"> No</span></label>' . "\n</div>\n</td>\n";
        print '<td>' . "\n" . '<div class="push-button binary">' . "\n";
        print '<input type="checkbox" id="y2-button' . $i . '" name="y2-' . $i . '">';
        print '<label for="y2-button' . $i . '"><span class="iconic iconic-x-alt red"></span>';
        print '<span class="binary-label"> No</span></label>' . "\n</div>\n</td>\n</tr>\n";
      }
      ?>
    </table>
  </div>
</div>

<script type="text/javascript">

  var sw_conf = eval('(<?php echo json_encode($sw_conf); ?>)');
  var query_data = {};
  var query_url = '';
  var incoming_query_data = '<?php if ($incoming_query_data) { echo json_encode($incoming_query_data); } ?>';

  // Add the styles for the ad-hoc search
  $('head').append('<link href="<?php echo URL; ?>app/css/datetimepicker.css" rel="stylesheet">')
      .append('<link href="<?php echo URL; ?>app/css/toggle-buttons.css" rel="stylesheet">')
      .append('<link href="<?php echo URL; ?>app/css/push-button.css" rel="stylesheet">')
      .append('<link href="<?php echo URL; ?>app/css/table.css" rel="stylesheet">')
      .append('<link href="<?php echo URL; ?>app/css/loader.css" rel="stylesheet">')
      .append('<link href="<?php echo URL; ?>app/css/tooltip.css" rel="stylesheet">');

  // Add the handler for the date/time picker and init the form objects
  loadScript("<?php echo URL; ?>app/js/lib/bootstrap-datetimepicker.js", function() {
    $('#start-time').datetimepicker({collapse: false});
    $('#end-time').datetimepicker({collapse: false});
  });

  // Load the handler for metric name autocompletion and init the form objects
  loadScript("<?php echo URL; ?>app/js/lib/jquery.autocomplete.js", function(){
    $(".metric-autocomplete").autocomplete({
      minChars: 2
      ,serviceUrl: '<?php echo URL; ?>api/tsdb_metric_list/'
      ,containerClass: 'autocomplete-suggestions dropdown-menu'
      ,zIndex: ''
      ,maxHeight: ''
    });
  });

  // If anomaly or week-over week displays are chosen update the
  // time span menu to limit the search to 1 week or less
  $('.section-toggle').click(function() {
    $('#' + $(this).attr('data-target')).removeClass('section-off').addClass('section-on').siblings('.section').addClass('section-off').removeClass('section-on');
    if ($(this).attr('data-target') == 'ad-hoc-anomaly' || $(this).attr('data-target') == 'ad-hoc-wow')
    {
      $('.optional-metric').removeClass('section-on');
      $('.optional-metric').addClass('section-off');
      $('#time-span-options').html('<li><span data-ms="<?php echo (MINUTE * 10); ?>">10 minutes</span></li>')
          .append('<li><span data-ms="<?php echo (MINUTE * 30); ?>">30 Minutes</span></li>')
          .append('<li><span data-ms="<?php echo HOUR ?>">1 hour</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 2) ?>">2 hours</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 4) ?>">4 hours</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 8) ?>">8 hours</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 12) ?>">12 hours</span></li>')
          .append('<li><span data-ms="<?php echo DAY ?>">1 day</span></li>')
      if ($(this).attr('data-target') === "ad-hoc-wow")
      {
        $('#time-span-options').append('<li><span data-ms="<?php echo WEEK ?>">1 week</span></li>');
        if ($('#time-span').attr('data-ms') > <?php echo WEEK; ?>)
        {
          $('#time-span').text('1 week').attr('data-ms', <?php echo WEEK; ?>);
        }
      }
      else
      {
        if ($('#time-span').attr('data-ms') > <?php echo DAY; ?>)
        {
          $('#time-span').text('1 day').attr('data-ms', <?php echo DAY; ?>);
        }
      }
    }
    else if ($(this).attr('data-target') == 'ad-hoc-no')
    {
      $('.optional-metric').removeClass('section-off');
      $('.optional-metric').addClass('section-on');
      $('#time-span-options').html('<li><span data-ms="<?php echo (MINUTE * 10); ?>">10 minutes</span></li>')
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

  $('#time-span-options > li').click(function() {
    $('input[name="time-span"]').attr('value', ($(this).text()));
  });

  // Click handler for drop-down menu items
  $('ul.dropdown-menu').on('click', 'li', function() {
    var button = $(this).parents('.ad-hoc-button').children('span');
    $(button).children('.ad-hoc-button-label').text($(this).text());
    $(button).children('div.ds-interval').attr('data-value', $(this).children('span').attr('data-value'));
    if ($(button).children('#time-span'))
    {
      $(button).children('#time-span').attr('data-ms', $(this).children('span').attr('data-ms'));
    }
  });

  $('label').click(function() {
    statuswolf_button(this);
  });

  $('.info-tooltip').tooltip({placement: 'bottom'});
  $('.info-tooltip').hover(function() {$(this).css('cursor', 'default')});

  // On initial page load switch to the search form, and add the handler
  // for the Enter key
  $(document).ready(function() {
    if (incoming_query_data.length > 1)
    {
      if (incoming_query_data.match(/Expired/))
      {
        $('.container').append('<div id="expired-popup" class="popup"><h5>Expired</h5><div class="popup-form-data">Your search has expired and is no longer available</div></div>');
        $.magnificPopup.open({
          items: {
            src: '#expired-popup'
            ,type: 'inline'
          }
          ,preloader: false
          ,removalDelay: 300
          ,mainClass: 'popup-animate'
          ,callbacks: {
            open: function() {
              $('.navbar').addClass('blur');
              $('.container').addClass('blur');
            }
            ,close: function() {
              $('.container').removeClass('blur');
              $('.navbar').removeClass('blur');
              window.history.pushState("", "StatusWolf", "/adhoc/");
              $('.widget').addClass('flipped');
            }
          }
        });
      }
      else if (incoming_query_data.match(/Not Allowed/))
      {
        $('.container').append('<div id="not-allowed-popup" class="popup"><h5>Not Allowed</h5><div class="popup-form-data">You do not have permission to view this saved search</div></div>');
        $.magnificPopup.open({
          items: {
            src: '#not-allowed-popup'
            ,type: 'inline'
          }
          ,preloader: false
          ,removalDelay: 300
          ,mainClass: 'popup-animate'
          ,callbacks: {
            open: function() {
              $('.navbar').addClass('blur');
              $('.container').addClass('blur');
            }
            ,close: function() {
              $('.container').removeClass('blur');
              $('.navbar').removeClass('blur');
              window.history.pushState("", "StatusWolf", "/adhoc/");
              $('.widget').addClass('flipped');
            }
          }
        });
      }
      else if (incoming_query_data.match(/Not Found/))
      {
        $('.container').append('<div id="not-found-popup" class="popup"><h5>Not Found</h5><div class="popup-form-data">The saved search was not found.</div></div>');
        $.magnificPopup.open({
          items: {
            src: '#not-found-popup'
            ,type: 'inline'
          }
          ,preloader: false
          ,removalDelay: 300
          ,mainClass: 'popup-animate'
          ,callbacks: {
            open: function() {
              $('.navbar').addClass('blur');
              $('.container').addClass('blur');
            }
            ,close: function() {
              $('.container').removeClass('blur');
              $('.navbar').removeClass('blur');
              window.history.pushState("", "StatusWolf", "/adhoc/");
              $('.widget').addClass('flipped');
            }
          }
        });
      }
      else
      {
        query_data = eval('(' + incoming_query_data + ')');
        populate_form(query_data);
      }
    }
    else
    {
      $('.widget').addClass('flipped');
    }
  }).keypress(function(e) {
    if (e.which === 13)
    {
      if ($('.widget').hasClass('flipped'))
      {
        go_click_handler(e);
      }
    }
  });

  function populate_form(query_data)
  {

    $('title').text(query_data.title + ' - StatusWolf');

    var prompt_user = false;
    var method_map = {sum: 'Sum', avg: 'Average', min: 'Minimum Value', max: 'Maximum Value', dev: 'Standard Deviation'};

    if (query_data['auto_update'] === "true") {
      $('label#auto-update-label').click();
      $('label#auto-update-label').parent('.push-button').addClass('pushed');
      $('label#auto-update-label').children('span.iconic').removeClass('iconic-x-alt red').addClass('iconic-check-alt green');
    }
    if (query_data.history_graph.match(/anomaly/))
    {
      var el = $('input#history-anomaly').parent('label');
      $(el).parent('div.toggle-button').addClass('toggle-on');
      $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
      $(el).children('input').attr('checked', 'Checked');
      $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
      $('input#history-anomaly.section-toggle').click();
    }
    else if (query_data.history_graph.match(/wow/))
    {
      var el = $('input#history-wow').parent('label');
      $(el).parent('div.toggle-button').addClass('toggle-on');
      $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
      $(el).children('input').attr('checked', 'Checked');
      $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
      $('input#history-wow.section-toggle').click();
    }
    if (query_data['time_span'])
    {
      var el = $('input#span-search').parent('label');
      $(el).parent('div.toggle-button').addClass('toggle-on');
      $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
      $(el).children('input').attr('checked', 'Checked');
      $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
      $('input#span-search.section-toggle').click();
      var span = query_data['time_span'];
      $('#time-span-options li span[data-ms="' + span + '"]').parent('li').click();
    }
    else
    {
      if ((start_in = parseInt(query_data['start_time'])) && (end_in = parseInt(query_data['end_time'])))
      {
        $('input:text[name="start-time"]').val(new Date(start_in * 1000).toString('yyyy/MM/dd HH:mm:ss'));
        $('input:text[name="end-time"]').val(new Date(end_in * 1000).toString('yyyy/MM/dd HH:mm:ss'));
      }
      else
      {
        prompt_user = true;
      }
    }

    $.each(query_data['metrics'], function(i, metric) {
      metric_num = i + 1;
      metric_string = metric.name;
      if (metric.tags)
      {
        $.each(metric.tags, function(i, tag) {
          metric_string += ' ' + tag;
        });
      }
      $('input[name="metric' + metric_num + '"]').val(metric_string);
      $('#active-aggregation-type' + metric_num).text(method_map[metric.agg_type]);
      $('#active-downsample-type' + metric_num).text(method_map[metric.ds_type]);
      $('#downsample-interval-options' + metric_num + ' li span[data-value="' + metric.ds_interval + '"]').parent('li').click();
      if (!metric.lerp || metric.lerp === "false")
      {
        $('input#lerp-button' + metric_num).siblings('label').click();
        $('input#lerp-button' + metric_num).parent('.push-button').removeClass('pushed');
        $('input#lerp-button' + metric_num).siblings('label').children('span.iconic').addClass('iconic-x-alt red').removeClass('iconic-check-alt green');
        $('input#lerp-button' + metric_num).siblings('label').children('span.binary-label').text('No');
      }
      if (metric.rate && metric.rate !== "false")
      {
        $('input#rate-button' + metric_num).siblings('label').click();
        $('input#rate-button' + metric_num).parent('.push-button').addClass('pushed');
        $('input#rate-button' + metric_num).siblings('label').children('span.iconic').removeClass('iconic-x-alt red').addClass('iconic-check-alt green');
        $('input#rate-button' + metric_num).siblings('label').children('span.binary-label').text('Yes');
      }
      if (metric.y2 && metric.y2 !== "false")
      {
        $('input#y2-button' + metric_num).siblings('label').click();
        $('input#y2-button' + metric_num).parent('.push-button').addClass('pushed');
        $('input#y2-button' + metric_num).siblings('label').children('span.iconic').removeClass('iconic-x-alt red').addClass('iconic-check-alt green');
        $('input#y2-button' + metric_num).siblings('label').children('span.binary-label').text('Yes');
      }
    });
    if (prompt_user)
    {
      $('.widget').addClass('flipped');
    }
    else
    {
      go_click_handler();
    }
  }

  // Function to build the graph when the form Go button is activated
  function go_click_handler(event)
  {
    query_data = {};
    query_data.datasource = 'OpenTSDB';
    query_data.downsample_master_interval = 0;
    query_data.new_query = true;
    var input_error = false;
    var methods = {'sum': 'sum', 'average': 'avg', 'minimum value': 'min', 'maximum value': 'max', 'standard deviation': 'dev'};

    if (typeof autoupdate_interval !== "undefined")
    {
      console.log('clearing auto-update timer');
      clearInterval(autoupdate_interval);
      delete autoupdate_interval;
    }
    // Validate the input before we do anything else

    // Date range validation
    var start, end;
    if ($('input:radio[name=date-span]:checked').val() == 'date-search')
    {
      if ($('input:text[name=start-time]').val().length < 1)
      {
        $('input:text[name=start-time]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
        alert("You must specify a start time");
      }
      else if ($('input:text[name=end-time]').val().length < 1)
      {
        $('input:text[name=end-time]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
        alert("You must specify an end time");
      }

      // Start date has to be before the End date.
      start = Date.parse($('input:text[name=start-time]').val()).getTime();
      start = start / 1000;
      end = Date.parse($('input:text[name=end-time]').val()).getTime();
      end = end / 1000;
      if (start >= end)
      {
        alert('Start time must come before end time');
        $('input:text[name=start-time]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
      }
      if (typeof query_data['time_span'] != "undefined")
      {
        delete query_data['time_span'];
      }
      query_data['time_span'] = end - start;
    }
    else
    {
      end = new Date.now().getTime();
      end = parseInt(end / 1000);
      var span = parseInt($('#time-span').attr('data-ms'));
      start = (end - span);
      var jstart = new Date(start * 1000).toString('yyyy/MM/dd HH:mm:ss');
      var jend = new Date(end * 1000).toString('yyyy/MM/dd HH:mm:ss');
      $('input[name=start-time]').val(jstart).change();
      $('input[name=end-time]').val(jend).change();
      query_data['time_span'] = span;
    }
    query_data['start_time'] = start;
    query_data['end_time'] = end;

    // Check for auto-update flag
    if ($('input:checkbox[name=auto-update]:checked').val() === 'on')
    {
      query_data['auto_update'] = true;
    }
    else
    {
      query_data['auto_update'] = false;
    }

    // Check for history display options
    query_data.history_graph = $('input:radio[name=history-graph]:checked').val();
    if (query_data.history_graph === 'no')
    {
      query_data['metrics_count'] = 6;
    }
    else
    {
      query_data['metrics_count'] = 1;
    }
    query_data['metrics'] = [];

    // Validate metrics to search on
    if (query_data['metrics_count'] > 1)
    {
      for (i=1; i<=query_data['metrics_count']; i++)
      {
        var build_metric = {};
        var metric_bits = $('input:text[name=metric'+ i + ']').val().split(' ');
        build_metric.name = metric_bits.shift();
        if (build_metric.name.length < 1)
        {
          continue;
        }
        if (metric_bits.length > 0)
        {
          build_metric.tags = metric_bits;
        }
        var agg_type = $('#active-aggregation-type' + i).text().toLowerCase();
        var ds_type = $('#active-downsample-type' + i).text().toLowerCase();
        build_metric.agg_type = methods[agg_type];
        build_metric.ds_type = methods[ds_type];
        build_metric.ds_interval = $('#active-downsample-interval' + i).attr('data-value');
        if ((query_data['downsample_master_interval'] < 1) || (build_metric.ds_interval < query_data['downsample_master_interval']))
        {
          query_data['downsample_master_interval'] = build_metric.ds_interval;
        }

        if ($('#rate-button' + i).prop('checked'))
        {
          build_metric.rate = true;
        }

        if ($('#lerp-button' + i).prop('checked'))
        {
          build_metric.lerp = true;
        }
        if ($('#y2-button' + i).prop('checked'))
        {
          build_metric.y2 = true;
        }

        query_data['metrics'].push(build_metric);

      }
      if (query_data['metrics'].length < 1)
      {
        $('input:text[name=metric1]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
        alert("You must specify at least one metric to search for");
        input_error = true;
      }
      else
      {
        input_error = false;
      }
    }
    else
    {
      if ($('input:text[name=metric1]').val().length < 1)
      {
        $('input:text[name=metric1]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
        alert("You must specify a metric to search for");
        input_error = true;
      }
      else if (query_data.history_graph === "wow" && (end - start) / 60 > 10080)
      {
        alert('Week-over-week history comparison searches are limited to 1 week or less of data');
        $('input:text[name=start-time]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
        input_error = true;
      }
      else if (query_data.history_graph === "anomaly" && (end - start) > 86400)
      {
        alert('Anomaly detection searches are limited to 1 day or less');
        $('input:text[name=start-time]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
        input_error = true;
      }
      else
      {
        input_error = false;
        var build_metric = {};
        var metric_bits = $('input:text[name=metric1]').val().split(' ');
        build_metric.name = metric_bits.shift();
        if (metric_bits.length > 0)
        {
          build_metric.tags = metric_bits;
        }
        var agg_type = $('#active-aggregation-type1').text().toLowerCase();
        var ds_type = $('#active-downsample-type1').text().toLowerCase();
        build_metric.agg_type = methods[agg_type];
        build_metric.ds_type = methods[ds_type];
        build_metric.ds_interval = $('#active-downsample-interval1').attr('data-value');
        query_data['downsample_master_interval'] = build_metric.ds_interval;

        if ($('#rate-button1').prop('checked'))
        {
          build_metric.rate = true;
        }

        if ($('#lerp-button1').prop('checked'))
        {
          build_metric.lerp = true;
        }

        if ($('y2-button1').prop('checked'))
        {
          build_metric.y2 = true;
        }
      }
      query_data['metrics'].push(build_metric);
    }

    // If we made it this far without errors in the form input, then
    // we build us a graph
    if (input_error == false)
    {
      var graph_element = $('#graphdiv');
      // Make sure the graph display div is empty
      graph_element.empty();
      // Clear the graph legend
      $('#legend').empty();
      // Load the waiting spinner
      graph_element.append('<div class="bowlG"><div class="bowl_ringG"><div class="ball_holderG"><div class="ballG"></div></div></div></div>');
      $('.widget').removeClass('flipped');
      graph_element.append('<div id="status-box" style="width: 100%; text-align: center;"><p id="status-message"></p></div>');
      $('#status-box').append('<p id=chuck style="margin: 0 25px"></p>');

      if (incoming_query_data.length > 1)
      {
        var form_change = 0;
        var incoming_query = eval('(' + incoming_query_data + ')');
        if (incoming_query.metrics.length == query_data.metrics.length)
        {
          $.each(incoming_query.metrics, function(i, metric)
          {
            if ((typeof metric.lerp !== "undefined") && (metric.lerp === "true"))
            {
              metric.lerp = true;
            }
            if ((typeof metric.rate !== "undefined") && (metric.rate === "true"))
            {
              metric.rate = true;
            }
            if ((typeof metric.y2 !== "undefined") && (metric.y2 === "true"))
            {
              metric.y2 = true;
            }
            if (typeof metric.history_graph != "undefined")
            {
              delete metric.history_graph;
            }
            if (JSON.stringify(incoming_query.metrics[i]) != JSON.stringify(query_data.metrics[i]))
            {
              form_change++;
            }
          });
        }
        else
        {
          form_change++;
        }

        if (form_change > 0)
        {
          window.history.pushState("", "StatusWolf", "/adhoc/");
          delete incoming_query;
          incoming_query_data = '';
        }
      }

      init_query(query_data);
    }
  }

  function init_query(query_data) {

    // Start deferred query for metric data
    $.when(opentsdb_search(query_data)).then(
        // done: Send the data over to be parsed
        function(data)
        {
          $.when(process_graph_data(data, query_data)).then(
              // done: Build the graph
              function(data)
              {
                build_graph(data.graphdata, data.querydata);
              }
              // fail: Show error image and error message
              ,function(status)
              {
                $('#bowlG').html('<img src="<?php echo URL; ?>app/img/error.png" style="width: 60px; height: 30px;">');
                $('#chuck').removeClass('section-on').addClass('section-off');
                $('#status-message').text(status);
              }
              // progress: Show any progress status messages received
              ,function(status)
              {
                $('#chuck').removeClass('section-on').addClass('section-off');
                $('#chuck').text(status);
                $('#chuck').removeClass('section-off').addClass('section-on');
              }
          );
        }
        // fail: Show error image and error message
        ,function(status)
        {
          $('#bowlG').css({'padding-top': '15%', 'margin-top': '0', 'margin-bottom': '5px', 'width': '120px', 'height': '60px'}).html('<img src="<?php echo URL; ?>app/img/error.png" style="width: 120px; height: 60px;">');
          $('#status-box').empty().append('<p>' + status.shift() + '</p>');
          if ((status[0].match("<!DOCTYPE")) || (status[0].match("<html>")))
          {
            var error_message = status.join(' ').replace(/'/g,"&#39");
            $('#status-box').append('<iframe style="margin: 0 auto; color: rgb(205, 205, 205);" width="80%" height="90%" srcdoc=\'' + error_message + '\' seamless></iframe>');
          }
          else
          {
            $('#status-box').text(status);
          }
        }
        // progress: Show any progress status messages received
        ,function(status)
        {
          $('#chuck').removeClass('section-on').addClass('section-off');
          $('#chuck').text(status);
          $('#chuck').removeClass('section-off').addClass('section-on');
        }
    );
  }

  // Function to wrap the OpenTSDB search
  function opentsdb_search(query_data)
  {
    var query_object = new $.Deferred();

    // Generate (or find the cached) model data for the metric
    if (query_data.history_graph === "anomaly")
    {
      $('#status-message').html('<p>Fetching Metric Data</p>');
      $.when(get_metric_data_anomaly(query_data)
          .done(function(data) {
            query_object.resolve(data);
          })
          .fail(function(data) {
            query_object.reject(data);
          })
      );
    }
    // Search current and previous week for metric data
    else if (query_data.history_graph === "wow")
    {
      $('#status-message').html('<p>Fetching Week-Over-Week Data</p>');
      $.when(get_metric_data_wow(query_data)
          .done(function(data) {
            query_object.resolve(data);
          })
          .fail(function(data) {
            query_object.reject(data);
          })
      );
    }
    else
    {
      $('#status-message').html('<p>Fetching Metric Data</p>');
      $.when(get_metric_data(query_data)
          .done(function(data) {
            query_object.resolve(data);
          })
          .fail(function(data) {
            query_object.reject(data);
          })
      );
    }

    return query_object.promise();

  }


  // AJAX function to search OpenTSDB
  function get_metric_data(query_data)
  {

    if (typeof ajax_request !== 'undefined')
    {
      console.log('Previous request still in flight, aborting');
      ajax_request.abort();
    }

    ajax_object = new $.Deferred();

    ajax_request = $.ajax({
          url: "<?php echo URL; ?>adhoc/search/OpenTSDB"
          ,type: 'POST'
          ,data: query_data
          ,dataType: 'json'
          ,timeout: 120000
        })
        ,chain = ajax_request.then(function(data) {
          return(data);
        });

    chain.done(function(data) {
      if (data[0] === "error")
      {
        ajax_request.abort();
        ajax_object.reject(data[1])
      }
      else if (Object.getOwnPropertyNames(data).length <= 5)
      {
        ajax_request.abort();
        ajax_object.reject(["0", "Query returned no data"]);
      }
      else
      {
        delete ajax_request;
        ajax_object.resolve(data);
      }
    });

    return ajax_object.promise();

  }

  // AJAX function to search for Week-Over-Week data in OpenTSDB
  function get_metric_data_wow(query_data)
  {

    if (typeof ajax_request !== 'undefined')
    {
      ajax_request.abort();
    }

    ajax_object = new $.Deferred();

    var metric_data = {};

    ajax_request = $.ajax({
          url: "<?php echo URL; ?>adhoc/search/OpenTSDB"
          ,type: 'POST'
          ,data: query_data
          ,data_type: 'json'
          ,timeout: 120000
        })
        ,chained = ajax_request.then(function(data) {
          metric_data[0] = eval('(' + data + ')');
          if (metric_data[0][0] === "error")
          {
            ajax_request.abort();
            ajax_object.reject(metric_data[0][1])
          }
          else if (Object.keys(metric_data[0]).length <= 5)
          {
            ajax_request.abort();
            ajax_object.reject(["0", "Current week query returned no data"]);
          }
          else
          {
            $('#status-message').html('<p>Fetching Metric Data</p>');
            metric_data.start = metric_data[0]['start'];
            delete metric_data[0]['start'];
            metric_data.end = metric_data[0]['end'];
            delete metric_data[0]['end'];
            metric_data.query_url = metric_data[0]['query_url'];
            delete metric_data[0]['query_url'];
            current_keys = Object.keys(metric_data[0]);
            current_key = 'Current - ' + current_keys[0];
            metric_data[current_key] = metric_data[0][current_keys[0]];
            delete metric_data[0];
            var past_query = $.extend(true, {}, query_data);
            var query_span = parseInt(query_data.end_time) - parseInt(query_data.start_time);
            past_query.end_time = parseInt(query_data.end_time - <?php echo WEEK; ?>);
            past_query.start_time = past_query.end_time - query_span;
            return $.ajax({
              url: "<?php echo URL; ?>adhoc/search/OpenTSDB"
              ,type: 'POST'
              ,data: past_query
              ,data_type: 'json'
              ,timeout: 120000
            });
          }
        });
    chained.done(function(data) {
      if (!data)
      {
        ajax_request.abort();
        ajax_object.reject();
      }
      else
      {
        metric_data[1] = eval('(' + data + ')');
        if (metric_data[1][0] === "error")
        {
          ajax_request.abort();
          ajax_object.reject(metric_data[1][1])
        }
        else if (Object.getOwnPropertyNames(metric_data[1]).length <= 5)
        {
          ajax_request.abort();
          ajax_object.reject(["0", "Previous week query returned no data"]);
        }
        else
        {
          delete metric_data[1].start;
          delete metric_data[1].end;
          delete metric_data[1].query_url;
          past_keys = Object.keys(metric_data[1]);
          past_key = 'Previous - ' + past_keys[0];
          metric_data[past_key] = metric_data[1][past_keys[0]];
          delete metric_data[1];
          $.each(metric_data[past_key], function(index, entry) {
            entry.timestamp = parseInt(entry.timestamp + <?php echo WEEK; ?>);
          });
          delete ajax_request;
          ajax_object.resolve(metric_data);
        }
      }
    });

    return ajax_object.promise();
  }

  // AJAX function to build/get the model data, current data and anomalies
  function get_metric_data_anomaly(query_data)
  {

    if (typeof ajax_request !== 'undefined')
    {
      ajax_request.abort();
    }

    ajax_object = new $.Deferred();

    var metric_data = {};
    var data_for_detection = [];

    var ajax_request = $.ajax({
      url: "<?php echo URL; ?>adhoc/search/OpenTSDB"
      ,type: 'POST'
      ,data: query_data
      ,dataType: 'json'
      ,timeout: 120000
    })
    ,chained_request_1 = ajax_request.then(function(data) {
      if (data[0] === "error")
      {
        ajax_request.abort();
        ajax_object.reject(data[1])
      }
      else if (Object.getOwnPropertyNames(data).length <= 5)
      {
        ajax_request.abort();
        ajax_object.reject(["0", "Query returned no data"]);
      }
      else
      {
        metric_data = data;
        var past_query = $.extend(true, {}, query_data);
        past_query.end_time = metric_data.start - 1;
        past_query.start_time = past_query.end_time - sw_conf.anomalies.pre_anomaly_period;
        past_query.cache_key = metric_data.cache_key + '_pre';
        $('#status-message').html('<p>Fetching data for anomaly detection</p>');
        return $.ajax({
          url: "<?php echo URL; ?>adhoc/search/OpenTSDB"
          ,type: 'POST'
          ,data: past_query
          ,data_type: 'json'
          ,timeout: 120000
        });
      }
    })
    ,chained_request_2 = chained_request_1.then(function(data) {
      if (!data)
      {
        ajax_request.abort();
        ajax_object.reject();
      }
      else
      {
        if (typeof(data) !== "object")
        {
          var pre_period_data = eval('(' + data + ')');
        }
        else
        {
          pre_period_data = data;
        }
        if (pre_period_data[0] === "error")
        {
          ajax_request.abort();
          ajax_object.reject(pre_period_data[1])
        }
        else if (Object.getOwnPropertyNames(pre_period_data).length <= 5)
        {
          ajax_request.abort();
          ajax_object.reject(["0", "Query returned no data"]);
        }
        else
        {
          var pre_period_cache = pre_period_data.query_cache;
          delete pre_period_data;
          data_for_detection = {metric: query_data.metrics[0]['name'], cache: metric_data.query_cache, pre_cache: pre_period_cache};
          $('#status-message').html('<p>Calculating anomalies</p>');
          return $.ajax({
            url: "<?php echo URL; ?>api/detect_timeseries_anomalies"
            ,type: 'POST'
            ,data: data_for_detection
            ,data_type: 'json'
          });
        }
      }
    });

    chained_request_2.done(function(data) {
      if (!data)
      {
        ajax_request.abort();
        ajax_object.reject();
      }
      else
      {
        metric_data['anomalies'] = eval('(' + data + ')');
        if (metric_data[0] === "error")
        {
          ajax_request.abort();
          ajax_object.reject(metric_data[1])
        }
        else
        {
          ajax_object.resolve(metric_data);
        }
      }
    }).fail(function(data) {
          console.log(data);
      ajax_request.abort();
      ajax_object.reject([data.status, data.statusText]);
    });

    return ajax_object.promise();
  }

  // Function to parse the returned OpenTSDB data
  function process_graph_data(data, query_data)
  {
    var parse_object = new $.Deferred();

    var labels = ['Timestamp'];
    var buckets = {};
    var bucket_interval = parseInt(query_data['downsample_master_interval'] * 60);
    var start = parseInt(data.start);
    var end = parseInt(data.end);
    query_url = data.query_url;
    query_data.cache_key = data.cache_key;
    delete data.start;
    delete data.end;
    delete data.query_url;
    delete data.cache_key;
    delete data.query_cache;
    if (query_data.history_graph === "anomaly")
    {
      var anomalies = data.anomalies;
      delete data.anomalies;
    }

    $('#status-message').html('<p>Parsing Metric Data</p>');

    for (var i = start; i <= end; i = i + bucket_interval)
    {
      buckets[i] = [];
    }

    for (var series in data) {
      if (data.hasOwnProperty(series))
      {
        if (query_data.history_graph === "anomaly")
        {
          query_data.metrics[0]['history_graph'] = "anomaly";
        }
        labels.push(series);

        var data_holder = {};
        data[series].forEach(function(series_data, index) {
          data_holder[series_data['timestamp']] = series_data['value'];
        });

        for (var timestamp in buckets)
        {
          if (buckets.hasOwnProperty(timestamp))
          {
            if (data_holder[timestamp] != undefined)
            {
              buckets[timestamp].push(data_holder[timestamp]);
            }
            else
            {
              buckets[timestamp].push(null);
            }
          }
        }

      }

      var graph_data = {};
      graph_data.labels = labels;
      graph_data.data = buckets;
      if (query_data.history_graph === "anomaly")
      {
        graph_data.anomalies = anomalies;
      }
    }

    var parsed_data = {graphdata: graph_data, querydata: query_data};

    parse_object.resolve(parsed_data);

    return parse_object.promise();

  }

  // Function to build the graph from the OpenTSDB metric data
  function build_graph(data, query_data)
  {

    $('.widget-footer-button.hidden').removeClass('hidden');

    var graph_data = data.data;
    var graph_labels = data.labels;
    var dygraph_format = [];
    var series_times = [];
    for (var timestamp in graph_data) {
      if (graph_data.hasOwnProperty(timestamp))
      {
        series_times.push(timestamp);
        jtime = new Date(parseInt(timestamp * 1000));
        values = [jtime];
        values = values.concat(graph_data[timestamp]);
        dygraph_format.push(values);
      }
    }

    series_times = series_times.splice(-4, 4);

    var labels_map = {};
    $.each(graph_labels, function(index, label) {
      var label_bits = label.split(' ');
      if (typeof labels_map[label_bits[0]] == 'undefined')
      {
        labels_map[label_bits[0]] = [];
      }
      labels_map[label_bits[0]].push(label);
    });

    var x_space = $('#graphdiv').width() / 12;
    var y_space = $('#graphdiv').height() / 12;
    var g_width = $('#graphdiv').innerWidth() * .95;

    g = new Dygraph(
        document.getElementById('graphdiv')
        ,dygraph_format
        ,{
          labels: graph_labels
          ,labelsDiv: 'legend'
          ,legend: 'always'
          ,axisLabelsFontSize: 13
          ,labelsKMB: true
          ,labelsDivWidth: g_width
          ,labelsSeparateLines: true
          ,rangeSelectorHeight: 10
          ,animatedZooms: true
          ,labelsDivStyles: {
            fontFamily: 'Arial'
            ,fontWeight: 'bold'
            ,color: 'rgba(234, 234, 234, 0.75)'
            ,backgroundColor: 'rgb(24, 24, 24)'
            ,textAlign: 'right'
          }
          ,strokeWidth: 2
          ,gridLineColor: 'rgba(234, 234, 234, 0.15)'
          ,axisLabelColor: 'rgba(234, 234, 234, 0.75)'
          ,colors: swcolors.Wheel_DarkBG[5]
          ,axes: {
            x: {
              pixelsPerLabel: x_space
              ,axisLineColor: 'rgba(234, 234, 234, 0.15)'
            }
            ,y: {
              pixelsPerLabel: y_space
              ,axisLineColor: 'rgba(234, 234, 234, 0.15)'
            }
          }
          ,highlightSeriesOpts: { strokeWidth: 3 }
          ,highlightSeriesBackgroundAlpha: 1
          ,connectSeparatedPoints: true
        }
    );
    // Set up the right axis labels, if requested
    var right_axis = '';
    $.each(query_data.metrics, function(i, metric) {
      if (metric.y2 == true)
      {
        var axis_bits = {};
        $.each(labels_map[metric.name], function(i, label)
        {
          if (right_axis.length < 1)
          {
            axis_bits = {};
            axis_bits[label] = {};
            axis_bits[label]['axis'] = {};
            right_axis = label;
          }
          else
          {
            axis_bits = {};
            axis_bits[label] = {};
            axis_bits[label]['axis'] = right_axis;
          }
          g.updateOptions(axis_bits);
        });
      }
    });

    // Set up the anomaly highlighting if requested
    if (query_data.history_graph == "anomaly")
    {
      anomalies = data.anomalies;
      g.updateOptions({
        underlayCallback: function(canvas, area, g) {
          canvas.fillStyle = "rgba(219, 54, 9, 0.25)";
          function highlight_period(x_start, x_end) {
            var canvas_left_x = g.toDomXCoord(x_start);
            var canvas_right_x = g.toDomXCoord(x_end);
            var canvas_width = canvas_right_x - canvas_left_x;
            canvas.fillRect(canvas_left_x, area.y, canvas_width, area.h);
          }
          $.each(anomalies, function(i, d) {
            highlight_period(new Date(parseInt(d.start * 1000)), new Date(parseInt(d.end * 1000)));
          });
        }
      })
    }
    $('.dygraph-xlabel').parent().css('top', '40%');

    // Set the interval for adding new data if Auto Update is selected
    if (query_data['auto_update'])
    {
      console.log('setting auto-update timer');
      autoupdate_interval = setInterval(function() {
        var new_start = parseInt(series_times[0]);
        var new_end = new Date.now().getTime();
        new_end = parseInt(new_end / 1000);
        query_data.start_time = new_start;
        query_data.end_time = new_end;
        query_data.new_query = false;
        $.when(opentsdb_search(query_data)).then(
            function(data)
            {
              $.when(process_graph_data(data, query_data)).then(
                  function(data)
                  {
                    var dygraph_update = new Array();
                    graph_data = data.graphdata.data;
                    var end_trim = 0;
                    for (var timestamp in graph_data) {
                      if (graph_data.hasOwnProperty(timestamp))
                      {
                        if ($.inArray(timestamp, series_times) >= 0)
                        {
                          end_trim++;
                        }
                        else
                        {
                          series_times.push(timestamp);
                        }
                        jtime = new Date(parseInt(timestamp * 1000));
                        values = [jtime];
                        values = values.concat(graph_data[timestamp]);
                        dygraph_update.push(values);
                      }
                    }
                    dygraph_format.splice(0, dygraph_update.length);
                    if (end_trim > 0)
                    {
                      dygraph_format.splice(-end_trim, end_trim);
                    }
                    dygraph_format = dygraph_format.concat(dygraph_update);
                    g.updateOptions({'file': dygraph_format});
                    series_times = series_times.splice(-4, 4);
                  }
              );
            }
        );
      }, 300 * 1000);
    }

  }


</script>