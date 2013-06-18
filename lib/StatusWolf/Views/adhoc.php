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
            <div class="widget-footer">
              <div class="widget-footer-btn" id="ad-hoc-edit" onClick="$(this).parents('.widget').addClass('flipped')"><span class="iconic iconic-pen-alt2"></span> Edit search parameters</div>
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

    <script type="text/javascript" src="<?php echo URL; ?>app/js/lib/dygraph-combined.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>app/js/lib/jquery.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>app/js/lib/bootstrap.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>app/js/lib/date.js"></script>
    <script type="text/javascript" src="<?php echo URL; ?>app/js/status_wolf_colors.js"></script>

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

      $(document).ready(function() {
        $('.widget-main').css('height', ($('.widget').innerHeight() - ($('.widget-title').height() + $('.widget-footer').height())));
      });

    </script>