/**
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 1 August 2013
 *
 */

function build_search_form(widget)
{

	$('head').append('<link rel="stylesheet" href="' + widget.options.sw_url + 'app/css/datetimepicker.css">' +
		'<link rel="stylesheet" href="' + widget.options.sw_url + 'app/css/toggle-buttons.css">' +
		'<link rel="stylesheet" href="' + widget.options.sw_url + 'app/css/push-button.css">' +
		'<link rel="stylesheet" href="' + widget.options.sw_url + 'app/css/table.css">' +
		'<link rel="stylesheet" href="' + widget.options.sw_url + 'app/css/loader.css">' +
		'<link rel="stylesheet" href="' + widget.options.sw_url + 'app/css/tooltip.css">' +
		'<link rel="stylesheet" href="' + widget.options.sw_url + 'app/Widgets/GraphWidget/css/custom.graph_widget.css">' +
		'<script type="text/javascript" src="' + widget.options.sw_url + 'app/js/lib/dygraph-combined.js"></script>' +
		'<script type="text/javascript" src="' + widget.options.sw_url + 'app/js/status_wolf_colors.js"></script>' +
		'<script type="text/javascript" src="' + widget.options.sw_url + 'app/js/push-button.js"></script>');

	var widget_num = widget.uuid
		,widget_element = widget.element;

	widget.metric_count = 0;

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
	var form_div = widget.sw_graphwidget_searchform;
  form_div.append('<table class="general-options-table" id="graph-search-general' + widget_num + '">');
	var form_table = form_div.children('table#graph-search-general' + widget_num);
  form_table.append('<tr><td><div class="toggle-button-group">' +
    '<div class="toggle-button toggle-on"><label>' +
    '<input type="radio" class="section-toggle date-search" name="date-span" value="date-search" checked="checked" data-target="graph-widget-dates' + widget_num + '">' +
    '<span>Date Range</span></label>' +
    '</div><div class="toggle-button"><label>' +
    '<input type="radio" class="section-toggle span-search" name="date-span" value="span-search" data-target="graph-widget-time-span' + widget_num + '">' +
    '<span>Time Span</span></label></div></div></td>' +
    '<td><div class="section section-on graph-widget-dates" id="graph-widget-dates' + widget_num + '">' +
    '<div class="graph-widget-form-item menu-label" id="start-time' + widget_num + '">' +
    '<h4>Start</h4>' +
    '<input type="text" class="input input-append date-input" data-format="yyyy/MM/dd hh:mm:ss" name="start-time">' +
    '<span class="input-addon-btn"><span class="iconic iconic-calendar-alt"></span></span></div>' +
    '<div class="graph-widget-form-item menu-label" id="end-time' + widget_num + '">' +
    '<h4>End</h4>' +
    '<input type="text" class="input input-append date-input" data-format="yyyy/MM/dd hh:mm:ss" name="end-time">' +
    '<span class="input-addon-btn"><span class="iconic iconic-calendar-alt"></span></span></div></div>' +
    '<div class="section section-off graph-widget-time-span" id="graph-widget-time-span' + widget_num + '">' +
    '<div class="graph-widget-form-item menu-label" style="margin-right: 0;">' +
    '<h4>Show Me The Past</h4>' +
    '<div class="dropdown graph-widget-button" style="display: inline-block;"><span data-toggle="dropdown">' +
    '<div class="graph-widget-button-label" id="time-span' + widget_num + '" data-ms="14400">4 Hours</div>' +
    '<span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span></span>' +
    '<ul class="dropdown-menu menu-left" id="time-span-options' + widget_num + '" role="menu" aria=labelledby="dLabel">' +
    long_span_menu + '</ul></div></div></div></td></tr>');
  form_table.append('<tr><td><div class="auto-update"><div class="push-button">' +
    '<input type="checkbox" name="auto-update"><label>' +
    '<span class="iconic iconic-x-alt red"></span><span> Auto Update</span></label></div></div></td>' +
    '<td><div class="toggle-button-group"><div class="toggle-button toggle-on"><label>' +
    '<input type="radio" class="section-toggle history-no" name="history-graph" checked="checked" data-target="history-no' + widget_num + '" value="no">' +
    '<span>No History</span></label>' +
    '</div><div class="toggle-button"><label>' +
    '<input type="radio" class="section-toggle history-anomaly" name="history-graph" data-target="history-anomaly' + widget_num + '" value="anomaly">' +
    '<span>Anomaly</span></label>' +
    '</div><div class="toggle-button"><label>' +
    '<input type="radio" class="section-toggle history-wow" name="history-graph" data-target="history-wow' + widget_num + '" value="wow">' +
    '<span>Week-Over-Week</span></label></div></div></td></tr>');

	form_div.append('<div class="graph-widget-form-row row3">');
	var form_row_3 = form_div.children('div.row3');
	form_row_3.append('<div class="metric-input-tabs tabbable tab-below" style="width: 99%;">' +
		'<div class="tab-content" id="tab-content' + widget_num + '"></div><ul class="nav nav-tabs" id="tab-list' + widget_num + '"></ul></div>');

  // Load the handler for the toggle-buttons
  loadScript(widget.options.sw_url + 'app/js/toggle-buttons.js', function(){});
  // Load the handler for metric name autocompletion and init the form objects
  loadScript(widget.options.sw_url + 'app/js/lib/jquery.autocomplete.js', function(){
    $(widget.sw_graphwidget_searchform.children('.row3').find('.metric-autocomplete')).autocomplete({
      minChars: 2
      ,serviceUrl: widget.options.sw_url + 'api/tsdb_metric_list/'
      ,containerClass: 'autocomplete-suggestions dropdown-menu'
      ,zIndex: ''
      ,maxHeight: ''
    });
  });

	var add_metric_button_id = 'add-metric-button' + widget_num + '-' + widget.metric_count;
	widget.sw_graphwidget_querycancelbutton.after('<div id="' + add_metric_button_id + '">');
	$('#' + add_metric_button_id).addClass('widget-footer-button left-button')
		.click(function() {
			widget.metric_count = add_tab(widget.metric_count, widget_num);
			$('input[name="metric' + widget_num + '-' + widget.metric_count + '"]').autocomplete({
				minChars: 2
				,serviceUrl: widget.options.sw_url + 'api/tsdb_metric_list/'
				,containerClass: 'autocomplete-suggestions dropdown-menu'
				,zIndex: ''
				,maxHeight: ''
			});
			if (widget.metric_count == 6)
			{
				$(this).addClass('hidden');
			}
		})
		.append('<span class="iconic iconic-plus-alt"><span class="font-reset"> Add Metric</span></span>');

	widget.sw_graphwidget_fronttitle.children('.graph-widget-legend').attr('id', 'legend' + widget_num);
	$(widget_element).children('.widget').children('.widget-front').children('.widget-main')
		.append('<div id="graphdiv' + widget_num + '" style="height: 99%; width: 99%;">');

	var auto_update = $(widget_element).find('div.auto-update').children('div.push-button');
	$(auto_update).children('input').attr('id', 'auto-update-button' + widget_num);
	$(auto_update).children('label').attr('for', 'auto-update-button' + widget_num);

	// Add the handler for the date/time picker and init the form objects
	var start_time = $('#start-time' + widget_num);
	var end_time = $('#end-time' + widget_num);
	loadScript(widget.options.sw_url + 'app/js/lib/bootstrap-datetimepicker.js', function() {
		$(start_time).datetimepicker({collapse: false});
		$(end_time).datetimepicker({collapse: false});
	});

	// If anomaly or week-over week displays are chosen update the
	// time span menu to limit the search to 1 week or less
	$('.section-toggle').click(function() {
		var data_target = $(this).attr('data-target');
		var section = $('#' + data_target);
		section.removeClass('section-off').addClass('section-on').siblings('.section').addClass('section-off').removeClass('section-on');
		if (data_target === 'history-anomaly' + widget_num || data_target === 'history-wow' + widget_num)
		{
			$('ul#time-span-options' + widget_num).html(short_span_menu);
			if ($('div#time-span' + widget_num).attr('data-ms') > 604800)
      {
				$('div#time-span' + widget_num).text('1 week').attr('data-ms', "608400");
			}
			$('ul#tab-list' + widget_num + ' a[href="#tab' + widget_num + '-1"]').click();
			$('ul#tab-list' + widget_num).addClass('hidden');
      $('#' + add_metric_button_id).addClass('hidden');
    }
    else if (data_target === 'history-no' + widget_num)
    {
      $('ul#time-span-options' + widget_num).html(long_span_menu);
			$('ul#tab-list' + widget_num).removeClass('hidden');
			$('#' + add_metric_button_id).removeClass('hidden');
		}
	});

	widget.metric_count = add_tab(widget.metric_count, widget_num);
	var widget_height = $(widget_element).children('.widget').innerHeight();
	var main_height = widget_height - (widget.sw_graphwidget_fronttitle.height() + widget.sw_graphwidget_frontfooter.height());
	widget.sw_graphwidget_frontmain.css('height', main_height);
	widget.sw_graphwidget_backmain.css('height', main_height);
//	var row1_height = form_row_1.outerHeight(true);
//	var row2_height = form_row_2.outerHeight(true);
//	form_row_3.css('height', main_height - (row1_height + row2_height));

	build_saved_search_menu(widget);

	$('label').click(function() {
		push_button(this);
	});

	$('ul.dropdown-menu').on('click', 'li', function() {
		dropdown_menu_handler(this, widget_num);
	});

}

function add_tab(tab_num, widget_num)
{
	tab_num++;

	var tab_content = $('div#tab-content' + widget_num)
		,tab_tag = widget_num + '-' + tab_num
		,tab_list = $('ul#tab-list' + widget_num);

	$(tab_content).append('<div class="tab-pane" id="tab' + tab_tag + '">');
	var tab_pane = $(tab_content).children('div#tab' + tab_tag);

  tab_pane.append('<table class="tab-table" id="metric-options' + tab_tag + '">');
  tab_table = $(tab_pane.children('table#metric-options' + tab_tag));
  tab_table.append('<tr><td colspan="3"><div class="metric-input-textbox">' +
    '<input type="text" class="metric-autocomplete" name="metric' + tab_tag + '" placeholder="Metric name and tags">' +
    '</div></td></tr>' +
    '<tr><td><div class="graph-widget-form-item menu-label" id="aggregation' + tab_tag + '" style="margin-right: 0;">' +
    '<h4>Aggregation</h4><div class="dropdown graph-widget-button">' +
    '<span data-toggle="dropdown"><div class="graph-widget-button-label" id="active-aggregation-type' + tab_tag + '">Sum</div>' +
    '<span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span></span>' +
    '<ul class="dropdown-menu" id="aggregation-type=options' + tab_tag + '" role="menu" aria-labelledby="dLabel">' +
    '<li><span>Sum</span></li>' +
    '<li><span>Average</span></li>' +
    '<li><span>Minimum Value</span></li>' +
    '<li><span>Maximum Value</span></li>' +
    '<li><span>Standard Deviation</span></li>' +
    '</ul></div></td>' +
    '<td colspan="2"><div class="graph-widget-form-item menu-label" id="downsample' + tab_tag + '" style="margin-right: 0; margin-left: 40px;">' +
    '<h4>Downsampling</h4>' +
    '<div class="dropdown graph-widget-button">' +
    '<span data-toggle="dropdown">' +
    '<div class="graph-widget-button-label" id="active-downsample-type' + tab_tag + '">Maximum Value</div>' +
    '<span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span></span>' +
    '<ul class="dropdown-menu" id="downsample-type-options' + tab_tag + '" role="menu" aria-labelledby="dLabel">' +
    '<li><span>Sum</span></li>' +
    '<li><span>Average</span></li>' +
    '<li><span>Minimum Value</span></li>' +
    '<li><span>Maximum Value</span></li></ul></div>' +
    '<div class="dropdown graph-widget-button">' +
    '<span data-toggle="dropdown">' +
    '<div class="graph-widget-button-label ds-interval" id="active-downsample-interval' + tab_tag + '" data-value="1">1 minute</div>' +
    '<span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span></span>' +
    '<ul class="dropdown-menu ds-values" id="downsample-interval-options' + tab_tag + '" role="menu" aria-labelledby="dLabel">' +
    '<li><span data-value="1">1 minute</span></li>' +
    '<li><span data-value="10">10 minutes</span></li>' +
    '<li><span data-value="30">30 minutes</span></li>' +
    '<li><span data-value="60">1 hour</span></li>' +
    '<li><span data-value="240">4 hours</span></li>' +
    '<li><span data-value="720">12 hours</span></li>' +
    '<li><span data-value="1440">1 day</span></li></ul></div></td></tr>');
  tab_table.append('<tr><td width="32%"><div class="graph-widget-form-item menu-label">' +
    '<h4>Interpolation</h4>' +
    '<div class="push-button binary pushed">' +
    '<input type="checkbox" id="lerp-button' + tab_tag + '" name="lerp' + tab_tag + '" checked>' +
    '<label for="lerp-button' + tab_tag + '"><span class="iconic iconic-check-alt green"></span>' +
    '<span class="binary-label">Yes</span></label></div></div></td>' +
    '<td width="30%"><div class="graph-widget-form-item menu-label">' +
    '<h4>Right Axis</h4>' +
    '<div class="push-button binary">' +
    '<input type="checkbox" id="y2-button' + tab_tag + '" name="y2-' + tab_tag + '">' +
    '<label for="y2-button' + tab_tag + '"><span class="iconic iconic-x-alt red"></span>' +
    '<span class="binary-label">No </span></label></div></div></td>' +
    '<td width="30%"><div class="graph-widget-form-item menu-label">' +
    '<h4>Rate</h4>' +
    '<div class="push-button binary">' +
    '<input type="checkbox" id="rate-button' + tab_tag + '" name="rate' + tab_tag + '">' +
    '<label for="rate-button' + tab_tag + '"><span class="iconic iconic-x-alt red"></span>' +
    '<span class="binary-label">No </span></label></div></div></td></tr>');

	tab_list.append('<li><a href="#tab' + tab_tag + '" data-toggle="tab">Metric ' + tab_num + '</a></li>');
	$('#' + tab_list.attr('id') + ' a[href="#tab' + tab_tag + '"]').click(function(event) {
		event.preventDefault();
		$(this).tab('show');
	});

	$('#' + tab_list.attr('id') + ' a[href="#tab' + tab_tag + '"]').tab('show');

	$('label').click(function() {
		push_button(this);
	});

	$('ul.dropdown-menu').on('click', 'li', function() {
		dropdown_menu_handler(this, widget_num);
	});

	return tab_num;

}

function dropdown_menu_handler(item, widget_num)
{
	var button = $(item).parent().parent().children('span');
	$(button).children('.graph-widget-button-label').text($(item).text());
	$(button).children('div.ds-interval').attr('data-value', $(item).children('span').attr('data-value'));
	if ($(item).parent().attr('id') === "time-span-options" + widget_num)
	{
		$(button).children('div#time-span' + widget_num).attr('data-ms', $(item).children('span').attr('data-ms')).text();
	}
}

function build_saved_search_menu(widget)
{
	var user_id = document._session_data.user_id;
	var api_url = widget.options.sw_url + 'api/get_saved_searches';
  console.log('building saved search menu for user id ' + user_id);

  api_query = {user_id: user_id};
	$.ajax({
		url: api_url
		,type: 'POST'
		,data: api_query
		,dataType: 'json'
		,success: function(data) {
			var my_searches = data['user_searches'];
			var public_searches = data['public_searches'];
			var saved_search_list = widget.sw_graphwidget_backtitle.children('.saved-searches-menu').children('ul.saved-searches-options')
			saved_search_list.empty();
			saved_search_list.append('<li class="menu-section"><span>My Searches</span></li>');
			if (my_searches)
			{
				$.each(my_searches, function(i, search) {
					saved_search_list.append('<li><span data-name="search-' + search['id'] + '">' + search['title'] + '</span></li>');
				});
			}
			if (public_searches)
			{
				saved_search_list.append('<li class="menu-section"><span class="divider"></span></li>');
				saved_search_list.append('<li class="menu-section"><span>Public Searches</span></li>');
				$.each(public_searches, function(i, public) {
					saved_search_list.append('<li><span data-name="search-' + public['id'] + '">' + public['title'] + ' (' + public['username'] + ')</span></li>');
				});
			}
		}
	});

  widget.sw_graphwidget_backtitle.children('.saved-searches-menu').children('ul.saved-searches-options').on('click', 'li', function() {
    var saved_query = {};
    if (search_name = $(this).children('span').attr('data-name'))
    {
      var search_bits = search_name.split('-');
      var search_id = search_bits[1];
      console.log('loading saved search #' + search_id);
      $.ajax({
        url: widget.options.sw_url + "api/load_saved_search/" + search_id
        ,type: 'GET'
        ,dataType: 'json'
        ,success: function(data) {
          saved_query = data;
          delete(saved_query.private);
          delete(saved_query.save_span);
          delete(saved_query.title);
          delete(saved_query.user_id);
          populate_search_form(saved_query, widget);
        }
      });
    }

  });

}

function populate_search_form(query_data, widget)
{

  console.log('populating search form');
  console.log(query_data);
  var prompt_user = false;
  var method_map = {sum: 'Sum', avg: 'Average', min: 'Minimum Value', max: 'Maximum Value', dev: 'Standard Deviation'};
  var widget_num = widget.uuid;

  if (query_data['auto_update'] === "true") {
    $('label[for="auto-update-button' + widget_num + '"]').click();
    $('label[for="auto-update-button' + widget_num + '"]').parent('.push-button').addClass('pushed');
    $('label[for="auto-update-button' + widget_num + '"]').children('span.iconic').removeClass('iconic-x-alt red').addClass('iconic-check-alt green');
  }
  if (query_data['history-graph'].match(/anomaly/))
  {
    var el = $('input[data-target="history-anomaly' + widget_num + '"]').parent('label');
    $(el).parent('div.toggle-button').addClass('toggle-on');
    $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
    $(el).children('input').attr('checked', 'Checked');
    $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
    $('input[data-target="history-anomaly' + widget_num + '"]').click();
  }
  else if (query_data['history-graph'].match(/wow/))
  {
    var el = $('input[data-target="history-wow' + widget_num + '"]').parent('label');
    $(el).parent('div.toggle-button').addClass('toggle-on');
    $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
    $(el).children('input').attr('checked', 'Checked');
    $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
    $('input[data-target="history-wow' + widget_num + '"]').click();
  }
  if (query_data['time_span'])
  {
    var el = $('input[data-target="graph-widget-time-span' + widget_num + '"]').parent('label');
    $(el).parent('div.toggle-button').addClass('toggle-on');
    $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
    $(el).children('input').attr('checked', 'Checked');
    $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
    $('input[data-target="graph-widget-time-span' + widget_num + '"]').click();
    var span = query_data['time_span'];
    $('#time-span-options' + widget_num + ' li span[data-ms="' + span + '"]').parent('li').click();
  }
  else
  {
    if ((start_in = parseInt(query_data['start_time'])) && (end_in = parseInt(query_data['end_time'])))
    {
      $('div#start-time' + widget_num).children('input').val(new Date(start_in * 1000).toString('yyyy/MM/dd HH:mm:ss'));
      $('div#end-time' + widget_num).children('input').val(new Date(end_in * 1000).toString('yyyy/MM/dd HH:mm:ss'));
    }
    else
    {
      prompt_user = true;
    }
  }

  $.each(query_data['metrics'], function(i, metric) {
    metric_num = i + 1;
    metric_string = metric.name;
    console.log('loading query data for metric ' + metric_num + ', ' + metric_string);
    if (metric_num > 1)
    {
      var metric_tab = $('div#tab' + widget.uuid + '-' + metric_num);
      console.log('checking for tab' + widget.uuid + '-' + metric_num);
      console.log(metric_tab);
      console.log(metric_tab.length);
      if (metric_tab.length == 0)
      {
        widget.metric_count = add_tab(i, widget.uuid);
        $('input[name="metric' + widget_num + '-' + widget.metric_count + '"]').autocomplete({
          minChars: 2
          ,serviceUrl: widget.options.sw_url + 'api/tsdb_metric_list/'
          ,containerClass: 'autocomplete-suggestions dropdown-menu'
          ,zIndex: ''
          ,maxHeight: ''
        });
      }
    }

    if (metric.tags)
    {
      $.each(metric.tags, function(i, tag) {
        metric_string += ' ' + tag;
      });
    }
    $('input[name="metric' + widget_num + '-' + metric_num + '"]').val(metric_string);
    $('#active-aggregation-type' + widget_num + '-' + metric_num).text(method_map[metric.agg_type]);
    $('#active-downsample-type' + widget_num + '-' + metric_num).text(method_map[metric.ds_type]);
    $('#downsample-interval-options' + widget_num + '-' + metric_num + ' li span[data-value="' + metric.ds_interval + '"]').parent('li').click();
    if (!metric.lerp)
    {
      $('input#lerp-button' + widget_num + '-' + metric_num).siblings('label').click();
      $('input#lerp-button' + widget_num + '-' + metric_num).parent('.push-button').removeClass('pushed');
      $('input#lerp-button' + widget_num + '-' + metric_num).siblings('label').children('span.iconic').addClass('iconic-x-alt red').removeClass('iconic-check-alt green');
      $('input#lerp-button' + widget_num + '-' + metric_num).siblings('label').children('span.binary-label').text('No');
    }
    if (metric.rate)
    {
      $('input#rate-button' + widget_num + '-' + metric_num).siblings('label').click();
      $('input#rate-button' + widget_num + '-' + metric_num).parent('.push-button').addClass('pushed');
      $('input#rate-button' + widget_num + '-' + metric_num).siblings('label').children('span.iconic').removeClass('iconic-x-alt red').addClass('iconic-check-alt green');
      $('input#rate-button' + widget_num + '-' + metric_num).siblings('label').children('span.binary-label').text('Yes');
    }
    if (metric.y2)
    {
      $('input#y2-button' + widget_num + '-' + metric_num).siblings('label').click();
      $('input#y2-button' + widget_num + '-' + metric_num).parent('.push-button').addClass('pushed');
      $('input#y2-button' + widget_num + '-' + metric_num).siblings('label').children('span.iconic').removeClass('iconic-x-alt red').addClass('iconic-check-alt green');
      $('input#y2-button' + widget_num + '-' + metric_num).siblings('label').children('span.binary-label').text('Yes');
    }
  });
  if (prompt_user)
  {
    $(widget.element).children('.widget').addClass('flipped');
  }
  else
  {
    go_click_handler('', widget);
  }
}

function go_click_handler(event, widget)
{

	var widget_num = widget.uuid;
	var widget_element = $(widget.element);
	widget.query_data = {};
	widget.query_data.datasource = 'OpenTSDB';
	widget.query_data.downsample_master_interval = 0;
	var input_error = false;
	var methods = {'sum': 'sum', 'average': 'avg', 'minimum value': 'min', 'maximum value': 'max', 'standard deviation': 'dev'};

	if (typeof widget.autoupdate_interval !== "undefined")
	{
		console.log('clearing auto-update timer');
		clearInterval(widget.autoupdate_interval);
		delete widget.autoupdate_interval;
	}

	// Map out all the elements we need to check
	var input_dates = $('div#graph-widget-dates' + widget_num);
	var input_time_span = $('div#time-span' + widget_num);
	var input_autoupdate = $('input#auto-update-button' + widget_num);
	var input_history = widget_element.find('input:radio[name="history-graph"]:checked');

	// Date range validation
	var start, end;
	var date_span_option = widget_element.find('input:radio[name="date-span"]:checked').val();
	if (date_span_option === 'date-search')
	{
		if ($(input_dates).children('div#start-time' + widget_num).children('input[name="start-time"]').val().length < 1)
		{
			$(input_dates).children('div#start-time' + widget_num).children('input[name="start-time"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
			alert("You must specify a start time");
		}
		else if ($(input_dates).children('div#end-time' + widget_num).children('input[name="end-time"]').val().length < 1)
		{
			$(input_dates).children('div#end-time' + widget_num).children('input[name="end-time"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
			alert("You must specify an end time");
		}

		// Start date has to be before the End date.
		start = Date.parse($(input_dates).children('div#start-time' + widget_num).children('input[name="start-time"]').val()).getTime();
		start = start / 1000;
		end = Date.parse($(input_dates).children('div#end-time' + widget_num).children('input[name="end-time"]').val()).getTime();
		end = end / 1000;
		if (start >= end)
		{
			alert('Start time must come before end time');
			$(input_dates).children('div#start-time' + widget_num).children('input[name="start-time"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
		}
		if (typeof widget.query_data['time_span'] != "undefined")
		{
			delete widget.query_data['time_span'];
		}
	}
	else
	{
    console.log('getting time span');
		end = new Date.now().getTime();
		end = parseInt(end / 1000);
		var span = parseInt($(input_time_span).attr('data-ms'));
		start = (end - span);
		var jstart = new Date(start * 1000).toString('yyyy/MM/dd HH:mm:ss');
		var jend = new Date(end * 1000).toString('yyyy/MM/dd HH:mm:ss');
		$(input_dates).children('div#start-time' + widget_num).children('input[name="start-time"]').val(jstart).change();
		$(input_dates).children('div#end-time' + widget_num).children('input[name="end-time"]').val(jend).change();
		widget.query_data['time_span'] = span;
	}
	widget.query_data['start_time'] = start;
	widget.query_data['end_time'] = end;

	// Check for auto-update flag
	if (input_autoupdate.prop('checked'))
	{
		widget.query_data['auto_update'] = true;
	}
	else
	{
		widget.query_data['auto_update'] = false;
	}

	// Check for history display options
	widget.query_data['metrics'] = [];
	widget.query_data['history-graph'] = $(input_history).val();
	if (widget.query_data['history-graph'] === 'no')
	{
		widget.query_data['metrics_count'] = widget.metric_count;
		for (i=1; i<=widget.query_data['metrics_count']; i++)
		{
			var build_metric = {};
			var metric_bits = $('input:text[name=metric'+ widget_num + '-' + i + ']').val().split(' ');
			build_metric.name = metric_bits.shift();
			if (build_metric.name.length < 1)
			{
				continue;
			}
			if (metric_bits.length > 0)
			{
				build_metric.tags = metric_bits;
			}
			var agg_type = $('#active-aggregation-type' + widget_num + '-' + i).text().toLowerCase();
			var ds_type = $('#active-downsample-type' + widget_num + '-' + i).text().toLowerCase();
			build_metric.agg_type = methods[agg_type];
			build_metric.ds_type = methods[ds_type];
			build_metric.ds_interval = $('#active-downsample-interval' + widget_num + '-' + i).attr('data-value');
			if ((widget.query_data['downsample_master_interval'] < 1) || (build_metric.ds_interval < widget.query_data['downsample_master_interval']))
			{
				widget.query_data['downsample_master_interval'] = build_metric.ds_interval;
			}

			if ($('#rate-button' + widget_num + '-' + i).prop('checked'))
			{
				build_metric.rate = true;
			}

			if ($('#lerp-button' + widget_num + '-' + i).prop('checked'))
			{
				build_metric.lerp = true;
			}
			if ($('#y2-button' + widget_num + '-' + i).prop('checked'))
			{
				build_metric.y2 = true;
			}

			widget.query_data['metrics'].push(build_metric);
		}
		if (widget.query_data['metrics'].length < 1)
		{
			widget.sw_graphwidget_searchform.find('ul#tab-list' + widget_num + ' a[href="#tab' + widget_num + '-1"]').click();
			$('input:text[name="metric'+ widget_num + '-1"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
			alert("You must specify at least one metric to search for");
			input_error = true;
		}
	}
	else
	{
		widget.query_data['metrics_count'] = 1;
		if ($('input:text[name="metric' + widget_num + '-1"]').val().length < 1)
		{
			$('input:text[name="metric'+ widget_num + '-1"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
			alert("You must specify a metric to search for");
			input_error = true;
		}
		else if ((end - start) / 60 > 10080)
		{
			alert('History comparison searches are limited to 1 week or less of data');
			$(input_dates).children('div[data-name="start-time"]').children('input[name="start-time"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
			input_error = true;
		}
		else
		{
			input_error = false;
			var build_metric = {};
			var metric_bits = $('input:text[name=metric' + widget_num + '-1]').val().split(' ');
			build_metric.name = metric_bits.shift();
			if (metric_bits.length > 0)
			{
				build_metric.tags = metric_bits;
			}
			var agg_type = $('#active-aggregation-type' + widget_num + '-1').text().toLowerCase();
			var ds_type = $('#active-downsample-type' + widget_num + '-1').text().toLowerCase();
			build_metric.agg_type = methods[agg_type];
			build_metric.ds_type = methods[ds_type];
			build_metric.ds_interval = $('#active-downsample-interval' + widget_num + '-1').attr('data-value');
			widget.query_data['downsample_master_interval'] = build_metric.ds_interval;

			if ($('#rate-button' + widget_num + '-1').prop('checked'))
			{
				build_metric.rate = true;
			}

			if ($('#lerp-button' + widget_num + '-1').prop('checked'))
			{
				build_metric.lerp = true;
			}

			if ($('y2-button' + widget_num + '-1').prop('checked'))
			{
				build_metric.y2 = true;
			}
			widget.query_data['metrics'].push(build_metric);
		}
	}

	// If we made it this far without errors in the form input, then
	// we build us a graph
	if (input_error == false)
	{
		var graph_element = $('#graphdiv' + widget_num);
		// Make sure the graph display div is empty
		graph_element.empty();
    // Clear the graph legend
    $('#legend' + widget_num).empty();
		// Load the waiting spinner
		graph_element.append('<div class="bowlG">' +
			'<div class="bowl_ringG"><div class="ball_holderG">' +
			'<div class="ballG"></div></div></div></div>');
		$(widget.element).children('.widget').removeClass('flipped');
		graph_element.append('<div id="status-box' + widget_num + '" style="width: 100%; text-align: center;">' +
			'<p id="status-message' + widget_num + '"></p></div>');
//      $('#status-box' + widget_num).append('<p id=chuck style="margin: 0 25px"></p>');
	}

	init_query(widget.query_data, widget);

}

function init_query(query_data, widget) {

	widget_num = widget.uuid;

	// Start deferred query for metric data
	$.when(opentsdb_search(query_data, widget)).then(
		// done: Send the data over to be parsed
		function(data)
		{
			$.when(process_graph_data(data, query_data, widget)).then(
				// done: Build the graph
				function(data)
				{
					build_graph(data.graphdata, data.querydata, widget);
				}
				// fail: Show error image and error message
				,function(status)
				{
					widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('.bowlG').html(widget.options.sw_url + 'app/img/error.png" style="width: 60px; height: 30px;">');
					widget.sw_graphwidget_frontmain.find('#status-message' + widget_num).text(status);
				}
			);
		}
		// fail: Show error image and error message
		,function(status)
		{
			widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('.bowlG')
				.css({'padding-top': '15%', 'margin-top': '0', 'margin-bottom': '5px', 'width': '120px', 'height': '60px'})
				.html('<img src="' + widget.options.sw_url + 'app/img/error.png" style="width: 120px; height: 60px;">');

			widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).empty()
				.append('<p>' + status.shift() + '</p>');

			if ((status[0].match("<!DOCTYPE")) || (status[0].match("<html>")))
			{
				var error_message = status.join(' ').replace(/'/g,"&#39");
				widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num)
					.append('<iframe style="margin: 0 auto; color: rgb(205, 205, 205);" width="80%" height="90%" srcdoc=\'' + error_message + '\' seamless></iframe>');
			}
			else
			{
				widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).text(status);
			}
		}
	);

}

// Function to wrap the OpenTSDB search
function opentsdb_search(query_data, widget)
{

	var query_object = new $.Deferred();
	var widget_num = widget.uuid;
	var status = widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).children('#status-message' + widget_num);

	// Generate (or find the cached) model data for the metric
	if (query_data['history-graph'] == "anomaly")
	{
		status.html('<p>Fetching Metric Data</p>');
		$.when(get_metric_data_anomaly(query_data, widget)
			.done(function(data) {
				query_object.resolve(data);
			})
			.fail(function(data) {
				query_object.reject(data);
			})
		);
	}
	// Search current and previous week for metric data
	else if (query_data['history-graph'] == "wow")
	{
		status.html('<p>Fetching Week-Over-Week Data</p>');
		$.when(get_metric_data_wow(query_data, widget)
			.done(function(data) {
				query_object.resolve(data);
			})
			.fail(function(data) {
				query_object.reject(data);
			})
		);
	}
	else
	{
		status.html('<p>Fetching Metric Data</p>');
		$.when(get_metric_data(query_data, widget)
			.done(function(data) {
				query_object.resolve(data);
			})
			.fail(function(data) {
				query_object.reject(data);
			})
		);
	}

	return query_object.promise();

}

// AJAX function to search OpenTSDB
function get_metric_data(query_data, widget)
{

	if (typeof ajax_request !== 'undefined')
	{
		console.log('Previous request still in flight, aborting');
		ajax_request.abort();
	}

	var ajax_object = new $.Deferred();

	var ajax_request = $.ajax({
			url: widget.options.sw_url + "adhoc/search/OpenTSDB"
			,type: 'POST'
			,data: query_data
			,dataType: 'json'
			,timeout: 120000
		})
		,chain = ajax_request.then(function(data) {
			return(data);
		});

	chain.done(function(data) {
		if (data[0] === "error")
		{
			ajax_request.abort();
			ajax_object.reject(data[1])
		}
		else if (Object.getOwnPropertyNames(data).length <= 5)
		{
			ajax_request.abort();
			ajax_object.reject(["0", "Query returned no data"]);
		}
		else
		{
			delete ajax_request;
			ajax_object.resolve(data);
		}
	});

	return ajax_object.promise();

}

function get_metric_data_wow(query_data, widget)
{

	var status = widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).children('#status-message' + widget_num);

	if (typeof ajax_request !== 'undefined')
	{
		ajax_request.abort();
	}

	var ajax_object = new $.Deferred();

	var metric_data = {};

	var ajax_request = $.ajax({
			url: widget.options.sw_url + "adhoc/search/OpenTSDB"
			,type: 'POST'
			,data: query_data
			,data_type: 'json'
			,timeout: 120000
	})
	,chained = ajax_request.then(function(data) {
		metric_data[0] = eval('(' + data + ')');
		if (metric_data[0][0] === "error")
		{
			ajax_request.abort();
			ajax_object.reject(metric_data[0][1])
		}
		else if (Object.keys(metric_data[0]).length <= 5)
		{
			ajax_request.abort();
			ajax_object.reject(["0", "Current week query returned no data"]);
		}
		else
		{
			status.html('<p>Fetching Metric Data</p>');
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
			past_query.end_time = parseInt(query_data.end_time - 604800);
			past_query.start_time = past_query.end_time - query_span;
			return $.ajax({
				url: widget.options.sw_url + "adhoc/search/OpenTSDB"
				,type: 'POST'
				,data: past_query
				,data_type: 'json'
				,timeout: 120000
				});
			}
		});
		chained.done(function(data) {
			if (!data)
			{
			ajax_request.abort();
			ajax_object.reject();
			}
		else
		{
			metric_data[1] = eval('(' + data + ')');
			if (metric_data[1][0] === "error")
			{
				ajax_request.abort();
				ajax_object.reject(metric_data[1][1])
			}
			else if (Object.getOwnPropertyNames(metric_data[1]).length <= 5)
			{
				ajax_request.abort();
				ajax_object.reject(["0", "Previous week query returned no data"]);
			}
			else
			{
				delete metric_data[1].start;
				delete metric_data[1].end;
				delete metric_data[1].query_url;
				past_keys = Object.keys(metric_data[1]);
				past_key = 'Previous - ' + past_keys[0];
				metric_data[past_key] = metric_data[1][past_keys[0]];
				delete metric_data[1];
				$.each(metric_data[past_key], function(index, entry) {
					entry.timestamp = parseInt(entry.timestamp + 604800);
				});
				delete ajax_request;
				ajax_object.resolve(metric_data);
			}
		}
	});

	return ajax_object.promise();
}

function get_metric_data_anomaly(query_data, widget)
{

  var status = widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).children('#status-message' + widget_num);

  if (typeof ajax_request !== 'undefined')
  {
 	 ajax_request.abort();
  }

	var ajax_object = new $.Deferred();

	var metric_data = {};
	var data_for_detection = [];

	var ajax_request = $.ajax({
		url: widget.options.sw_url + "adhoc/search/OpenTSDB"
		,type: 'POST'
		,data: query_data
		,dataType: 'json'
		,timeout: 120000
	})
	,chained_request_1 = ajax_request.then(function(data) {
		if (data[0] === "error")
		{
			ajax_request.abort();
			ajax_object.reject(data[1])
		}
		else if (Object.getOwnPropertyNames(data).length <= 5)
	  	{
	  		ajax_request.abort();
			ajax_object.reject(["0", "Query returned no data"]);
	  	}
		else
	  	{
	 		metric_data = data;
			var past_query = $.extend(true, {}, query_data);
			past_query.end_time = metric_data.start - 1;
			past_query.start_time = past_query.end_time - document._sw_conf.anomalies.pre_anomaly_period;
			past_query.cache_key = metric_data.cache_key + '_pre';
			status.html('<p>Fetching data for anomaly detection</p>');
			return $.ajax({
				url: widget.options.sw_url + "adhoc/search/OpenTSDB"
				,type: 'POST'
				,data: past_query
				,data_type: 'json'
				,timeout: 120000
			});
		}
	})
	,chained_request_2 = chained_request_1.then(function(data) {
		if (!data)
		{
			ajax_request.abort();
			ajax_object.reject();
		}
		else
	  	{
			if (typeof(data) !== "object")
		  	{
		  		var pre_period_data = eval('(' + data + ')');
		  	}
			else
			{
				pre_period_data = data;
			}

			if (pre_period_data[0] === "error")
			{
				ajax_request.abort();
				ajax_object.reject(pre_period_data[1])
			}
			else if (Object.getOwnPropertyNames(pre_period_data).length <= 5)
			{
				ajax_request.abort();
				ajax_object.reject(["0", "Query returned no data"]);
			}
			else
			{
				var pre_period_cache = pre_period_data.query_cache;
				delete pre_period_data;
				data_for_detection = {metric: query_data.metrics[0]['name'], cache: metric_data.query_cache, pre_cache: pre_period_cache};
				status.html('<p>Calculating anomalies</p>');
				return $.ajax({
					url: widget.options.sw_url + "api/detect_timeseries_anomalies"
					,type: 'POST'
					,data: data_for_detection
					,data_type: 'json'
				});
			}
		}
	});

	chained_request_2.done(function(data) {
		if (!data)
		{
			ajax_request.abort();
			ajax_object.reject();
		}
		else
		{
	  		metric_data['anomalies'] = eval('(' + data + ')');
	  		if (metric_data[0] === "error")
	  		{
	  			ajax_request.abort();
	  			ajax_object.reject(metric_data[1])
	  		}
			else
			{
				ajax_object.resolve(metric_data);
			}
		}
	}).fail(function(data) {
		ajax_request.abort();
		ajax_object.reject([data.status, data.statusText]);
	});

	return ajax_object.promise();

}

function process_graph_data(data, query_data, widget)
{
	var parse_object = new $.Deferred();
	var status = widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).children('#status-message' + widget_num);

	var labels = ['Timestamp'];
	var buckets = {};
	var bucket_interval = parseInt(query_data['downsample_master_interval'] * 60);
	var start = parseInt(data.start);
	var end = parseInt(data.end);
	query_url = data.query_url;
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

	status.html('<p>Parsing Metric Data</p>');

	for (var i = start; i <= end; i = i + bucket_interval)
	{
		buckets[i] = [];
	}

	for (var series in data) {
		if (data.hasOwnProperty(series))
		{
			if (data[series] !== null)
			{
				if (query_data['history-graph'] == "anomaly")
				{
					query_data.metrics[0]['history-graph'] = "anomaly";
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
			else
			{
				console.log(series + ' is null, skipping');
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

	parse_object.resolve(parsed_data);

	return parse_object.promise();

}

function build_graph(data, query_data, widget)
{

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
        values = values.concat(graph_data[timestamp]);
        dygraph_format.push(values);
      }
    }

    var graphdiv_id = 'graphdiv' + widget.uuid;

    series_times = series_times.splice(-4, 4);

    var labels_map = {};
    $.each(graph_labels, function(index, label) {
      var label_bits = label.split(' ');
      if (typeof labels_map[label_bits[0]] == 'undefined')
      {
        labels_map[label_bits[0]] = [];
      }
      labels_map[label_bits[0]].push(label);
    });

    var x_space = $('#' + graphdiv_id).width() / 12;
	var y_space = $('#' + graphdiv_id).height() / 12;
	var g_width = $('#' + graphdiv_id).innerWidth() * .95;

	widget.g = new Dygraph(
		document.getElementById('graphdiv' + widget.uuid)
        ,dygraph_format
        ,{
			labels: graph_labels
			,labelsDiv: 'legend' + widget.uuid
			,legend: 'always'
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
			,gridLineColor: 'rgba(205, 205, 205, 0.1)'
			,axisLabelColor: 'rgba(234, 234, 234, 0.75)'
			,colors: swcolors.Wheel_DarkBG[5]
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

	// Set up the right axis labels, if requested
	var right_axis = '';
	$.each(query_data.metrics, function(i, metric) {
		if (metric.y2 == true)
		{
			var axis_bits = {};
			$.each(labels_map[metric.name], function(i, label)
			{
				if (right_axis.length < 1)
				{
					axis_bits = {};
					axis_bits[label] = {};
					axis_bits[label]['axis'] = {};
            		right_axis = label;
				}
				else
  				{
            		axis_bits = {};
            		axis_bits[label] = {};
            		axis_bits[label]['axis'] = right_axis;
				}
				widget.g.updateOptions(axis_bits);
			});
  		}
    });

    $('.widget-footer-btn.hidden').removeClass('hidden');

    // Set up the anomaly highlighting if requested
    if (query_data['history-graph'] == "anomaly")
    {
		anomalies = data.anomalies;
		widget.g.updateOptions({
        	underlayCallback: function(canvas, area, g) {
				canvas.fillStyle = "rgba(219, 54, 9, 0.25)";
        		function highlight_period(x_start, x_end) {
            		var canvas_left_x = widget.g.toDomXCoord(x_start);
            		var canvas_right_x = widget.g.toDomXCoord(x_end);
            		var canvas_width = canvas_right_x - canvas_left_x;
            		canvas.fillRect(canvas_left_x, area.y, canvas_width, area.h);
          		}
          		$.each(anomalies, function(i, d) {
            		highlight_period(new Date(parseInt(d.start * 1000)), new Date(parseInt(d.end * 1000)));
          		});
        	}
      	})
    }
    $('.dygraph-xlabel').parent().css('top', '40%');

	// Set the interval for adding new data if Auto Update is selected
	if (query_data['auto_update'])
	{
		console.log('setting auto-update timer');
    	autoupdate_interval = setInterval(function() {
        	var new_start = series_times[0];
        	var new_end = new Date.now().getTime();
        	new_end = parseInt(new_end / 1000);
        	query_data.start_time = new_start;
        	query_data.end_time = new_end;
          console.log('updating graph for widget ' + widget.element.attr('id'));
        	$.when(opentsdb_search(query_data, widget)).then(function(data)
            {
				$.when(process_graph_data(data, query_data, widget)).then(
                	function(data)
                  	{
                    	var dygraph_update = new Array();
                    	graph_data = data.graphdata.data;
                    	for (var timestamp in graph_data) {
                      		if (graph_data.hasOwnProperty(timestamp))
                      		{
                        		series_times.push(timestamp);
                        		jtime = new Date(parseInt(timestamp * 1000));
                        		values = [jtime];
                        		if (query_data['history-graph'] == 'anomaly')
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
                    	dygraph_format.splice(0, (dygraph_update.length - 2));
                    	dygraph_format.splice(-2, 2);
                    	dygraph_format = dygraph_format.concat(dygraph_update);
                    	widget.g.updateOptions({'file': dygraph_format});
                    	series_times = series_times.splice(-4, 4);
                  	}
              	);
            });
      	}, 300 * 1000);
    }

}

