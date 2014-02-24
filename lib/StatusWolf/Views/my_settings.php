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
              <div id="search-title" style="display: inline-block;"></div><div id="search-actions" style="display: inline-block">
                <span class="sw-button" id="view-search-button">View Search</span>
                <span class="sw-button" id="edit-search-button">Edit Search</span>
              </div>
              <div id="search-datasource"></div>
              <div id="search-guts"></div>
            </div>
          </div>
          <div class="section section-off" id="edit-saved-dashboards">
            <div id="dashboard-list-pane"></div>
            <div id="dashboard-info-pane">
              <div id="dashboard-title" style="display: inline-block"></div><div id="save-dashboard-changes" class="sw-button" style="display: inline-block">Save Changes</div>
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
      $('#dashboard-info-pane').off('click', '#save-dashboard-changes');
      load_saved_dashboard(dash_id);
    }, 250);
    $('#dashboard-title > h3').text($(this).text());
  });

  // Handler for the fancy check boxes in the saved search list
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
    $(this).parents('label').parents('div.toggle-button').addClass('toggle-on')
        .siblings('div.toggle-button').removeClass('toggle-on');
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
          $('#dashboard-title').empty().append('<h3>' + my_dashboards[0]['title'] + '</h3>');
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
        populate_search_info(query_data, search_id);
      }
    });

  }

  function load_saved_dashboard(dash_id)
  {
    $('#dashboard-info-pane').attr('data-id', dash_id);
    var dash_data = {};

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
        $('#dashboard-title').empty().append('<h3>' + dash_data.title + '</h3>');
        $('#dashboard-guts').empty().append('<ul class="saved-dashboard-info" id="dashboard-widgets">');
        var dashboard_widget_count = 0;
        $.each(dash_data.widgets, function(widget_id, widget_config) {
          dashboard_widget_count++;
          var metric_string = '';
          $('#dashboard-widgets').append('<li data-id="' + widget_id + '">');
          $.each(widget_config.metrics, function(i, metric_info) {
            if ('tags' in metric_info)
            {
              metric_string = metric_string + '<div style="margin-left: 15px;">' + metric_info.name + ' {' + metric_info.tags.join(', ') + '}</div>';
            }
            else
            {
              metric_string = metric_string + '<div style="margin-left: 15px;">' + metric_info.name + '</div>';
            }
          });
          $('li[data-id="' + widget_id + '"]').append('<span class="button-wrapper"><span class="sw-button remove-dashboard-widget" style="vertical-align: top;">Remove</span></span>' +
              '<span class="dashboard-widget-info">' +
              '<span style="color: rgba(205, 205, 205, 0.8);"> Widget ' + dashboard_widget_count + ' Metrics:</span>' +
              '<span>' + metric_string + '</span></span>');
          var dashboard_widget_info = $('li[data-id="' + widget_id + '"] > span.dashboard-widget-info')
          dashboard_widget_info.css('width', (dashboard_widget_info.parent('li').innerWidth() - 100));
        });
        setTimeout(function() {
          $('#dashboard-guts').removeClass('hidden')
        }, 250);
        console.log('saved dashboard loaded');
        console.log(dash_data);
      }
    });

    $('#dashboard-info-pane').on('click', '#save-dashboard-changes', function() {
      save_dashboard_click_handler(dash_data)
    });

    $('#dashboard-guts').on('click', '.remove-dashboard-widget', function() {
      $(this).parent('span').parent('li').addClass('marked-for-removal');
      $(this).addClass('cancel-remove-dashboard-widget').removeClass('remove-dashboard-widget').text('Cancel');
    });

    $('#dashboard-guts').on('click', '.cancel-remove-dashboard-widget', function() {
      $(this).parent('span').parent('li').removeClass('marked-for-removal');
      $(this).addClass('remove-dashboard-widget').removeClass('cancel-remove-dashboard-widget').text('Remove');
    });
  }

  // Takes the loaded query info and populates the info form with the current
  // state of the saved search
  function populate_search_info(query_data, search_id)
  {
    $('#view-search-button').click(function() {
      window.location.href = window.location.origin + '/adhoc/saved/' + search_id
    });
    $('#edit-search-button').click(function() {
      window.location.href = window.location.origin + '/adhoc/saved/' + search_id + '?edit=true';
    });
    var spans_map = {
      '600': '10 Minutes',
      '1800': '30 Minutes',
      '3600': '1 Hour',
      '7200': '2 Hours',
      '14400': '4 Hours',
      '28800': '8 Hours',
      '43200': '12 Hours',
      '86400': '1 Day',
      '604800': '1 Week',
      '1209600': '2 Weeks',
      '2592000': '1 Month'
    };

    var ds_map = {
      '1': '1 Minute',
      '10': '10 Minutes',
      '30': '30 Minutes',
      '60': '1 Hour',
      '240': '4 Hours',
      '720': '12 Hours',
      '1440': '1 Day'
    };

    $('#search-title').empty().append('<h3>' + query_data.title + ' (' + query_data.datasource + ')</h3>');
    $('#search-guts').empty().append('<table class="general-options-table" id="saved-search-options"></table>');
    var guts_table = $('table#saved-search-options');
    guts_table.append('<tr id="time-row"></tr>');
    if (query_data.period === "date-search") {
      var start = new Date(query_data.start_time * 1000);
      var end = new Date(query_data.end_time * 1000);
      $('tr#time-row').append('<th>Date Range</th><td>' +
        'Start: ' + start + '<br>End: ' + end + '</td>');
    } else {
      $('tr#time-row').append('<th>Time Span</th><td>' + spans_map[query_data.time_span] + '</td>');
    }
    var graph_type = 'No History';
    if (query_data.history === "anomaly") {
      graph_type = 'Anomaly Detection';
    } else if (query_data.history === "wow") {
      graph_type = 'Week-Over-Week';
    }
    guts_table.append('<tr><th>Auto Update:</th><td>' + query_data.auto_update + '</td></tr>')
        .append('<tr><th>Graph Type:</th><td>' + graph_type + '</td></tr>');

    var metric_number = 1;
    $.each(query_data.metrics, function(key, metric) {
      guts_table.append('<tr><th>Metric ' + metric_number + ':</th><td>' + metric.name + '</td></tr>');
      guts_table.append('<tr class="metric-info-row"><td></td><td>' +
              '<table id="metric-' + metric_number + '-info" class="metric-info-sub-table">' +
              '<tr><th>Tags:</th><td>' + (typeof metric.tags !== "undefined" ? metric.tags.join(' ') : '') + '</td></tr>' +
              '<tr><th>Aggregation Type:</th><td>' + metric.agg_type.charAt(0).toUpperCase() + metric.agg_type.slice(1) + '</td></tr>' +
              '<tr><th>Downsample Type:</th><td>' + metric.ds_type.charAt(0).toUpperCase() + metric.ds_type.slice(1) + '</td></tr>' +
              '<tr><th>Downsample Interval:</th><td>' + ds_map[metric.ds_interval] + '</td></tr>' +
              '<tr><th>Rate?</th><td>' + (typeof metric.rate !== "undefined" ? metric.rate : 'false') + '</td></tr>' +
              '<tr><th>Right Axis?</th><td>' + (typeof metric.y2 !== "undefined" ? metric.y2 : 'false') + '</td></tr>' +
              '<tr><th>Treat Null As Zero?</th><td>' + (typeof metric.null_zero !== "undefined" ? metric.null_zero : 'false') + '</td></tr>' +
              '</table></td></tr>');
      metric_number++;
    });

    setTimeout(function() {
      $('#search-guts').removeClass('hidden');
    }, 250);

  }

  function save_dashboard_click_handler(dash_data) {
    dash_data.title = $('#dashboard-title > h3').text();
    var marked_for_death = $('li.marked-for-removal');
    $.each(marked_for_death, function(i, dead_pool) {
      var removal_id = $(dead_pool).attr('data-id');
      delete(dash_data.widgets[removal_id]);
    });
    console.log('saving dashboard changes');
    console.log(dash_data);
    $.ajax({
      url: '<?php echo URL; ?>api/save_dashboard/' + dash_data.id + '/Confirm'
      ,type: 'POST'
      ,data: dash_data
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
      }
    });
    $('li.saved-dashboard-item[data-id="' + dash_data.id + '"]').children('span.saved-dashboard-title').text(dash_data.title);
    $('#dashboard-guts').addClass('hidden');
    setTimeout(function() {
      load_saved_dashboard(dash_data.id);
    }, 250);
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
      $.ajax({
        url: "<?php echo URL; ?>api/delete_saved_searches"
        ,type: 'POST'
        ,data: selected_search_ids
        ,dataType: 'json'
        ,success: function(data) {
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
      $.ajax({
        url: "<?php echo URL; ?>api/delete_saved_dashboards"
        ,type: 'POST'
        ,data: selected_dashboard_ids
        ,dataType: 'json'
        ,success: function(data) {
          $.each(data, function(i, id) {
            $('li.saved-dashboard-item[data-id="' + id + '"]').remove();
          })
        }
      })
    }
  }

</script>