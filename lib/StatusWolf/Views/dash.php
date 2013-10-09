<?php
/**
 * dash.php
 *
 * Master view for custom dashboards
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 10 July 2013
 *
 * @package StatusWolf.Views
 */

// The basics - current session, app config, database connection
$_session_data = $_SESSION[SWConfig::read_values('auth.sessionName')];
$_sw_conf = SWConfig::read_values('statuswolf');
$db_conf = $_sw_conf['session_handler'];

?>

<link rel="stylesheet" href="<?php echo URL; ?>app/css/widget_base.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/dash.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/popups.css?v=1.0">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/push-button.css">

<div class="container">
  <div id="dash-container"></div>
</div>

<div id="save-dashboard-popup" class="popup mfp-hide">
  <div id="save-dashboard-form">
    <form onsubmit="return false;">
      <h5 style="display: inline-block;">Title: </h5>
      <div class="popup-form-data" style="display: inline-block">
        <input type="text" class="input" id="save-dashboard-title" name="dashboard-title" value="" style="width: 250px;">
      </div>
      <div class="push-button" style="display: inline-block; margin-left: 10px;">
        <input type="checkbox" id="shared" name="shared"><label for="shared"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> Shared Dashboard</span></label>
      </div>
    </form>
  </div>
  <div class="flexy widget-footer" style="margin-top: 10px;">
    <div class="widget-footer-button" id="cancel-save-dashboard-button" onClick="$.magnificPopup.close()"><span class="iconic iconic-x-alt"><span class="font-reset"> Cancel</span></span></div>
    <div class="glue1"></div>
    <div class="widget-footer-button" id="save-dashboard-button" onClick="save_click_handler(event, 0)"><span class="iconic iconic-download"><span class="font-reset"> Save</span></span></div>
  </div>
</div>

<div id="success-popup" class="popup mfp-hide"><h5>Success</h5><div class="popup-form-data">Your dashboard has been saved.</div></div>
<div id="failure-popup" class="popup mfp-hide"><h5>Error</h5><div id="failure-info" class="popup-form-data">There was an error when saving your dashboard, please try again later.</div></div>
<div id="confirmation-popup" class="popup mfp-hide">
  <div id="confirmation-main">
    <div id="confirmation-info" class="popup-form-data"></div>
  </div>
  <div class="flexy widget-footer" style="margin-top: 10px;">
    <div class="widget-footer-button" id="cancel-confirm-button" onClick="$.magnificPopup.close()"><span class="iconic iconic-x-alt"><span class="font-reset"> Cancel</span></span></div>
    <div class="glue1"></div>
    <div class="widget-footer-button" id="confirm-save-button"><span class="iconic iconic-download"><span class="font-reset"> Overwrite</span></span></div>
  </div>
</div>
<div id="dashboard-list-popup" class="popup mfp-hide">
  <div id="my-dashboards-box">
    <div id="my-dashboard-head">
      <h2>My Dashboards</h2>
    </div>
    <div id="my-dashboard-list">
      <table id="my-dashboard-list-table" class="table">
        <tr>
          <th>Dashboard Title</th>
        </tr>
      </table>
    </div>
  </div>
  <div id="shared-dashboards-box">
    <div id="shared-dashboard-head">
      <h2>Shared Dashboards</h2>
    </div>
    <div id="shared-dashboard-list">
      <table id="shared-dashboard-list-table" class="table">
        <tr class="header-row">
          <th>Dashboard Title</th>
          <th>User</th>
        </tr>
      </table>
    </div>
  </div>
</div>

<link rel="stylesheet" href="<?php echo URL; ?>app/css/datetimepicker.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/toggle-buttons.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/table.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/loader.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/tooltip.css">

<script type="text/javascript" src="<?php echo URL; ?>app/js/sw_lib.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/jquery-ui.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/bootstrap.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/date.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/md5.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/dygraph-combined.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/status_wolf_colors.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/jquery.autocomplete.js"></script>


<?php

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

// Activate the widget types
foreach($widgets as $widget_key)
{
  $widget_info = file_get_contents($widget_main . DS . $widget_key . DS . $widget_key . '.json');
  $widget_info = implode('', explode("\n", $widget_info));
  $widget_list[$widget_key] = json_decode($widget_info, true);
  echo '<script type="text/javascript" src="' . URL . WIDGETS_URL . $widget_key . '/js/' . $widget_list[$widget_key]['name'] . '.js"></script>';
}

?>

<script type="text/javascript">

  // Reset the browser tab title and the dashboard title in the nav bar
  $('title').text('Dashboard - StatusWolf');
  $('div#dashboard_title').remove();

  var _session_data = '<?php echo json_encode($_session_data); ?>';
  if (typeof _session_data == "string")
  {
    document._session_data = eval('(' + _session_data + ')');
  }
  else
  {
    document._session_data = _session_data
  }
  var _sw_conf = '<?php echo json_encode($_sw_conf); ?>';
  if (typeof _sw_conf == "string")
  {
    document._sw_conf = eval('(' + _sw_conf + ')');
  }
  else
  {
    document._sw_conf = _sw_conf;
  }

  // Grab the config for the dashboard we're loading (if there is one)
  if (typeof document._session_data.data.dashboard_id !== "undefined")
  {
    console.log('loading dashboard ' + document._session_data.data.dashboard_id);
    $.when(get_dashboard_config(document._session_data.data.dashboard_id)).then(
        function(data) {
          build_dashboard(data);
        }
    );
  }

  $(document).ready(function() {
    var widgets = eval('(<?php echo json_encode($widget_list); ?>)');
    var loaded_widgets = [];
    this.sw_url = '<?php echo URL; ?>';

    // Add the dashboard menu
    $('#menu-placeholder').replaceWith('<div class="dashboard-menu left-button dropdown menu-btn" id="dashboard-menu">');
    $('#dashboard-menu').append('<span class="flexy" id="dashboard-menu-content" data-toggle="dropdown">')
    $('#dashboard-menu-content').append('<span class="menu-label" id="dashboard-menu-label">Dashboard</span>');
    $('#dashboard-menu').append('<ul class="dropdown-menu sub-menu-item" id="dashboard-menu-options" role="menu" aria-labelledby="dLabel">');
    $('#dashboard-menu-options').append('<li id="clear-dashboard-menu-choice"><a href="<?php echo URL; ?>dashboard/"><span>Clear</span></a></li>');
    $('#dashboard-menu-options').append('<li class="flexy dropdown" id="add-widget-menu-item"><span>Add Widget</span></span><span class="iconic iconic-play"></span></li>');
    $('#add-widget-menu-item').append('<ul class="dropdown-menu sub-menu" id="add-widget-menu-options">');
    $.each(widgets, function(widget_index, widget_data) {
      var widget_type = widget_data.name.split('.');
      $('#add-widget-menu-options').append('<li onClick="add_widget(\'' + widget_type[1] + '\')"><span>' + widget_data.title + '</span></li>');
      $('head').append('<link rel="stylesheet" href="<?php echo URL . WIDGETS_URL; ?>' + widget_index + '/css/' + widget_data.name + '.css">');

    });
    $('#dashboard-menu-options').append('<li class="flexy dropdown" id="load-dashboard-menu-item"><span>Load Dashboard</span></span><span class="iconic iconic-play"></span></li>');
    $('#load-dashboard-menu-item').append('<ul class="dropdown-menu sub-menu" id="load-dashboard-menu-options">');
    $('#dashboard-menu-options').append('<li id="save-dashboard-menu-choice"><span>Save Dashboard</span></li>');

    // Add the sub menu for the list of saved dashboards
    $.when(build_dashboard_list_menu()).then(
      function(data)
      {
        if (data[1] > data[0])
        {
          $('#load-dashboard-menu-options').append('<li id="full-dashboard-list"><span>More...</span></li>')
        }
      }
    );

    // If the list is too long we truncate it and give the user a "More..." option (see above)
    // to load a popup with the complete list.
    $('#dashboard-menu').on('click', 'li#full-dashboard-list', function() {
      $.magnificPopup.open({
        items: {
          src: '#dashboard-list-popup'
          ,type: 'inline'
        }
        ,preloader: false
        ,removalDelay: 300
        ,mainClass: 'popup-animate'
        ,callbacks: {
          open: function() {
            setTimeout(function() {
              $('.container').addClass('blur');
              $('.navbar').addClass('blur');
            }, 150);
          }
          ,close: function() {
            $('.container').removeClass('blur');
            $('.navbar').removeClass('blur');
          }
        }
      });
    });

    // Handler for the save dashboard dialog
    $('#save-dashboard-menu-choice').magnificPopup({
      items: {
        src: '#save-dashboard-popup'
        ,type: 'inline'
      }
      ,preloader: false
      ,focus: '#save-dashboard-title'
      ,removalDelay: 300
      ,mainClass: 'popup-animate'
      ,callbacks: {
        open: function() {
          setTimeout(function() {
            $('.container').addClass('blur');
            $('.navbar').addClass('blur');
          }, 150);
        }
        ,close: function() {
          $('.container').removeClass('blur');
          $('.navbar').removeClass('blur');
        }
      }
    });

  });

  // Add a widget to the dashboard
  function add_widget(widget_type)
  {
    var username = "<?php echo $_session_data['username'] ?>";
    var widget_id = "widget" + md5(username + new Date.now().getTime());
    var widget;
    $('#dash-container').append('<div class="widget-container" id="' + widget_id + '" data-widget-type="' + widget_type + '">');
    if (widget_type === "graphwidget")
    {
      widget = $('#' + widget_id).graphwidget();
      setTimeout(function() {
        widget.data('sw-graphwidget').sw_graphwidget_editparamsbutton.click();
      }, 250);
    }
    setTimeout(function() {
      $('#' + widget_id).removeClass('transparent');
    }, 100);
  }

  // Add a new widget as a duplicate of the selected widget
  function clone_widget(widget)
  {
    var username = "<?php echo $_session_data['username'] ?>";
    var widget_id = "widget" + md5(username + new Date.now().getTime());
    var widget_element = $(widget.element);
    var widget_type = $(widget_element).attr('data-widget-type');
    if (widget_type === "graphwidget")
    {
      $('#dash-container').append('<div class="widget-container" id="' + widget_id + '" data-widget-type="' + widget_type + '">');
      new_widget = $('div#' + widget_id).graphwidget(widget.options);
      new_widget_object = $(new_widget).data('sw-' + new_widget.attr('data-widget-type'));
      new_widget_object.populate_search_form(widget.query_data, new_widget_object, 'clone');
      $('#' + widget_id).removeClass('transparent');
    }
    else
    {
      console.log('unknown widget type: ' + widget_element.widget_type);
    }

  }

  // What to do when the user chooses "Save dashboard" from the menu
  function save_click_handler(event, confirmation, dashboard_id)
  {
    var dashboard_widgets = {};
    var dashboard_config = {};
    var widget_list = $('.widget-container');

    // Make sure there are actually widgets on the dashboard
    if (widget_list.length > 0)
    {
      $.each(widget_list, function(i, widget) {
        var sw_widget = $(widget).data('sw-' + $(widget).attr('data-widget-type'));
        var widget_id = $(widget).attr('id');
        if (typeof sw_widget.query_data === "undefined")
        {
          console.log('no query data defined for widget ' + widget_id);
        }
        else
        {
          dashboard_widgets[widget_id] = sw_widget.query_data;
          dashboard_widgets[widget_id]['widget_type'] = $(widget).attr('data-widget-type');
          if (typeof sw_widget.options.sw_url !== "undefined")
          {
            delete(sw_widget.options.sw_url);
          }
          dashboard_widgets[widget_id]['options'] = sw_widget.options;
        }
      })
    }
    else
    {
      // No widgets, no save
      alert('Blank dashboard, saving you from yourself and refusing to save this...');
    }
    // If this is a brand new dashboard, create an id for it
    if (typeof dashboard_id == "undefined")
    {
      dashboard_id = md5("dashboard" + document._session_data.username + new Date.now().getTime());
    }
    dashboard_config = { id: dashboard_id
      ,title: $('input#save-dashboard-title').val()
      ,shared: $('#shared').prop('checked')?1:0
      ,username: document._session_data.username
      ,user_id: document._session_data.user_id
      ,widgets: dashboard_widgets };

    save_dash_url = "<?php echo URL; ?>api/save_dashboard/" + dashboard_id;
    if (confirmation > 0)
    {
      save_dash_url += "/Confirm";
    }
    $.ajax({
      url: save_dash_url
      ,type: 'POST'
      ,dataType: 'json'
      ,data: dashboard_config
      ,success: function(data) {
        if (typeof data == "string")
        {
          data = eval('(' + data + ')');
        }
        if (data.query_result === "Error")
        {
          switch (data.query_info)
          {
            case "Title":
              $('#confirmation-info').empty().append("<span>A dashboard with that name already exists, overwrite?</span>");
              $('#confirm-save-button').click( function(event) {
                save_click_handler(event, 1, data.dashboard_id);
              });
              $.magnificPopup.open({
                items: {
                  src: '#confirmation-popup'
                  ,type: 'inline'
                }
                ,preloader: false
                ,mainClass: 'popup-animate'
                ,callbacks: {
                  close: function() {
                    $('#confirmation-popup').remove();
                  }
                }
              });
              return;
            default:
              $.magnificPopup.open({
                items: {
                  src: '#failure-popup'
                  ,type: 'inline'
                }
                ,preloader: false
                ,removalDelay: 300
                ,mainClass: 'popup-animate'
                ,callbacks: {
                  close: function() {
                    $('#failure-popup').remove();
                  }
                }
              })
              setTimeout(function() {
                $.magnificPopup.close();
              }, 750);
          }
        }
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
              $('#success-popup').remove();
            }
          }
        });
        setTimeout(function() {
          $.magnificPopup.close();
        }, 750);
      }
    });
  }

  // Pull the list of saved dashboards from the database, build the menu and the popup list
  function build_dashboard_list_menu()
  {
    var api_url = '<?php echo URL; ?>api/get_saved_dashboards';
    api_query = {user_id: document._session_data.user_id};
    var menu_length = 15;
    var item_length = 0;

    dashboard_menu = new $.Deferred();

    dashboard_list_query = $.ajax({
        url: api_url
        ,type: 'POST'
        ,data: api_query
        ,dataType: 'json'
      })
      ,chain = dashboard_list_query.then(function(data) {
        return(data);
      });
    chain.done(function(data) {
      var my_dashboards = data['user_dashboards'];
      var shared_dashboards = data['shared_dashboards'];
      var menu_items = menu_length;
      $('#load-dashboard-menu-options').empty();
      $('#load-dashboard-menu-options').append('<li class="menu-section"><span>My Dashboards</span></li>');
      if (my_dashboards)
      {
        var max = (my_dashboards.length < menu_length ? my_dashboards.length : menu_length);
        for (i = 0; i < max; i++) {
          $('#load-dashboard-menu-options').append('<li><span><a href="<?php echo URL; ?>dashboard/' + my_dashboards[i]['id'] + '">' + my_dashboards[i]['title'] + '</span></li>');
          menu_length--;
        }
        $.each(my_dashboards, function(i, dashboard) {
          $('table#my-dashboard-list-table').append('<tr class="dashboard-item"><td><a href="<?php echo URL; ?>dashboard/' + dashboard.id + '">' + dashboard.title + '</a></td></tr>');
        });
      }

      if (menu_length > 0 && shared_dashboards)
      {
        $('#load-dashboard-menu-options').append('<li class="divider">');
        $('#load-dashboard-menu-options').append('<li class="menu-section"><span>Shared Dashboards</span></li>');
        var max = (shared_dashboards.length < menu_length ? shared_dashboards.length : menu_length);
        for (i = 0; i < max; i++)
        {
          if (shared_dashboards[i]['user_id'] === document._session_data.user_id)
          {
            $('#load-dashboard-menu-options').children('li.menu-section:last').after('<li><span><a href="<?php echo URL; ?>dashboard/' + shared_dashboards[i]['id'] + '">' + shared_dashboards[i]['title'] + ' (' + shared_dashboards[i]['username'] + ')</a></span></li>');
          }
          else
          {
            $('#load-dashboard-menu-options').append('<li><span><a href="<?php echo URL; ?>dashboard/' + shared_dashboards[i]['id'] + '">' + shared_dashboards[i]['title'] + ' (' + shared_dashboards[i]['username'] + ')</a></span></li>');
          }
          menu_length--;
        }
        $.each(shared_dashboards, function(i, shared) {
          if (shared.user_id === document._session_data.user_id)
          {
            $('table#shared-dashboard-list-table').children('tbody').children('tr.header-row').after('<tr class="dashboard-item"><td><a href="<?php echo URL; ?>dashboard/' + shared.id + '">' + shared.title + '</a></td><td>' + shared.username + '</td></tr>');
          }
          else
          {
            $('table#shared-dashboard-list-table').append('<tr class="dashboard-item"><td><a href="<?php echo URL; ?>dashboard/' + shared.id + '">' + shared.title + '</a></td><td>' + shared.username + '</td></tr>');
          }
        });
      }

      var my_dash_count = (typeof my_dashboards !== "undefined" ? my_dashboards.length : 0);
      var shared_dash_count = (typeof shared_dashboards !== "undefined" ? shared_dashboards.length : 0);
      item_length = my_dash_count + shared_dash_count;

      dashboard_menu.resolve([menu_length, item_length]);

    });

    return dashboard_menu.promise();
  }

  // Fetch the config for a requested saved dashboard
  function get_dashboard_config(dash_id)
  {
    var api_url = '<?php echo URL; ?>api/load_saved_dashboard/' + dash_id;

    console.log('get_dashboard_config - ' + dash_id);
    var dashboard_config = new $.Deferred();

    dashboard_config_query = $.ajax({
        url: api_url
        ,type: 'GET'
        ,dataType: 'json'
      })
      ,chain = dashboard_config_query.then(function(data) {
        return(data);
      });
    chain.done(function(data) {
      dashboard_config.resolve(data);
    });

    return dashboard_config.promise();

  }

  // Load up the requested saved dashboard
  function build_dashboard(dashboard_config)
  {
    if (typeof dashboard_config.error !== "undefined")
    {
      // Alert if the user doesn't own the dashboard they tried to load
      if (dashboard_config.error === "Not Allowed")
      {
        $('.container').append('<div id="not-allowed-popup" class="popup"><h5>Not Allowed</h5><div class="popup-form-data">You do not have permission to view this dashboard</div></div>');
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
              window.history.pushState("", "StatusWolf", "/dashboard/");
            }
          }
        });
      }
      // Alert if the user tried to load a non-existent dashboard
      else if(dashboard_config.error === "Not Found")
      {
        $('.container').append('<div id="not-found-popup" class="popup"><h5>Not Found</h5><div class="popup-form-data">The dashboard was not found.</div></div>');
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
              window.history.pushState("", "StatusWolf", "/dashboard/");
            }
          }
        });
      }
    }
    else
    {
      // Load us up a dashboard
      // Set the browser tab title, put the dashboard name in the nav bar
      $('title').text(dashboard_config.title + ' - StatusWolf');
      $('input#save-dashboard-title').val(dashboard_config.title);
      $('div#dashboard-menu').after('<div id="dashboard-title">' + dashboard_config.title + '</div>');

      $.each(dashboard_config.widgets, function(widget_id, query_data) {
        if (query_data.widget_type === "graphwidget")
        {
          $('#dash-container').append('<div class="widget-container" id="' + widget_id + '" data-widget-type="' + query_data.widget_type + '">');
          if (typeof query_data.options !== "undefined" && typeof query_data.options.sw_url !== "undefined")
          {
            delete(query_data.options.sw_url);
          }
          new_widget = $('div#' + widget_id).graphwidget(query_data.options);
          widget_object = $(new_widget).data('sw-' + new_widget.attr('data-widget-type'));
          widget_object.populate_search_form(query_data, widget_object);
          $('#' + widget_id).removeClass('transparent');
        }
        else
        {
          console.log('unknown widget type: ' + query_data.widget_type);
        }
      });
    }
  }
</script>