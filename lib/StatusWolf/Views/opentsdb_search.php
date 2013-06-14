<?php $sw_conf = SWConfig::read_values('statuswolf'); ?>

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
      <div class="ad-hoc-form-item menu-label" style="margin-right: 0;">
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
        <th>Interpolation</th>
        <th>Rate</th>
        <th>Right Axis</th>
      </tr>
      <tr>
        <td width="40%">
          <div class="metric-input-textbox" id="metric1-input-textbox">
            <input type="text" class="metric-autocomplete" name="metric1">
          </div>
        </td>
        <td width="10%">
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
        <td width="10%" style="padding-right: 0;">
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
        <td width="7.5%" style="padding-left: 0;">
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
          <div class="push-button binary pushed">
            <input type="checkbox" id="lerp-button1" name="lerp1" checked><label for="lerp-button1"><span class="iconic iconic-check-alt green"></span><span class="binary-label"> Yes</span></label>
          </div>
        </td>
        <td width="7.5%">
          <div class="push-button binary">
            <input type="checkbox" id="rate-button1" name="rate1"><label for="rate-button1"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> No</span></label>
          </div>
        </td>
        <td width="7.55%">
          <div class="push-button binary">
            <input type="checkbox" id="y2-button1" name="y2-1"><label for="y2-button1"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> No</span></label>
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
        <td style="padding-right: 0;">
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
        <td style="padding-left: 0;">
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
          <div class="push-button binary pushed">
            <input type="checkbox" id="lerp-button2" name="lerp2" checked><label for="lerp-button2"><span class="iconic iconic-check-alt green"></span><span class="binary-label"> Yes</span></label>
          </div>
        </td>
        <td>
          <div class="push-button binary">
            <input type="checkbox" id="rate-button2" name="rate2"><label for="rate-button2"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> No</span></label>
          </div>
        </td>
        <td>
          <div class="push-button binary">
            <input type="checkbox" id="y2-button2" name="y2-2"><label for="y2-button2"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> No</span></label>
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
        <td style="padding-right: 0;">
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
        <td style="padding-left: 0;">
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
          <div class="push-button binary pushed">
            <input type="checkbox" id="lerp-button3" name="lerp3" checked><label for="lerp-button3"><span class="iconic iconic-check-alt green"></span><span class="binary-label"> Yes</span></label>
          </div>
        </td>
        <td>
          <div class="push-button binary">
            <input type="checkbox" id="rate-button3" name="rate3"><label for="rate-button3"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> No</span></label>
          </div>
        </td>
        <td>
          <div class="push-button binary">
            <input type="checkbox" id="y2-button3" name="y2-3"><label for="y2-button3"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> No</span></label>
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
        <td style="padding-right: 0;">
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
        <td style="padding-left: 0;">
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
          <div class="push-button binary pushed">
            <input type="checkbox" id="lerp-button4" name="lerp4" checked><label for="lerp-button4"><span class="iconic iconic-check-alt green"></span><span class="binary-label"> Yes</span></label>
          </div>
        </td>
        <td>
          <div class="push-button binary">
            <input type="checkbox" id="rate-button4" name="rate4"><label for="rate-button4"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> No</span></label>
          </div>
        </td>
        <td>
          <div class="push-button binary">
            <input type="checkbox" id="y2-button4" name="y2-4"><label for="y2-button4"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> No</span></label>
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
        <td style="padding-right: 0;">
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
        <td style="padding-left: 0;">
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
          <div class="push-button binary pushed">
            <input type="checkbox" id="lerp-button5" name="lerp5" checked><label for="lerp-button5"><span class="iconic iconic-check-alt green"></span><span class="binary-label"> Yes</span></label>
          </div>
        </td>
        <td>
          <div class="push-button binary">
            <input type="checkbox" id="rate-button5" name="rate5"><label for="rate-button5"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> No</span></label>
          </div>
        </td>
        <td>
          <div class="push-button binary">
            <input type="checkbox" id="y2-button5" name="y2-5"><label for="y2-button5"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> No</span></label>
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
        <td style="padding-right: 0;">
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
        <td style="padding-left: 0;">
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
          <div class="push-button binary pushed">
            <input type="checkbox" id="lerp-button6" name="lerp6" checked><label for="lerp-button6"><span class="iconic iconic-check-alt green"></span><span class="binary-label"> Yes</span></label>
          </div>
        </td>
        <td>
          <div class="push-button binary">
            <input type="checkbox" id="rate-button6" name="rate6"><label for="rate-button6"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> No</span></label>
          </div>
        </td>
        <td>
          <div class="push-button binary">
            <input type="checkbox" id="y2-button6" name="y2-6"><label for="y2-button6"><span class="iconic iconic-x-alt red"></span><span class="binary-label"> No</span></label>
          </div>
        </td>
      </tr>
    </table>
  </div>
</div>

<script type="text/javascript">

  var sw_conf = '<?php echo json_encode($sw_conf); ?>';
  sw_conf = eval('(' + sw_conf + ')');
  console.log(sw_conf);

  $('head').append('<link href="/app/css/datetimepicker.css" rel="stylesheet">')
      .append('<link href="/app/css/toggle-buttons.css" rel="stylesheet">')
      .append('<link href="/app/css/push-button.css" rel="stylesheet">')
      .append('<link href="/app/css/table.css" rel="stylesheet">')
      .append('<link href="/app/css/loader.css" rel="stylesheet">');

  loadScript("<?php echo URL; ?>app/js/lib/bootstrap-datetimepicker.js", function() {
    $('#start-time').datetimepicker({collapse: false});
    $('#end-time').datetimepicker({collapse: false});
  });

  loadScript("<?php echo URL; ?>app/js/toggle-buttons.js", function(){});
  loadScript("<?php echo URL; ?>app/js/push-button.js", function(){});
  loadScript("<?php echo URL; ?>app/js/lib/jquery.autocomplete.js", function(){
    $(".metric-autocomplete").autocomplete({
      minChars: 2
      ,serviceUrl: '<?php echo URL; ?>api/tsdb_metric_list/'
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
      $('#time-span-options').html('<li><span data-ms="<?php echo (MINUTE * 10); ?>">10 minutes</span></li>')
          .append('<li><span data-ms="<?php echo (MINUTE * 30); ?>">30 Minutes</span></li>')
          .append('<li><span data-ms="<?php echo HOUR ?>">1 hour</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 2) ?>;">2 hours</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 4) ?>">4 hours</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 8) ?>">8 hours</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 12) ?>">12 hours</span></li>')
          .append('<li><span data-ms="<?php echo DAY ?>">1 day</span></li>')
          .append('<li><span data-ms="<?php echo WEEK ?>">1 week</span></li>');
      if ($('#time-span').attr('data-ms') > <?php echo WEEK; ?>)
      {
        $('#time-span').text('1 week').attr('data-ms', <?php echo WEEK; ?>);
      }
    }
    else if ($(this).attr('data-target') == 'ad-hoc-no')
    {
      $('.optional-metric').removeClass('section-off');
      $('.optional-metric').addClass('section-on');
      $('#time-span-options').html('<li><span data-ms="<?php echo (MINUTE * 10); ?>">10 minutes</span></li>')
          .append('<li><span data-ms="<?php echo (MINUTE * 30); ?>">30 Minutes</span></li>')
          .append('<li><span data-ms="<?php echo HOUR ?>">1 hour</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 2) ?>;">2 hours</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 4) ?>">4 hours</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 8) ?>">8 hours</span></li>')
          .append('<li><span data-ms="<?php echo (HOUR * 12) ?>">12 hours</span></li>')
          .append('<li><span data-ms="<?php echo DAY ?>">1 day</span></li>')
          .append('<li><span data-ms="<?php echo WEEK ?>">1 week</span></li>')
          .append('<li><span data-ms="<?php echo (WEEK * 2) ?>">2 weeks</span></li>')
          .append('<li><span data-ms="<?php echo MONTH ?>">1 month</span></li>');
    }
  });

  $('#time-span-options > li').click(function() {
    $('input[name="time-span"]').attr('value', ($(this).text()));
  });

  $('ul.dropdown-menu').on('click', 'li', function() {
    var button = $(this).parents('.ad-hoc-button').children('span');
    $(button).children('.ad-hoc-button-label').text($(this).text());
    if ($(button).children('#time-span'))
    {
      $(button).children('#time-span').attr('data-ms', $(this).children('span').attr('data-ms'));
    }
    $(button).children('div.ds-interval').attr('data-value', $(this).children('span').attr('data-value'));
  });

  $(document).ready(function() {
    $('.widget').addClass('flipped');
  });

  function go_click_handler(event)
  {

    var query_data = {};
    query_data['downsample_master_interval'] = 0;
    var input_error = false;
    var methods = {'sum': 'sum', 'average': 'avg', 'minimum value': 'min', 'maximum value': 'max', 'standard deviation': 'dev'};

    // Validate the input before we do anything else

    // Date range validation
    var start, end;
    if ($('input:radio[name=date-span]:checked').val() == 'date-search')
    {
      if ($('input:text[name=start-time]').val().length < 1)
      {
        $('input:text[name=start-time]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
        alert("You must specify a start time");
      }
      else if ($('input:text[name=end-time]').val().length < 1)
      {
        $('input:text[name=end-time]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
        alert("You must specify an end time");
      }

      start = Date.parse($('input:text[name=start-time]').val()).getTime();
      start = start / 1000;
      end = Date.parse($('input:text[name=end-time]').val()).getTime();
      end = end / 1000;
      alert('start: ' + start + ', end: ' + end);
      if (start >= end)
      {
        alert('Start time must come before end time');
        $('input:text[name=start-time]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
      }
    }
    else
    {
      end = new Date.now().getTime();
      end = parseInt(end / 1000);
      var span = parseInt($('#time-span').attr('data-ms'));
      start = (end - span);
      var jstart = new Date(start * 1000).toString('yyyy/MM/dd hh:mm:ss');
      var jend = new Date(end * 1000).toString('yyyy/MM/dd hh:mm:ss');
      $('input[name=start-time]').val(jstart).change();
      $('input[name=end-time]').val(jend).change();
    }
    query_data['start_time'] = start;
    query_data['end_time'] = end;

    // Check for auto-update flag
    if ('input:checkbox[name=auto-update]:checked')
    {
      query_data['auto_update'] = true;
    }
    else
    {
      query_data['auto_update'] = true;
    }

    // Check for history display options
    query_data['history-graph'] = $('input:radio[name=history-graph]:checked').val();
    if (query_data['history-graph'] == 'no')
    {
      query_data['metrics_count'] = 6;
    }
    else
    {
      query_data['metrics_count'] = 1;
    }
    query_data['metrics'] = [];

    // Validate metrics to search on
    if (query_data['metrics_count'] > 1)
    {
      for (i=1; i<=query_data['metrics_count']; i++)
      {
        var build_metric = {};
        var metric_bits = $('input:text[name=metric'+ i + ']').val().split(' ');
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
        var ds_type = $('#active-downsample-type' + i).text().toLowerCase();
        build_metric.agg_type = methods[agg_type];
        build_metric.ds_type = methods[ds_type];
        build_metric.ds_interval = $('#active-downsample-interval' + i).attr('data-value');
        if ((query_data['downsample_master_interval'] < 1) || (build_metric.ds_interval < query_data['downsample_master_interval']))
        {
          query_data['downsample_master_interval'] = build_metric.ds_interval;
        }

        if ($('#rate-button' + i).prop('checked'))
        {
          build_metric.rate = true;
        }

        if ($('#lerp-button' + i).prop('checked'))
        {
          build_metric.lerp = true;
        }
        if ($('#y2-button' + i).prop('checked'))
        {
          build_metric.y2 = true;
        }

        query_data['metrics'].push(build_metric);

      }
      if (query_data['metrics'].length < 1)
      {
        $('input:text[name=metric1]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
        alert("You must specify at least one metric to search for");
        input_error = true;
      }
      else
      {
        input_error = false;
      }
    }
    else
    {
      if ($('input:text[name=metric1]').val().length < 1)
      {
        $('input:text[name=metric1]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
        alert("You must specify a metric to search for");
        input_error = true;
      }
      else
      {
        input_error = false;
        var build_metric = {};
        var metric_bits = $('input:text[name=metric1]').val().split(' ');
        build_metric.name = metric_bits.shift();
        if (metric_bits.length > 0)
        {
          build_metric.tags = metric_bits;
        }
        var agg_type = $('#active-aggregation-type1').text().toLowerCase();
        var ds_type = $('#active-downsample-type1').text().toLowerCase();
        build_metric.agg_type = methods[agg_type];
        build_metric.ds_type = methods[ds_type];
        build_metric.ds_interval = $('#active-downsample-interval1').attr('data-value');
        query_data['downsample_master_interval'] = build_metric.ds_interval;

        if ($('#rate-button1').prop('checked'))
        {
          build_metric.rate = true;
        }

        if ($('#lerp-button1').prop('checked'))
        {
          build_metric.lerp = true;
        }

        if ($('y2-button1').prop('checked'))
        {
          build_metric.y2 = true;
        }
      }
      query_data['metrics'].push(build_metric);
    }

    if (input_error == false)
    {
      var graph_element = $('#graphdiv');
      graph_element.empty();
      graph_element.append('<div id="bowlG"><div id="bowl_ringG"><div class="ball_holderG"><div class="ballG"></div></div></div></div>');
      $('.widget').removeClass('flipped');
      graph_element.append('<div id="status-box" style="width: 100%; text-align: center;"><p id="status-message"></p></div>');
      $('#status-box').append('<p id=chuck style="margin: 0 25px"></p>');

      $.when(opentsdb_search(query_data)).then(
          function(data)
          {
            console.log(data);
            console.log('raw data recieved');
            $.when(process_graph_data(data, query_data)).then(
                function(data)
                {
                  console.log('parsed data received');
                  build_graph(data.graphdata, data.querydata);
                }
                ,function(status)
                {
                  $('#bowlG').html('<img src="<?php echo URL; ?>app/img/error.png" style="height: 60px; width: 30px;">');
                  $('#chuck').removeClass('section-on').addClass('section-off');
                  $('#status-message').text(status);
                }
                ,function(status)
                {
                  $('#chuck').removeClass('section-on').addClass('section-off');
                  $('#chuck').text(status);
                  $('#chuck').removeClass('section-off').addClass('section-on');
                }
            );
          }
          ,function(status)
          {
            $('#bowlG').html('<img src="<?php echo URL; ?>app/img/error.png" style="height: 60px; width: 30px;">');
            $('#chuck').addClass('section-off');
            $('#status-message').text(status);
          }
          ,function(status)
          {
            $('#chuck').removeClass('section-on').addClass('section-off');
            $('#chuck').text(status);
            $('#chuck').removeClass('section-off').addClass('section-on');
          }
      );
    }
  }

  function opentsdb_search(query_data)
  {
    var query_object = new $.Deferred();

    console.log('Fetching data from OpenTSDB');
    console.log(query_data);
    setInterval(function() {
      if (query_object.state() === "pending")
      {
        if(sw_conf.waiting.source == "chuck")
        {
          url = "<?php echo URL; ?>api/fortune/chuck";
        }
        else
        {
          if (typeof sw_conf.waiting.category != 'undefined')
          {
            url = "<?php echo URL; ?>api/fortune/quotes/" + sw_conf.waiting.category;
          }
          else
          {
            url = "<?php echo URL; ?>api/fortune/quotes/random";
          }
        }
        $.ajax({
          url: url
          ,type: 'GET'
          ,dataType: 'json'
          ,success: function(data) {
            query_object.notify(data);
          }
        })
      }
    }, 15000);

    if (query_data['history-graph'] == "anomaly")
    {
      $('#status-message').html('<p>Building Anomaly Model (this may take a few minutes)</p>');
      $.when(get_metric_data_anomaly(query_data).then(
          function(data) {
            query_object.resolve(data);
          })
      );
    }
    else if (query_data['history-graph'] == "wow")
    {
      $('<#status-message').html('<p>Fetching Week-Over-Week Data</p>');
      $.when(get_metric_data_wow(query_data).then(
          function(data) {
            query_object.resolve(data);
          })
      );
    }
    else
    {
      $('#status-message').html('<p>Fetching Metric Data</p>');
      $.when(get_metric_data(query_data).then(
          function(data) {
            query_object.resolve(data);
          })
      );
    }

    return query_object.promise();

  }

  function get_metric_data(query_data)
  {
    ajax_object = new $.Deferred();

    var ajax_request = $.ajax({
          url: "<?php echo URL; ?>adhoc/search/OpenTSDB"
          ,type: 'POST'
          ,data: query_data
          ,data_type: 'json'
          ,timeout: 120000
        })
        ,chain = ajax_request.then(function(data) {
          data_object = eval('(' + data + ')');
          return(data_object);
        });

    chain.done(function(data) {
          ajax_object.resolve(data);
    });

    return ajax_object.promise();

  }

  function get_metric_data_wow(query_data)
  {

    var ajax_object = new $.Deferred();

    $('#status-message').html('<p>Fetching Metric Data</p>');

    console.log('Fetching Week-Over-Week data from OpenTSDB');
    console.log(query_data);
    var metric_data = {};

    var current_request = $.ajax({
          url: "<?php echo URL; ?>adhoc/search/OpenTSDB"
          ,type: 'POST'
          ,data: query_data
          ,data_type: 'json'
          ,timeout: 120000
        })
        ,chained = current_request.then(function(data) {
          metric_data[0] = eval('(' + data + ')');
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
          console.log(past_query);
          return $.ajax({
            url: "<?php echo URL; ?>adhoc/search/OpenTSDB"
            ,type: 'POST'
            ,data: past_query
            ,data_type: 'json'
            ,timeout: 120000
          });
        });
    chained.done(function(data) {
      metric_data[1] = eval('(' + data + ')');
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
      ajax_object.resolve(metric_data);
    });

    return ajax_object.promise();
  }

  function get_metric_data_anomaly(query_data)
  {

    var ajax_object = new $.Deferred();

    console.log('Building anomaly model');

    var metric_data = {};
    var anomaly_request = $.ajax({
          url: "<?php echo URL; ?>api/opentsdb_anomaly_model"
          ,type: 'POST'
          ,data: query_data
          ,data_type: 'json'
        })
        ,chained = anomaly_request.then(function(data) {
          model_data_cache = eval('(' + data + ')');
          console.log(model_data_cache);
          $('#status-message').html('<p>Fetching Metric Data</p>');
          return $.ajax({
            url: "<?php echo URL; ?>adhoc/search/OpenTSDB"
            ,type: 'POST'
            ,data: query_data
            ,data_type: 'json'
            ,timeout: 120000
          });
        })
        ,chained_two = chained.then(function(data) {
          live_data = eval('(' + data + ')');
          metric_data['start'] = live_data['start'];
          metric_data['end'] = live_data['end'];
          metric_data['query_url'] = live_data['query_url'];
          metric_data['cache_key'] = live_data['cache_key'];
          metric_data['query_cache'] = live_data['query_cache'];
          delete live_data['start'];
          delete live_data['end'];
          delete live_data['query_url'];
          delete live_data['cache_key'];
          delete live_data['query_cache'];
          live_keys = Object.keys(live_data);
          live_key = live_keys[0];
          metric_data[live_key] = live_data[live_key];
          delete live_data;
          console.log('loaded ' + metric_data[live_key].length + ' metric data points');
          console.log(metric_data[live_key]);
          $('#status-message').html('<p>Building Projection</p>');
          console.log('Fetching projected data');
          return $.ajax({
            url: "<?php echo URL; ?>api/time_series_projection"
            ,type: 'POST'
            ,data: {model_cache: model_data_cache, query_cache: metric_data['query_cache'], key: live_key}
            ,data_type: 'json'
            ,timeout: 120000
          });
        });
    chained_two.done(function(data) {
      var projection_data = eval('(' + data + ')');
      console.log('projection data for ' + projection_data['projection'].length + ' metric data points');
      console.log(projection_data);
      metric_data[live_key] = projection_data['projection'];
      metric_data['anomalies'] = projection_data['anomalies'];
      console.log(metric_data);
      ajax_object.resolve(metric_data);
    });

    return ajax_object.promise();
  }

  function process_graph_data(data, query_data)
  {
    var parse_object = new $.Deferred();

    console.log('Parsing');
    var labels = ['Timestamp'];
    var buckets = {};
    var bucket_interval = parseInt(query_data['downsample_master_interval'] * 60);
    var start = parseInt(data.start);
    var end = parseInt(data.end);
    var query_url = data.query_url;
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

    console.log('Metric data recieved from ' + query_url);
    $('#status-message').html('<p>Parsing Metric Data</p>');

    for (var i = start; i <= end; i = i + bucket_interval)
    {
      buckets[i] = [];
    }

    for (var series in data) {
      if (data.hasOwnProperty(series))
      {
        if (query_data['history-graph'] == "anomaly")
        {
          labels.push(series + ' Projected');
          query_data.metrics[0]['history_graph'] = "anomaly";
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

      var graph_data = {};
      graph_data.labels = labels;
      graph_data.data = buckets;
      if (query_data['history-graph'] == "anomaly")
      {
        graph_data.anomalies = anomalies;
      }
    }

    var parsed_data = {graphdata: graph_data, querydata: query_data};
    console.log(parsed_data);

    parse_object.resolve(parsed_data);

    return parse_object.promise();

  }

  function build_graph(data, query_data)
  {

    options = query_data['metrics'][0];
    console.log('Building metric graph');
    console.log(query_data);
    console.log(options);
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
        if (options['history_graph'] == 'anomaly')
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
        dygraph_format.push(values);
      }
    }

    series_times = series_times.splice(-4, 4);
    console.log(series_times);
    console.log('last timestamp: ' + series_times[3]);

    var labels_map = {};
    $.each(graph_labels, function(index, label) {
      var label_bits = label.split(' ');
      labels_map[label_bits[0]] = label;
    });

    var x_space = $('#graphdiv').width() / 12;
    var y_space = $('#graphdiv').height() / 12;
    var g_width = $('#graphdiv').innerWidth() * .95;

    g = new Dygraph(
        document.getElementById('graphdiv')
        ,dygraph_format
        ,{
          labels: graph_labels
          ,labelsDiv: 'legend'
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
          ,colors: swcolors.Wheel[5]
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
    var right_axis = '';
//      console.log(options);
    if (options.y2 == true)
    {
      var axis_bits = {};
//      console.log('adding ' + option_values.name + ' to right axis');
      if (right_axis.length < 1)
      {
        axis_bits = {};
        axis_bits[labels_map[option_values.name]] = {};
        axis_bits[labels_map[option_values.name]]['axis'] = {};
        right_axis = labels_map[option_values.name];
        g.updateOptions(axis_bits);
      }
      else
      {
        axis_bits = {};
        axis_bits[labels_map[option_values.name]] = {};
        axis_bits[labels_map[option_values.name]]['axis'] = right_axis;
        g.updateOptions(axis_bits);
      }
    }
    if (options.history_graph == "anomaly")
    {
      anomalies = data.anomalies;
      g.updateOptions({
        customBars: true
        ,underlayCallback: function(canvas, area, g) {
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
//      console.log(g);
    $('.dygraph-xlabel').parent().css('top', '40%');

    if (query_data['auto_update'])
    {
      setInterval(function() {
        console.log('Updating graph data');
        var new_start = series_times[0];
        var new_end = new Date.now().getTime();
        new_end = parseInt(new_end / 1000);
        query_data.start_time = new_start;
        query_data.end_time = new_end;
        $.when(opentsdb_search(query_data)).then(
            function(data)
            {
              console.log(data);
              console.log('raw data recieved');
              $.when(process_graph_data(data, query_data)).then(
                  function(data)
                  {
                    console.log('graph data updates:');
                    console.log(data);
                    var dygraph_update = new Array();
                    graph_data = data.graphdata.data;
                    for (var timestamp in graph_data) {
                      if (graph_data.hasOwnProperty(timestamp))
                      {
                        series_times.push(timestamp);
                        jtime = new Date(parseInt(timestamp * 1000));
                        values = [jtime];
                        if (options['history_graph'] == 'anomaly')
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
                    console.log(dygraph_update);
                    dygraph_format.splice(0, (dygraph_update.length - 2));
                    dygraph_format.splice(-2, 2);
                    dygraph_format = dygraph_format.concat(dygraph_update);
                    console.log(dygraph_format);
                    g.updateOptions({'file': dygraph_format});
                    series_times = series_times.splice(-4, 4);
                    console.log(series_times);
                    console.log('last timestamp: ' + series_times[3]);
                  }
              );
            }
        );
      }, 300 * 1000);
    }

  }


</script>