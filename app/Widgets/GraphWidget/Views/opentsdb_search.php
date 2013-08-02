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
  <div class="auto-update">
    <div class="push-button">
      <input type="checkbox" name="auto-update">
      <label>
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
<div class="graph-widget-form-row row3">
  <div class="metric-input-tabs tabbable tab-below" style="width: 99%;">
    <div class="tab-content"></div>
    <ul class="nav nav-tabs"></ul>
  </div>
</div>

<link rel="stylesheet" href="<?php echo URL; ?>app/css/datetimepicker.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/toggle-buttons.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/push-button.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/table.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/loader.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/tooltip.css">
<link rel="stylesheet" href="<?php echo URL . WIDGETS_URL; ?>GraphWidget/css/custom.graph_widget.css">
<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/dygraph-combined.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/status_wolf_colors.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/push-button.js"></script>

<script type="text/javascript">

  var widget_instance = $('div.graph-widget-search-form').parents('div.widget-container').data('sw-graphwidget');
  var widget_num = widget_instance.uuid;

  widget_instance.sw_graphwidget_fronttitle.children('.graph-widget-legend').attr('id', 'legend' + widget_num);
  $(widget_instance.element).children('.widget').children('.widget-front').children('.widget-main')
      .append('<div id="graphdiv' + widget_num + '" style="height: 99%; width: 99%;">');

  var auto_update = $(widget_instance.element).find('div.auto-update').children('div.push-button');
  $(auto_update).children('input').attr('id', 'auto-update-button' + widget_num);
  $(auto_update).children('label').attr('for', 'auto-update-button' + widget_num);

  var sw_conf = eval('(<?php echo json_encode($sw_conf); ?>)')
      ,query_data = {}
      ,query_url = ''
      ,incoming_query_data = '<?php if ($incoming_query_data) { echo json_encode($incoming_query_data); } ?>';

  widget_instance.metric_count = 0;

  // Load the handler for the toggle-buttons
  loadScript("<?php echo URL; ?>app/js/toggle-buttons.js", function(){});
  // Load the handler for metric name autocompletion and init the form objects
  loadScript("<?php echo URL; ?>app/js/lib/jquery.autocomplete.js", function(){
    $(widget_instance.sw_graphwidget_searchform.children('.row3').find('.metric-autocomplete')).autocomplete({
      minChars: 2
      ,serviceUrl: '<?php echo URL; ?>api/tsdb_metric_list/'
      ,containerClass: 'autocomplete-suggestions dropdown-menu'
      ,zIndex: ''
      ,maxHeight: ''
      ,width: '300px'
    });
  });

  var add_metric_button_id = 'add-metric-button' + widget_num + '-' + widget_instance.metric_count;
  widget_instance.sw_graphwidget_querycancelbutton.after('<div id="' + add_metric_button_id + '">');
  $('#' + add_metric_button_id).addClass('widget-footer-button left-button')
      .click(function() {
        widget_instance.metric_count = add_tab(widget_instance.metric_count, widget_num);
        widget_instance.sw_graphwidget_searchform.find('input[name="metric' + widget_num + '-' + widget_instance.metric_count + '"]').autocomplete({
          minChars: 2
          ,serviceUrl: '<?php echo URL; ?>api/tsdb_metric_list/'
          ,containerClass: 'autocomplete-suggestions dropdown-menu'
          ,zIndex: ''
          ,maxHeight: ''
          ,width: '300px'
        });
        if (widget_instance.metric_count == 6)
        {
          $(this).addClass('hidden');
        }
      })
      .append('<span class="iconic iconic-plus-alt"> Add Metric</span>');


  // Add the handler for the date/time picker and init the form objects
  var start_time = $(widget_instance.element).find('div[data-name="start-time"]');
  var end_time = $(widget_instance.element).find('div[data-name="end-time"]');
  loadScript("<?php echo URL; ?>app/js/lib/bootstrap-datetimepicker.js", function() {
    $(start_time).datetimepicker({collapse: false});
    $(end_time).datetimepicker({collapse: false});
  });

  // If anomaly or week-over week displays are chosen update the
  // time span menu to limit the search to 1 week or less
  $('.section-toggle').click(function() {
    var data_target = $(this).attr('data-target');
    var section = $(widget_instance.element).find('div[data-name="' + data_target + '"]');
    console.log(section);
    section.removeClass('section-off').addClass('section-on').siblings('.section').addClass('section-off').removeClass('section-on');
    if (data_target == 'history-anomaly' || data_target == 'history-wow')
    {
      $(widget_instance.element).find('div.optional-metric').removeClass('section-on');
      $(widget_instance.element).find('div.optional-metric').addClass('section-off');
      $(widget_instance.element).find('ul.dropdown-menu[data-name="time-span-options"]').html('<li><span data-ms="<?php echo (MINUTE * 10); ?>">10 minutes</span></li>')
          .append('<li><span data-ms="<?php echo (MINUTE * 30); ?>">30 Minutes</span></li>')
          .append('<li><span data-ms="<?php echo HOUR ?>">1 hour</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 2) ?>">2 hours</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 4) ?>">4 hours</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 8) ?>">8 hours</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 12) ?>">12 hours</span></li>')
          .append('<li><span data-ms="<?php echo DAY ?>">1 day</span></li>')
          .append('<li><span data-ms="<?php echo WEEK ?>">1 week</span></li>');
      if ($(widget_instance.element).find('div.graph-widget-button-label[data-name="time-span"]').attr('data-ms') > <?php echo WEEK; ?>)
      {
        $(widget_instance.element).find('div.graph-widget-button-label[data-name="time-span"]').text('1 week').attr('data-ms', <?php echo WEEK; ?>);
      }
      widget_instance.sw_graphwidget_searchform.find('ul#tab-list' + widget_num + ' a[href="#tab' + widget_num + '-1"]').click();
      widget_instance.sw_graphwidget_searchform.find('ul.nav-tabs').addClass('hidden');
      $('#' + add_metric_button_id).addClass('hidden');
    }
    else if (data_target == 'history-no')
    {
      $(widget_instance.element).find('div.optional-metric').removeClass('section-off');
      $(widget_instance.element).find('div.optional-metric').addClass('section-on');
      $(widget_instance.element).find('ul.dropdown-menu[data-name="time-span-options"]').html('<li><span data-ms="<?php echo (MINUTE * 10); ?>">10 minutes</span></li>')
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
      widget_instance.sw_graphwidget_searchform.find('ul.nav-tabs').removeClass('hidden');
      $('#' + add_metric_button_id).removeClass('hidden');
    }

//    var time_span_options = $(widget_instance.element).find('ul[data-name="time-span-options"]');
//    $(time_span_options).children('li').click(function() {
//      $(time_span_options).parent().children('span').children('div[data-name="time-span"]')
//          .attr('data-ms', $(this).children('span').attr('data-ms')).text($(this).text());
//    });


  });

  $(function() {
    build_saved_search_menu(widget_instance);
    console.log('adding first metric tab');
    widget_instance.metric_count = add_tab(widget_instance.metric_count, widget_num);
    var widget_height = $(widget_instance.element).children('.widget').innerHeight();
    var main_height = widget_height - (widget_instance.sw_graphwidget_fronttitle.height() + widget_instance.sw_graphwidget_frontfooter.height());
    console.log('widget height: ' + widget_height + ', main height: ' + main_height);
    widget_instance.sw_graphwidget_frontmain.css('height', main_height);
    widget_instance.sw_graphwidget_backmain.css('height', main_height);
    var row1 = widget_instance.sw_graphwidget_searchform.children('.row1').outerHeight(true);
    var row2 = widget_instance.sw_graphwidget_searchform.children('.row2').outerHeight(true);
    $(widget_instance.sw_graphwidget_searchform.children('.row3')).css('height', main_height - (row1 + row2));
    console.log('row 3: ' + widget_instance.sw_graphwidget_searchform.children('.row3').height());
    $('label').click(function() {
      push_button(this);
    });
    $('ul.dropdown-menu').on('click', 'li', function() {
      dropdown_menu_handler(this);
    });
  });

  function dropdown_menu_handler(item)
  {
    var button = $(item).parent().parent().children('span');
    $(button).children('.graph-widget-button-label').text($(item).text());
    $(button).children('div.ds-interval').attr('data-value', $(item).children('span').attr('data-value'));
    if ($(this).parent().attr('data-name') === "time-span-options")
    {
      console.log('setting time span');
      $(button).find('div[data-name="time-span"]').attr('data-ms', $(item).children('span').attr('data-ms')).text($(item).text());
    }
  }
  function build_saved_search_menu(widget)
  {
    var user_id = "<?php echo $_session_data['user_id']; ?>";
    var api_url = '<?php echo URL; ?>api/get_saved_searches';
    api_query = {user_id: user_id};
    $.ajax({
      url: api_url
      ,type: 'POST'
      ,data: api_query
      ,dataType: 'json'
      ,success: function(data) {
        my_searches = data['user_searches'];
        public_searches = data['public_searches'];
        var saved_search_list = widget.sw_graphwidget_backtitle.children('.saved-searches-menu').children('ul.saved-searches-options')
        saved_search_list.empty();
        saved_search_list.append('<li class="menu-section"><span>My Searches</span></li>');
        if (my_searches)
        {
          $.each(my_searches, function(i, search) {
            saved_search_list.append('<li><span data-name="search-' + search['id'] + '">' + search['title'] + '</span></li>');
          });
        }
        if (public_searches)
        {
          saved_search_list.append('<li class="menu-section"><span class="divider"></span></li>');
          saved_search_list.append('<li class="menu-section"><span>Public Searches</span></li>');
          $.each(public_searches, function(i, public) {
            saved_search_list.append('<li><span data-name="search-' + public['id'] + '">' + public['title'] + ' (' + public['username'] + ')</span></li>');
          });
        }
      }
    });
  }

  function add_tab(tab_num, widget_num)
  {
    tab_num++;

    var tab_content = $(widget_instance.element).find('div.tab-content')
        ,tab_tag = widget_num + '-' + tab_num
        ,tab_list = $(widget_instance.element).find('ul.nav-tabs');

    console.log('adding tab' + tab_tag);

    if (typeof tab_list.attr('id') === "undefined")
    {
      tab_list.attr('id', 'tab-list' + widget_num);
    }

    $(tab_content).append('<div class="tab-pane" id="tab' + tab_tag + '">');
    var tab_pane = $(tab_content).children('div#tab' + tab_tag);
    tab_pane.append('<div class="flexy tab-pane-row" id="tab' + tab_tag + '-row1">');
    tab_pane.append('<div class="flexy tab-pane-row" id="tab' + tab_tag + '-row2">');
    tab_pane.append('<div class="flexy tab-pane-row" id="tab' + tab_tag + '-row3" style="margin-bottom: 5px;">');

    var tab_row1 = $(tab_pane).children('div#tab' + tab_tag + '-row1')
        ,tab_row2 = $(tab_pane).children('div#tab' + tab_tag + '-row2')
        ,tab_row3 = $(tab_pane).children('div#tab' + tab_tag + '-row3');

    tab_row1.append('<div class="metric-input-textbox">' +
      '<input type="text" class="metric-autocomplete" name="metric' + tab_tag + '" placeholder="Metric name and tags">');
    tab_row2.append('<div class="graph-widget-form-item menu-label" id="aggregation' + tab_tag + '" style="margin-right: 0;">');
    tab_row2.children('div#aggregation' + tab_tag).append('<h4>Aggregation</h4>' +
      '<div class="dropdown graph-widget-button">' +
      '<span class="flexy" data-toggle="dropdown">' +
      '<div class="graph-widget-button-label" id="active-aggregation-type' + tab_tag + '">Sum</div>' +
      '<span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span></span>' +
      '<ul class="dropdown-menu" id="aggregation-type-options' + tab_tag + '" role="menu" aria-labelledby="dLabel">' +
      '<li><span>Sum</span></li>' +
      '<li><span>Average</span></li>' +
      '<li><span>Minimum Value</span></li>' +
      '<li><span>Maximum Value</span></li>' +
      '<li><span>Standard Deviation</span></li></ul></div>');
    tab_row2.append('<div class="graph-widget-form-item menu-label" id="downsample' + tab_tag + '" style="margin-right: 0; margin-left: 40px;">');
    tab_row2.children('div#downsample' + tab_tag).append('<h4>Downsampling</h4>' +
      '<div class="dropdown graph-widget-button">' +
      '<span class="flexy" data-toggle="dropdown">' +
      '<div class="graph-widget-button-label" id="active-downsample-type' + tab_tag + '">Maximum Value</div>' +
      '<span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span></span>' +
      '<ul class="dropdown-menu" id="downsample-type-options' + tab_tag + '" role="menu" aria-labelledby="dLabel">' +
      '<li><span>Sum</span></li>' +
      '<li><span>Average</span></li>' +
      '<li><span>Minimum Value</span></li>' +
      '<li><span>Maximum Value</span></li></ul></div>');
    tab_row2.append('<div class="graph-widget-form-item">' +
      '<div class="dropdown graph-widget-button" style="min-width: 25px; margin-left: 2px;">' +
      '<span class="flexy" data-toggle="dropdown">' +
      '<div class="graph-widget-button-label ds-interval" id="active-downsample-interval' + tab_tag + '" data-value="1">1 minute</div>' +
      '<span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span></span>' +
      '<ul class="dropdown-menu ds-values" id="downsample-interval-options' + tab_tag + '" role="menu" aria-labelledby="dLabel">' +
      '<li><span data-value="1">1 minute</span></li>' +
      '<li><span data-value="10">10 minutes</span></li>' +
      '<li><span data-value="30">30 minutes</span></li>' +
      '<li><span data-value="60">1 hour</span></li>' +
      '<li><span data-value="240">4 hours</span></li>' +
      '<li><span data-value="720">12 hours</span></li>' +
      '<li><span data-value="1440">1 day</span></li></ul></div></div>');
    tab_row3.append('<div style="width: 25%"><div class="graph-widget-form-item menu-label"><h4>Interpolation</h4> ' +
      '<div class="push-button binary pushed">' +
      '<input type="checkbox" id="lerp-button' + tab_tag + '" name="lerp' + tab_tag + '" checked>' +
      '<label for="lerp-button' + tab_tag + '"><span class="iconic iconic-check-alt green"></span>' +
      '<span class="binary-label">Yes</span></label></div></div></div>');
    tab_row3.append('<div style="width: 25%"><div class="graph-widget-form-item menu-label"><h4>Right Axis</h4> ' +
        '<div class="push-button binary">' +
        '<input type="checkbox" id="y2-button' + tab_tag + '" name="y2-' + tab_tag + '">' +
        '<label for="y2-button' + tab_tag + '"><span class="iconic iconic-x-alt red"></span>' +
        '<span class="binary-label">No </span></label></div></div></div>');
    tab_row3.append('<div style="width: 25%"><div class="graph-widget-form-item menu-label"><h4>Rate</h4> ' +
      '<div class="push-button binary">' +
      '<input type="checkbox" id="rate-button' + tab_tag + '" name="rate' + tab_tag +'">' +
      '<label for="rate-button' + tab_tag + '"><span class="iconic iconic-x-alt red"></span>' +
      '<span class="binary-label">No </span></label></div></div></div>');

    tab_list.append('<li><a href="#tab' + tab_tag + '" data-toggle="tab">Metric ' + tab_num + '</a></li>');
    $('#' + tab_list.attr('id') + ' a[href="#tab' + tab_tag + '"]').click(function(event) {
      event.preventDefault();
      $(this).tab('show');
    });

    $('#' + tab_list.attr('id') + ' a[href="#tab' + tab_tag + '"]').tab('show');

    $('label').click(function() {
      push_button(this);
    });

    $('ul.dropdown-menu').on('click', 'li', function() {
      dropdown_menu_handler(this);
    });

    return tab_num;

  }

  function go_click_handler(event, widget)
  {
    console.log(widget);

    var widget_num = widget.uuid;
    var widget_element = $(widget.element);
    console.log(widget_element);
    widget.query_data = {};
    widget.query_data.datasource = 'OpenTSDB';
    widget.query_data.downsample_master_interval = 0;
    var input_error = false;
    var methods = {'sum': 'sum', 'average': 'avg', 'minimum value': 'min', 'maximum value': 'max', 'standard deviation': 'dev'};

    if (typeof widget.autoupdate_interval !== "undefined")
    {
      console.log('clearing auto-update timer');
      clearInterval(widget.autoupdate_interval);
      delete widget.autoupdate_interval;
    }

    // Map out all the elements we need to check
    var input_dates = widget_element.find('div[data-name="graph-widget-dates"]');
    var input_time_span = widget_element.find('div[data-name="time-span"]');
    var input_autoupdate = widget_element.find('input:checkbox[name="auto-update"]');
    var input_history = widget_element.find('input:radio[name="history-graph"]:checked');
    console.log(input_history);

    // Date range validation
    var start, end;
    var date_span_option = widget_element.find('input:radio[name="date-span"]:checked').val();
    if (date_span_option === 'date-search')
    {
      if ($(input_dates).children('div[data-name="start-time"]').children('input[name="start-time"]').val().length < 1)
      {
        $(input_dates).children('div[data-name="start-time"]').children('input[name="start-time"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
        alert("You must specify a start time");
      }
      else if ($(input_dates).children('div[data-name="end-time"]').children('input[name="end-time"]').val().length < 1)
      {
        $(input_dates).children('div[data-name="end-time"]').children('input[name="end-time"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
        alert("You must specify an end time");
      }

      // Start date has to be before the End date.
      start = Date.parse($(input_dates).children('div[data-name="start-time"]').children('input[name="start-time"]').val()).getTime();
      start = start / 1000;
      end = Date.parse($(input_dates).children('div[data-name="end-time"]').children('input[name="end-time"]').val()).getTime();
      end = end / 1000;
      if (start >= end)
      {
        alert('Start time must come before end time');
        $(input_dates).children('div[data-name="start-time"]').children('input[name="start-time"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
      }
      if (typeof widget.query_data['time_span'] != "undefined")
      {
        delete widget.query_data['time_span'];
      }
    }
    else
    {
      end = new Date.now().getTime();
      end = parseInt(end / 1000);
      var span = parseInt($(input_time_span).attr('data-ms'));
      start = (end - span);
      var jstart = new Date(start * 1000).toString('yyyy/MM/dd HH:mm:ss');
      var jend = new Date(end * 1000).toString('yyyy/MM/dd HH:mm:ss');
      $(input_dates).children('div[data-name="start-time"]').children('input[name="start-time"]').val(jstart).change();
      $(input_dates).children('div[data-name="end-time"]').children('input[name="end-time"]').val(jend).change();
      widget.query_data['time_span'] = span;
    }
    widget.query_data['start_time'] = start;
    widget.query_data['end_time'] = end;

    // Check for auto-update flag
    if (input_autoupdate.prop('checked'))
    {
      widget.query_data['auto_update'] = true;
    }
    else
    {
      widget.query_data['auto_update'] = false;
    }

    // Check for history display options
    widget.query_data['metrics'] = [];
    widget.query_data['history-graph'] = $(input_history).val();
    if (widget.query_data['history-graph'] === 'no')
    {
      widget.query_data['metrics_count'] = widget.metric_count;
      for (i=1; i<=widget.query_data['metrics_count']; i++)
      {
        var build_metric = {};
        var metric_bits = $('input:text[name=metric'+ widget_num + '-' + i + ']').val().split(' ');
        build_metric.name = metric_bits.shift();
        if (build_metric.name.length < 1)
        {
          continue;
        }
        if (metric_bits.length > 0)
        {
          build_metric.tags = metric_bits;
        }
        var agg_type = $('#active-aggregation-type' + widget_num + '-' + i).text().toLowerCase();
        var ds_type = $('#active-downsample-type' + widget_num + '-' + i).text().toLowerCase();
        build_metric.agg_type = methods[agg_type];
        build_metric.ds_type = methods[ds_type];
        build_metric.ds_interval = $('#active-downsample-interval' + widget_num + '-' + i).attr('data-value');
        if ((widget.query_data['downsample_master_interval'] < 1) || (build_metric.ds_interval < widget.query_data['downsample_master_interval']))
        {
          widget.query_data['downsample_master_interval'] = build_metric.ds_interval;
        }

        if ($('#rate-button' + widget_num + '-' + i).prop('checked'))
        {
          build_metric.rate = true;
        }

        if ($('#lerp-button' + widget_num + '-' + i).prop('checked'))
        {
          build_metric.lerp = true;
        }
        if ($('#y2-button' + widget_num + '-' + i).prop('checked'))
        {
          build_metric.y2 = true;
        }

        widget.query_data['metrics'].push(build_metric);
      }
      if (widget.query_data['metrics'].length < 1)
      {
        widget.sw_graphwidget_searchform.find('ul#tab-list' + widget_num + ' a[href="#tab' + widget_num + '-1"]').click();
        $('input:text[name="metric'+ widget_num + '-1"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
        alert("You must specify at least one metric to search for");
        input_error = true;
      }
    }
    else
    {
      widget.query_data['metrics_count'] = 1;
      if ($('input:text[name="metric' + widget_num + '-1"]').val().length < 1)
      {
        $('input:text[name="metric'+ widget_num + '-1"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
        alert("You must specify a metric to search for");
        input_error = true;
      }
      else if ((end - start) / 60 > 10080)
      {
        alert('History comparison searches are limited to 1 week or less of data');
        $(input_dates).children('div[data-name="start-time"]').children('input[name="start-time"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
        input_error = true;
      }
      else
      {
        input_error = false;
        var build_metric = {};
        var metric_bits = $('input:text[name=metric' + widget_num + '-1]').val().split(' ');
        build_metric.name = metric_bits.shift();
        if (metric_bits.length > 0)
        {
          build_metric.tags = metric_bits;
        }
        var agg_type = $('#active-aggregation-type' + widget_num + '-1').text().toLowerCase();
        var ds_type = $('#active-downsample-type' + widget_num + '-1').text().toLowerCase();
        build_metric.agg_type = methods[agg_type];
        build_metric.ds_type = methods[ds_type];
        build_metric.ds_interval = $('#active-downsample-interval' + widget_num + '-1').attr('data-value');
        widget.query_data['downsample_master_interval'] = build_metric.ds_interval;

        if ($('#rate-button' + widget_num + '-1').prop('checked'))
        {
          build_metric.rate = true;
        }

        if ($('#lerp-button' + widget_num + '-1').prop('checked'))
        {
          build_metric.lerp = true;
        }

        if ($('y2-button' + widget_num + '-1').prop('checked'))
        {
          build_metric.y2 = true;
        }
        widget.query_data['metrics'].push(build_metric);
      }
    }

    // If we made it this far without errors in the form input, then
    // we build us a graph
    if (input_error == false)
    {
      var graph_element = $('#graphdiv' + widget_num);
      // Make sure the graph display div is empty
      graph_element.empty();
      // Load the waiting spinner
      graph_element.append('<div class="bowlG">' +
        '<div class="bowl_ringG"><div class="ball_holderG">' +
        '<div class="ballG"></div></div></div></div>');
      $(widget_instance.element).children('.widget').removeClass('flipped');
      graph_element.append('<div id="status-box' + widget_num + '" style="width: 100%; text-align: center;">' +
        '<p id="status-message' + widget_num + '"></p></div>');
//      $('#status-box' + widget_num).append('<p id=chuck style="margin: 0 25px"></p>');
    }

    console.log(widget.query_data);

    init_query(widget.query_data, widget);

  }

  function init_query(query_data, widget) {

    widget_num = widget.uuid;

    // Start deferred query for metric data
    $.when(opentsdb_search(query_data, widget)).then(
        // done: Send the data over to be parsed
        function(data)
        {
          console.log(query_data);
          console.log(data);
          $.when(process_graph_data(data, query_data, widget)).then(
              // done: Build the graph
              function(data)
              {
                console.log(data);
                build_graph(data.graphdata, data.querydata, widget);
              }
              // fail: Show error image and error message
              ,function(status)
              {
                widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('.bowlG').html('<img src="<?php echo URL; ?>app/img/error.png" style="width: 60px; height: 30px;">');
                widget.sw_graphwidget_frontmain.find('#status-message' + widget_num).text(status);
              }
          );
        }
        // fail: Show error image and error message
        ,function(status)
        {
          widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('.bowlG')
              .css({'padding-top': '15%', 'margin-top': '0', 'margin-bottom': '5px', 'width': '120px', 'height': '60px'})
              .html('<img src="<?php echo URL; ?>app/img/error.png" style="width: 120px; height: 60px;">');

          widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).empty()
              .append('<p>' + status.shift() + '</p>');

          if ((status[0].match("<!DOCTYPE")) || (status[0].match("<html>")))
          {
            var error_message = status.join(' ').replace(/'/g,"&#39");
            widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num)
                .append('<iframe style="margin: 0 auto; color: rgb(205, 205, 205);" width="80%" height="90%" srcdoc=\'' + error_message + '\' seamless></iframe>');
          }
          else
          {
            widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).text(status);
          }
        }
    );

  }

  // Function to wrap the OpenTSDB search
  function opentsdb_search(query_data, widget)
  {

    var query_object = new $.Deferred();
    var widget_num = widget.uuid;
    var status = widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).children('#status-message' + widget_num);

    // Generate (or find the cached) model data for the metric
    if (query_data['history-graph'] == "anomaly")
    {
      status.html('<p>Fetching Metric Data</p>');
      $.when(get_metric_data_anomaly(query_data, widget)
          .done(function(data) {
            query_object.resolve(data);
          })
          .fail(function(data) {
            query_object.reject(data);
          })
      );
    }
    // Search current and previous week for metric data
    else if (query_data['history-graph'] == "wow")
    {
      status.html('<p>Fetching Week-Over-Week Data</p>');
      $.when(get_metric_data_wow(query_data, widget)
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
      status.html('<p>Fetching Metric Data</p>');
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

    var ajax_object = new $.Deferred();

    var ajax_request = $.ajax({
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

  function get_metric_data_wow(query_data, widget)
  {

    var status = widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).children('#status-message' + widget_num);

    if (typeof ajax_request !== 'undefined')
    {
      ajax_request.abort();
    }

    var ajax_object = new $.Deferred();

    var metric_data = {};

    var ajax_request = $.ajax({
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
        status.html('<p>Fetching Metric Data</p>');
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

  function get_metric_data_anomaly(query_data, widget)
  {

    var status = widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).children('#status-message' + widget_num);

    if (typeof ajax_request !== 'undefined')
    {
      ajax_request.abort();
    }

    var ajax_object = new $.Deferred();

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
            status.html('<p>Fetching data for anomaly detection</p>');
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
              status.html('<p>Calculating anomalies</p>');
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

  function process_graph_data(data, query_data, widget)
  {
    var parse_object = new $.Deferred();
    var status = widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).children('#status-message' + widget_num);

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
    if (query_data['history-graph'] == "anomaly")
    {
      var anomalies = data.anomalies;
      delete data.anomalies;
    }

    status.html('<p>Parsing Metric Data</p>');

    for (var i = start; i <= end; i = i + bucket_interval)
    {
      buckets[i] = [];
    }

    for (var series in data) {
      if (data.hasOwnProperty(series))
      {
        console.log(data[series]);
        if (data[series] !== null)
        {
          console.log(series);
          if (query_data['history-graph'] == "anomaly")
          {
            query_data.metrics[0]['history-graph'] = "anomaly";
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
        else
        {
          console.log(series + ' is null, skipping');
        }

      }

      var graph_data = {};
      graph_data.labels = labels;
      graph_data.data = buckets;
      if (query_data['history-graph'] == "anomaly")
      {
        graph_data.anomalies = anomalies;
      }
    }

    var parsed_data = {graphdata: graph_data, querydata: query_data};

    parse_object.resolve(parsed_data);

    return parse_object.promise();

  }

  function build_graph(data, query_data, widget)
  {

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

    var graphdiv_id = 'graphdiv' + widget.uuid;

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

    var x_space = $('#' + graphdiv_id).width() / 12;
    var y_space = $('#' + graphdiv_id).height() / 12;
    var g_width = $('#' + graphdiv_id).innerWidth() * .95;

    var g = new Dygraph(
        document.getElementById('graphdiv' + widget.uuid)
        ,dygraph_format
        ,{
          labels: graph_labels
          ,labelsDiv: 'legend' + widget.uuid
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

    $('.widget-footer-btn.hidden').removeClass('hidden');

    // Set up the anomaly highlighting if requested
    if (query_data['history-graph'] == "anomaly")
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
        var new_start = series_times[0];
        var new_end = new Date.now().getTime();
        new_end = parseInt(new_end / 1000);
        query_data.start_time = new_start;
        query_data.end_time = new_end;
        $.when(opentsdb_search(query_data)).then(
            function(data)
            {
              $.when(process_graph_data(data, query_data)).then(
                  function(data)
                  {
                    var dygraph_update = new Array();
                    graph_data = data.graphdata.data;
                    for (var timestamp in graph_data) {
                      if (graph_data.hasOwnProperty(timestamp))
                      {
                        series_times.push(timestamp);
                        jtime = new Date(parseInt(timestamp * 1000));
                        values = [jtime];
                        if (query_data['history-graph'] == 'anomaly')
                        {
                          var value_bucket = new Array();
                          $.each(graph_data[timestamp], function(k, d) {
                            if (d == null)
                            {
                              value_bucket.push([null,null,null]);
                              value_bucket.push([null,null,null]);
                            }
                            else
                            {
                              $.each(d, function(kk, dd) {
                                value_bucket.push(dd);
                              });
                            }
                          });
                          values = values.concat(value_bucket);
                        }
                        else
                        {
                          values = values.concat(graph_data[timestamp]);
                        }
                        dygraph_update.push(values);
                      }
                    }
                    dygraph_format.splice(0, (dygraph_update.length - 2));
                    dygraph_format.splice(-2, 2);
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