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

?>

    <link href="<?php echo URL; ?>app/css/adhoc.css?v=1.0" rel="stylesheet">
    <div class="container">
      <div class="widget-container" id="ad-hoc-widget">
        <div class="widget">
          <div class="widget-front" id="ad-hoc-front">
            <div class="widget-title">
              <div class="widget-title-head"><h4>Ad-Hoc Search</h4></div>
              <div id="legend"></div>
            </div>
            <div class="widget-main">
              <div id="graphdiv" style="width: 99%; height: 99%;"></div>
            </div>
            <div class="flexy widget-footer">
              <div class="widget-footer-btn" id="ad-hoc-edit" onClick="$(this).parents('.widget').addClass('flipped')"><span class="iconic iconic-pen-alt2"></span> Edit search parameters</div>
              <div class="glue1"></div>
              <div class="widget-footer-btn" id="save_popup_button"><span class="iconic iconic-download"> Save Search</span></div>
            </div>
          </div>
          <div class="widget-back" id="ad-hoc-back">
            <div class="widget-title">
              <div class="widget-title-head"><h4>Ad-Hoc Search</h4></div>
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
  <!--        <div id="query_data_head"><h5>Search Parameters:</h5></div>-->
  <!--        <div id="query_data"></div>-->
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

      $(document).ready(function() {
        $('.widget-main').css('height', ($('.widget').innerHeight() - ($('.widget-title').height() + $('.widget-footer').height())));
      });

      function save_click_handler(event, query_data)
      {
        console.log(query_data);
      }

    </script>