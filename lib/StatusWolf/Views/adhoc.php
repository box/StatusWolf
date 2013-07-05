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
  $app_config = SWConfig::read_values('statuswolf');

  // Load any available saved and public searches
  $sw_db = new mysqli($app_config['session_handler']['db_host'], $app_config['session_handler']['db_user'], $app_config['session_handler']['db_password'], $app_config['session_handler']['database']);
  if (mysqli_connect_error())
  {
    throw new SWException('Saved searches database connect error: ' . mysqli_connect_errno() . ' ' . mysqli_connect_error());
}
  $saved_searches_query = sprintf("SELECT * FROM saved_searches where user_id='%s'", $_session_data['user_id']);
  $user_searches_result = $sw_db->query($saved_searches_query);
  if ($user_searches_result->num_rows && $user_searches->num_rows > 0)
  {
    $user_searches = $user_searches_result->fetch_assoc();
    $_session_data['data']['user_searches'] = $user_searches;
  }
  $shared_searches_query = sprintf("SELECT * FROM saved_searches where private=0");
  $shared_searches_result = $sw_db->query($shared_searches_query);
  if ($shared_searches_result->num_rows && $shared_searches_result->num_rows > 0)
  {
    $shared_searches = $shared_searches_result->fetch_assoc();
    $_session_data['data']['shared_searches'] = $shared_searches;
}

?>

    <link href="<?php echo URL; ?>app/css/adhoc.css?v=1.0" rel="stylesheet">
    <div class="container">
      <div class="widget-container" id="ad-hoc-widget">
        <div class="widget">
          <div class="widget-front" id="ad-hoc-front">
            <div class="widget-title">
              <div class="widget-title-head"><h4><a href="<?php echo URL; ?>adhoc/">Ad-Hoc Search</a></h4></div>
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
            </div>
          </div>
          <div class="widget-back" id="ad-hoc-back">
            <div class="widget-title">
              <div class="widget-title-head"><h4><a href="<?php echo URL; ?>adhoc/">Ad-Hoc Search</a></h4></div>
              <div class="dropdown" id="datasource-menu">
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
        <form target="#">
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
                <input type="checkbox" id="private" name="private"><label for="private"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> Public Search</span></label>
              </div>
            </div>
          </div>
        </form>
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

    <script type="text/javascript" src="<?php echo URL; ?>app/js/sw_lib.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>app/js/lib/dygraph-combined.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>app/js/lib/jquery.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>app/js/lib/bootstrap.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>app/js/lib/date.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>app/js/status_wolf_colors.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>app/js/lib/magnific-popup.js"></script>
    <link href="<?php echo URL; ?>app/css/popups.css?v=1.0" rel="stylesheet">

    <script type="text/javascript">

      var datasource = $('#active-datasource').text().toLowerCase() + '_search';
      show_datasource_form(datasource);

      $('#datasource-options > li').click(function() {
        $('#active-datasource').text($(this).text());
        datasource = $(this).text().toLowerCase() + '_search';
        show_datasource_form(datasource);
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
              <?php if (array_key_exists('saved_searches', $_session_data['data'])) { $search_count = $_session_data['data']['saved_searches']; } else { $search_count = 0; } ?>
              $('#search_title').attr('value', '<?php echo $_session_data['username']; ?>' + '_' + datasource + '_' + '<?php echo $search_count + 1; ?>');
            }
          }
          ,close: function() {
            $('.container').removeClass('blur');
            $('.navbar').removeClass('blur');
          }
        }
      });

      function save_click_handler(event, query_data)
      {
        console.log(query_data);
      }

      $(document).ready(function() {
        $('.widget-main').css('height', ($('.widget').innerHeight() - ($('.widget-title').height() + $('.widget-footer').height())));
      });

      $(window).resize(function() {
        $('.widget-main').css('height', ($('.widget').innerHeight() - ($('.widget-title').height() + $('.widget-footer').height())));
      })

    </script>