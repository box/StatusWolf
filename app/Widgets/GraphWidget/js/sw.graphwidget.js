/**
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 15 July 2013
 *
 */


(function($, undefined) {

	var sw_graphwidget_classes = 'widget';

	$.widget('sw.graphwidget', {
		version: "1.0"
		,defaultElement: "<div>"
		,options: {
			disabled: null
			,label: null
      ,datasource: "OpenTSDB"
      ,legend: "on"
      ,sw_url: null
		}
		,_create: function() {

			var that = this
				,options = this.options
				,sw_graphwidget
				,sw_graphwidget_container
				,sw_graphwidget_containerid
				,sw_graphwidget_front
				,sw_graphwidget_back
				,sw_graphwidget_close
				,sw_graphwidget_fronttitle
				,sw_graphwidget_backtitle
				,sw_graphwidget_backfooter
				,sw_graphwidget_frontmain
				,sw_graphwidget_backmain
				,sw_graphwidget_legend_hover
				,sw_graphwidget_savedsearchesmenu
				,sw_graphwidget_datasourcemenu
				,sw_graphwidget_searchform
				,sw_graphwidget_editparamsbutton
        ,sw_graphwidget_clonebutton
				,sw_graphwidget_maximizebutton
        ,sw_graphwidget_action
				,sw_graphwidget_querycancelbutton
				,sw_graphwidget_gobutton
				,sw_graphwidget_datasource;

      if (this.options.sw_url === null)
      {
        this.options.sw_url = this.get_sw_url();
      }

			sw_graphwidget_container = (this.sw_graphwidget_container = $(this.element));
			$(sw_graphwidget_container).addClass('transparent');


			sw_graphwidget = (this.sw_graphwidget = $('<div>'))
				.addClass(sw_graphwidget_classes)
				.appendTo(this.element);

			sw_graphwidget_containerid = (this.sw_graphwidget_containerid = '#' + $(this.sw_graphwidget_container).attr('id'));

			// The graph widget has a front and back face
			sw_graphwidget_front = (this.sw_graphwidget_front = $('<div>'))
				.addClass('widget-front')
				.appendTo(sw_graphwidget);
			sw_graphwidget_back = (this.sw_graphwidget_back = $('<div>'))
				.addClass('widget-back')
				.appendTo(sw_graphwidget);

			// Each face has a title bar, a main content area,
			// and a footer bar
			sw_graphwidget_fronttitle = (this.sw_graphwidget_fronttitle = $('<div>'))
				.addClass('flexy widget-title')
				.appendTo(sw_graphwidget_front);
			sw_graphwidget_frontmain = (this.sw_graphwidget_frontmain = $('<div>'))
				.addClass('widget-main')
				.appendTo(sw_graphwidget_front);
			sw_graphwidget_backtitle = (this.sw_graphwidget_backtitle = $('<div>'))
				.addClass('flexy widget-title')
				.appendTo(sw_graphwidget_back);
			sw_graphwidget_backmain = (this.sw_graphwidget_backmain = $('<div>'))
				.addClass('widget-main')
				.appendTo(sw_graphwidget_back);
			sw_graphwidget_backfooter = (this.sw_graphwidget_backfooter = $('<div>'))
				.addClass('flexy widget-footer')
				.appendTo(sw_graphwidget_back);

			// Define the standard buttons for the graph widget
			// Buttons that will go in the title bar:
      sw_graphwidget_maximizebutton = (this.sw_graphwidget_maximize_button = $('<div>'))
        .addClass("widget-title-button left-button fullscreen-out")
        .append('<span class="maximize-me iconic iconic-fullscreen">')
        .click(function(event) {
          event.preventDefault();
          that.maximize_widget(that);
        })
        .appendTo(sw_graphwidget_fronttitle);

      sw_graphwidget_editparamsbutton = (this.sw_graphwidget_editparamsbutton = $('<div>'))
        .addClass("widget-title-button left-button info-tooltip")
        .attr('title', 'Edit Parameters')
        .click(function() {
          sw_graphwidget.addClass("flipped");
        })
        .append('<span class="iconic iconic-pen-alt2"></span>')
        .appendTo(sw_graphwidget_fronttitle);

      sw_graphwidget_clonebutton = (this.sw_graphwidget_clonebutton = $('<div>'))
        .addClass("widget-title-button left-button info-tooltip")
        .attr('title', 'Clone Widget')
        .click(function() {
          clone_widget(that);
        })
        .append('<span class="iconic iconic-new-window"></span>')
        .appendTo(sw_graphwidget_fronttitle);

      sw_graphwidget_action = (this.sw_graphwidget_action = $('<div>'))
        .addClass('dropdown widget-title-dropdown')
        .append('<span class="widget-title-button left-button" data-toggle="dropdown">' +
          '<span class="iconic iconic-cog"></span></span>' +
          '<ul class="dropdown-menu widget-action-options" role="menu">' +
          '<li data-action="usespan"><span>Use this time span for all widgets</span></li></ul>')
        .appendTo(sw_graphwidget_fronttitle);

      sw_graphwidget_highlight = (this.sw_graphwidget_legend_hover = $('<div>'))
				.addClass('legend-hover glue4')
				.appendTo(sw_graphwidget_fronttitle);

			sw_graphwidget_close = (this.sw_graphwidget_close = $('<div>'))
				.addClass('widget-title-button right-button close-widget')
				.click(function(event) {
					event.preventDefault();
					$(sw_graphwidget_container).addClass('transparent');
					var that_id = sw_graphwidget_containerid;
					setTimeout(function() {
						that.destroy(event);
						sw_graphwidget_container.remove();
					}, 600);
				})
				.append('<span class="iconic iconic-x">')
				.appendTo(sw_graphwidget_fronttitle);

			sw_graphwidget_savedsearchesmenu = (this.sw_graphwidget_savedsearchesmenu = $('<div>'))
				.addClass('dropdown widget-title-dropdown saved-searches-menu')
				.append('<span class="widget-title-button left-button" data-toggle="dropdown">' +
				  '<span class="widget-button-label">Saved Searches </span>' +
				  '<span class="iconic iconic-play rotate-90"></span></span>' +
				  '<ul class="dropdown-menu saved-searches-options" role="menu" aria-labelledby="dLabel"></ul>')
				.appendTo(sw_graphwidget_backtitle);

			sw_graphwidget_backtitle.append('<div class="glue1">');

			sw_graphwidget_datasourcemenu = (this.sw_graphwidget_datasourcemenu = $('<div>'))
				.addClass('dropdown widget-title-dropdown datasource-menu')
				.append('<span class="widget-title-button right-button" data-toggle="dropdown">' +
					'<span class="widget-button-label active-datasource">OpenTSDB </span>' +
					'<span class="iconic iconic-play rotate-90"></span></span>' +
					'<ul class="dropdown-menu menu-left datasource-options" role="menu" aria-labelledby="dLabel">' +
					'<li data-action="set-datasource"><span>OpenTSDB</span></li></ul>')
				.appendTo(sw_graphwidget_backtitle);

			// Buttons that will go in the footer bar
			sw_graphwidget_querycancelbutton = (this.sw_graphwidget_querycancelbutton = $('<div>'))
				.addClass("widget-footer-button left-button query_cancel")
				.click(function() {
					sw_graphwidget.removeClass("flipped")
				})
				.append('<span class="iconic iconic-x-alt"><span class="font-reset"> Cancel</span></span>')
				.appendTo(sw_graphwidget_backfooter);

			sw_graphwidget_backfooter.append('<div class="glue1">');

			sw_graphwidget_gobutton = (this.sw_graphwidget_gobutton = $('<div>'))
				.addClass("widget-footer-button right-button go-button")
				.click(function(event) {
					that.go_click_handler(event, that);
				})
				.append('<span class="iconic iconic-bolt"><span class="font-reset"> Go</span></span>')
				.appendTo(sw_graphwidget_backfooter);

			// Define the div for the search form
			sw_graphwidget_searchform = (this.sw_graphwidget_searchform = $('<div>'))
				.addClass('graph-widget-search-form')
				.appendTo(sw_graphwidget_backmain);

			sw_graphwidget_datasource = $.trim($(sw_graphwidget_datasourcemenu).children('span.widget-title-button').children('span.active-datasource').text().toLowerCase());
      that.build_search_form(that);

      $(sw_graphwidget).on('click', 'li[data-action]', function() {
        if ($(this).attr('data-action') === "usespan")
        {
          that.set_all_spans(this, that);
        }
        else
        {
          that.dropdown_menu_handler(this, that);
        }
      });

    }

		,_destroy: function() {
			console.log('_destroy called');
      if (typeof this.autoupdate_interval !== "undefined")
      {
        clearInterval(this.autoupdate_interval);
      }
			this.sw_graphwidget_gobutton.remove();
			this.sw_graphwidget_querycancelbutton.remove();
			this.sw_graphwidget_searchform.remove();
			this.sw_graphwidget_datasourcemenu.remove();
			this.sw_graphwidget_savedsearchesmenu.remove();
			this.sw_graphwidget_backfooter.remove();
			this.sw_graphwidget_backmain.remove();
			this.sw_graphwidget_backtitle.remove();
      this.sw_graphwidget_action.remove();
			this.sw_graphwidget_maximize_button.remove();
			this.sw_graphwidget_editparamsbutton.remove();
      this.sw_graphwidget_clonebutton.remove();
			this.sw_graphwidget_close.remove();
			this.sw_graphwidget_frontmain.remove();
      this.sw_graphwidget_legend_hover.remove();
			this.sw_graphwidget_fronttitle.remove();
			this.sw_graphwidget_front.remove();
			this.sw_graphwidget_back.remove();
			this.sw_graphwidget.remove();
		}

    ,get_sw_url: function() {
      var base_uri = window.location.origin + '/';
      var path_bits = window.location.pathname.toString().split('/');
      if (path_bits[1] !== "dashboard")
      {
        base_uri += path_bits[1] + '/';
      }
      return base_uri;
    }

		,maximize_widget: function(widget) {
			if ($(widget.sw_graphwidget_container).hasClass('maximize-widget'))
			{
				$(widget.sw_graphwidget_container).removeClass('maximize-widget');
				$('body').removeClass('no-overflow');
				$('.navbar').removeClass('hidden');
				$(widget.sw_graphwidget_container).children('span.maximize-me').removeClass('iconic-fullscreen-exit').addClass('iconic-fullscreen');
				widget.resize_graph(widget);
			}
			else
			{
        $(window).scrollTop(0);
				$(widget.sw_graphwidget_container).addClass('maximize-widget');
				$('.navbar').addClass('hidden');
				$('body').addClass('no-overflow');
				$(widget.sw_graphwidget_container).children('span.maximize-me').removeClass('iconic-fullscreen').addClass('iconic-fullscreen-exit');
				widget.resize_graph(widget);
			}
		}

		,resize_graph: function(widget) {
      var widget_main = widget.sw_graphwidget_frontmain
          graph_div = widget.sw_graphwidget_frontmain.children('div.graphdiv');
          graph_legend = widget.sw_graphwidget_frontmain.children('div.legend-container');
      graph_legend.css('width', widget_main.innerWidth());
      widget_main.css('height', widget.sw_graphwidget.innerHeight());
      graph_div.css('height', widget_main.innerHeight() - (graph_legend.outerHeight(true) + widget.sw_graphwidget_fronttitle.outerHeight()));
      widget.g.resize();
		}

    ,hide_legend: function(widget, button)
    {
      if (typeof button === "undefined")
      {
        button = widget.sw_graphwidget_frontmain.children('div.legend-container').children('button.legend-toggle');
      }
      var differential = $(button).parents('div.legend-container').outerHeight(true) - $(button).parents('div.legend-container').height();
      $(button).removeClass('legend-hide');
      $(button).addClass('legend-show');
      $(button).siblings('div.legend').addClass('nodisplay');
      $(button).parents('div.legend-container').addClass('hidden-legend');
      $(button).parents('div.legend-container').siblings('div.graphdiv')
        .css('height', widget.sw_graphwidget_frontmain.innerHeight() - differential);
      $(button).children('span.iconic').removeClass('rotate-90').addClass('rotate-90r');
      if (typeof widget.g !== "undefined")
      {
        widget.g.resize();
      }
      widget.options.legend = 'off';
    }

    ,show_legend: function(widget, button)
    {
      if (typeof button === "undefined")
      {
        button = widget.sw_graphwidget_frontmain.children('div.legend-container').children('button.legend-toggle');
      }
      $(button).removeClass('legend-show');
      $(button).addClass('legend-hide');
      $(button).parents('div.legend-container').removeClass('hidden-legend');
      $(button).siblings('div.legend').removeClass('nodisplay');
      $(button).parents('div.legend-container').siblings('div.graphdiv')
        .css('height', widget.sw_graphwidget_frontmain.innerHeight() - $(button).parents('div.legend-container').outerHeight(true));
      $(button).children('span.iconic').removeClass('rotate-90r').addClass('rotate-90');
      if (typeof widget.g !== "undefined")
      {
        widget.g.resize();
      }
      widget.options.legend = 'on'
    }

    ,build_search_form: function(widget)
    {

      if (widget.options.datasource === "OpenTSDB")
      {
        var widget_num = widget.uuid
          ,widget_element = widget.element;

        widget.metric_count = 0;

        var anomaly_span_menu = '<li data-action="set-span"><span data-ms=600>10 minutes</span></li>' +
          '<li data-action="set-span"><span data-ms=1800>30 minutes</span></li>' +
          '<li data-action="set-span"><span data-ms=3600>1 Hour</span></li>' +
          '<li data-action="set-span"><span data-ms=7200>2 Hours</span></li>' +
          '<li data-action="set-span"><span data-ms=14400>4 Hours</span></li>' +
          '<li data-action="set-span"><span data-ms=28800>8 Hours</span></li>' +
          '<li data-action="set-span"><span data-ms=43200>12 Hours</span></li>' +
          '<li data-action="set-span"><span data-ms=86400>1 Day</span></li>'
          ,wow_span_menu = '<li data-action="set-span"><span data-ms=600>10 minutes</span></li>' +
            '<li data-action="set-span"><span data-ms=1800>30 minutes</span></li>' +
            '<li data-action="set-span"><span data-ms=3600>1 Hour</span></li>' +
            '<li data-action="set-span"><span data-ms=7200>2 Hours</span></li>' +
            '<li data-action="set-span"><span data-ms=14400>4 Hours</span></li>' +
            '<li data-action="set-span"><span data-ms=28800>8 Hours</span></li>' +
            '<li data-action="set-span"><span data-ms=43200>12 Hours</span></li>' +
            '<li data-action="set-span"><span data-ms=86400>1 Day</span></li>' +
            '<li data-action="set-span"><span data-ms=604800>1 Week</span></li>'
          ,long_span_menu = '<li data-action="set-span"><span data-ms="600">10 minutes</span></li>' +
            '<li data-action="set-span"><span data-ms=1800>30 minutes</span></li>' +
            '<li data-action="set-span"><span data-ms=3600>1 Hour</span></li>' +
            '<li data-action="set-span"><span data-ms=7200>2 Hours</span></li>' +
            '<li data-action="set-span"><span data-ms=14400>4 Hours</span></li>' +
            '<li data-action="set-span"><span data-ms=28800>8 Hours</span></li>' +
            '<li data-action="set-span"><span data-ms=43200>12 Hours</span></li>' +
            '<li data-action="set-span"><span data-ms=86400>1 Day</span></li>' +
            '<li data-action="set-span"><span data-ms=604800>1 Week</span></li>' +
            '<li data-action="set-span"><span data-ms=1209600>2 Weeks</span></li>' +
            '<li data-action="set-span"><span data-ms=2592000>1 Month</span></li>';

        $('#' + widget_element.attr('id') + ' .widget-title .saved-searches-menu')
          .after('<h3 id="search-title' + widget_num +'" class="search-title search-title-prompt"></h3>' +
          '<input type="text" name="search-title-input' + widget_num + '" class="nodisplay">');
        $('#search-title' + widget_num).text('Click to set search title');

        $('.widget-title').on('click', 'h3', function() {
          var search_title_text = $(this).text();
          $(this).addClass('nodisplay');
          var title_input = $(this).parent('div').children('input');
          $(title_input).removeClass('nodisplay');
          if ($(this).hasClass('search-title-prompt'))
          {
            $(title_input).attr('placeholder', search_title_text);
          }
          else
          {
            $(title_input).val(search_title_text);
          }
          $(title_input).css({
            'font-size': $(this).css('font-size')
            ,'font-weight': $(this).css('font-weight')
          }).focus();
        });

        $('.widget-title').on('blur', 'input', function() {
          var search_title_text = $(this).siblings('h3').text();
          var changed_title = $(this).val();
          $(this).addClass('nodisplay');
          if (changed_title.length > 1)
          {
            $(this).siblings('h3').text(changed_title).removeClass('nodisplay search-title-prompt');
          }
          else
          {
            $(this).siblings('h3').text(search_title_text).removeClass('nodisplay');
          }
        });

        // The enter keypress when the search name edit field has focus
        // does the same as above
        $('.widget-title').on('keydown', 'input', function(event) {
          if (event.which === 13)
          {
            $(this).blur();
          }
        });

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

        form_table.append('<tr></td><td><div class="auto-update"><div class="push-button">' +
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

        var add_metric_button_id = 'add-metric-button' + widget_num + '-' + widget.metric_count;
        widget.sw_graphwidget_querycancelbutton.after('<div id="' + add_metric_button_id + '">');
        $('#' + add_metric_button_id).addClass('widget-footer-button left-button')
          .click(function() {
            widget.metric_count = widget.add_tab(widget, widget.metric_count, widget_num);
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

        widget.sw_graphwidget_fronttitle.children('.legend-hover').attr('id', 'legend-hover' + widget_num);

        widget.sw_graphwidget_frontmain.append('<div id="graph-title' + widget_num + '" class="graph-title">')
          .append('<div id="graphdiv' + widget_num + '" class="graphdiv" style="width: 99%;">')
          .append('<div id="legend-container' + widget_num + '" class="legend-container hidden">' +
            '<button type="button" class="legend-toggle legend-hide"><span class="iconic iconic-play rotate-90"></span></button>' +
            '<div id="legend' + widget_num + '" class="legend"></div></div>');

        if (widget.options.legend === "off")
        {
          widget.hide_legend(widget);
        }

        widget.sw_graphwidget_frontmain.on('click', 'button.legend-hide', function() {
          widget.hide_legend(widget, this);
        });

        widget.sw_graphwidget_frontmain.on('click', 'button.legend-show', function() {
          widget.show_legend(widget, this);
        });

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
          if (data_target === 'history-anomaly' + widget_num)
          {
            $('ul#time-span-options' + widget_num).html(anomaly_span_menu);
            if ($('div#time-span' + widget_num).attr('data-ms') > 86400)
            {
              $('div#time-span' + widget_num).text('1 day').attr('data-ms', "86400");
            }
            $('ul#tab-list' + widget_num + ' a[href="#tab' + widget_num + '-1"]').click();
            $('ul#tab-list' + widget_num).addClass('hidden');
            $('#' + add_metric_button_id).addClass('hidden');
          }
          else if (data_target === 'history-wow' + widget_num)
          {
            $('ul#time-span-options' + widget_num).html(wow_span_menu);
            if ($('div#time-span' + widget_num).attr('data-ms') > 604800)
            {
              $('div#time-span' + widget_num).text('1 week').attr('data-ms', "604800");
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

        widget.metric_count = widget.add_tab(widget, widget.metric_count, widget_num);
        $(form_div.children('.row3').find('.metric-autocomplete')).autocomplete({
          minChars: 2
          ,serviceUrl: widget.options.sw_url + 'api/tsdb_metric_list/'
          ,containerClass: 'autocomplete-suggestions dropdown-menu'
          ,zIndex: ''
          ,maxHeight: ''
        });

        var widget_height = $(widget_element).children('.widget').innerHeight();
        var main_height = widget_height;
        widget.sw_graphwidget_frontmain.css('height', main_height);
        widget.sw_graphwidget_backmain.css('height', main_height);

        widget.build_saved_search_menu(widget);

        $('label').click(function() {
          statuswolf_button(this);
        });

      }
    }

    ,add_tab: function(widget, tab_num, widget_num)
    {
      tab_num++;

      var tab_content = $('div#tab-content' + widget_num)
        ,tab_tag = widget_num + '-' + tab_num
        ,tab_list = $('ul#tab-list' + widget_num);

      $(tab_content).append('<div class="tab-pane" id="tab' + tab_tag + '">');
      var tab_pane = $(tab_content).children('div#tab' + tab_tag);

      if (widget.options.datasource === "OpenTSDB")
      {
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
          '<li data-action="set-agg-type"><span>Sum</span></li>' +
          '<li data-action="set-agg-type"><span>Average</span></li>' +
          '<li data-action="set-agg-type"><span>Minimum Value</span></li>' +
          '<li data-action="set-agg-type"><span>Maximum Value</span></li>' +
          '<li data-action="set-agg-type"><span>Standard Deviation</span></li>' +
          '</ul></div></td>' +
          '<td colspan="2"><div class="graph-widget-form-item menu-label" id="downsample' + tab_tag + '" style="margin-right: 0; margin-left: 40px;">' +
          '<h4>Downsampling</h4>' +
          '<div class="dropdown graph-widget-button">' +
          '<span data-toggle="dropdown">' +
          '<div class="graph-widget-button-label" id="active-downsample-type' + tab_tag + '">Sum</div>' +
          '<span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span></span>' +
          '<ul class="dropdown-menu" id="downsample-type-options' + tab_tag + '" role="menu" aria-labelledby="dLabel">' +
          '<li data-action="set-ds-type"><span>Sum</span></li>' +
          '<li data-action="set-ds-type"><span>Average</span></li>' +
          '<li data-action="set-ds-type"><span>Minimum Value</span></li>' +
          '<li data-action="set-ds-type"><span>Maximum Value</span></li></ul></div>' +
          '<div class="dropdown graph-widget-button">' +
          '<span data-toggle="dropdown">' +
          '<div class="graph-widget-button-label ds-interval" id="active-downsample-interval' + tab_tag + '" data-value="1">1 minute</div>' +
          '<span class="dropdown-arrow-container"><span class="iconic iconic-play rotate-90"></span></span></span>' +
          '<ul class="dropdown-menu ds-values" id="downsample-interval-options' + tab_tag + '" role="menu" aria-labelledby="dLabel">' +
          '<li data-action="set-ds-span"><span data-value="1">1 minute</span></li>' +
          '<li data-action="set-ds-span"><span data-value="10">10 minutes</span></li>' +
          '<li data-action="set-ds-span"><span data-value="30">30 minutes</span></li>' +
          '<li data-action="set-ds-span"><span data-value="60">1 hour</span></li>' +
          '<li data-action="set-ds-span"><span data-value="240">4 hours</span></li>' +
          '<li data-action="set-ds-span"><span data-value="720">12 hours</span></li>' +
          '<li data-action="set-ds-span"><span data-value="1440">1 day</span></li></ul></div></td></tr>');
        tab_table.append('<tr><td width="32%"><div class="graph-widget-form-item menu-label">' +
          '<h4>Interpolation</h4>' +
          '<div class="push-button binary info-tooltip" title="Interpolation should be disabled unless you are absolutely sure that you need it.">' +
          '<input type="checkbox" id="lerp-button' + tab_tag + '" name="lerp' + tab_tag + '">' +
          '<label for="lerp-button' + tab_tag + '"><span class="iconic iconic-check-alt red"></span>' +
          '<span class="binary-label">No</span></label></div></div></td>' +
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

      }

      tab_list.append('<li><a href="#tab' + tab_tag + '" data-toggle="tab">Metric ' + tab_num + '</a></li>');
      $('#' + tab_list.attr('id') + ' a[href="#tab' + tab_tag + '"]').click(function(event) {
        event.preventDefault();
        $(this).tab('show');
      });

      $('#' + tab_list.attr('id') + ' a[href="#tab' + tab_tag + '"]').tab('show');

      $('label').click(function() {
        statuswolf_button(this);
      });

      $('.info-tooltip').tooltip({placement: 'bottom'});
      $('.info-tooltip-right').tooltip({placement: 'right'});
      $('.info-tooltip-left').tooltip({placement: 'left'});
      $('.info-tooltip-top').tooltip({placement: 'top'});
//      $('.info-tooltip').hover(function() {$(this).css('cursor', 'default')});

      return tab_num;
    }

    ,set_all_spans: function(item, widget)
    {
      var widget_id = widget.sw_graphwidget_containerid;
      var widget_list = $('.widget-container[data-widget-type="graphwidget"]');
      console.log('setting all widgets to span from ' + widget_id);
      $.each(widget_list, function(i, action_widget_element) {
        var action_widget_id = '#' + $(action_widget_element).attr('id');
        if ( action_widget_id !== widget_id)
        {
          var action_widget = $('#' + $(action_widget_element).attr('id')).data('sw-graphwidget');
          action_widget.query_data.period = widget.query_data.period;
          action_widget.query_data.time_span = widget.query_data.time_span;
          action_widget.query_data.end_time = widget.query_data.end_time;
          action_widget.query_data.start_time = widget.query_data.start_time;
          action_widget.populate_search_form(action_widget.query_data, action_widget);
        }
      })
    }

    ,dropdown_menu_handler: function(item, widget)
    {
      var button = $(item).parent().parent().children('span');
      var action = $(item).attr('data-action');
      $(button).children('.graph-widget-button-label').text($(item).text());
      $(button).children('div.ds-interval').attr('data-value', $(item).children('span').attr('data-value'));
      if ($(item).parent().attr('id') === "time-span-options" + widget.uuid)
      {
        $(button).children('div#time-span' + widget.uuid).attr('data-ms', $(item).children('span').attr('data-ms')).text();
      }
    }

    ,build_saved_search_menu: function(widget)
    {
      var user_id = document._session_data.user_id;
      var api_url = widget.options.sw_url + 'api/get_saved_searches';

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
          $.ajax({
            url: widget.options.sw_url + "api/load_saved_search/" + search_id
            ,type: 'GET'
            ,dataType: 'json'
            ,success: function(data) {
              saved_query = data;
              delete(saved_query.private);
              delete(saved_query.save_span);
              delete(saved_query.user_id);
              widget.populate_search_form(saved_query, widget);
            }
          });
        }

      });
    }

    ,populate_search_form: function(query_data, widget, force_prompt_user)
    {

      var prompt_user = false;
      var widget_num = widget.uuid;
      if (typeof force_prompt_user !== "undefined")
      {
        prompt_user = true;
      }
      if (typeof query_data.period === "undefined" && typeof query_data.time_span === "undefined")
      {
        query_data.period = 'date-search';
      }
      else if (typeof query_data.period === "undefined" && typeof query_data.time_span !== "undefined")
      {
        query_data.period = 'span-search';
      }

      if (typeof query_data.history_graph === "undefined")
      {
        if (typeof query_data['history-graph'] !== "undefined")
        {
          query_data.history_graph = query_data['history-graph'];
          delete(query_data['history-graph']);
        }
        else
        {
          query_data.history_graph = 'no';
        }
      }

      if (typeof query_data.title !== "undefined" && query_data.title.length > 1)
      {
        $('h3#search-title' + widget.uuid).text(query_data.title).removeClass('search-title-prompt');
        $('.widget-title > input[name="search-title-input' + widget.uuid + '"]').val(query_data.title);
      }

      if (widget.options.datasource === "OpenTSDB")
      {
        var method_map = {sum: 'Sum', avg: 'Average', min: 'Minimum Value', max: 'Maximum Value', dev: 'Standard Deviation'};

        if (query_data['auto_update'] === "true") {
          $('label[for="auto-update-button' + widget_num + '"]').click();
          $('label[for="auto-update-button' + widget_num + '"]').parent('.push-button').addClass('pushed');
          $('label[for="auto-update-button' + widget_num + '"]').children('span.iconic').removeClass('iconic-x-alt red').addClass('iconic-check-alt green');
        }
        if (query_data.history_graph.match(/anomaly/))
        {
          var el = $('input[data-target="history-anomaly' + widget_num + '"]').parent('label');
          $(el).parent('div.toggle-button').addClass('toggle-on');
          $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
          $(el).children('input').attr('checked', 'Checked');
          $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
          $('input[data-target="history-anomaly' + widget_num + '"]').click();
        }
        else if (query_data.history_graph.match(/wow/))
        {
          var el = $('input[data-target="history-wow' + widget_num + '"]').parent('label');
          $(el).parent('div.toggle-button').addClass('toggle-on');
          $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
          $(el).children('input').attr('checked', 'Checked');
          $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
          $('input[data-target="history-wow' + widget_num + '"]').click();
        }
        if (query_data.period === "span-search")
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
          var el = $('input[data-target="graph-widget-dates' + widget_num + '"]').parent('label');
          $(el).parent('div.toggle-button').addClass('toggle-on');
          $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
          $(el).children('input').attr('checked', 'Checked');
          $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
          $('input[data-target="graph-widget-dates' + widget_num + '"]').click();
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
          if (metric_num > 1)
          {
            var metric_tab = $('div#tab' + widget.uuid + '-' + metric_num);
            if (metric_tab.length == 0)
            {
              widget.metric_count = widget.add_tab(widget, i, widget.uuid);
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
          if (metric.lerp && metric.lerp !== "false")
          {
            $('input#lerp-button' + widget_num + '-' + metric_num).siblings('label').click();
            $('input#lerp-button' + widget_num + '-' + metric_num).parent('.push-button').addClass('pushed');
            $('input#lerp-button' + widget_num + '-' + metric_num).siblings('label').children('span.iconic').addClass('iconic-x-alt green').removeClass('iconic-check-alt red');
            $('input#lerp-button' + widget_num + '-' + metric_num).siblings('label').children('span.binary-label').text('Yes');
          }
          if (metric.rate && metric.rate !== "false")
          {
            $('input#rate-button' + widget_num + '-' + metric_num).siblings('label').click();
            $('input#rate-button' + widget_num + '-' + metric_num).parent('.push-button').addClass('pushed');
            $('input#rate-button' + widget_num + '-' + metric_num).siblings('label').children('span.iconic').removeClass('iconic-x-alt red').addClass('iconic-check-alt green');
            $('input#rate-button' + widget_num + '-' + metric_num).siblings('label').children('span.binary-label').text('Yes');
          }
          if (metric.y2 && metric.rate !== "false")
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
          widget.go_click_handler('', widget);
        }

      }
    }

    ,go_click_handler: function(event, widget)
    {

      var widget_num = widget.uuid;
      var widget_element = $(widget.element);
      widget.query_data = {};
      widget.query_data.downsample_master_interval = 0;
      widget.query_data.new_query = true;
      var input_error = false;

      $('#graph-title' + widget_num).empty();
      $('#legend' + widget_num).empty();

      if (typeof widget.autoupdate_interval !== "undefined")
      {
        console.log('clearing auto-update timer');
        clearInterval(widget.autoupdate_interval);
        delete widget.autoupdate_interval;
      }

      if (widget.options.datasource === "OpenTSDB")
      {
        var methods = {'sum': 'sum', 'average': 'avg', 'minimum value': 'min', 'maximum value': 'max', 'standard deviation': 'dev'};
        // Map out all the elements we need to check
        var input_dates = $('div#graph-widget-dates' + widget_num);
        var input_time_span = $('div#time-span' + widget_num);
        var input_autoupdate = $('input#auto-update-button' + widget_num);
        var history_buttons = widget_element.find('input:radio[name="history-graph"]');
        widget.query_data.title = $('input[name="search-title-input' + widget_num + '"]').val();
        $.each(history_buttons, function(i, history_type) {
          if ($(history_type).attr('checked'))
          {
            input_history = history_type;
          }
        });
        if (typeof input_history === "undefined")
        {
          input_error = true;
        }


        // Date range validation
        var start, end;
        var date_span_option = widget_element.find('input:radio[name="date-span"]:checked').val();
        if (date_span_option === 'date-search')
        {
          console.log('date search - checking start and end times');
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
          widget.query_data['period'] = 'date-search';
          widget.query_data['time_span'] = end - start;
        }
        else
        {
          console.log('span search - getting start and end times');
          end = new Date.now().getTime();
          end = parseInt(end / 1000);
          var span = parseInt($(input_time_span).attr('data-ms'));
          start = (end - span);
          var jstart = new Date(start * 1000).toString('yyyy/MM/dd HH:mm:ss');
          var jend = new Date(end * 1000).toString('yyyy/MM/dd HH:mm:ss');
          $(input_dates).children('div#start-time' + widget_num).children('input[name="start-time"]').val(jstart).change();
          $(input_dates).children('div#end-time' + widget_num).children('input[name="end-time"]').val(jend).change();
          widget.query_data['period'] = 'span-search';
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
        widget.query_data.history_graph = $(input_history).val();
        if (widget.query_data.history_graph === 'no')
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
          else if (widget.query_data.history_graph === "wow" && (end - start) / 60 > 10080)
          {
            alert('Week-over-week history comparison searches are limited to 1 week or less of data');
            $('input:text[name=start-time]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
            input_error = true;
          }
          else if (widget.query_data.history_graph === "anomaly" && (end - start) > 86400)
          {
            alert('Anomaly detection searches are limited to 1 day or less');
            $('input:text[name=start-time]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
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

      }

      // If we made it this far without errors in the form input, then
      // we build us a graph
      if (input_error == false)
      {
        var graph_element = $('#graphdiv' + widget_num);
        // Make sure the graph display div is empty
        graph_element.empty();
        // Clear the graph legend
        $('#legend-hover' + widget_num).empty();
        // Load the waiting spinner
        graph_element.append('<div class="bowlG">' +
          '<div class="bowl_ringG"><div class="ball_holderG">' +
          '<div class="ballG"></div></div></div></div>');
        widget.sw_graphwidget_fronttitle.removeClass('nodisplay');
        $(widget.element).children('.widget').removeClass('flipped');
        graph_element.append('<div id="status-box' + widget_num + '" style="width: 100%; text-align: center;">' +
          '<p id="status-message' + widget_num + '"></p></div>');
        widget.init_query(widget.query_data, widget);
      }
      else
      {
        if (! widget_element.children('.widget').hasClass('flipped'))
        {
          widget_element.children('.widget').addClass('flipped');
        }
      }


    }

    ,init_query: function(query_data, widget)
    {

      widget_num = widget.uuid;

      if (widget.options.datasource === "OpenTSDB")
      {

        // Start deferred query for metric data
        $.when(widget.opentsdb_search(query_data, widget)).then(
          // done: Send the data over to be parsed
          function(data)
          {
            $.when(widget.process_timeseries_data(data, query_data, widget)).then(
              // done: Build the graph
              function(data)
              {
                widget.build_line_graph(data.graphdata, data.querydata, widget);
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
              .css({'padding-top': '5%', 'margin-top': '0', 'margin-bottom': '5px', 'width': '120px', 'height': '60px'})
              .html('<img src="' + widget.options.sw_url + 'app/img/error.png" style="width: 120px; height: 60px;">');

            widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).empty()
              .append('<p>' + status.shift() + '</p>');

            if (typeof status[0] === "string")
            {
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
          }
        );

      }

    }

    ,opentsdb_search: function(query_data, widget)
    {


      var query_object = new $.Deferred();
      var widget_num = widget.uuid;
      var status = widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).children('#status-message' + widget_num);

      // Generate (or find the cached) model data for the metric
      if (query_data.history_graph == "anomaly")
      {
        status.html('<p>Fetching Metric Data</p>');
        $.when(widget.get_opentsdb_data_anomaly(query_data, widget)
          .done(function(data) {
            query_object.resolve(data);
          })
          .fail(function(data) {
            query_object.reject(data);
          })
        );
      }
      // Search current and previous week for metric data
      else if (query_data.history_graph == "wow")
      {
        status.html('<p>Fetching Week-Over-Week Data</p>');
        $.when(widget.get_opentsdb_data_wow(query_data, widget)
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
        $.when(widget.get_opentsdb_data(query_data, widget)
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

    ,get_opentsdb_data: function(query_data, widget)
    {

      if (typeof widget.ajax_request !== 'undefined')
      {
        widget.ajax_request.abort();
      }

      widget.ajax_object = new $.Deferred();

      widget.ajax_request = $.ajax({
          url: widget.options.sw_url + "adhoc/search/OpenTSDB"
          ,type: 'POST'
          ,data: query_data
          ,dataType: 'json'
          ,timeout: 120000
        })
        ,chain = widget.ajax_request.then(function(data) {
          return(data);
        });

      chain.done(function(data) {
        if (data[0] === "error")
        {
          widget.ajax_request.abort();
          widget.ajax_object.reject(data[1])
        }
        else if (Object.getOwnPropertyNames(data).length <= 5)
        {
          widget.ajax_request.abort();
          widget.ajax_object.reject(["0", "Query returned no data"]);
        }
        else
        {
          delete widget.ajax_request;
          widget.ajax_object.resolve(data);
        }
      });

      return widget.ajax_object.promise();

    }

    ,get_opentsdb_data_wow: function(query_data, widget)
    {

      var status = widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).children('#status-message' + widget_num);

      if (typeof widget.ajax_request !== 'undefined')
      {
        console.log('Previous request still in flight, aborting');
        widget.ajax_request.abort();
      }

      widget.ajax_object = new $.Deferred();

      var metric_data = {};

      widget.ajax_request = $.ajax({
          url: widget.options.sw_url + "adhoc/search/OpenTSDB"
          ,type: 'POST'
          ,data: query_data
          ,data_type: 'json'
          ,timeout: 120000
        })
        ,chained = widget.ajax_request.then(function(data) {
          metric_data[0] = eval('(' + data + ')');
          if (metric_data[0][0] === "error")
          {
            widget.ajax_request.abort();
            widget.ajax_object.reject(metric_data[0][1])
          }
          else if (Object.keys(metric_data[0]).length <= 5)
          {
            widget.ajax_request.abort();
            widget.ajax_object.reject(["0", "Current week query returned no data"]);
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
            current_keys = Object.keys(metric_data[0])
            current_key = 'Current - ' + current_keys[0];
            metric_data.legend = [];
            metric_data.legend[current_key] = 'Current';
            metric_data[current_key] = metric_data[0][current_keys[0]];
            delete metric_data[0];
            var past_query = $.extend(true, {}, query_data);
            var query_span = parseInt(query_data.end_time) - parseInt(query_data.start_time);
            past_query.end_time = parseInt(query_data.end_time - 604800);
            past_query.start_time = past_query.end_time - query_span;
            past_query.previous = true;
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
          widget.ajax_request.abort();
          widget.ajax_object.reject();
        }
        else
        {
          metric_data[1] = eval('(' + data + ')');
          if (metric_data[1][0] === "error")
          {
            widget.ajax_request.abort();
            widget.ajax_object.reject(metric_data[1][1])
          }
          else if (Object.getOwnPropertyNames(metric_data[1]).length <= 5)
          {
            widget.ajax_request.abort();
            widget.ajax_object.reject(["0", "Previous week query returned no data"]);
          }
          else
          {
            delete metric_data[1].start;
            delete metric_data[1].end;
            delete metric_data[1].query_url;
            past_keys = Object.keys(metric_data[1]);
            past_key = 'Previous - ' + past_keys[0];
            metric_data.legend[past_key] = 'Previous';
            metric_data[past_key] = metric_data[1][past_keys[0]];
            delete metric_data[1];
            $.each(metric_data[past_key], function(index, entry) {
              entry.timestamp = parseInt(entry.timestamp + 604800);
            });
            delete widget.ajax_request;
            widget.ajax_object.resolve(metric_data);
          }
        }
      });

      return widget.ajax_object.promise();

    }

    ,get_opentsdb_data_anomaly: function(query_data, widget)
    {

      var status = widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).children('#status-message' + widget_num);

      if (typeof widget.ajax_request !== 'undefined')
      {
        console.log('Previous request still in flight, aborting');
        widget.ajax_request.abort();
      }

      widget.ajax_object = new $.Deferred();

      var metric_data = {};
      var data_for_detection = [];

      widget.ajax_request = $.ajax({
          url: widget.options.sw_url + "adhoc/search/OpenTSDB"
          ,type: 'POST'
          ,data: query_data
          ,dataType: 'json'
          ,timeout: 120000
        })
        ,chained_request_1 = widget.ajax_request.then(function(data) {
          if (data[0] === "error")
          {
            widget.ajax_request.abort();
            widget.ajax_object.reject(data[1])
          }
          else if (Object.getOwnPropertyNames(data).length <= 5)
          {
            widget.ajax_request.abort();
            widget.ajax_object.reject(["0", "Query returned no data"]);
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
            widget.ajax_request.abort();
            widget.ajax_object.reject();
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
              widget.ajax_request.abort();
              widget.ajax_object.reject(pre_period_data[1])
            }
            else if (Object.getOwnPropertyNames(pre_period_data).length <= 5)
            {
              widget.ajax_request.abort();
              widget.ajax_object.reject(["0", "Query returned no data"]);
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
          widget.ajax_request.abort();
          widget.ajax_object.reject();
        }
        else
        {
          metric_data['anomalies'] = eval('(' + data + ')');
          if (metric_data[0] === "error")
          {
            widget.ajax_request.abort();
            widget.ajax_object.reject(metric_data[1])
          }
          else
          {
            widget.ajax_object.resolve(metric_data);
          }
        }
      }).fail(function(data) {
          widget.ajax_request.abort();
          widget.ajax_object.reject([data.status, data.statusText]);
        });

      return widget.ajax_object.promise();

    }

    ,process_timeseries_data: function(data, query_data, widget)
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
      var legend_map = data.legend;
      delete data.start;
      delete data.end;
      delete data.query_url;
      delete data.cache_key;
      delete data.query_cache;
      delete data.legend;
      if (query_data.history_graph == "anomaly")
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
            if (query_data.history_graph == "anomaly")
            {
              query_data.metrics[0]['history_graph'] = "anomaly";
            }
            labels.push(legend_map[series]);

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
                else if (typeof document._sw_conf.graphing.treat_null_as_zero !== "undefined" && document._sw_conf.graphing.treat_null_as_zero === "1")
                {
                  buckets[timestamp].push(0);
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
        if (query_data.history_graph == "anomaly")
        {
          graph_data.anomalies = anomalies;
        }
      }

      var parsed_data = {graphdata: graph_data, querydata: query_data};

      parse_object.resolve(parsed_data);

      return parse_object.promise();

    }

    ,build_line_graph: function(data, query_data, widget)
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
      var graph_title_id = 'graph-title' + widget.uuid;
      widget.sw_graphwidget_frontmain.css('height', widget.sw_graphwidget.innerHeight());
      widget.sw_graphwidget_frontmain.children('.graphdiv').css('height', (widget.sw_graphwidget_frontmain.innerHeight() - widget.sw_graphwidget_frontmain.children('.legend-container').outerHeight(true)));
      widget.sw_graphwidget_frontmain.children('.legend-container').css('width', widget.sw_graphwidget_frontmain.innerWidth())
        .removeClass('hidden');

      $('#' + graph_title_id).empty();
      if (typeof query_data.title !== "undefined")
      {
        $('#' + graph_title_id).append('<h1>' + query_data.title + '</h1>');
      }

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

      if (graph_labels.length > 9)
      {
        $('#legend' + widget.uuid).css({
          '-webkit-columns': 'auto 4'
          ,'-moz-columns': 'auto 4'
          ,columns: 'auto 4'
        });
      }
      else if (graph_labels.length > 6)
      {
        $('#legend' + widget.uuid).css({
          '-webkit-columns': 'auto 2'
          ,'-moz-columns': 'auto 3'
          ,columns: 'auto 3'
        });
      }
      else if (graph_labels.length > 3)
      {
        $('#legend' + widget.uuid).css({
          '-webkit-columns': 'auto 2'
          ,'-moz-columns': 'auto 2'
          ,columns: 'auto 2'
        });
      }

      var x_space = $('#' + graphdiv_id).width() / 12;
      var y_space = $('#' + graphdiv_id).height() / 12;
      var g_width = $('#' + graphdiv_id).innerWidth() * .95;

      widget.g = new Dygraph(
        document.getElementById(graphdiv_id)
        ,dygraph_format
        ,{
          labels: graph_labels
          ,labelsDiv: 'legend-hover' + widget.uuid
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
      var right_axis_labels = [];
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
            right_axis_labels.push(label);
          });
        }
      });

      $('.widget-footer-btn.hidden').removeClass('hidden');

      // Set up the anomaly highlighting if requested
      if (query_data.history_graph == "anomaly")
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

      $.each(widget.g.colorsMap_, function(legend_key, color) {
        if (right_axis.length > 0)
        {
          if ($.inArray(legend_key, right_axis_labels) >= 0)
          {
            $('#legend' + widget.uuid).append('<span style="color: ' + color + '"><b>' + legend_key + ' </b><span class="iconic iconic-play"></span></span>');
          }
          else
          {
            $('#legend' + widget.uuid).append('<span style="color: ' + color + '"><span class="iconic iconic-play rotate-180"></span><b> ' + legend_key + '</b></span>');
          }
        }
        else
        {
          $('#legend' + widget.uuid).append('<span style="color: ' + color + '"><b>' + legend_key + '</b></span>');
        }
      });

      widget.sw_graphwidget_fronttitle.addClass('nodisplay');
      $('#' + graphdiv_id).css({
        height: widget.sw_graphwidget_frontmain.innerHeight() - ($('#' + graphdiv_id).siblings('div.legend-container').outerHeight(true))
        ,top: '10px'
      });
      widget.g.resize();

      // Set the interval for adding new data if Auto Update is selected
      if (query_data['auto_update'])
      {
        console.log('setting auto-update timer');
        widget.autoupdate_interval = setInterval(function() {
          var new_start = parseInt(series_times[0]);
          var new_end = new Date.now().getTime();
          new_end = parseInt(new_end / 1000);
          query_data.start_time = new_start;
          query_data.end_time = new_end;
          query_data.new_query = false;
          $.when(widget.opentsdb_search(query_data, widget)).then(function(data)
          {
            $.when(widget.process_timeseries_data(data, query_data, widget)).then(
              function(data)
              {
                var dygraph_update = new Array();
                graph_data = data.graphdata.data;
                var end_trim = 0;
                for (var timestamp in graph_data) {
                  if (graph_data.hasOwnProperty(timestamp))
                  {
                    if ($.inArray(timestamp, series_times) >= 0)
                    {
                      end_trim++;
                    }
                    else
                    {
                      series_times.push(timestamp);
                    }
                    jtime = new Date(parseInt(timestamp * 1000));
                    values = [jtime];
                    values = values.concat(graph_data[timestamp]);
                    dygraph_update.push(values);
                  }
                }
                dygraph_format.splice(0, dygraph_update.length);
                if (end_trim > 0)
                {
                  dygraph_format.splice(-end_trim, end_trim);
                }
                dygraph_format = dygraph_format.concat(dygraph_update);
                widget.g.updateOptions({'file': dygraph_format});
                series_times = series_times.splice(-4, 4);
              }
            );
          });
        }, 300 * 1000);
      }

      widget.sw_graphwidget_frontmain.children('div.graphdiv').mouseenter(function() {
//        var graphdiv = widget.sw_graphwidget_frontmain.children('div.graphdiv');
        var legend_box = $(this).siblings('div.legend-container');
        var title_bar = widget.sw_graphwidget_fronttitle;
        title_bar.removeClass('nodisplay');
        $(this).css({
          height: widget.sw_graphwidget_frontmain.innerHeight() - (legend_box.outerHeight(true) + title_bar.outerHeight())
          ,top: title_bar.outerHeight() + 10
        });
        widget.g.resize();
      }).mouseleave(function() {
//          var graphdiv = widget.sw_graphwidget_frontmain.children('div.graphdiv');
          var legend_box = $(this).siblings('div.legend-container');
          var title_bar = widget.sw_graphwidget_fronttitle;
          $(this).css({
            height: widget.sw_graphwidget_frontmain.innerHeight() - (legend_box.outerHeight(true))
            ,top: '10px'
          });
          title_bar.addClass('nodisplay');
          widget.g.resize();
        });

    }

	})
}(jQuery));
