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

?>

    <link href="<?php echo URL; ?>app/css/adhoc.css?v=1.0" rel="stylesheet">
    <div class="container">
      <div class="widget-container" id="ad-hoc-widget">
        <div class="widget">
          <div class="widget-front" id="ad-hoc-front">
            <div class="flexy widget-title">
              <div class="widget-title-head" style="-webkit-box-flex: 1;"><h4><a href="<?php echo URL; ?>adhoc/">Ad-Hoc Search</a></h4></div>
              <div class="glue1"></div>
              <div id="legend"></div>
            </div>
            <div class="widget-main">
              <div id="graphdiv" style="width: 99%; height: 99%;"></div>
            </div>
            <div class="flexy widget-footer">
              <div class="widget-footer-btn" id="ad-hoc-edit" onClick="$(this).parents('.widget').addClass('flipped')"><span class="iconic iconic-pen-alt2"></span> Edit search parameters</div>
              <div class="widget-footer-btn hidden" id="get-datasource-url"><span class="iconic iconic-target"> Get Raw Datasource URL</span></div>
              <div class="glue1"></div>
              <div class="widget-footer-btn hidden" id="share-search"><span class="iconic iconic-share"> Share Search</span></div>
              <div class="widget-footer-btn hidden" id="save_popup_button"><span class="iconic iconic-download"> Save Search</span></div>
              <div class="widget-footer-btn fullscreen-out" id="fullscreen"><span id="fullscreen-button" class="iconic iconic-fullscreen"> </span></div>
            </div>
          </div>
          <div class="widget-back" id="ad-hoc-back">
            <div class="flexy widget-title">
              <div class="widget-title-head"><h4><a href="<?php echo URL; ?>adhoc/">Ad-Hoc Search</a></h4></div>
              <div class="dropdown widget-title-dropdown" id="saved-searches-menu">
                <span class="widget-title-button" data-toggle="dropdown"><span class="ad-hoc-button-label" id="saved-searches">Saved Searches</span><span class="iconic iconic-play rotate-90"></span></span>
                <ul class="dropdown-menu" id="saved-searches-options" role="menu" aria-labelledby="dLabel"></ul>
              </div>
              <div class="glue1"></div>
              <div class="dropdown widget-title-dropdown" id="datasource-menu">
                <span class="widget-title-button" data-toggle="dropdown"><span class="ad-hoc-button-label" id="active-datasource" >OpenTSDB</span><span class="iconic iconic-play rotate-90"></span></span>
                <ul class="dropdown-menu menu-left" id="datasource-options" role="menu" aria-labelledby="dLabel">
                  <li><span>OpenTSDB</span></li>
                </ul>
              </div>
            </div>
            <div class="widget-main">
              <div id="ad-hoc-search-form"></div>
            </div>
            <div class="flexy widget-footer">
              <div class="widget-footer-btn" id="query-cancel" onClick="$(this).parents('.widget').removeClass('flipped')"><span class="iconic iconic-x-alt"> Cancel</span></div>
              <div class="glue1"></div>
              <div class="widget-footer-btn" id="go-button" onClick="go_click_handler(event)"><span class="iconic iconic-bolt"> Go</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div id="save_form_popup" class="popup mfp-hide">
      <div id="save_query_form">
        <form onsubmit="return false;">
          <h5>Title: </h5>
          <div class="popup_form_data">
            <input type="text" class="input" id="search_title" name="search_title" style="width: 250px;">
          </div>
          <h5>Options:</h5>
          <div class="popup_form_data">
            <div class="save_form_row" style="font-size: 1em;">
              <div class="push-button">
                <input type="checkbox" id="save_span" name="save_span"><label for="save_span"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> Save Search Times</span></label>
              </div>
              <div class="push-button">
                <input type="checkbox" id="public" name="public"><label for="public"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> Public Search</span></label>
              </div>
            </div>
          </div>
        </form>
      </div>
      <div id="save_query_info">
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
        <div class="widget-footer-btn" id="cancel_save_query_data_button" onClick="$.magnificPopup.close()"><span class="iconic iconic-x-alt"> Cancel</span></div>
        <div class="glue1"></div>
        <div class="widget-footer-btn" id="save_query_data_button" onClick="save_click_handler(event, query_data)"><span class="iconic iconic-download"> Save</span></div>
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
        <div class="uneditable-input" id="datasource-url" onClick="select_text('datasource-url')" style="width: 95%; vertical-align: middle; padding: 6px 10px 2px 10px;"></div>
      </div>
    </div>

    <div id="success-popup" class="popup mfp-hide"><h5>Success</h5><div class="popup-form-data">Your search has been saved.</div></div>

    <script type="text/javascript" src="<?php echo URL; ?>app/js/sw_lib.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>app/js/lib/dygraph-combined.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>app/js/lib/jquery.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>app/js/lib/bootstrap.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>app/js/lib/date.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>app/js/status_wolf_colors.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>app/js/lib/magnific-popup.js"></script>
    <link href="<?php echo URL; ?>app/css/popups.css?v=1.0" rel="stylesheet">

    <script type="text/javascript">

      build_saved_search_menu();
      var datasource = $('#active-datasource').text().toLowerCase() + '_search';
      show_datasource_form(datasource);

      $('#datasource-options > li').click(function() {
        $('#active-datasource').text($(this).text());
        datasource = $(this).text().toLowerCase() + '_search';
        show_datasource_form(datasource);
      });

      $('#fullscreen').click(function() {
        if ($('.widget-container').hasClass('maximize'))
        {
          $('.widget-container').removeClass('maximize');
          $('.navbar').removeClass('hidden');
          $('#fullscreen-button').removeClass('iconic-fullscreen-exit').addClass('iconic-fullscreen');
          var evt = document.createEvent('UIEvents');
          evt.initUIEvent('resize', true, false,window,0);
          window.dispatchEvent(evt);
        }
        else
        {
          $('.widget-container').addClass('maximize');
          $('.navbar').addClass('hidden');
          $('#fullscreen-button').removeClass('iconic-fullscreen-exit').addClass('iconic-fullscreen-exit');
          var evt = document.createEvent('UIEvents');
          evt.initUIEvent('resize', true, false,window,0);
          window.dispatchEvent(evt);
        }
      });

      function show_datasource_form(datasource)
      {
        var view_url = "<?php echo URL; ?>api/datasource_form/" + datasource;
        $.ajax({
          url: view_url
          ,method: 'GET'
          ,dataType: 'json'
          ,success: function(data) {
            $('#ad-hoc-search-form').html(data['form_source']);
          }
        });
      }

      $('#get-datasource-url').magnificPopup({
        items: {
          src: '#datasource-url-popup'
          ,type: 'inline'
        }
        ,preloader: false
        ,removalDelay: 300
        ,mainClass: 'popup-animate'
        ,callbacks: {
          beforeOpen: function() {
            var ds_url = query_url;
            if (query_data['datasource'] === "OpenTSDB")
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

      $('#share-search').magnificPopup({
        items: {
          src: '#share-search-popup'
          ,type: 'inline'
        }
        ,preloader: false
        ,removalDelay: 300
        ,mainClass: 'popup-animate'
        ,callbacks: {
          beforeOpen: function() {
            var api_url = "<?php echo URL; ?>api/get_shared_search";
            if (query_data['time_span'])
            {
              delete query_data['start_time'];
              delete query_data['end_time'];
            }
            $.ajax({
              url: api_url
              ,type: 'POST'
              ,data: query_data
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

      $('#save_popup_button').magnificPopup({
        items: {
          src: '#save_form_popup'
          ,type: 'inline'
        }
        ,preloader: false
        ,focus: '#search_title'
        ,removalDelay: 300
        ,mainClass: 'popup-animate'
        ,callbacks: {
          open: function() {
            setTimeout(function() {
              $('.container').addClass('blur');
              $('.navbar').addClass('blur');
            }, 150);
            if ($('#search_title').attr('value').length < 1)
            {
              <?php if (array_key_exists('user_searches', $_session_data['data'])) { $search_count = count($_session_data['data']['user_searches']); } else { $search_count = 0; } ?>
              $('#search_title').attr('value', '<?php echo $_session_data['username']; ?>' + '_' + datasource + '_' + '<?php echo $search_count + 1; ?>');
            }
          }
          ,close: function() {
            $('.container').removeClass('blur');
            $('.navbar').removeClass('blur');
          }
        }
      });

      function save_click_handler(event)
      {
        if ($('input#search_title').val().length < 1)
        {
          $('input#search_title').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
          alert("You must specify a title for your saved search");
        }
        query_data['user_id'] = "<?php echo $_session_data['user_id']; ?>";
        query_data['title'] = $('input#search_title').val();
        $('#save_span').prop('checked')?query_data['save_span'] = 1:query_data['save_span'] = 0;
        $('#public').prop('checked')?query_data['private'] = 0:query_data['private'] = 1;
        var api_url = '<?php echo URL; ?>api/save_adhoc_search';
        $.ajax({
          url: api_url
          ,type: 'POST'
          ,data: query_data
          ,dataType: 'json'
          ,success: function(data) {
            build_saved_search_menu();
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
        api_query = {user_id: user_id};
        $.ajax({
          url: api_url
          ,type: 'POST'
          ,data: api_query
          ,dataType: 'json'
          ,success: function(data) {
            my_searches = data['user_searches'];
            public_searches = data['public_searches'];
            $('#saved-searches-options').empty();
            $('#saved-searches-options').append('<li class="menu-section"><span>My Searches</span></li>');
            if (my_searches)
            {
              $.each(my_searches, function(i, search) {
                $('#saved-searches-options').append('<li><span><a href="<?php echo URL; ?>adhoc/saved/' + search['id'] + '">' + search['title'] + '</span></li>');
              });
            }
            if (public_searches)
            {
              $('#saved-searches-options').append('<li class="menu-section"><span class="divider"></span></li>');
              $('#saved-searches-options').append('<li class="menu-section"><span>Public Searches</span></li>');
              $.each(public_searches, function(i, public) {
                $('#saved-searches-options').append('<li><span><a href="<?php echo URL; ?>adhoc/saved/' + public['id'] + '">' + public['title'] + ' (' + public['username'] + ')</a></span></li>');
              });
            }
          }
        });
      }

      $(document).ready(function() {
        $('.widget-main').css('height', ($('.widget').innerHeight() - ($('.widget-title').height() + $('.widget-footer').height())));
      });

      $(window).resize(function() {
        $('.widget-main').css('height', ($('.widget').innerHeight() - ($('.widget-title').height() + $('.widget-footer').height())));
      });

    </script>