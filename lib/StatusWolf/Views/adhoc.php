<?php
/**
 * adhoc.php
 *
 * View for adhoc search interface
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 01 June 2013
 *
 * @package StatusWolf.Views
 */
  $_session_data = $_SESSION[SWConfig::read_values('auth.sessionName')];
  $_sw_conf = SWConfig::read_values('statuswolf');
  $db_conf = $_sw_conf['session_handler'];
?>

<link rel="stylesheet" href="<?php echo URL; ?>app/css/widget_base.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/adhoc.css">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/popups.css?v=1.0">
<link rel="stylesheet" href="<?php echo URL; ?>app/css/push-button.css">

<div class="container">
  <div id="adhoc-container"></div>
</div>

<div id="saved-search-list-popup" class="popup mfp-hide">
  <div id="my-searches-box">
    <div id="my-searches-head">
      <h2>My Searches</h2>
    </div>
    <div id="my-searches-list">
      <table id="my-searches-list-table" class="table">
        <tr>
          <th>Search Title</th>
        </tr>
      </table>
    </div>
  </div>
  <div id="public-searches-box">
    <div id="public-searches-head">
      <h2>Public Searches</h2>
    </div>
    <div id="public-searches-list">
      <table id="public-searches-list-table" class="table">
        <tr class="header-row">
          <th>Search Title</th>
          <th>User</th>
        </tr>
      </table>
    </div>
  </div>
</div>

<div id="save-form-popup" class="popup mfp-hide">
  <div id="save-query-form">
    <form onsubmit="return false;">
      <h5>Title: </h5>
      <div class="popup-form-data">
        <input type="text" class="input" id="save-search-title" name="save-search-title" value="" style="width: 90%; font-size: 1em;">
        <input type="hidden" class="hidden" id="search-id" name="search-id" value="">
      </div>
      <h5>Options:</h5>
      <div class="popup-form-data">
        <div class="save-form-row" style="font-size: 1em;">
          <div class="push-button">
            <input type="checkbox" id="save-span" name="save-span"><label for="save-span"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> Save Search Times</span></label>
          </div>
          <div class="push-button">
            <input type="checkbox" id="public" name="public"><label for="public"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> Public Search</span></label>
          </div>
        </div>
      </div>
    </form>
  </div>
  <div id="save-query-info">
    <ul>
      <li><em>Save Search Times:</em> If selected will save the exact times for this search. If not selected and
        the search times were specified specifically you will be prompted for new times when loading the shared
        search. If the search times were specified as a time span (e.g. "Show me the past 8 hours") the span setting
        will be saved with the search and used when it is loaded again.</li>
      <li><em>Public Search: </em>If selected the saved search will show up for all logged in users in their Public
        Searches list, otherwise it will only be visible to you, in your Saved Searches list.</li>
    </ul>
  </div>
  <div class="flexy widget-footer" style="margin-top: 10px;">
    <div class="widget-footer-button" id="cancel-save-query-data-button" onClick="$.magnificPopup.close()"><span class="iconic iconic-x-alt"><span class="font-reset"> Cancel</span></span></div>
    <div class="glue1"></div>
    <div class="widget-footer-button" id="save-query-data-button" onClick="save_click_handler(event, 0)"><span class="iconic iconic-download"><span class="font-reset"> Save</span></span></div>
  </div>
</div>

<div id="share-search-popup" class="popup mfp-hide">
  <h5>Share Link to Search</h5>
  <div class="popup-form-data">
    <p id="share-info">Copy and paste this link into email, chat, etc. to share this search. Shared links will expire after 24 hours.</p>
    <div class="uneditable-input" id="shared-search-url" onClick="select_text('shared-search-url')" style="width: 95%; vertical-align: middle; padding: 6px 10px 2px 10px;"></div>
  </div>
</div>

<div id="datasource-url-popup" class="popup mfp-hide">
  <h5>Raw Datasource URL</h5>
  <div class="popup-form-data">
    <div class="uneditable-input" id="datasource-url" onClick="select_text('datasource-url')" style="width: 95%; vertical-align: middle; padding: 6px 10px 2px 10px; font-size: 0.8em;"></div>
  </div>
</div>

<div id="success-popup" class="popup mfp-hide"><h5>Success</h5><div class="popup-form-data">Your search has been saved.</div></div>
<div id="failure-popup" class="popup mfp-hide"><h5>Error</h5><div id="failure-info" class="popup-form-data">There was an error when saving your search, please try again later.</div></div>
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
<div id="raw-data" class="popup mfp-hide">
  <table id="raw-data-table" class="table"></table>
</div>

<div id="range-set-popup" class="popup mfp-hide"><div class="popup-form-data"></div></div>

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
<script type="text/javascript" src="<?php echo URL; ?>app/js/status_wolf_colors.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/jquery.autocomplete.js"></script>
<script type="text/javascript" src="<?php echo URL; ?>app/js/lib/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?php echo $_sw_conf['graphing']['d3_location'] ?>"></script>

<script type="text/javascript">

  $('title').text('AdHoc Search - StatusWolf');

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

  document._session_data.data.dashboard_columns = 1;
  $.ajax({
    url: '<?php echo URL; ?>api/session_data/set/dashboard_columns=1'
    ,type: 'GET'
    ,dataType: 'json'
    ,done: function(data) {
    }
  });

  $('.popup').on('click', '.push-button', function() {
    statuswolf_button(this);
  });

  $(document).ready(function() {
    this.sw_url = '<?php echo URL; ?>';

    $('head').append('<link rel="stylesheet" href="<?php echo URL . WIDGETS_URL; ?>GraphWidget/css/sw.graphwidget.css">');
    loadScript('<?php echo URL . WIDGETS_URL ?>GraphWidget/js/sw.graphwidget.js', function(){

      // Grab the config for shared search or saved search we're loading (if there is one)
      if (typeof document._session_data.shared_search_key !== "undefined")
      {
        $.when(get_shared_search_query(document._session_data.shared_search_key)).then(
            function(query_data)
            {
              load_search(query_data);
            }
        );
      }
      else if (typeof document._session_data.saved_search_key !== "undefined")
      {
        if (typeof document._session_data.saved_search_options !== "undefined") {
          var search_option = document._session_data.saved_search_options[0].split('=');
          if (search_option[0] === "edit") {
            $.when(get_saved_search_query(document._session_data.saved_search_key)).then(
                function(query_data) {
                  load_search(query_data, true);
                }
            )
          }
        } else {
          $.when(get_saved_search_query(document._session_data.saved_search_key)).then(
              function(query_data)
              {
                load_search(query_data, false);
              }
          )
        }
      }
      else
      {
        var username = "<?php echo $_session_data['username'] ?>";
        var widget_id = "widget" + md5(username + new Date.now().getTime());
        $('div#adhoc-container').append('<div class="widget-container cols-1" id="' + widget_id + '" data-widget-type="graphwidget">');
        var widget_div = $('#' + widget_id).graphwidget({'nointerpolation': document._sw_conf.nointerpolation});
        adhoc_fixes('#' + widget_id);
        setTimeout(function() {
          widget_div.children('.widget').addClass('flipped');
        }, 250);
        setTimeout(function() {
          widget_div.removeClass('transparent');
        }, 100);
      }
    });

    // Add the adhoc menu
    $('#menu-placeholder').replaceWith('<div class="adhoc-menu left-button dropdown menu-btn" id="adhoc-menu" style="top: -2px">');
    $('#adhoc-menu').append('<span class="flexy" id="adhoc-menu-content" data-toggle="dropdown">')
    $('#adhoc-menu-content').append('<span class="menu-label" id="adhoc-menu-label">Ad-Hoc Search</span>');
    $('#adhoc-menu').append('<ul class="dropdown-menu sub-menu-item" id="adhoc-menu-options" role="menu" aria-labelledby="dLabel">');
    $('#adhoc-menu-options').append('<li id="clear-adhoc-menu-choice"><a href="<?php echo URL; ?>adhoc/"><span>New Search</span></a></li>');
    $('#adhoc-menu-options').append('<li class="flexy dropdown" id="load-search-menu-item"><span>Load Search</span></span><span class="iconic iconic-play"></span></li>');
    $('#load-search-menu-item').append('<ul class="dropdown-menu sub-menu" id="load-search-menu-options">');
    $('#adhoc-menu-options').append('<li id="save-search-menu-choice"><span>Save Search</span></li>');
    $('#adhoc-menu-options').append('<li id="share-search-menu-choice"><span>Share Search</span></li>');
    $('#adhoc-menu-options').append('<li id="get-datasource-url-choice"><span>Get Datasource URL</span></li>');
    $('#adhoc-menu-options').append('<li id="show-raw-data-menu-choice"><span>Show Graph Data</span></li>');

    // Load the list of saved searches
    $.when(build_saved_search_menu()).then(
        function(data)
        {
          if (data[1] > data[0])
          {
            $('#load-search-menu-options').append('<li id="full-saved-search-list"><span>More...</span></li>')
          }
        }
    );

    // If the list is too long we truncate it and give the user a "More..." option (see above)
    // to load a popup with the complete list.
    $('#adhoc-menu').on('click', 'li#full-saved-search-list', function() {
      $.magnificPopup.open({
        items: {
          src: '#saved-search-list-popup'
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

    // Handler for the save search dialog
    $('#save-search-menu-choice').magnificPopup({
      items: {
        src: '#save-form-popup'
        ,type: 'inline'
      }
      ,preloader: false
      ,focus: '#save-search-title'
      ,removalDelay: 300
      ,mainClass: 'popup-animate'
      ,callbacks: {
        open: function() {
          setTimeout(function() {
            $('.container').addClass('blur');
            $('.navbar').addClass('blur');
          }, 150);
          if ($('input[name="save-search-title"]').attr('value').length < 1)
          {
            if ($('.graph-title').text().length < 1)
            {
              <?php if (array_key_exists('user_searches', $_session_data['data'])) { $search_count = count($_session_data['data']['user_searches']); } else { $search_count = 0; } ?>
              $('input[name="save-search-title"]').attr('value', '<?php echo $_session_data['username']; ?>' + '_' + $('.widget-container').data('sw-graphwidget').options.datasource + '_' + '<?php echo $search_count + 1; ?>');
            }
            else
            {
              $('input[name="save-search-title"]').attr('value', $('.graph-title').text());
            }
          }
        }
        ,close: function() {
          $('.container').removeClass('blur');
          $('.navbar').removeClass('blur');
        }
      }
    });

    $('#share-search-menu-choice').magnificPopup({
      items: {
        src: '#share-search-popup'
        ,type: 'inline'
      }
      ,preloader: false
      ,removalDelay: 300
      ,mainClass: 'popup-animate'
      ,callbacks: {
        beforeOpen: function() {
          var api_url = "<?php echo URL; ?>api/save_shared_search";
          var widget = $('.widget-container').data('sw-graphwidget');
          widget.query_data['title'] = 'Shared search from <?php echo $_session_data['username']; ?>';
          if (widget.query_data['period'] === "span-search")
          {
            delete widget.query_data['start_time'];
            delete widget.query_data['end_time'];
          }
          $.ajax({
            url: api_url
            ,type: 'POST'
            ,data: widget.query_data
            ,dataType: 'json'
            ,success: function(data) {
              var share_url = "<?php echo URL; ?>adhoc/shared/" + data['search_id'];
              $("#shared-search-url").text(share_url);
            }
          });
        }
        ,open: function() {
          setTimeout(function() {
            $('.navbar').addClass('blur');
            $('.container').addClass('blur');
          }, 150);
        }
        ,close: function() {
          $('.container').removeClass('blur');
          $('.navbar').removeClass('blur');
        }
      }
    });

    $('#get-datasource-url-choice').magnificPopup({
      items: {
        src: '#datasource-url-popup'
        ,type: 'inline'
      }
      ,preloader: false
      ,removalDelay: 300
      ,mainClass: 'popup-animate'
      ,callbacks: {
        beforeOpen: function() {
          var widget = $('.widget-container').data('sw-graphwidget');
          var ds_url = widget.query_url;
          if (widget.query_data['datasource'] === "OpenTSDB")
          {
            ds_url = ds_url.replace('&ascii','');
          }
          $('body').append('<div id="width-test" style="position: absolute; visibility: hidden;">' + ds_url + '</div>');
          var text_width = $('#width-test').width();
          $('#width-test').remove();
          $('#datasource-url-popup').css('width', text_width);
          $('#datasource-url').text(ds_url);
        }
        ,open: function() {
          setTimeout(function() {
            $('.navbar').addClass('blur');
            $('.container').addClass('blur');
          }, 150);
        }
        ,close: function() {
          $('.container').removeClass('blur');
          $('.navbar').removeClass('blur');
        }
      }
    });

    $('#show-raw-data-menu-choice').click(function() {
      var widget = $('.widget-container').data('sw-graphwidget');
      widget.show_graph_data();
    });

  });

  function adhoc_fixes(widget_id)
  {
    widget = $(widget_id).data('sw-graphwidget');
    widget.sw_graphwidget_savedsearchesmenu.remove();
    widget.sw_graphwidget_action.children('ul').empty();
    widget.sw_graphwidget_action.children('ul')
        .append('<li data-menu-action="maximize_widget"><span class="maximize-me">Maximize</span></li>' +
            '<li id="edit_params"><span>Edit Parameters</span></li>' +
            '<li data-menu-action="set_all_spans"><span>Use this time span</span></li>');
    $('li#edit_params').click(function(event)
    {
      event.stopImmediatePropagation();
      widget.sw_graphwidget.addClass('flipped');
    });

  }

  function get_shared_search_query(search_id)
  {
    var api_url = '<?php echo URL; ?>api/get_shared_search_query/' + search_id;
    document._session_data.shared_search_key = search_id;
    var shared_search = new $.Deferred();

    var shared_search_query = $.ajax({
      url: api_url
      ,type: 'GET'
      ,dataType: 'json'
    })
    ,chain = shared_search_query.then(function(data) {
      return(data);
    });
    chain.done(function(data) {
      delete(shared_search_query);
      shared_search.resolve(data);
    });

    return shared_search.promise();

  }

  function get_saved_search_query(search_id)
  {
    var api_url = '<?php echo URL; ?>api/load_saved_search/' + search_id;
    document._session_data.saved_search_key = search_id;
    var saved_search = new $.Deferred();

    var saved_search_query = $.ajax({
        url: api_url
        ,type: 'GET'
        ,dataType: 'json'
      })
      ,chain = saved_search_query.then(function(data)
      {
        return(data);
      });
    chain.done(function(data)
    {
      delete(saved_search_query);
      saved_search.resolve(data);
    });

    return saved_search.promise();

  }

  function load_search(query_data, edit_search)
  {
    console.log(query_data);
    if (typeof query_data === "string" && query_data.length > 1)
    {
      if (query_data.match(/Expired/))
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
            }
          }
        });
      }
      else if (query_data.match(/Not Allowed/))
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
            }
          }
        });
      }
      else if (query_data.match(/Not Found/))
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
            }
          }
        });
      }
    }
    else
    {
      if ($('.widget-container').length == 0)
      {
        var username = "<?php echo $_session_data['username'] ?>";
        var widget_id = "widget" + md5(username + new Date.now().getTime());
        $('div#adhoc-container').append('<div class="widget-container cols-1" id="' + widget_id + '" data-widget-type="graphwidget">');
        if (typeof query_data.options !== "undefined" && typeof query_data.options.sw_url !== "undefined")
        {
          delete(query_data.options.sw_url);
        }
        var new_widget = $('div#' + widget_id).graphwidget(query_data.options);
        var widget_object = $(new_widget).data('sw-graphwidget');
        adhoc_fixes('#' + widget_id);
      }
      else
      {
        var widget_object = $('.widget-container').data('sw-graphwidget');
        var url_path = '/adhoc/';
        if (typeof document._session_data.shared_search_key !== "undefined")
        {
          url_path = '/adhoc/shared/' + document._session_data.shared_search_key;
        }
        else if (typeof document._session_data.saved_search_key !== "undefined")
        {
          url_path = '/adhoc/saved/' + document._session_data.saved_search_key;
        }

        window.history.pushState("", "StatusWolf", url_path );
      }
      console.log('loading search id ' + document._session_data.saved_search_key + ' edit search is ' + edit_search);
      widget_object.populate_search_form(query_data, edit_search);
    }
  }

  function save_click_handler(event, confirmation)
  {
    widget = $('.widget-container').data('sw-graphwidget');
    if ($('input[name="save-search-title"]').val().length < 1)
    {
      $('input[name="save-search-title"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
      alert("You must specify a title for your saved search");
    }
    widget.query_data['user_id'] = "<?php echo $_session_data['user_id']; ?>";
    widget.query_data['title'] = $('input[name="save-search-title"]').val();
    widget.query_data['datasource'] = widget.options.datasource;
    $('#save-span').prop('checked')?widget.query_data['save_span'] = 1:widget.query_data['save_span'] = 0;
    $('#public').prop('checked')?widget.query_data['private'] = 0:widget.query_data['private'] = 1;
    var api_url = '<?php echo URL; ?>api/save_adhoc_search';
    if (typeof widget.query_data.search_id !== "undefined")
    {
      api_url += '/' + widget.query_data.search_id;
      if (confirmation > 0)
      {
        api_url += '/Confirm'
      }
    }
    $.ajax({
      url: api_url
      ,type: 'POST'
      ,data: widget.query_data
      ,dataType: 'json'
      ,success: function(data) {
        if (typeof data === "string")
        {
          data = eval('(' + data + ')');
        }
        if (data.query_result === "Error")
        {
          switch (data.query_info)
          {
            case "Title":
              widget.query_data.search_id = data.search_id;
              $('#confirmation-info').empty().append("<span>A search with that name already exists, overwrite?</span>");
              $('#confirm-save-button').click(function(event) {
                save_click_handler(event, 1);
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
        $.when(build_saved_search_menu()).then(
            function(data)
            {
              if (data[1] > data[0])
              {
                $('#load-search-menu-options').append('<li id="full-saved-search-list"><span>More...</span></li>')
              }
            }
        );
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

  function build_saved_search_menu()
  {
    var user_id = "<?php echo $_session_data['user_id']; ?>";
    var api_url = '<?php echo URL; ?>api/get_saved_searches';
    var menu_length = 15;
    var item_length = 0;

    api_query = {user_id: user_id};

    var saved_search_menu = new $.Deferred();
    var saved_search_menu_query = $.ajax({
        url: api_url
        ,type: 'POST'
        ,data: api_query
        ,dataType: 'json'
      })
      ,chain = saved_search_menu_query.then(function(data)
      {
        return(data);
      });
    chain.done(function(data)
    {
      var my_searches = data.user_searches;
      var public_searches = data.public_searches;
      $('#load-search-menu-options').empty();
      $('#load-search-menu-options').append('<li class="menu-section"><span>My Searches</span></li>');
      if (my_searches)
      {
        var max = (my_searches.length < menu_length ? my_searches.length : menu_length);
        for (i = 0; i < max; i++)
        {
          $('#load-search-menu-options').append('<li><span data-search-id="' + my_searches[i]['id'] + '">' + my_searches[i]['title'] + '</span></li>');
          menu_length--;
        }
        $.each(my_searches, function(i, search) {
          $('table#my-searches-list-table').append('<tr class="search-item"><td><span data-search-id="' + search.id + '">' + search.title + '</span></td></tr>');
        });
      }
      if (menu_length > 0 && public_searches)
      {
        $('#load-search-menu-options').append('<li class="menu-section"><span class="divider"></span></li>');
        $('#load-search-menu-options').append('<li class="menu-section"><span>Public Searches</span></li>');
        var max = (public_searches.length < menu_length ? public_searches.length : menu_length);
        for (i = 0; i < max ; i++)
        {
          if (public_searches[i]['user_id'] === document._session_data.user_id)
          {
            $('#load-search-menu-options').children('li.menu-section:last').after('<li><span data-search-id="' + public_searches[i]['id'] + '">' + public_searches[i]['title'] + ' (' + public_searches[i]['username'] + ')</span></li>');
          }
          else
          {
            $('#load-search-menu-options').append('<li><span data-search-id="' + public_searches[i]['id'] + '">' + public_searches[i]['title'] + ' (' + public_searches[i]['username'] + ')</span></li>');
          }
        }
      }
      $.each(public_searches, function(i, public) {
        if (public.username === document._session_data.username)
        {
          $('table#public-searches-list-table').children('tbody').children('tr.header-row').after('<tr class="search-item"><td><span data-search-id="' + public.id + '">' + public.title + '</span></td><td>' + public.username + '</td></tr>');
        }
        else
        {
          $('table#public-searches-list-table').append('<tr class="search-item"><td><span data-search-id="' + public.id + '">' + public.title + '</span></td><td>' + public.username + '</td></tr>');
        }
      });
      $('#load-search-menu-options').on('click', 'span', function()
      {
        if (typeof $(this).attr("data-search-id") !== "undefined")
        {
          $.when(get_saved_search_query($(this).attr('data-search-id'))).then(
              function(query_data)
              {
                load_search(query_data);
              }
          )
        }
      });
      $('#saved-search-list-popup').on('click', 'tr.search-item', function()
      {
        $.when(get_saved_search_query($(this).children('td').children('span').attr('data-search-id'))).then(
            function(query_data)
            {
              $.magnificPopup.close();
              load_search(query_data);
            }
        )
      });
      var my_search_count = (typeof my_searches !== "undefined" ? my_searches.length : 0);
      var public_search_count = (typeof public_searches !== "undefined" ? public_searches.length : 0);
      item_length = my_search_count + public_search_count;

      saved_search_menu.resolve([menu_length, item_length]);
    });

    return saved_search_menu.promise();

  }

</script>