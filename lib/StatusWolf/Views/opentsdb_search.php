<div class="ad-hoc-form-row" id="row1">
  <div id="auto-update">
    <div class="push-button">
      <input type="checkbox" id="auto-update-button" name="auto-update"><label for="auto-update-button"><span class="iconic iconic-x-alt red"></span><span> Auto Update</span></label>
    </div>
  </div>
  <div class="flexy" id="history-toggle">
    <div class="toggle-button-group">
      <div class="toggle-button toggle-on">
        <label><input type="radio" class='section-toggle' id="history-no" name="history-graph" checked="checked" data-target="ad-hoc-no" value="no"><span>No History</span></label>
      </div><div class="toggle-button">
        <label><input type="radio" class='section-toggle' id="history-anomaly" name="history-graph" data-target="ad-hoc-anomaly" value="anomaly"><span>Anomaly</span></label>
      </div><div class="toggle-button">
        <label><input type="radio" class='section-toggle' id="history-wow" name="history-graph" data-target="ad-hoc-wow" value="wow"><span>Week-Over-Week</span></label>
      </div>
    </div>
  </div>
  <div class="flexy" id="date-span-toggle">
    <div class="toggle-button-group">
      <div class="toggle-button toggle-on">
        <label><input type="radio" class='section-toggle' id="date-search" name="date-span" value="date-search" checked="checked" data-target="ad-hoc-dates"><span>Date Range</span></label>
      </div><div class="toggle-button">
        <label><input type="radio" class='section-toggle' id="span-search" name="date-span" value="span-search" data-target="ad-hoc-time-span"><span>Time Span</span></label>
      </div>
    </div>
  </div>
  <div class="glue4">
    <div class="flexy section section-on" id="ad-hoc-dates">
      <div class="ad-hoc-form-item menu-label" id="start-time">
        <h4>Start</h4>
        <input type="text" class="input input-append date-input" data-format="yyyy/MM/dd hh:mm:ss" name="start-time"><span class="input-addon-btn"><span class="iconic iconic-calendar-alt"></span></span>
      </div>
      <div class="ad-hoc-form-item menu-label" id="end-time">
        <h4>End</h4>
        <input type="text" class="input input-append date-input" data-format="yyyy/MM/dd hh:mm:ss" name="end-time"><span class="input-addon-btn"><span class="iconic iconic-calendar-alt"></span></span>
      </div>
    </div>
    <div class="flexy section section-off" id="ad-hoc-time-span">
      <div class="ad-hoc-form-item menu-label">
        <h4>Show Me The Past</h4>
      </div>
      <div class="dropdown ad-hoc-button">
<!--        <input type="text" class="input input-append" name="time-span" value="4 hours"><span data-toggle="dropdown" class="input-addon-btn"><span class="iconic iconic-play rotate-90"></span></span>-->
        <span class="flexy" data-toggle="dropdown">
          <div class="ad-hoc-button-label" id="time-span" data-ms="<?php echo (HOUR * 4) ?>">4 hours</div>
          <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
        </span>
        <ul class="dropdown-menu menu-left" id="time-span-options" role="menu" aria-labelledby="dLabel">
          <li><span data-ms="<?php echo (MINUTE * 10); ?>">10 minutes</span></li>
          <li><span data-ms="<?php echo (MINUTE * 30); ?>">30 Minutes</span></li>
          <li><span data-ms="<?php echo HOUR ?>">1 hour</span></li>
          <li><span data-ms="<?php echo (HOUR * 2) ?>;">2 hours</span></li>
          <li><span data-ms="<?php echo (HOUR * 4) ?>">4 hours</span></li>
          <li><span data-ms="<?php echo (HOUR * 8) ?>">8 hours</span></li>
          <li><span data-ms="<?php echo (HOUR * 12) ?>">12 hours</span></li>
          <li><span data-ms="<?php echo DAY ?>">1 day</span></li>
          <li><span data-ms="<?php echo WEEK ?>">1 week</span></li>
          <li><span data-ms="<?php echo (WEEK * 2) ?>">2 weeks</span></li>
          <li><span data-ms="<?php echo MONTH ?>">1 month</span></li>
        </ul>
      </div>
    </div>
  </div>
</div>
<div class="ad-hoc-form-row" id="row2">
  <div class="ad-hoc-form-item menu-label" id="metric-search-table">
    <table class="table">
      <tr>
        <th>Metric</th>
        <th>Aggregation</th>
        <th colspan="2">Downsampling</th>
        <th>Rate</th>
        <th>Interpolation</th>
      </tr>
      <tr>
        <td width="40%">
          <div class="metric-input-textbox" id="metric1-input-textbox">
            <input type="text" class="metric-autocomplete" name="metric1">
          </div>
        </td>
        <td width="15%">
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
        <td width="15%">
          <div class="dropdown ad-hoc-button">
            <span class="flexy" data-toggle="dropdown">
              <div class="ad-hoc-button-label" id="active-downsample-type1">Sum</div>
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
        <td width="8%">
          <div class="dropdown ad-hoc-button" id="downsample-interval-button1" style="min-width: 25px;">
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
          <div class="push-button">
            <input type="checkbox" id="rate-button1" name="rate1"><label for="rate-button1"><span class="iconic iconic-x-alt red"></span><span> Rate</span></label>
          </div>
        </td>
        <td width="8%">
          <div class="push-button pushed">
            <input type="checkbox" id="lerp-button1" name="lerp1" checked><label for="lerp-button1"><span class="iconic iconic-check-alt green"></span><span> LERP</span></label>
          </div>
        </td>
      </tr>
      <tr class="optional-metric" id="metric-row2">
        <td>
          <div class="metric-input-textbox" id="metric2-input-textbox">
            <input type="text" class="metric-autocomplete" name="metric2">
          </div>
        </td>
        <td>
          <div class="dropdown ad-hoc-button">
           <span class="flexy" data-toggle="dropdown">
             <div class="ad-hoc-button-label" id="active-aggregation-type2">Sum</div>
             <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
            </span>
            <ul class="dropdown-menu" id="aggregation-type-options2 role="menu" aria-labelledby="dLabel">
              <li><span>Sum</span></li>
              <li><span>Average</span></li>
              <li><span>Minimum Value</span></li>
              <li><span>Maximum Value</span></li>
              <li><span>Standard Deviation</span></li>
            </ul>
          </div>
        </td>
        <td>
          <div class="dropdown ad-hoc-button">
            <span class="flexy" data-toggle="dropdown">
              <div class="ad-hoc-button-label" id="active-downsample-type2">Sum</div>
              <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
            </span>
            <ul class="dropdown-menu" id="downsample-type-options2" role="menu" aria-labelledby="dLabel">
              <li><span>Sum</span></li>
              <li><span>Average</span></li>
              <li><span>Minimum Value</span></li>
              <li><span>Maximum Value</span></li>
            </ul>
          </div>
        </td>
        <td>
          <div class="dropdown ad-hoc-button" id="downsample-interval-button2">
            <span class="flexy" data-toggle="dropdown">
              <div class="ad-hoc-button-label ds-interval" id="active-downsample-interval2" data-value="1">1 minute</div>
              <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
            </span>
            <ul class="dropdown-menu ds-values" id="downsample-interval-options2" role="menu" aria-labelledby="dLabel">
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
        <td>
          <div class="push-button">
            <input type="checkbox" id="rate-button2" name="rate2"><label for="rate-button2"><span class="iconic iconic-x-alt red"></span><span> Rate</span></label>
          </div>
        </td>
        <td>
          <div class="push-button pushed">
            <input type="checkbox" id="lerp-button2" name="lerp2" checked><label for="lerp-button2"><span class="iconic iconic-check-alt green"></span><span> LERP</span></label>
          </div>
        </td>
      </tr>
      <tr class="optional-metric" id="metric-row3">
        <td>
          <div class="metric-input-textbox" id="metric3-input-textbox">
            <input type="text" class="metric-autocomplete" name="metric3">
          </div>
        </td>
        <td>
          <div class="dropdown ad-hoc-button">
           <span class="flexy" data-toggle="dropdown">
             <div class="ad-hoc-button-label" id="active-aggregation-type3">Sum</div>
             <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
            </span>
            <ul class="dropdown-menu" id="aggregation-type-options3 role="menu" aria-labelledby="dLabel">
            <li><span>Sum</span></li>
            <li><span>Average</span></li>
            <li><span>Minimum Value</span></li>
            <li><span>Maximum Value</span></li>
            <li><span>Standard Deviation</span></li>
            </ul>
          </div>
        </td>
        <td>
          <div class="dropdown ad-hoc-button">
            <span class="flexy" data-toggle="dropdown">
              <div class="ad-hoc-button-label" id="active-downsample-type3">Sum</div>
              <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
            </span>
            <ul class="dropdown-menu" id="downsample-type-options3" role="menu" aria-labelledby="dLabel">
              <li><span>Sum</span></li>
              <li><span>Average</span></li>
              <li><span>Minimum Value</span></li>
              <li><span>Maximum Value</span></li>
            </ul>
          </div>
        </td>
        <td>
          <div class="dropdown ad-hoc-button" id="downsample-interval-button3">
            <span class="flexy" data-toggle="dropdown">
              <div class="ad-hoc-button-label ds-interval" id="active-downsample-interval3" data-value="1">1 minute</div>
              <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
            </span>
            <ul class="dropdown-menu ds-values" id="downsample-interval-options3" role="menu" aria-labelledby="dLabel">
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
        <td>
          <div class="push-button">
            <input type="checkbox" id="rate-button3" name="rate3"><label for="rate-button3"><span class="iconic iconic-x-alt red"></span><span> Rate</span></label>
          </div>
        </td>
        <td>
          <div class="push-button pushed">
            <input type="checkbox" id="lerp-button3" name="lerp3" checked><label for="lerp-button3"><span class="iconic iconic-check-alt green"></span><span> LERP</span></label>
          </div>
        </td>
      </tr>
      <tr class="optional-metric" id="metric-row4">
        <td>
          <div class="metric-input-textbox" id="metric4-input-textbox">
            <input type="text" class="metric-autocomplete" name="metric4">
          </div>
        </td>
        <td>
          <div class="dropdown ad-hoc-button">
           <span class="flexy" data-toggle="dropdown">
             <div class="ad-hoc-button-label" id="active-aggregation-type4">Sum</div>
             <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
            </span>
            <ul class="dropdown-menu" id="aggregation-type-options4 role="menu" aria-labelledby="dLabel">
            <li><span>Sum</span></li>
            <li><span>Average</span></li>
            <li><span>Minimum Value</span></li>
            <li><span>Maximum Value</span></li>
            <li><span>Standard Deviation</span></li>
            </ul>
          </div>
        </td>
        <td>
          <div class="dropdown ad-hoc-button">
            <span class="flexy" data-toggle="dropdown">
              <div class="ad-hoc-button-label" id="active-downsample-type4">Sum</div>
              <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
            </span>
            <ul class="dropdown-menu" id="downsample-type-options4" role="menu" aria-labelledby="dLabel">
              <li><span>Sum</span></li>
              <li><span>Average</span></li>
              <li><span>Minimum Value</span></li>
              <li><span>Maximum Value</span></li>
            </ul>
          </div>
        </td>
        <td>
          <div class="dropdown ad-hoc-button" id="downsample-interval-button4">
            <span class="flexy" data-toggle="dropdown">
              <div class="ad-hoc-button-label ds-interval" id="active-downsample-interval4" data-value="1">1 minute</div>
              <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
            </span>
            <ul class="dropdown-menu ds-values" id="downsample-interval-options4" role="menu" aria-labelledby="dLabel">
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
        <td>
          <div class="push-button">
            <input type="checkbox" id="rate-button4" name="rate4"><label for="rate-button4"><span class="iconic iconic-x-alt red"></span><span> Rate</span></label>
          </div>
        </td>
        <td>
          <div class="push-button pushed">
            <input type="checkbox" id="lerp-button4" name="lerp4" checked><label for="lerp-buttong"><span class="iconic iconic-check-alt green"></span><span> LERP</span></label>
          </div>
        </td>
      </tr>
      <tr class="optional-metric" id="metric-row5">
        <td>
          <div class="metric-input-textbox" id="metric5-input-textbox">
            <input type="text" class="metric-autocomplete" name="metric5">
          </div>
        </td>
        <td>
          <div class="dropdown ad-hoc-button">
           <span class="flexy" data-toggle="dropdown">
             <div class="ad-hoc-button-label" id="active-aggregation-type5">Sum</div>
             <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
            </span>
            <ul class="dropdown-menu" id="aggregation-type-options5 role="menu" aria-labelledby="dLabel">
            <li><span>Sum</span></li>
            <li><span>Average</span></li>
            <li><span>Minimum Value</span></li>
            <li><span>Maximum Value</span></li>
            <li><span>Standard Deviation</span></li>
            </ul>
          </div>
        </td>
        <td>
          <div class="dropdown ad-hoc-button">
            <span class="flexy" data-toggle="dropdown">
              <div class="ad-hoc-button-label" id="active-downsample-type5">Sum</div>
              <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
            </span>
            <ul class="dropdown-menu" id="downsample-type-options5" role="menu" aria-labelledby="dLabel">
              <li><span>Sum</span></li>
              <li><span>Average</span></li>
              <li><span>Minimum Value</span></li>
              <li><span>Maximum Value</span></li>
            </ul>
          </div>
        </td>
        <td>
          <div class="dropdown ad-hoc-button" id="downsample-interval-button5">
            <span class="flexy" data-toggle="dropdown">
              <div class="ad-hoc-button-label ds-interval" id="active-downsample-interval5" data-value="1">1 minute</div>
              <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
            </span>
            <ul class="dropdown-menu ds-values" id="downsample-interval-options5" role="menu" aria-labelledby="dLabel">
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
        <td>
          <div class="push-button">
            <input type="checkbox" id="rate-button5" name="rate5"><label for="rate-button5"><span class="iconic iconic-x-alt red"></span><span> Rate</span></label>
          </div>
        </td>
        <td>
          <div class="push-button pushed">
            <input type="checkbox" id="lerp-button5" name="lerp5" checked><label for="lerp-button5"><span class="iconic iconic-check-alt green"></span><span> LERP</span></label>
          </div>
        </td>
      </tr>
      <tr class="optional-metric" id="metric-row6">
        <td>
          <div class="metric-input-textbox" id="metric6-input-textbox">
            <input type="text" class="metric-autocomplete" name="metric6">
          </div>
        </td>
        <td>
          <div class="dropdown ad-hoc-button">
           <span class="flexy" data-toggle="dropdown">
             <div class="ad-hoc-button-label" id="active-aggregation-type6">Sum</div>
             <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
            </span>
            <ul class="dropdown-menu" id="aggregation-type-options6 role="menu" aria-labelledby="dLabel">
            <li><span>Sum</span></li>
            <li><span>Average</span></li>
            <li><span>Minimum Value</span></li>
            <li><span>Maximum Value</span></li>
            <li><span>Standard Deviation</span></li>
            </ul>
          </div>
        </td>
        <td>
          <div class="dropdown ad-hoc-button">
            <span class="flexy" data-toggle="dropdown">
              <div class="ad-hoc-button-label" id="active-downsample-type6">Sum</div>
              <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
            </span>
            <ul class="dropdown-menu" id="downsample-type-options6" role="menu" aria-labelledby="dLabel">
              <li><span>Sum</span></li>
              <li><span>Average</span></li>
              <li><span>Minimum Value</span></li>
              <li><span>Maximum Value</span></li>
            </ul>
          </div>
        </td>
        <td>
          <div class="dropdown ad-hoc-button" id="downsample-interval-button6">
            <span class="flexy" data-toggle="dropdown">
              <div class="ad-hoc-button-label ds-interval" id="active-downsample-interval6" data-value="1">1 minute</div>
              <span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span>
            </span>
            <ul class="dropdown-menu ds-values" id="downsample-interval-options6" role="menu" aria-labelledby="dLabel">
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
        <td>
          <div class="push-button">
            <input type="checkbox" id="rate-button6" name="rate6"><label for="rate-button6"><span class="iconic iconic-x-alt red"></span><span> Rate</span></label>
          </div>
        </td>
        <td>
          <div class="push-button pushed">
            <input type="checkbox" id="lerp-button6" name="lerp6" checked><label for="lerp-button6"><span class="iconic iconic-check-alt green"></span><span> LERP</span></label>
          </div>
        </td>
      </tr>
    </table>
  </div>
</div>

<script type="text/javascript">

  $('head').append('<link href="/app/css/datetimepicker.css" rel="stylesheet">')
      .append('<link href="/app/css/toggle-buttons.css" rel="stylesheet">')
      .append('<link href="/app/css/push-button.css" rel="stylesheet">')
      .append('<link href="/app/css/table.css" rel="stylesheet">')
      .append('<link href="/app/css/loader.css" rel="stylesheet">');

  loadScript("<?php echo URL; ?>/app/js/lib/bootstrap-datetimepicker.js", function() {
    $('#start-time').datetimepicker({collapse: false});
    $('#end-time').datetimepicker({collapse: false});
  });

  loadScript("<?php echo URL; ?>/app/js/toggle-buttons.js", function(){});
  loadScript("<?php echo URL; ?>/app/js/push-button.js", function(){});
  loadScript("<?php echo URL; ?>/app/js/lib/jquery.autocomplete.js", function(){
    $(".metric-autocomplete").autocomplete({
      minChars: 2
      ,serviceUrl: '/api/tsdb_metric_list/'
      ,containerClass: 'autocomplete-suggestions dropdown-menu'
      ,zIndex: ''
      ,maxHeight: ''
    });
  });

  $('.section-toggle').click(function() {
    $('#' + $(this).attr('data-target')).removeClass('section-off').addClass('section-on').siblings('.section').addClass('section-off').removeClass('section-on');
    if ($(this).attr('data-target') == 'ad-hoc-anomaly' || $(this).attr('data-target') == 'ad-hoc-wow')
    {
      $('.optional-metric').removeClass('section-on');
      $('.optional-metric').addClass('section-off');
    }
    else if ($(this).attr('data-target') == 'ad-hoc-no')
    {
      $('.optional-metric').removeClass('section-off');
      $('.optional-metric').addClass('section-on');
    }
  });

  $('#time-span-options > li').click(function() {
    $('input[name="time-span"]').attr('value', ($(this).text()));
  });

  $('li').click(function() {
    var button = $(this).parents('.ad-hoc-button').children('span');
    $(button).children('.ad-hoc-button-label').text($(this).text());
  });

</script>
