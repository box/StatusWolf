<?php

$_session_data = $_SESSION[SWConfig::read_values('auth.sessionName')];

?>
<link rel="stylesheet" href="<?php echo URL; ?>app/css/widget_base.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/push-button.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/toggle-buttons.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/datetimepicker.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/popups.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/table.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/account.css">

<div class="container">
  <div class="widget-container" id="account-widget">
    <div class="widget">
      <div class="widget-front" id="account-widget-front">
        <div class="flexy widget-title">
          <div class="toggle-button-group">
            <div class="widget-title-button left-button toggle-button toggle-on">
              <label><input type="radio" class="section-toggle" id="saved-search" name="account-panel" checked="checked" data-target="edit-saved-searches"><span>Saved Searches</span></label>
            </div><div class="widget-title-button left-button toggle-button">
              <label><input type="radio" class="section-toggle" id="saved-dashboards" name="account-panel" data-target="edit-saved-dashboards"><span>Saved Dashboards</span></label>
            </div><div class="widget-title-button left-button toggle-button hidden">
              <label><input type="radio" class="section-toggle" id="preferences" name="account-panel" data-target="edit-preferences"><span>Preferences</span></label>
            </div>
          </div>
        </div>
        <div class="widget-main">
          <div class="section section-on" id="edit-saved-searches">
            <div id="search-list-pane"></div>
            <div id="search-info-pane">
              <div id="search-title">
                <h3></h3>
              </div>
              <div id="search-datasource">
                <h5></h5>
              </div>
              <div id="search-guts">

              </div>
            </div>
          </div>
          <div class="section section-off" id="edit-saved-dashboards">
            <div id="dashboard-list-pane"></div>
            <div id="dashboard-info-pane">
              <div id="dashboard-title">
                <h3></h3>
              </div>
              <div id="dashboard-guts"></div>
            </div>
          </div>
          <div class="section section-off" id="edit-preferences"></div>
        </div>
        <div class="flexy widget-footer"></div>
      </div>
    </div>
  </div>
</div>

<div id="success-popup" class="popup mfp-hide"><h5>Success</h5>
  <div class="popup-form-data">Your changes have been saved.</div>
  <div class="sw-button" id="success-ok" style="position: absolute; bottom: 0; right: 0; margin: 20px 20px 10px 0;" onClick="$.magnificPopup.close()">OK!</div>
</div>

<script type="text/javascript" src="<?php echo URL; ?>app/js/sw_lib.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/bootstrap.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/bootstrap-datetimepicker.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/date.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/jquery.autocomplete.js"></script>

<script type="text/javascript">

  var short_span_menu = '<li><span data-ms=600>10 minutes</span></li>' +
          '<li><span data-ms=1800>30 minutes</span></li>' +
          '<li><span data-ms=3600>1 Hour</span></li>' +
          '<li><span data-ms=7200>2 Hours</span></li>' +
          '<li><span data-ms=14400>4 Hours</span></li>' +
          '<li><span data-ms=28800>8 Hours</span></li>' +
          '<li><span data-ms=43200>12 Hours</span></li>' +
          '<li><span data-ms=86400>1 Day</span></li>' +
          '<li><span data-ms=604800>1 Week</span></li>'
      ,long_span_menu = '<li><span data-ms="600">10 minutes</span></li>' +
          '<li><span data-ms=1800>30 minutes</span></li>' +
          '<li><span data-ms=3600>1 Hour</span></li>' +
          '<li><span data-ms=7200>2 Hours</span></li>' +
          '<li><span data-ms=14400>4 Hours</span></li>' +
          '<li><span data-ms=28800>8 Hours</span></li>' +
          '<li><span data-ms=43200>12 Hours</span></li>' +
          '<li><span data-ms=86400>1 Day</span></li>' +
          '<li><span data-ms=604800>1 Week</span></li>' +
          '<li><span data-ms=1209600>2 Weeks</span></li>' +
          '<li><span data-ms=2592000>1 Month</span></li>';

  $(document).ready(function() {

    get_saved_searches();
    get_saved_dashboards();

    $('.widget-footer').empty();
    $('.widget-footer').append('<div class="widget-footer-button left-button" id="delete-saved-searches">');
    $('#delete-saved-searches').append('<span class="iconic iconic-x-alt red"><span class="font-reset"> Delete Selected</span></span>');
    $('.widget-footer').append('<div class="widget-footer-button left-button" id="select-all-saved-searches">');
    $('#select-all-saved-searches').append('<span class="iconic">Select All</span>');
    $('.widget-footer').append('<div class="widget-footer-button left-button" id="select-no-saved-searches">');
    $('#select-no-saved-searches').append('<span class="iconic">Select None</span>');
    $('title').text('Edit Saved Searches - StatusWolf');

  });

  // Clicking on the name of a search in the search info pane switches
  // to a text box for changing the name of the search
  $('#search-title').on('click', 'h3', function() {
    var title_text = $(this).text();
    $(this).css('display', 'none');
    $('#search-title').append('<input id="change-search-title" type="text" name="change-search-title" value="' + title_text + '">');
    $('#search-title').children('input').css({
      'font-size': $(this).css('font-size')
      ,'font-weight': $(this).css('font-weight')
      ,'width': '90%'
      ,'margin': '8px 0 2px 0'
    }).focus();
  });

  $('#dashboard-title').on('click', 'h3', function() {
    var title_text = $(this).text();
    $(this).css('display', 'none');
    $('#dashboard-title').append('<input id="change-dashboard-title" type="text" name="change-dashboard-title" value=' + title_text + '">');
    $('#dashboard-title').children('input').css({
      'font-size': $(this).css('font-size')
      ,'font-weight': $(this).css('font-weight')
      ,'width': '90%'
      ,'margin': '8px 0 2px 0'
    }).focus();
  });

  // Clicking, tabbing, etc. away from the search name text box resets it
  // back to a head for the info pane, with new name if it was changed
  $('#search-title').on('blur', 'input', function() {
    var title_text = $('#search-title').children('h3').text();
    var changed_title = $('input#change-search-title').val();
    if (changed_title.length > 1)
    {
      $('#change-search-title').remove();
      $('#search-title').children('h3').text(changed_title).css('display', 'inline-block');
    }
    else
    {
      $('#change-search-title').remove();
      $('#search-title').children('h3').text(title_text).css('display', 'inline-block');
    }
  });

  // The enter keypress when the search name edit field has focus
  // does the same as above
  $('#search-title').on('keydown', 'input', function(event) {
    if (event.which === 13)
    {
      $('#change-search-title').blur();
    }
  });

  // Clicking on the name of a saved search in the list loads
  // it in the info pane
  $('#search-list-pane').on('click', 'span.saved-search-title', function() {
    var search_id = $(this).parent('li').attr('data-id');
    $('#search-guts').addClass('hidden');
    setTimeout(function() {
      load_saved_search(search_id);
    }, 250);
    $('#search-title > h3').text($(this).text());
  });

  $('#dashboard-list-pane').on('click', 'span.saved-dashboard-title', function() {
    var dash_id = $(this).parent('li').attr('data-id');
    $('#dashboard-guts').addClass('hidden');
    setTimeout(function() {
      load_saved_dashboard(dash_id);
    }, 250);
    $('#dashboard-title > h3').text($(this).text());
  });

  // Handler for the fance check boxes in the saved search list
  $('.widget-main').on('click', 'span.sw-check-box', function() {
    if ($(this).hasClass('check-on'))
    {
      $(this).removeClass('iconic-check-alt green check-on').addClass('empty grey');
    }
    else
    {
      $(this).removeClass('empty grey').addClass('iconic-check-alt green check-on');
    }
  });

  // Handler for toggling tabs and other changing parts of the interface
  $('.widget').on('click', 'input.section-toggle', function() {
    var data_target = $(this).attr('data-target');
    $('#' + data_target).removeClass('section-off').addClass('section-on').siblings('.section').addClass('section-off').removeClass('section-on');

    // If we're switching to the Edit Saved Searches tab, add the Delete,
    // Select All and Select None buttons to the interface
    if (data_target === "edit-saved-searches")
    {
      $('.widget-footer').empty();
      $('.widget-footer').append('<div class="widget-footer-button left-button" id="delete-saved-searches">');
      $('#delete-saved-searches').append('<span class="iconic iconic-x-alt red"><span class="font-reset"> Delete Selected</span></span>');
      $('.widget-footer').append('<div class="widget-footer-button left-button" id="select-all-saved-searches">');
      $('#select-all-saved-searches').append('<span class="iconic">Select All</span>');
      $('.widget-footer').append('<div class="widget-footer-button left-button" id="select-no-saved-searches">');
      $('#select-no-saved-searches').append('<span class="iconic">Select None</span>');
      $('title').text('Edit Saved Searches - StatusWolf');
    }
    else if (data_target === "edit-saved-dashboards")
    {
      $('.widget-footer').empty();
      $('title').text('Edit Saved Dashboards - StatusWolf');
      $('.widget-footer').append('<div class="widget-footer-button left-button" id="delete-saved-dashboards">');
      $('#delete-saved-dashboards').append('<span class="iconic iconic-x-alt red"><span class="font-reset"> Delete Selected</span></span>');
      $('.widget-footer').append('<div class="widget-footer-button left-button" id="select-all-saved-dashboards">');
      $('#select-all-saved-dashboards').append('<span class="iconic">Select All</span>');
      $('.widget-footer').append('<div class="widget-footer-button left-button" id="select-no-saved-dashboards">');
      $('#select-no-saved-dashboards').append('<span class="iconic">Select None</span>');
      $('title').text('Edit Saved Dashboards - StatusWolf');
    }
    else if (data_target === "edit-preferences")
    {
      $('.widget-footer').empty();
      $('title').text('Edit Preferences - StatusWolf');
    }

    if (data_target === 'history-anomaly' || data_target === 'history-wow')
    {
      $('ul#time-span-options').html(short_span_menu);
      if ($('div#time-span').attr('data-ms') > 604800)
      {
        $('div#time-span').text('1 week').attr('data-ms', "608400");
      }
      $('ul#tab-list a[href="#tab1"]').click();
      $('ul#tab-list').addClass('hidden');
      $('#add-metric-button').addClass('hidden');
    }
    else if (data_target === 'history-no')
    {
      $('ul#time-span-options').html(long_span_menu);
      $('ul#tab-list').removeClass('hidden');
      $('#add-metric-button').removeClass('hidden');
    }
  });

  // Handler for the buttons in the footer
  $('.widget').on('click', '.widget-footer-button', function() {
    if ($(this).attr('id') === "select-no-saved-searches")
    {
      $('li.saved-search-item > span.sw-check-box').removeClass('iconic-check-alt green check-on').addClass('empty grey');
    }
    else if ($(this).attr('id') === "select-no-saved-dashboards")
    {
      $('li.saved-dashboard-item > span.sw-check-box').removeClass('iconic-check-alt green check-on').addClass('empty grey');
    }
    else if ($(this).attr('id') === "select-all-saved-searches")
    {
      $('li.saved-search-item > span.sw-check-box').removeClass('empty grey').addClass('iconic-check-alt green check-on');
    }
    else if ($(this).attr('id') === "select-all-saved-dashboards")
    {
      $('li.saved-dashboard-item > span.sw-check-box').removeClass('empty grey').addClass('iconic-check-alt green check-on');
    }
    else if ($(this).attr('id') === "delete-saved-searches")
    {
      delete_click_handler('search');
    }
    else if ($(this).attr('id') === "delete-saved-dashboards")
    {
      delete_click_handler('dashboard');
    }
  });

  $('.widget-container').on('click', 'label', function() {
    statuswolf_button(this);
  });

  // Load up the list of saved searches
  function get_saved_searches()
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
        $('#search-list-pane').empty();
        $('#search-list-pane').append('<h4>My Private Searches</h4>');
        $('#search-list-pane').append('<ul class="saved-search-list" id="my-searches">');
        if (typeof my_searches !== "undefined")
        {
          $.each(my_searches, function(i, search) {
            $('#my-searches').append('<li class="saved-search-item" data-id="' + search['id'] + '"><span class="iconic empty grey sw-check-box"></span><span class="saved-search-title">' + search['title'] + '</span></li>');
          });
          $('#search-title > h3').text(my_searches[0]['title']);
          $('#search-guts').addClass('hidden');
          setTimeout(function() {
            load_saved_search(my_searches[0]['id']);
          }, 250);
        }
        $('#search-list-pane').append('<h4>My Public Searches</h4>');
        $('#search-list-pane').append('<ul class="saved-search-list" id="public-searches">');
        if (typeof public_searches !== "undefined")
        {
          $.each(public_searches, function(i, public) {
            if (public['user_id'] == user_id)
            {
              $('#public-searches').append('<li class="saved-search-item" data-id="' + public['id'] + '"><span class="iconic empty grey sw-check-box"></span><span class="saved-search-title">' + public['title'] + '</span></li>');
            }
          });
        }
      }
    });

  }

  function get_saved_dashboards()
  {
    var user_id = "<?php echo $_session_data['user_id']; ?>";
    var api_url = '<?php echo URL; ?>api/get_saved_dashboards/' + user_id;
    api_query = {user_id: user_id};
    $.ajax({
      url: api_url
      ,type: 'POST'
      ,data: api_query
      ,dataType: 'json'
      ,success: function(data) {
        my_dashboards = data['user_dashboards'];
        public_dashboards = data['shared_dashboards'];
        $('#dashboard-list-pane').empty();
        $('#dashboard-list-pane').append('<h4>My Private Dashboards</h4>');
        $('#dashboard-list-pane').append('<ul class="saved-dashboard-list" id="my-dashboards">');
        if (typeof my_dashboards !== "undefined")
        {
          $.each(my_dashboards, function(i, dash) {
            $('#my-dashboards').append('<li class="saved-dashboard-item" data-id="' + dash['id'] + '"><span class="iconic empty grey sw-check-box"></span><span class="saved-dashboard-title">' + dash['title'] + '</span></li>');
          });
          $('#dashboard-title > h3').text(my_dashboards[0]['title']);
          $('#dashboard-guts').addClass('hidden');
          setTimeout(function() {
            load_saved_dashboard(my_dashboards[0]['id']);
          }, 250);
        }
        $('#dashboard-list-pane').append('<h4>My Public Dashboards</h4>');
        $('#dashboard-list-pane').append('<ul class="saved-dashboard-list" id="public-dashboards">');
        if (typeof public_dashboards !== "undefined")
        {
          $.each(public_dashboards, function(i, public) {
            if (public['user_id'] == user_id)
            {
              $('#public-dashboards').append('<li class="saved-dashboard-item" data-id="' + public['id'] + '"><span class="iconic empty grey sw-check-box"></span><span class="saved-dashboard-title">' + public['title'] + '</span></li>');
            }
          });
        }
      }
    });

  }
  // Adds a tab to the search definition, to add another metric to the search
  function add_tab(tab_num, data_source)
  {
    tab_num++;

    var tab_content = $('div#tab-content')
        ,tab_list = $('ul#tab-list');

    $(tab_content).append('<div class="tab-pane" id="tab' + tab_num + '">');
    var tab_pane = $(tab_content).children('div#tab' + tab_num);

    if (data_source === "OpenTSDB")
    {
      tab_pane.append('<table class="tab-table" id="metric-options' + tab_num + '">');
      tab_table = $(tab_pane.children('table#metric-options' + tab_num));
      tab_table.append('<tr><td colspan="3"><div class="metric-input-textbox">' +
          '<input type="text" class="metric-autocomplete" name="metric' + tab_num + '" placeholder="Metric name and tags">' +
          '</div></td></tr>' +
          '<tr><td><div class="saved-search-form-item menu-label" id="aggregation' + tab_num + '" style="margin-right: 0;">' +
          '<h4>Aggregation</h4><div class="dropdown saved-search-button">' +
          '<span data-toggle="dropdown"><div class="saved-search-button-label" id="active-aggregation-type' + tab_num + '">Sum</div>' +
          '<span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span></span>' +
          '<ul class="dropdown-menu" id="aggregation-type-options' + tab_num + '" role="menu" aria-labelledby="dLabel" style="min-width: 130px;">' +
          '<li><span>Sum</span></li>' +
          '<li><span>Average</span></li>' +
          '<li><span>Minimum Value</span></li>' +
          '<li><span>Maximum Value</span></li>' +
          '<li><span>Standard Deviation</span></li>' +
          '</ul></div></td>' +
          '<td colspan="2"><div class="saved-search-form-item menu-label" id="downsample' + tab_num + '" style="margin-right: 0; margin-left: 40px;">' +
          '<h4>Downsampling</h4>' +
          '<div class="dropdown saved-search-button">' +
          '<span data-toggle="dropdown">' +
          '<div class="saved-search-button-label" id="active-downsample-type' + tab_num + '">Maximum Value</div>' +
          '<span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span></span>' +
          '<ul class="dropdown-menu" id="downsample-type-options' + tab_num + '" role="menu" aria-labelledby="dLabel">' +
          '<li><span>Sum</span></li>' +
          '<li><span>Average</span></li>' +
          '<li><span>Minimum Value</span></li>' +
          '<li><span>Maximum Value</span></li></ul></div>' +
          '<div class="dropdown saved-search-button">' +
          '<span data-toggle="dropdown">' +
          '<div class="saved-search-button-label ds-interval" id="active-downsample-interval' + tab_num + '" data-value="1">1 minute</div>' +
          '<span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span></span>' +
          '<ul class="dropdown-menu ds-values" id="downsample-interval-options' + tab_num + '" role="menu" aria-labelledby="dLabel">' +
          '<li><span data-value="1">1 minute</span></li>' +
          '<li><span data-value="10">10 minutes</span></li>' +
          '<li><span data-value="30">30 minutes</span></li>' +
          '<li><span data-value="60">1 hour</span></li>' +
          '<li><span data-value="240">4 hours</span></li>' +
          '<li><span data-value="720">12 hours</span></li>' +
          '<li><span data-value="1440">1 day</span></li></ul></div></td></tr>');
      tab_table.append('<tr><td width="32%"><div class="saved-search-form-item menu-label">' +
          '<h4>Interpolation</h4>' +
          '<div class="push-button binary pushed">' +
          '<input type="checkbox" id="lerp-button' + tab_num + '" name="lerp' + tab_num + '" checked>' +
          '<label for="lerp-button' + tab_num + '"><span class="iconic iconic-check-alt green"></span>' +
          '<span class="binary-label">Yes</span></label></div></div></td>' +
          '<td width="23%"><div class="saved-search-form-item menu-label">' +
          '<h4>Rate</h4>' +
          '<div class="push-button binary">' +
          '<input type="checkbox" id="rate-button' + tab_num + '" name="rate' + tab_num + '">' +
          '<label for="rate-button' + tab_num + '"><span class="iconic iconic-x-alt red"></span>' +
          '<span class="binary-label">No </span></label></div></div></td>' +
          '<td width="35%"><div class="saved-search-form-item menu-label">' +
          '<h4>Right Axis</h4>' +
          '<div class="push-button binary">' +
          '<input type="checkbox" id="y2-button' + tab_num + '" name="y2-' + tab_num + '">' +
          '<label for="y2-button' + tab_num + '"><span class="iconic iconic-x-alt red"></span>' +
          '<span class="binary-label">No </span></label></div></div></td></tr>');

    }

    tab_list.append('<li><a href="#tab' + tab_num + '" data-toggle="tab">Metric ' + tab_num + '</a></li>');
    $('#' + tab_list.attr('id') + ' a[href="#tab' + tab_num + '"]').click(function(event) {
      event.preventDefault();
      $(this).tab('show');
    });

    $('#' + tab_list.attr('id') + ' a[href="#tab' + tab_num + '"]').tab('show');

    $('label').click(function() {
      statuswolf_button(this);
    });

    $('ul.dropdown-menu').on('click', 'li', function() {
      dropdown_menu_handler(this);
    });

    return tab_num;
  }

  // Deals with dropdown menus
  function dropdown_menu_handler(item, widget_num)
  {
    var button = $(item).parent().parent().children('span');
    $(button).children('.saved-search-button-label').text($(item).text());
    $(button).children('div.ds-interval').attr('data-value', $(item).children('span').attr('data-value'));
    if ($(item).parent().attr('id') === "time-span-options")
    {
      $(button).children('div#time-span').attr('data-ms', $(item).children('span').attr('data-ms')).text();
    }
  }

  // Loads the actual query and other info for a saved search when the name
  // of the search is clicked on in the list
  function load_saved_search(search_id)
  {

    $.ajax({
      url: "<?php echo URL; ?>api/load_saved_search/" + search_id
      ,type: "GET"
      ,dataType: "json"
      ,success: function(data) {
        if (typeof data === "string")
        {
          query_data = eval('(' + data + ')');
        }
        else
        {
          query_data = data;
        }
        populate_search_form(query_data, search_id);
      }
    });

  }

  function load_saved_dashboard(dash_id)
  {

    $.ajax({
      url: "<?php echo URL; ?>api/load_saved_dashboard/" + dash_id
      ,type: 'GET'
      ,dataType: 'json'
      ,success: function(data) {
        if (typeof data === "string")
        {
          dash_data = eval('(' + data + ')');
        }
        else
        {
          dash_data = data;
        }
        console.log(dash_data);
        $('#dashboard-guts').append('<ul class="saved-dashboard-list" id="dashboard-widgets">');
        $.each(dash_data.widgets, function(widget_id, widget_config) {
          var metric_string = '';
          $('#dashboard-widgets').append('<li class="saved-dashboard-item" data-id="' + widget_id + '">');
          $.each(widget_config.metrics, function(i, metric_info) {
            metric_string = metric_string + ',' + metric_info.name;
          });
          $('li[data-id="' + widget_id + '"]').append('<span class="saved-dashboard-title">' + metric_string + '</span>');
        });
        setTimeout(function() {
          $('#dashboard-guts').removeClass('hidden')
        }, 250);
      }
    });
  }

  // Takes the loaded query info and populates the info form with the current
  // state of the saved search
  function populate_search_form(query_data, search_id)
  {

    console.log('getting search query data');
    console.log(query_data);
    if (query_data.datasource === "OpenTSDB")
    {
      $('#search-title > h5').text('Datasource: OpenTSDB');
      $('#search-guts').empty();
      $('.autocomplete-suggestions').remove();
      $('.datetimepicker-widget').remove();

      $('#search-guts').append('<table class="general-options-table" id="saved-search-options">' +
        '<tr>' +
        '<td width="30%">' +
        '<div class="toggle-button-group">' +
        '<div class="toggle-button toggle-on">' +
        '<label><input type="radio" class="section-toggle date-search" name="date-span" value="date-search" checked="checked" data-target="saved-search-dates">' +
        '<span>Date Range</span>' +
        '</label></div><div class="toggle-button">' +
        '<label><input type="radio" class="section-toggle span-search" name="date-span" value="span-search" data-target="saved-search-time-span">' +
        '<span>Time Span</span>' +
        '</label>' +
        '</div></div></td>' +
        '<td width="59%">' +
        '<div class="section section-on saved-search-dates" id="saved-search-dates">' +
        '<div class="saved-search-form-item menu-label" id="start-time">' +
        '<h4>Start</h4>' +
        '<input type="text" class="input input-append date-input" data-format="yyyy/MM/dd hh:mm:ss" name="start-time"><span class="input-addon-btn"><span class="iconic iconic-calendar-alt"></span></span>' +
        '</div><div class="saved-search-form-item menu-label" id="end-time">' +
        '<h4>End</h4>' +
        '<input type="text" class="input input-append date-input" data-format="yyyy/MM/dd hh:mm:ss" name="end-time"><span class="input-addon-btn"><span class="iconic iconic-calendar-alt"></span></span>' +
        '</div></div>' +
        '<div class="section section-off saved-search-time-span" id="saved-search-time-span">' +
        '<div class="saved-search-form-item menu-label" style="margin-right: 0;"><h4>Show Me The Past</h4>' +
        '<div class="dropdown saved-search-button" style="display: inline-block;">' +
        '<span data-toggle="dropdown">' +
        '<div class="saved-search-button-label" id="time-span" data-ms="14400">4 Hours</div>' +
        '<span class="dropdown-arrow-container">' +
        '<span class="iconic iconic-play rotate-90"></span>' +
        '</span></span>' +
        '<ul class="dropdown-menu menu-left" id="time-span-options" role="menu" aria-labelledby="dLabel">' +
        '<li><span data-ms="600">10 minutes</span></li>' +
        '<li><span data-ms="1800">30 minutes</span></li>' +
        '<li><span data-ms="3600">1 Hour</span></li>' +
        '<li><span data-ms="7200">2 Hours</span></li>' +
        '<li><span data-ms="14400">4 Hours</span></li>' +
        '<li><span data-ms="28800">8 Hours</span></li>' +
        '<li><span data-ms="43200">12 Hours</span></li>' +
        '<li><span data-ms="86400">1 Day</span></li>' +
        '<li><span data-ms="604800">1 Week</span></li>' +
        '<li><span data-ms="1209600">2 Weeks</span></li>' +
        '<li><span data-ms="2592000">1 Month</span></li>' +
        '</ul></div></div></div></td></tr>' +
        '<tr><td><div class="auto-update">' +
        '<div class="push-button">' +
        '<input type="checkbox" name="auto-update" id="auto-update-button">' +
        '<label for="auto-update-button"><span class="iconic iconic-x-alt red"></span><span> Auto Update</span></label>' +
        '</div></div></td>' +
        '<td><div class="toggle-button-group">' +
        '<div class="toggle-button toggle-on">' +
        '<label><input type="radio" class="section-toggle history-no" name="history-graph" checked="checked" data-target="history-no" value="no">' +
        '<span>No History</span></label></div><div class="toggle-button">' +
        '<label><input type="radio" class="section-toggle history-anomaly" name="history-graph" data-target="history-anomaly" value="anomaly">' +
        '<span>Anomaly</span></label></div><div class="toggle-button">' +
        '<label><input type="radio" class="section-toggle history-wow" name="history-graph" data-target="history-wow" value="wow">' +
        '<span>Week-Over-Week</span></label></div></div></td></tr></table>' +
        '<div class="saved-search-form-row row3">' +
        '<div class="metric-input-tabs tabbable tab-below">' +
        '<div class="tab-content" id="tab-content"></div>' +
        '<ul class="nav nav-tabs" id="tab-list"></ul></div></div>' +
        '<div class="saved-search-form-row row4" style="font-size: 1em;"><div class="sw-button" id="add-metric-button"><span class="iconic iconic-plus-alt"> Add Metric</span></div></div>' +
        '<div class="saved-search-form-row row5">' +
        '<div class="push-button">' +
        '<input type="checkbox" id="save-span" name="save-span"><label for="save-span"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> Save Search Times</span></label>' +
        '</div>' +
        '<div class="push-button">' +
        '<input type="checkbox" id="public" name="public"><label for="public"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> Public Search</span></label>' +
        '</div>' +
        '<div class="action-buttons" style="display: inline-block; position: absolute; right: 0;">' +
        '<div class="sw-button"><a href="<?php echo URL; ?>adhoc/saved/' + search_id + '" target="new">View Search</a></div>' +
        '<div class="sw-button" id="save-changes">Save Changes</div></div></div>');

      $('#start-time').datetimepicker({collapse: false});
      $('#end-time').datetimepicker({collapse: false});

      $('#add-metric-button').click(function() {
        document.metric_count = add_tab(document.metric_count, query_data.datasource);
        if (document.metric_count == 6)
        {
          $('#add-metric-button').addClass('hidden');
        }
      });

      $('#save-changes').click(function() {
        save_click_handler(event, search_id, query_data);
      });

      if (query_data.save_span > 0)
      {
        $('label[for="save-span"]').click();
      }
      if (query_data.private < 1)
      {
        $('label[for="public"]').click();
      }

      var method_map = {sum: 'Sum', avg: 'Average', min: 'Minimum Value', max: 'Maximum Value', dev: 'Standard Deviation'};

      if (query_data['auto_update'] === "true") {
        $('label[for="auto-update-button"]').click();
        $('label[for="auto-update-button"]').parent('.push-button').addClass('pushed');
        $('label[for="auto-update-button"]').children('span.iconic').removeClass('iconic-x-alt red').addClass('iconic-check-alt green');
      }
      if (query_data.history_graph.match(/anomaly/))
      {
        var el = $('input[data-target="history-anomaly"]').parent('label');
        $(el).parent('div.toggle-button').addClass('toggle-on');
        $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
        $(el).children('input').attr('checked', 'Checked');
        $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
        $('input[data-target="history-anomaly"]').click();
      }
      else if (query_data.history_graph.match(/wow/))
      {
        var el = $('input[data-target="history-wow"]').parent('label');
        $(el).parent('div.toggle-button').addClass('toggle-on');
        $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
        $(el).children('input').attr('checked', 'Checked');
        $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
        $('input[data-target="history-wow"]').click();
      }
      if (query_data.time_span !== "undefined" && query_data.time_span > 0)
      {
        var el = $('input[data-target="saved-search-time-span"]').parent('label');
        $(el).parent('div.toggle-button').addClass('toggle-on');
        $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
        $(el).children('input').attr('checked', 'Checked');
        $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
        $('input[data-target="saved-search-time-span"]').click();
        var span = query_data.time_span;
        console.log('setting time span to ' + span);
        $('#time-span').attr('data-ms', span).text($('ul#time-span-options > li > span[data-ms="' + span + '"]').text());
      }
      else
      {
        if ((start_in = parseInt(query_data['start_time'])) && (end_in = parseInt(query_data['end_time'])))
        {
          $('div#start-time').children('input').val(new Date(start_in * 1000).toString('yyyy/MM/dd HH:mm:ss'));
          $('div#end-time').children('input').val(new Date(end_in * 1000).toString('yyyy/MM/dd HH:mm:ss'));
        }
      }

      $.each(query_data['metrics'], function(i, metric) {
        metric_num = i + 1;
        metric_string = metric.name;
        var metric_tab = $('div#tab' + metric_num);
        if (metric_tab.length == 0)
        {
          document.metric_count = add_tab(i, query_data.datasource);
          $('input[name="metric' + document.metric_count + '"]').autocomplete({
            minChars: 2
            ,serviceUrl: '<?php echo URL; ?>api/tsdb_metric_list/'
            ,containerClass: 'autocomplete-suggestions dropdown-menu'
            ,zIndex: ''
            ,maxHeight: ''
          });
        }

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
      $('#tab-list:first-child').addClass('active');

      $('input:checkbox[name="auto-update"]').change(function() {
        $(this).prop('checked') ? query_data.auto_update = true : query_data.auto_update = false;
      });
      $('input:radio[name="history-graph"]').change(function() {
        query_data.history_graph = $('input:radio[name="history-graph"]:checked').val();
      });
      query_data.privacy_change = 0;
      $('input:checkbox[name="public"]').change(function() {
        query_data.privacy_change = 1;
      });
    }

    setTimeout(function() {
      $('#search-guts').removeClass('hidden');
    }, 250);

  }

  // Save the new version of the saved search
  function save_click_handler(event, search_id, query_data)
  {

    console.log('saving query');
    query_data.user_id = "<?php echo $_session_data['user_id']; ?>";
    query_data.title = $('#search-title').children('h3').text();
    var privacy_change = query_data.privacy_change;
    delete query_data.privacy_change;
    delete query_data.metrics;
    query_data.metrics = [];
    if (query_data.datasource === "OpenTSDB")
    {
      var methods = {'sum': 'sum', 'average': 'avg', 'minimum value': 'min', 'maximum value': 'max', 'standard deviation': 'dev'};

      $('#save-span').prop('checked')?query_data['save_span'] = 1:query_data['save_span'] = 0;
      $('#public').prop('checked')?query_data['private'] = 0:query_data['private'] = 1;
      if (query_data.history_graph !== 'no')
      {
        query_data.metrics_count = 1;
      }
      else
      {
        query_data.metrics_count = document.metric_count;
      }

      for (i = 1; i <= query_data.metrics_count; i++)
      {
        var build_metric = {};
        var metric_bits = $('input:text[name="metric' + i +'"]').val().split(' ');
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
        var ds_type = $('#active-downsample-type' + i).text().toLowerCase()
        build_metric.agg_type = methods[agg_type];
        build_metric.ds_type = methods[ds_type];
        build_metric.ds_interval = $('#active-downsample-interval' + i).attr('data-value');
        if ((query_data.downsample_master_interval < 1) || (build_metric.ds_interval < query_data.downsample_master_interval))
        {
          query_data.downsample_master_interval = build_metric.ds_interval;
        }
        $('#rate-button' + i).prop('checked') ? build_metric.rate = true : build_metric.rate = false;
        $('#lerp-button' + i).prop('checked') ? build_metric.lerp = true : build_metric.lerp = false;
        $('#y2-button' + i).prop('checked') ? build_metric.y2 = true : build_metric.y2 = false;

        query_data.metrics.push(build_metric);
      }

      var date_span_options = $('input:radio[name="date-span"]:checked').val();
      console.log(date_span_options);
      if (date_span_options === "span-search")
      {
        query_data.time_span = $('#time-span').attr('data-ms');
      }
      else
      {
        delete(query_data.time_span);
        if (query_data.save_span > 0)
        {
          var start = $('input:text[name="start-time"]').val();
          if (start.length < 1)
          {
            alert('You must specify a start time');
            $('input:text[name="start-time"]').css({'border-color': 'red', 'background-color': 'rgb(255, 200, 200)'}).focus();
            return;
          }
          start = Date.parse(start).getTime();
          start = start / 1000;
          var end = $('input:text[name="end-time"]').val();
          if (end.length < 1)
          {
            alert('You must specify an end time');
            $('input:text[name="end-time"]').css({'border-color': 'red', 'background-color': 'rgb(255, 200, 200)'}).focus();
            return;
          }
          end = Date.parse(end).getTime();
          end = end / 1000;
          if (start >= end)
          {
            alert('Start time must come before end time');
            $('input:text[name="start-time"]').css({'border-color': 'red', 'background-color': 'rgb(255, 200, 200)'}).focus();
            return;
          }
          query_data.start_time = start;
          query_data.end_time = end;
        }
      }
    }
    console.log(query_data);
    var api_url = '<?php echo URL; ?>api/save_adhoc_search/' + search_id;
    $.ajax({
      url: api_url
      ,type: 'POST'
      ,data: query_data
      ,dataType: 'json'
      ,success: function(data) {
        $.magnificPopup.open({
          items: {
            src: '#success-popup'
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
            }
          }
        });
        if (privacy_change > 0)
        {
          $('li.saved-search-item[data-id="' + search_id + '"]').remove();
          if (query_data.private > 0)
          {
            $('#my-searches').append('<li class="saved-search-item" data-id="' + search_id + '"><span class="iconic empty grey sw-check-box"></span><span class="saved-search-title">' + query_data.title + '</span></li>');
          }
          else
          {
            $('#public-searches').append('<li class="saved-search-item" data-id="' + search_id + '"><span class="iconic empty grey sw-check-box"></span><span class="saved-search-title">' + query_data.title + '</span></li>');
          }
        }
        else
        {
          $('li.saved-search-item[data-id="' + search_id + '"]').children('span.saved-search-title').text(query_data.title);
        }
      }
    });

  }

  // Delete saved searches
  function delete_click_handler(type)
  {
    if (type === "search")
    {
      var selected_searches = $('li.saved-search-item > span.sw-check-box.check-on').parent('li');
      var selected_search_ids = {};
      $.each(selected_searches, function(i, search) {
        selected_search_ids[$(search).children('span.saved-search-title').text()] = $(search).attr('data-id');
      });
      console.log('deleting searches');
      console.log(selected_search_ids);
      $.ajax({
        url: "<?php echo URL; ?>api/delete_saved_searches"
        ,type: 'POST'
        ,data: selected_search_ids
        ,dataType: 'json'
        ,success: function(data) {
          console.log(data);
          $.each(data, function(i, id) {
            $('li.saved-search-item[data-id="' + id + '"]').remove();
          })
        }
      })
    }
    else if (type === "dashboard")
    {
      var selected_dashboards = $('li.saved-dashboard-item > span.sw-check-box.check-on').parent('li');
      var selected_dashboard_ids = {};
      $.each(selected_dashboards, function(i, dash) {
        selected_dashboard_ids[$(dash).children('span.saved-dashboard-title').text()] = $(dash).attr('data-id');
      });
      console.log('deleting dashboards');
      console.log(selected_dashboard_ids);
      $.ajax({
        url: "<?php echo URL; ?>api/delete_saved_dashboards"
        ,type: 'POST'
        ,data: selected_dashboard_ids
        ,dataType: 'json'
        ,success: function(data) {
          console.log(data);
          $.each(data, function(i, id) {
            $('li.saved-dashboard-item[data-id="' + id + '"]').remove();
          })
        }
      })
    }
  }

</script>