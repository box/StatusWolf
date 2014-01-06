/**
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 15 July 2013
 *
 */


(function ($, undefined) {

    var sw_graphwidget_classes = 'widget';

    $.widget('sw.graphwidget', {
        version: "1.0",
        defaultElement: "<div>",
        options: {
            disabled: null,
            label: null,
            datasource: "OpenTSDB",
            legend: "on",
            sw_url: null,
            nointerpolation: false
        }, _create: function () {

            var that = this
                , options = this.options
                , sw_graphwidget
                , sw_graphwidget_container
                , sw_graphwidget_containerid
                , sw_graphwidget_front
                , sw_graphwidget_back
                , sw_graphwidget_close
                , sw_graphwidget_backtitle
                , sw_graphwidget_backfooter
                , sw_graphwidget_frontmain
                , sw_graphwidget_backmain
                , sw_graphwidget_savedsearchesmenu
                , sw_graphwidget_datasourcemenu
                , sw_graphwidget_searchform
                , sw_graphwidget_action
                , sw_graphwidget_querycancelbutton
                , sw_graphwidget_gobutton
                , sw_graphwidget_datasource;

            if (this.options.sw_url === null) {
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
            sw_graphwidget_frontmain = (this.sw_graphwidget_frontmain = $('<div>'))
                .addClass('widget-main')
                .addClass('front')
                .appendTo(sw_graphwidget_front);
            sw_graphwidget_backtitle = (this.sw_graphwidget_backtitle = $('<div>'))
                .addClass('flexy widget-title')
                .appendTo(sw_graphwidget_back);
            sw_graphwidget_backmain = (this.sw_graphwidget_backmain = $('<div>'))
                .addClass('widget-main')
                .addClass('back')
                .appendTo(sw_graphwidget_back);
            sw_graphwidget_backfooter = (this.sw_graphwidget_backfooter = $('<div>'))
                .addClass('flexy widget-footer')
                .appendTo(sw_graphwidget_back);

            sw_graphwidget_action = (this.sw_graphwidget_action = $('<div>'))
                .addClass('action-widget dropdown')
                .append('<span data-toggle="dropdown">' +
                    '<span class="iconic iconic-cog"></span></span>' +
                    '<ul class="dropdown-menu sub-menu-item widget-action-options" role="menu">' +
                    '<li data-menu-action="maximize_widget"><span class="maximize-me">Maximize</span></li>' +
                    '<li data-menu-action="edit_params"><span>Edit Parameters</span></li>' +
                    '<li data-menu-action="show_graph_data"><span>Show graph data</span></li>' +
                    '<li class="clone-widget" data-parent="' + that.element.attr('id') + '"><span>Clone Widget</span></li>' +
                    '<li class="dropdown"><span>Options</span><span class="iconic iconic-play"></span>' +
                    '<ul class="dropdown-menu sub-menu graphwidget-options-menu">' +
                    '<li data-menu-action="set_all_spans"><span>Use this time span for all Graph Widgets</span></li>' +
                    '<li data-menu-action="set_all_tags_form"><span>Set tags for all Graph Widgets</span></li>' +
                    '<li data-menu-action="add_tags_to_all_form"><span>Add tag(s) to all Graph Widgets</span></li></ul></li>' +
                    '<li data-menu-action="go_click_handler"><span>Refresh Graph</span></li></ul>')
                .appendTo(sw_graphwidget_frontmain);
            $('li.clone-widget').click(function (event) {
                event.stopImmediatePropagation();
                that.clone_widget($(this).attr('data-parent'));
            });

            sw_graphwidget_close = (this.sw_graphwidget_close = $('<div>'))
                .addClass('close-widget')
                .click(function (event) {
                    event.preventDefault();
                    $(sw_graphwidget_container).addClass('transparent');
                    var that_id = sw_graphwidget_containerid;
                    setTimeout(function () {
                        that.destroy(event);
                        sw_graphwidget_container.remove();
                    }, 600);
                })
                .append('<span class="iconic iconic-x">')
                .appendTo(sw_graphwidget_frontmain);

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
                .click(function () {
                    that.flip_to_front();
                })
                .append('<span class="iconic iconic-x-alt"><span class="font-reset"> Cancel</span></span>')
                .appendTo(sw_graphwidget_backfooter);

            sw_graphwidget_backfooter.append('<div class="glue1">');

            sw_graphwidget_gobutton = (this.sw_graphwidget_gobutton = $('<div>'))
                .addClass("widget-footer-button right-button go-button")
                .click(function (event) {
                    that.go_click_handler(event);
                })
                .append('<span class="iconic iconic-bolt"><span class="font-reset"> Go</span></span>')
                .appendTo(sw_graphwidget_backfooter);

            // Define the div for the search form
            sw_graphwidget_searchform = (this.sw_graphwidget_searchform = $('<div>'))
                .addClass('graph-widget-search-form')
                .appendTo(sw_graphwidget_backmain);

            sw_graphwidget_datasource = $.trim($(sw_graphwidget_datasourcemenu).children('span.widget-title-button').children('span.active-datasource').text().toLowerCase());
            that.build_search_form();

            $(sw_graphwidget).on('click', 'li[data-menu-action]', function (event) {
                event.preventDefault();
                var action = $(this).attr('data-menu-action');
                that[action](this, that);
            });

            $(sw_graphwidget).on('click', 'li[data-action]', function () {
                that.dropdown_menu_handler(this);
            });

        }, _destroy: function () {
            console.log('_destroy called');
            if (typeof this.autoupdate_timer !== "undefined") {
                clearTimeout(this.autoupdate_timer);
            }
            if (typeof this.ajax_request !== "undefined") {
                this.ajax_request.abort();
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
            this.sw_graphwidget_close.remove();
            this.sw_graphwidget_frontmain.remove();
            this.sw_graphwidget_front.remove();
            this.sw_graphwidget_back.remove();
            this.sw_graphwidget.remove();
        }, get_sw_url: function () {
            var base_uri = window.location.origin + '/';
            var path_bits = window.location.pathname.toString().split('/');
            if (path_bits[1] !== "dashboard" && path_bits[1] !== "adhoc") {
                base_uri += path_bits[1] + '/';
            }
            return base_uri;
        }

        // Add a new widget as a duplicate of the selected widget
        , clone_widget: function (widget_id) {
            var widget = $('#' + widget_id).data('sw-graphwidget');
            console.log('Cloning widget ' + widget.element.attr('id'));
            var username = document._session_data.username;
            var new_widget_id = "widget" + md5(username + new Date.now().getTime());
            console.log('Adding new widget ' + new_widget_id);
            $('#dash-container').append('<div class="widget-container" id="' + new_widget_id + '" data-widget-type="graphwidget">');
            var new_widget = $('div#' + new_widget_id).graphwidget(widget.options);
            new_widget.addClass('cols-' + document._session_data.data.dashboard_columns);
            new_widget_object = $(new_widget).data('sw-graphwidget');
            new_widget_object.populate_search_form(widget.query_data, 'clone');
            $('#search-title' + new_widget_object.uuid).css('width', $('#search-title' + widget.uuid).width());
            $('#' + new_widget_object.element.attr('id')).removeClass('transparent');
        }, maximize_widget: function (element) {
            var widget = this;
            if ($(widget.sw_graphwidget_container).hasClass('maximize-widget')) {
                $(widget.sw_graphwidget_container).removeClass('maximize-widget');
                $(widget.sw_graphwidget_container).addClass('cols-' + document._session_data.data.dashboard_columns);
                $('body').removeClass('no-overflow');
                $('.navbar').removeClass('hidden');
                $('#' + widget.element.attr('id') + ' span.maximize-me').text('Maximize');
                widget.resize_graph();
            }
            else {
                $(window).scrollTop(0);
                $(widget.sw_graphwidget_container).removeClass('cols-' + document._session_data.data.dashboard_columns);
                $(widget.sw_graphwidget_container).addClass('maximize-widget');
                $('.navbar').addClass('hidden');
                $('body').addClass('no-overflow');
                $('#' + widget.element.attr('id') + ' span.maximize-me').text('Minimize');
                widget.resize_graph();
            }
        }, resize_graph: function () {
            var widget = this;
            var widget_main = widget.sw_graphwidget_frontmain
                , graph_div = widget.sw_graphwidget_frontmain.children('div.graphdiv')
                , graph_div_offset = graph_div.position().top
                , graph_legend = widget.sw_graphwidget_frontmain.children('div.legend-container');
            graph_legend.css('width', widget_main.innerWidth());
            widget_main.css('height', widget.sw_graphwidget.innerHeight());
            graph_div.css('height', widget_main.innerHeight() - graph_legend.outerHeight(true) - graph_div_offset);
            widget.graph.width = graph_div.innerWidth() - widget.graph.margin.left - widget.graph.margin.right;
            widget.graph.height = graph_div.innerHeight() - widget.graph.margin.top - widget.graph.margin.bottom;
            widget.svg.attr({width: graph_div.innerWidth(), height: graph_div.innerHeight()});
            widget.svg.select('#clip' + widget.uuid).select('rect')
                .attr('width', widget.graph.width)
                .attr('height', widget.graph.height);
            widget.graph.zoombox.selectAll('rect.background')
                .attr('height', widget.graph.height)
                .attr('width', widget.graph.width);
            widget.graph.y.range([widget.graph.height, 0]);
            widget.graph.y_axis.tickSize(-widget.graph.width, 0);
            widget.svg.select('.y.axis').call(widget.graph.y_axis);
            widget.svg.selectAll('.y.axis text').attr('dy', '0.75em');
            if (widget.graph.right_axis == true) {
                widget.graph.y1.range([widget.graph.height, 0]);
                widget.svg.select('.y1.axis')
                    .attr('transform', 'translate(' + widget.graph.width + ', 0)')
                    .call(widget.graph.y_axis_right);
                widget.svg.selectAll('.y1.axis text').attr('dy', '0.75em');
            }
            widget.graph.x.range([0, widget.graph.width]);
            widget.graph.x_axis.tickSize(-widget.graph.height, 0);
            widget.svg.select('.x.axis')
                .attr('transform', 'translate(0,' + widget.graph.height + ')')
                .call(widget.graph.x_axis);
            $(widget.sw_graphwidget_containerid + ' text.graph-title').remove();
            widget.svg.select('.x.axis')
                .append('text')
                .classed('graph-title', 1)
                .classed('hidden', 1)
                .attr('text-anchor', 'middle')
                .text(widget.query_data.title);
            widget.format_graph_title();
            var dots = widget.svg.selectAll('.dots');
            dots.selectAll('.dot')
                .attr('cx', function (d) {
                    return widget.graph.x(d.date);
                })
                .attr('cy', function (d) {
                    if (d3.select(this).attr('data-axis') === "right") {
                        return widget.graph.y1(d.value);
                    } else {
                        return widget.graph.y(+d.value);
                    }
                });
            if (widget.query_data['history_graph'] === "anomaly") {
                widget.svg.selectAll('.anomaly-bars').selectAll('rect')
                    .attr('x', function (d) {
                        return widget.graph.x(d.start_time);
                    })
                    .attr('height', widget.graph.height)
                    .attr('width', function (d) {
                        return (widget.graph.x(d.end_time) - widget.graph.x(d.start_time));
                    });
            }
            var metric = widget.svg.selectAll('.metric.left');
            metric.selectAll('path')
                .attr('d', function (d) {
                    return widget.graph.line(d.values);
                });
            if (widget.graph.right_axis == true) {
                widget.svg.selectAll('.metric.right').selectAll('path')
                    .attr('d', function (d) {
                        return widget.graph.line_right(d.values);
                    });
            }
        }, edit_params: function () {
            var widget = this;
            widget.start_height = widget.sw_graphwidget.parent().height();
            widget.start_width = widget.sw_graphwidget.parent().width();
            widget.widget_position = widget.sw_graphwidget.parent().position();
            widget.sw_graphwidget.addClass('flipped');
            $('div.container').after('<div class="bodyshade transparent">');
            setTimeout(function () {
                widget.sw_graphwidget.parent().css({position: 'absolute', height: widget.start_height + 'px', width: widget.start_width + 'px', top: widget.widget_position.top, left: widget.widget_position.left, 'z-index': '500'})
                    .after('<div class="spacer-box" style="display: inline-block; width: ' + widget.start_width +
                        'px; height: ' + widget.start_height +
                        'px; margin: ' + widget.sw_graphwidget.parent().css('margin') + '"></div>');
                widget.sw_graphwidget.parent().css({top: '15%', left: '15%', height: '500px', width: '800px'});
                $('div.bodyshade').removeClass('transparent');
            }, 700);
        }, flip_to_front: function () {
            var widget = this;
            if ($('div.bodyshade').length > 0) {
                $('div.bodyshade').addClass('transparent');
                widget.sw_graphwidget.parent().css({width: widget.start_width, height: '', top: widget.widget_position.top, left: widget.widget_position.left});
                setTimeout(function () {
                    widget.sw_graphwidget.parent().css({position: '', top: '', left: '', 'z-index': '', height: '', width: ''})
                        .siblings('div.spacer-box').remove();
                    $('div.bodyshade').remove();
                }, 600);
                setTimeout(function () {
                    if (typeof widget.svg !== "undefined") {
                        widget.resize_graph();
                    }
                    widget.sw_graphwidget.removeClass('flipped');
                }, 700);
            }
            else {
                widget.sw_graphwidget.removeClass('flipped');
                if ($('#' + widget.element.attr('id')).hasClass('transparent')) {
                    $('#' + widget.element.attr('id')).removeClass('transparent');
                }
            }
        }, hide_legend: function (button) {
            var widget = this;
            if (typeof button === "undefined") {
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
            if (typeof widget.svg !== "undefined") {
                widget.resize_graph();
            }
            widget.options.legend = 'off';
        }, show_legend: function (button) {
            var widget = this;
            if (typeof button === "undefined") {
                button = widget.sw_graphwidget_frontmain.children('div.legend-container').children('button.legend-toggle');
            }
            $(button).removeClass('legend-show');
            $(button).addClass('legend-hide');
            $(button).parents('div.legend-container').removeClass('hidden-legend');
            $(button).siblings('div.legend').removeClass('nodisplay');
            $(button).parents('div.legend-container').siblings('div.graphdiv')
                .css('height', widget.sw_graphwidget_frontmain.innerHeight() - $(button).parents('div.legend-container').outerHeight(true));
            $(button).children('span.iconic').removeClass('rotate-90r').addClass('rotate-90');
            if (typeof widget.svg !== "undefined") {
                widget.resize_graph();
            }
            widget.options.legend = 'on'
        }, build_search_form: function () {

            var widget = this;
            if (widget.options.datasource === "OpenTSDB") {
                var widget_num = widget.uuid
                    , widget_element = widget.element;

                widget.metric_count = 0;

                var anomaly_span_menu = '<li data-action="set-span"><span data-ms=600>10 minutes</span></li>' +
                        '<li data-action="set-span"><span data-ms=1800>30 minutes</span></li>' +
                        '<li data-action="set-span"><span data-ms=3600>1 Hour</span></li>' +
                        '<li data-action="set-span"><span data-ms=7200>2 Hours</span></li>' +
                        '<li data-action="set-span"><span data-ms=14400>4 Hours</span></li>' +
                        '<li data-action="set-span"><span data-ms=28800>8 Hours</span></li>' +
                        '<li data-action="set-span"><span data-ms=43200>12 Hours</span></li>' +
                        '<li data-action="set-span"><span data-ms=86400>1 Day</span></li>'
                    , wow_span_menu = '<li data-action="set-span"><span data-ms=600>10 minutes</span></li>' +
                        '<li data-action="set-span"><span data-ms=1800>30 minutes</span></li>' +
                        '<li data-action="set-span"><span data-ms=3600>1 Hour</span></li>' +
                        '<li data-action="set-span"><span data-ms=7200>2 Hours</span></li>' +
                        '<li data-action="set-span"><span data-ms=14400>4 Hours</span></li>' +
                        '<li data-action="set-span"><span data-ms=28800>8 Hours</span></li>' +
                        '<li data-action="set-span"><span data-ms=43200>12 Hours</span></li>' +
                        '<li data-action="set-span"><span data-ms=86400>1 Day</span></li>' +
                        '<li data-action="set-span"><span data-ms=604800>1 Week</span></li>'
                    , long_span_menu = '<li data-action="set-span"><span data-ms="600">10 minutes</span></li>' +
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
                    .after('<h3 id="search-title' + widget_num + '" class="search-title search-title-prompt"></h3>' +
                        '<input type="text" name="search-title-input' + widget_num + '" class="nodisplay">');
                $('#search-title' + widget_num)
                    .css('width', widget.sw_graphwidget_backtitle.innerWidth() -
                        (widget.sw_graphwidget_savedsearchesmenu.outerWidth() +
                            widget.sw_graphwidget_datasourcemenu.outerWidth() +
                            parseInt($('#search-title' + widget_num).css('margin-left'), 10))
                    );
                $('#search-title' + widget_num).text('Click to set search title');

                $('.widget-title').on('click', 'h3', function () {
                    var search_title_text = $(this).text();
                    $(this).addClass('nodisplay').removeClass('search-title');
                    var title_input = $(this).parent('div').children('input');
                    $(title_input).removeClass('nodisplay');
                    if ($(this).hasClass('search-title-prompt')) {
                        $(title_input).attr('placeholder', search_title_text);
                    }
                    else {
                        $(title_input).val(search_title_text);
                    }
                    $(title_input).css({
                        'font-size': $(this).css('font-size'), 'font-weight': $(this).css('font-weight')
                    }).focus();
                });

                $('.widget-title').on('blur', 'input', function () {
                    var search_title_text = $(this).siblings('h3').text();
                    var changed_title = $(this).val();
                    $(this).addClass('nodisplay');
                    if (changed_title.length > 1) {
                        $(this).siblings('h3').text(changed_title).removeClass('nodisplay search-title-prompt').addClass('search-title');
                    }
                    else {
                        $(this).siblings('h3').text(search_title_text).removeClass('nodisplay').addClass('search-title');
                    }
                });

                // The enter keypress when the search name edit field has focus
                // does the same as above
                $('.widget-title').on('keydown', 'input', function (event) {
                    if (event.which === 13) {
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
                    .click(function () {
                        widget.metric_count = widget.add_tab(widget.metric_count, widget_num);
                        $('input[name="metric' + widget_num + '-' + widget.metric_count + '"]').autocomplete({
                            minChars: 2, serviceUrl: widget.options.sw_url + 'api/tsdb_metric_list/', containerClass: 'autocomplete-suggestions dropdown-menu', zIndex: '', maxHeight: ''
                        });
                        if (widget.metric_count == 6) {
                            $(this).addClass('hidden');
                        }
                    })
                    .append('<span class="iconic iconic-plus-alt"><span class="font-reset"> Add Metric</span></span>');

                widget.sw_graphwidget_frontmain.append('<div id="graphdiv' + widget_num + '" class="graphdiv" style="width: 99%;">')
                    .append('<div id="legend-container' + widget_num + '" class="legend-container hidden">' +
                        '<button type="button" class="legend-toggle legend-hide"><span class="iconic iconic-play rotate-90"></span></button>' +
                        '<div id="legend' + widget_num + '" class="legend"></div></div>');

                if (widget.options.legend === "off") {
                    widget.hide_legend();
                }

                widget.sw_graphwidget_frontmain.on('click', 'button.legend-hide', function () {
                    widget.hide_legend(this);
                });

                widget.sw_graphwidget_frontmain.on('click', 'button.legend-show', function () {
                    widget.show_legend(this);
                });

                var auto_update = $(widget_element).find('div.auto-update').children('div.push-button');
                $(auto_update).children('input').attr('id', 'auto-update-button' + widget_num);
                $(auto_update).children('label').attr('for', 'auto-update-button' + widget_num);

                // Add the handler for the date/time picker and init the form objects
                var start_time = $('#start-time' + widget_num);
                var end_time = $('#end-time' + widget_num);
                loadScript(widget.options.sw_url + 'app/js/lib/bootstrap-datetimepicker.js', function () {
                    $(start_time).datetimepicker({collapse: false});
                    $(end_time).datetimepicker({collapse: false});
                });

                // If anomaly or week-over week displays are chosen update the
                // time span menu to limit the search to 1 week or less
                $('.section-toggle').click(function () {
                    var data_target = $(this).attr('data-target');
                    var section = $('#' + data_target);
                    section.removeClass('section-off').addClass('section-on').siblings('.section').addClass('section-off').removeClass('section-on');
                    if (data_target === 'history-anomaly' + widget_num) {
                        $('ul#time-span-options' + widget_num).html(anomaly_span_menu);
                        if ($('div#time-span' + widget_num).attr('data-ms') > 86400) {
                            $('div#time-span' + widget_num).text('1 day').attr('data-ms', "86400");
                        }
                        $('ul#tab-list' + widget_num + ' a[href="#tab' + widget_num + '-1"]').click();
                        $('ul#tab-list' + widget_num).addClass('hidden');
                        $('#' + add_metric_button_id).addClass('hidden');
                    }
                    else if (data_target === 'history-wow' + widget_num) {
                        $('ul#time-span-options' + widget_num).html(wow_span_menu);
                        if ($('div#time-span' + widget_num).attr('data-ms') > 604800) {
                            $('div#time-span' + widget_num).text('1 week').attr('data-ms', "604800");
                        }
                        $('ul#tab-list' + widget_num + ' a[href="#tab' + widget_num + '-1"]').click();
                        $('ul#tab-list' + widget_num).addClass('hidden');
                        $('#' + add_metric_button_id).addClass('hidden');
                    }
                    else if (data_target === 'history-no' + widget_num) {
                        $('ul#time-span-options' + widget_num).html(long_span_menu);
                        $('ul#tab-list' + widget_num).removeClass('hidden');
                        $('#' + add_metric_button_id).removeClass('hidden');
                    }
                });

                widget.metric_count = widget.add_tab(widget.metric_count, widget_num);
                $(form_div.children('.row3').find('.metric-autocomplete')).autocomplete({
                    minChars: 2, serviceUrl: widget.options.sw_url + 'api/tsdb_metric_list/', containerClass: 'autocomplete-suggestions dropdown-menu', zIndex: '', maxHeight: ''
                });

                var widget_height = $(widget_element).children('.widget').innerHeight();
                var main_height = widget_height;
                widget.sw_graphwidget_frontmain.css('height', main_height);
//        widget.sw_graphwidget_backmain.css('height', main_height);

                widget.build_saved_search_menu();

                $('label').click(function () {
                    statuswolf_button(this);
                });

            }
        }, add_tab: function (tab_num, widget_num) {
            var widget = this;
            tab_num++;

            var tab_content = $('div#tab-content' + widget_num)
                , tab_tag = widget_num + '-' + tab_num
                , tab_list = $('ul#tab-list' + widget_num);

            $(tab_content).append('<div class="tab-pane" id="tab' + tab_tag + '">');
            var tab_pane = $(tab_content).children('div#tab' + tab_tag);

            if (widget.options.datasource === "OpenTSDB") {
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
                tab_table.append('<tr><td width="32%"><div id="lerp-button-container" class="hidden graph-widget-form-item menu-label">' +
                    '<h4>Interpolation</h4>' +
                    '<div class="push-button binary info-tooltip" title="Interpolation should be disabled unless you are absolutely sure that you need it.">' +
                    '<input type="checkbox" id="lerp-button' + tab_tag + '" name="lerp' + tab_tag + '">' +
                    '<label for="lerp-button' + tab_tag + '"><span class="iconic iconic-x-alt red"></span>' +
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
                if (widget.options.nointerpolation) {
                    $('div#lerp-button-container').removeClass('hidden');
                } else {
                    $('input#lerp-button' + tab_tag).prop('checked', true);
                }
            }

            tab_list.append('<li><a href="#tab' + tab_tag + '" data-toggle="tab">Metric ' + tab_num + '</a></li>');
            $('#' + tab_list.attr('id') + ' a[href="#tab' + tab_tag + '"]').click(function (event) {
                event.preventDefault();
                $(this).tab('show');
            });

            $('#' + tab_list.attr('id') + ' a[href="#tab' + tab_tag + '"]').tab('show');

            $('label').click(function () {
                statuswolf_button(this);
            });

            $('.info-tooltip').tooltip({placement: 'bottom'});
            $('.info-tooltip-right').tooltip({placement: 'right'});
            $('.info-tooltip-left').tooltip({placement: 'left'});
            $('.info-tooltip-top').tooltip({placement: 'top'});

            return tab_num;
        }, set_all_spans: function (item) {
            var proto_widget = this;
            if (proto_widget.graph.brush.empty() !== true) {
                var proto_period = 'date-search';
                var proto_no_autoupdate = true;
            }
            else {
                var proto_period = proto_widget.query_data.period;
            }
            var proto_start_time = parseInt(proto_widget.graph.x.domain()[0].getTime() / 1000);
            var proto_end_time = parseInt(proto_widget.graph.x.domain()[1].getTime() / 1000);
            var widget_list = $('.widget-container[data-widget-type="graphwidget"]');
            $.each(widget_list, function (i, widget_element) {
                var widget_id = '#' + $(widget_element).attr('id');
                var widget = $(widget_id).data('sw-graphwidget');
                widget.query_data.period = proto_period;
                widget.query_data.end_time = proto_end_time;
                widget.query_data.start_time = proto_start_time;
                if (typeof proto_no_autoupdate !== "undefined") {
                    widget.query_data.autoupdate = false;
                }
                widget.populate_search_form(widget.query_data);
            })
        }, set_all_tags_form: function (item) {
            var widget = this;
            var widget_list = $('.widget-container[data-widget-type="graphwidget"]');
            var tags_popup = '<div class="popup" id="set-all-tags-popup">' +
                '<div id="set-all-tags-box"><div id="set-all-tags-head"><h4>Set tags for all Graph Widgets</h4></div>' +
                '<div id="set-all-tags-info"><p>Replaces any existing tags for the searches in all Graph Widgets with the tags specified here.</p></div>' +
                '<div id="set-all-tags-form" style="margin-bottom: 25px;"><div class="popup-form-data"><form onsubmit="return false;">' +
                '<input type="text" class="input" id="new-tags" name="new-tags" value="" style="width: 500px;" onkeypress="if (event.which === 13) { $.magnificPopup.instance.items[0].data.set_all_tags(); }">' +
                '</form></div></div>' +
                '<div class="flexy widget-footer" style="margin-top: 10px;">' +
                '<div class="widget-footer-button" id="cancel-set-all-tags" onClick="$.magnificPopup.close()"><span class="iconic iconic-x-alt"><span class="font-reset"> Cancel</span></span></div>' +
                '<div class="glue1"></div>' +
                '<div class="widget-footer-button" id="set-all-tags-button" onClick="$.magnificPopup.instance.items[0].data.set_all_tags()">' +
                '<span class="iconic iconic-download"><span class="font-reset"> Save</span></span></div>' +
                '</div>';
            $.magnificPopup.open({
                items: {
                    src: tags_popup, type: 'inline', set_all_tags: function () {
                        var new_tag_string = $('input#new-tags').val();
                        if (new_tag_string.length > 0) {
                            var new_tags = [];
                            if (new_tag_string.match(',')) {
                                new_tags = new_tag_string.split(',');
                            }
                            else {
                                new_tags = new_tag_string.split(' ');
                            }
                            widget_list = $('.widget-container[data-widget-type="graphwidget"]');
                            $.each(widget_list, function (i, widget_element) {
                                var widget_id = '#' + $(widget_element).attr('id');
                                var widget = $(widget_id).data('sw-graphwidget');
                                $.each(widget.query_data.metrics, function (search_key, search_params) {
                                    search_params.tags = new_tags;
                                });
                                widget.populate_search_form(widget.query_data);
                            });
                        }
                        $.magnificPopup.close();
                    }
                }, preloader: false, removalDelay: 300, mainClass: 'popup-animate', callbacks: {
                    open: function () {
                        setTimeout(function () {
                            $('.container').addClass('blur');
                            $('.navbar').addClass('blur');
                        }, 150);
                    }, close: function () {
                        $('.container').removeClass('blur');
                        $('.navbar').addClass('blur');
                    }, afterClose: function () {
                        $('#set-all-tags-popup').remove();
                    }
                }
            });
        }, add_tags_to_all_form: function (item) {
            var widget = this;
            var widget_list = $('.widget-container[data-widget-type="graphwidget"]');
            var tags_popup = '<div class="popup" id="add-tags-popup">' +
                '<div id="add-tags-box"><div id="add-tags-head"><h4>Set tags for all Graph Widgets</h4></div>' +
                '<div id="add-tags-info"><p>Add tag(s) specified here to all Graph Widgets.</p></div>' +
                '<div id="add-tags-form" style="margin-bottom: 25px;"><div class="popup-form-data"><form onsubmit="return false;">' +
                '<input type="text" class="input" id="new-tags" name="new-tags" value="" style="width: 500px;" onkeypress="if (event.which === 13) { $.magnificPopup.instance.items[0].data.add_tag_to_all(); }">' +
                '</form></div></div>' +
                '<div class="flexy widget-footer" style="margin-top: 10px;">' +
                '<div class="widget-footer-button" id="cancel-add-tags" onClick="$.magnificPopup.close()"><span class="iconic iconic-x-alt"><span class="font-reset"> Cancel</span></span></div>' +
                '<div class="glue1"></div>' +
                '<div class="widget-footer-button" id="add-tags-button" onClick="$.magnificPopup.instance.items[0].data.add_tag_to_all()">' +
                '<span class="iconic iconic-download"><span class="font-reset"> Save</span></span></div>' +
                '</div>';
            $.magnificPopup.open({
                items: {
                    src: tags_popup, type: 'inline', add_tag_to_all: function () {
                        var new_tag_string = $('input#new-tags').val();
                        if (new_tag_string.length > 0) {
                            var new_tags = [];
                            if (new_tag_string.match(',')) {
                                new_tags = new_tag_string.split(',');
                            }
                            else {
                                new_tags = new_tag_string.split(' ');
                            }
                            widget_list = $('.widget-container[data-widget-type="graphwidget"]');
                            $.each(widget_list, function (i, widget_element) {
                                var widget_id = '#' + $(widget_element).attr('id');
                                var widget = $(widget_id).data('sw-graphwidget');
                                $.each(widget.query_data.metrics, function (search_key, search_params) {
                                    search_params.tags.push(new_tags);
                                });
                                widget.populate_search_form(widget.query_data);
                            });
                        }
                        $.magnificPopup.close();
                    }
                }, preloader: false, removalDelay: 300, mainClass: 'popup-animate', callbacks: {
                    open: function () {
                        setTimeout(function () {
                            $('.container').addClass('blur');
                            $('.navbar').addClass('blur');
                        }, 150);
                    }, close: function () {
                        $('.container').removeClass('blur');
                        $('.navbar').addClass('blur');
                    }, afterClose: function () {
                        $('#set-all-tags-popup').remove();
                    }
                }
            });
        }, show_graph_data: function() {
            var widget = this;
            var table_data = [];
            if (widget.graph.data_left.length > 0) {
                $.each(widget.graph.data_left, function(i, metric) {
                    $.each(metric.values, function(i, point_data) {
                        table_data.push([metric.name, point_data.date, point_data.timestamp, point_data.value, metric.axis]);
                    });
                });
            }
            if (typeof widget.graph.data_right !== "undefined") {
                $.each(widget.graph.data_right, function(i, metric) {
                    $.each(metric.values, function(i, point_data) {
                        table_data.push([metric.name, point_data.date, point_data.timestamp, point_data.value, metric.axis]);
                    });
                });
            }
            $('#raw-data-table').dataTable({
                'aaData': table_data,
                'aoColumns': [
                    {'sTitle': 'Metric Name'},
                    {'sTitle': 'Time'},
                    {'sTitle': 'Timestamp'},
                    {'sTitle': 'Value'},
                    {'sTitle': 'Display Axis'}
                ],
                'bDestroy': true
            });
            $.magnificPopup.open({
                items: {
                    src: '#raw-data',
                    type: 'inline'
                },
                preloader: false,
                removalDelay: 300,
                mainClass: 'popup-animate',
                callbacks: {
                    beforeOpen: function() {
                        $('#raw-data-table').siblings('div.dataTables_filter').children('label').children('input').attr('size', '50');
                        $('#raw-data-table').before('<div class="dataTables_exportCSV" id="raw-data-table_exportcsv"><span class="sw-button raw-data-export-csv-button" id="raw-data-export-csv-button">Export CSV</span></div>');
                    },
                    open: function() {
                        $('span.raw-data-export-csv-button').click(function() {
                            var raw_data_csv = 'Metric Name, Time, Timestamp, Value, Display Axis\n';
                            $.each(table_data, function(i, line) {
                                raw_data_csv += line.join(',') + '\n';
                            });
                            window.location.href = 'data:text/csv;charset=UTF-8,' + encodeURIComponent(raw_data_csv);
                        });
                    },
                    afterClose: function() {
                        widget.reset_data_table();
                    }
                }
            });
        }, reset_data_table: function() {
            $('#raw-data-table').dataTable().fnDestroy();
            $('#raw-data').empty().append('<table id="raw-data-table" class="table"></table>');
        }, dropdown_menu_handler: function (item) {
            var widget = this;
            var button = $(item).parent().parent().children('span');
            var action = $(item).attr('data-action');
            $(button).children('.graph-widget-button-label').text($(item).text());
            $(button).children('div.ds-interval').attr('data-value', $(item).children('span').attr('data-value'));
            if ($(item).parent().attr('id') === "time-span-options" + widget.uuid) {
                $(button).children('div#time-span' + widget.uuid).attr('data-ms', $(item).children('span').attr('data-ms')).text();
            }
        }, build_saved_search_menu: function () {
            var widget = this;
            var user_id = document._session_data.user_id;
            var api_url = widget.options.sw_url + 'api/get_saved_searches';

            api_query = {user_id: user_id};
            $.ajax({
                url: api_url, type: 'POST', data: api_query, dataType: 'json', success: function (data) {
                    var my_searches = data['user_searches'];
                    var public_searches = data['public_searches'];
                    var saved_search_list = widget.sw_graphwidget_backtitle.children('.saved-searches-menu').children('ul.saved-searches-options')
                    saved_search_list.empty();
                    saved_search_list.append('<li class="menu-section"><span>My Searches</span></li>');
                    if (my_searches) {
                        $.each(my_searches, function (i, search) {
                            saved_search_list.append('<li><span data-name="search-' + search['id'] + '">' + search['title'] + '</span></li>');
                        });
                    }
                    if (public_searches) {
                        saved_search_list.append('<li class="menu-section"><span class="divider"></span></li>');
                        saved_search_list.append('<li class="menu-section"><span>Public Searches</span></li>');
                        $.each(public_searches, function (i, public) {
                            saved_search_list.append('<li><span data-name="search-' + public['id'] + '">' + public['title'] + ' (' + public['username'] + ')</span></li>');
                        });
                    }
                }
            });

            widget.sw_graphwidget_backtitle.children('.saved-searches-menu').children('ul.saved-searches-options').on('click', 'li', function () {
                var saved_query = {};
                if (search_name = $(this).children('span').attr('data-name')) {
                    var search_bits = search_name.split('-');
                    var search_id = search_bits[1];
                    $.ajax({
                        url: widget.options.sw_url + "api/load_saved_search/" + search_id, type: 'GET', dataType: 'json', success: function (data) {
                            saved_query = data;
                            delete(saved_query.private);
                            delete(saved_query.save_span);
                            delete(saved_query.user_id);
                            widget.populate_search_form(saved_query);
                        }
                    });
                }

            });
        }

//    ,populate_search_form: function(query_data, widget, force_prompt_user)
        , populate_search_form: function (query_data, force_prompt_user) {

            var widget = this;
            var prompt_user = false;
            var widget_num = widget.uuid;
            $(widget.sw_graphwidget_containerid + ' ul.nav-tabs').empty();
            $(widget.sw_graphwidget_containerid + ' div.tab-content').empty();
            widget.metric_count = widget.add_tab(0, widget.uuid);
            if (typeof force_prompt_user !== "undefined") {
                prompt_user = true;
            }
            if (typeof query_data.period === "undefined") {
                if (typeof query_data.time_span === "undefined") {
                    query_data.period = 'date-search';
                }
                else {
                    query_data.period = 'span-search';
                }
            }

            if (typeof query_data.history_graph === "undefined") {
                if (typeof query_data['history-graph'] !== "undefined") {
                    query_data.history_graph = query_data['history-graph'];
                    delete(query_data['history-graph']);
                }
                else {
                    query_data.history_graph = 'no';
                }
            }

            if (typeof query_data.title !== "undefined" && query_data.title.length > 1 && force_prompt_user !== "clone") {
                $('h3#search-title' + widget.uuid).text(query_data.title).removeClass('search-title-prompt');
                $('.widget-title > input[name="search-title-input' + widget.uuid + '"]').val(query_data.title);
            }

            if (widget.options.datasource === "OpenTSDB") {
                var method_map = {sum: 'Sum', avg: 'Average', min: 'Minimum Value', max: 'Maximum Value', dev: 'Standard Deviation'};

                var auto_update_input = $('input#auto-update-button' + widget_num);
                if (query_data.auto_update === "true") {
                    auto_update_input.parent().addClass('pushed');
                    if (!auto_update_input.prop('checked')) {
                        auto_update_input.siblings('label').click();
                        auto_update_input.prop('checked', true);
                        auto_update_input.children('span.iconic').removeClass('iconic-x-alt red').addClass('iconic-check-alt green');
                    }
                }
                else {
                    auto_update_input.parent().removeClass('pushed');
                    if (auto_update_input.prop('checked')) {
                        auto_update_input.siblings('label').click();
                        auto_update_input.prop('checked', false);
                        auto_update_input.children('span.iconic').removeClass('iconix-check-alt green').addClass('iconic-x-alt red');
                    }
                }

                if (query_data.history_graph.match(/anomaly/)) {
                    var el = $('input[data-target="history-anomaly' + widget_num + '"]').parent('label');
                    $(el).parent('div.toggle-button').addClass('toggle-on');
                    $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
                    $(el).children('input').attr('checked', 'Checked');
                    $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
                    $('input[data-target="history-anomaly' + widget_num + '"]').click();
                }
                else if (query_data.history_graph.match(/wow/)) {
                    var el = $('input[data-target="history-wow' + widget_num + '"]').parent('label');
                    $(el).parent('div.toggle-button').addClass('toggle-on');
                    $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
                    $(el).children('input').attr('checked', 'Checked');
                    $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
                    $('input[data-target="history-wow' + widget_num + '"]').click();
                }
                else {
                    var el = $('input[data-target="history-no' + widget_num + '"]').parent('label');
                    $(el).parent('div.toggle-button').addClass('toggle-on');
                    $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
                    $(el).children('input').attr('checked', 'Checked');
                    $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
                    $('input[data-target="history-no' + widget_num + '"]').click();
                }

                if (query_data.period === "span-search") {
                    var el = $('input[data-target="graph-widget-time-span' + widget_num + '"]').parent('label');
                    $(el).parent('div.toggle-button').addClass('toggle-on');
                    $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
                    $(el).children('input').attr('checked', 'Checked');
                    $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
                    $('input[data-target="graph-widget-time-span' + widget_num + '"]').click();
                    var span = query_data['time_span'];
                    $('#time-span-options' + widget_num + ' li span[data-ms="' + span + '"]').parent('li').click();
                }
                else {
                    var el = $('input[data-target="graph-widget-dates' + widget_num + '"]').parent('label');
                    $(el).parent('div.toggle-button').addClass('toggle-on');
                    $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
                    $(el).children('input').attr('checked', 'Checked');
                    $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
                    $('input[data-target="graph-widget-dates' + widget_num + '"]').click();
                    if ((start_in = parseInt(query_data.start_time)) && (end_in = parseInt(query_data.end_time))) {
                        $('div#start-time' + widget_num).children('input').val(new Date(start_in * 1000).toString('yyyy/MM/dd HH:mm:ss'));
                        $('div#end-time' + widget_num).children('input').val(new Date(end_in * 1000).toString('yyyy/MM/dd HH:mm:ss'));
                    }
                    else {
                        prompt_user = true;
                    }
                }

                var metric_num = 0;
                $.each(query_data.metrics, function (search_key, metric) {
                    metric_num++;
                    metric_string = metric.name;
                    if (metric_num > 1) {
                        var metric_tab = $('div#tab' + widget.uuid + '-' + metric_num);
                        if (metric_tab.length == 0) {
                            widget.metric_count = widget.add_tab(metric_num - 1, widget.uuid);
                            $('input[name="metric' + widget_num + '-' + widget.metric_count + '"]').autocomplete({
                                minChars: 2, serviceUrl: widget.options.sw_url + 'api/tsdb_metric_list/', containerClass: 'autocomplete-suggestions dropdown-menu', zIndex: '', maxHeight: ''
                            });
                        }
                    }

                    if (metric.tags) {
                        $.each(metric.tags, function (i, tag) {
                            metric_string += ' ' + tag;
                        });
                    }
                    $('input[name="metric' + widget_num + '-' + metric_num + '"]').val(metric_string);
                    $('#active-aggregation-type' + widget_num + '-' + metric_num).text(method_map[metric.agg_type]);
                    $('#active-downsample-type' + widget_num + '-' + metric_num).text(method_map[metric.ds_type]);
                    $('#downsample-interval-options' + widget_num + '-' + metric_num + ' li span[data-value="' + metric.ds_interval + '"]').parent('li').click();

                    var lerp_input = $('input#lerp-button' + widget_num + '-' + metric_num);
                    if (metric.lerp && metric.lerp !== "false") {
                        lerp_input.parent('.push-button').addClass('pushed');
                        lerp_input.siblings('label').children('span.iconic').addClass('iconic-check-alt green').removeClass('iconic-x-alt red');
                        lerp_input.siblings('label').children('span.binary-label').text('Yes');
                        if (!lerp_input.prop('checked')) {
                            lerp_input.siblings('label').click();
                            lerp_input.prop('checked', true);
                        }
                    }
                    else {
                        lerp_input.parent('.push-button').removeClass('pushed');
                        lerp_input.siblings('label').children('span.iconic').addClass('iconic-x-alt red').removeClass('iconic-check-alt green');
                        lerp_input.siblings('label').children('span.binary-label').text('No');
                        if (lerp_input.prop('checked')) {
                            lerp_input.siblings('label').click();
                            lerp_input.prop('checked', false);
                        }
                    }

                    var rate_input = $('input#rate-button' + widget_num + '-' + metric_num);
                    if (metric.rate && metric.rate !== "false") {
                        rate_input.parent('.push-button').addClass('pushed');
                        rate_input.siblings('label').children('span.iconic').addClass('iconic-check-alt green').removeClass('iconic-x-alt red');
                        rate_input.siblings('lablel').children('span.binary-label').text('Yes');
                        if (!rate_input.prop('checked')) {
                            rate_input.siblings('label').click();
                            rate_input.prop('checked', true);
                        }
                    }
                    else {
                        rate_input.parent('.push-button').removeClass('pushed');
                        rate_input.siblings('label').children('span.iconic').addClass('iconic-x-alt red').removeClass('iconic-check-alt green');
                        rate_input.siblings('label').children('span.binary-label').text('No');
                        if (rate_input.prop('checked')) {
                            rate_input.siblings('label').click();
                            rate_input.prop('checked', false);
                        }
                    }

                    var y2_input = $('input#y2-button' + widget_num + '-' + metric_num);
                    if (metric.y2 && metric.y2 !== "false") {
                        y2_input.parent('.push-button').addClass('pushed');
                        y2_input.siblings('label').children('span.iconic').addClass('iconic-check-alt gree').removeClass('iconic-x-alt red');
                        y2_input.siblings('label').children('span.binary-label').text('Yes');
                        if (!y2_input.prop('checked')) {
                            y2_input.siblings('label').click();
                            y2_input.prop('checked', true);
                        }
                    }
                    else {
                        y2_input.parent('.push-button').removeClass('pushed');
                        y2_input.siblings('label').children('span.iconic').addClass('iconic-x-alt red').removeClass('iconic-check-alt green');
                        y2_input.siblings('label').children('span.binary-label').text('No');
                        if (y2_input.prop('checked')) {
                            y2_input.siblings('label').click();
                            y2_input.prop('checked', false);
                        }
                    }
                });

                if (prompt_user) {
                    setTimeout(function () {
                        if ($(widget.sw_graphwidget_containerid).parent().attr('id') === "adhoc-container") {
                            widget.sw_graphwidget.addClass('flipped');
                        }
                        else {
                            widget.edit_params();
                        }
                    }, 500);
                }
                else {
                    widget.go_click_handler();
                }

            }
        }, go_click_handler: function (event) {

            var widget = this;
            var widget_num = widget.uuid;
            var widget_element = $(widget.element);
            widget.query_data = {};
            widget.query_data.downsample_master_interval = 0;
            widget.query_data.new_query = true;
            var input_error = false;

            $('#graph-title' + widget_num).empty();
            $('#legend' + widget_num).empty();

            if (typeof widget.autoupdate_timer !== "undefined") {
                console.log('clearing auto-update timer');
                clearTimeout(widget.autoupdate_timer);
                delete widget.autoupdate_timer;
            }

            if (widget.options.datasource === "OpenTSDB") {
                var methods = {'sum': 'sum', 'average': 'avg', 'minimum value': 'min', 'maximum value': 'max', 'standard deviation': 'dev'};
                // Map out all the elements we need to check
                var input_dates = $('div#graph-widget-dates' + widget_num);
                var input_time_span = $('div#time-span' + widget_num);
                var input_autoupdate = $('input#auto-update-button' + widget_num);
                var history_buttons = widget_element.find('input:radio[name="history-graph"]');
                widget.query_data.title = $('input[name="search-title-input' + widget_num + '"]').val();
                $.each(history_buttons, function (i, history_type) {
                    if ($(history_type).attr('checked')) {
                        input_history = history_type;
                    }
                });
                if (typeof input_history === "undefined") {
                    input_error = true;
                }


                // Date range validation
                var start, end;
                var date_span_option = widget_element.find('input:radio[name="date-span"]:checked').val();
                if (date_span_option === 'date-search') {
                    console.log('date search - checking start and end times');
                    if ($(input_dates).children('div#start-time' + widget_num).children('input[name="start-time"]').val().length < 1) {
                        $(input_dates).children('div#start-time' + widget_num).children('input[name="start-time"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
                        alert("You must specify a start time");
                    }
                    else if ($(input_dates).children('div#end-time' + widget_num).children('input[name="end-time"]').val().length < 1) {
                        $(input_dates).children('div#end-time' + widget_num).children('input[name="end-time"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
                        alert("You must specify an end time");
                    }

                    // Start date has to be before the End date.
                    start = Date.parse($(input_dates).children('div#start-time' + widget_num).children('input[name="start-time"]').val()).getTime();
                    start = start / 1000;
                    end = Date.parse($(input_dates).children('div#end-time' + widget_num).children('input[name="end-time"]').val()).getTime();
                    end = end / 1000;
                    if (start >= end) {
                        alert('Start time must come before end time');
                        $(input_dates).children('div#start-time' + widget_num).children('input[name="start-time"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
                    }
                    if (typeof widget.query_data['time_span'] != "undefined") {
                        delete widget.query_data['time_span'];
                    }
                    widget.query_data['period'] = 'date-search';
                    widget.query_data['time_span'] = end - start;
                }
                else {
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
                if (input_autoupdate.prop('checked')) {
                    widget.query_data['auto_update'] = true;
                }
                else {
                    widget.query_data['auto_update'] = false;
                }

                // Check for history display options
                widget.query_data.metrics = {};
                widget.query_data.history_graph = $(input_history).val();
                if (widget.query_data.history_graph === 'no') {
                    widget.query_data['metrics_count'] = widget.metric_count;
                    for (i = 1; i <= widget.query_data['metrics_count']; i++) {
                        var build_metric = {};
                        var metric_bits = [];
                        var metric_search_string = $('input:text[name=metric' + widget_num + '-' + i + ']').val();
                        // Check for OpenTSDB-style tags (metric.name{tag1=foo,tag2=bar})
                        if (metric_search_string.match('{')) {
                            metric_bits = metric_search_string.split('{')
                            build_metric.name = metric_bits.shift();
                            var metric_tag_string = metric_bits.shift();
                            metric_tag_string = metric_tag_string.substring(0, metric_tag_string.length - 1);
                            build_metric.tags = metric_tag_string.split(',');
                        }
                        else {
                            metric_bits = metric_search_string.split(' ');
                            build_metric.name = metric_bits.shift();
                            if (build_metric.name.length < 1) {
                                continue;
                            }
                            if (metric_bits.length > 0) {
                                build_metric.tags = metric_bits;
                            }
                        }
                        var agg_type = $('#active-aggregation-type' + widget_num + '-' + i).text().toLowerCase();
                        var ds_type = $('#active-downsample-type' + widget_num + '-' + i).text().toLowerCase();
                        build_metric.agg_type = methods[agg_type];
                        build_metric.ds_type = methods[ds_type];
                        build_metric.ds_interval = $('#active-downsample-interval' + widget_num + '-' + i).attr('data-value');
                        if ((widget.query_data['downsample_master_interval'] < 1) || (build_metric.ds_interval > widget.query_data['downsample_master_interval'])) {
                            widget.query_data['downsample_master_interval'] = build_metric.ds_interval;
                        }

                        if ($('#rate-button' + widget_num + '-' + i).prop('checked')) {
                            build_metric.rate = true;
                        }

                        if ($('#lerp-button' + widget_num + '-' + i).prop('checked')) {
                            build_metric.lerp = true;
                        }
                        if ($('#y2-button' + widget_num + '-' + i).prop('checked')) {
                            build_metric.y2 = true;
                        }

                        var search_key = md5(JSON.stringify(build_metric));
                        widget.query_data.metrics[search_key] = (build_metric);
                    }
                    if (widget.query_data['metrics'].length < 1) {
                        widget.sw_graphwidget_searchform.find('ul#tab-list' + widget_num + ' a[href="#tab' + widget_num + '-1"]').click();
                        $('input:text[name="metric' + widget_num + '-1"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
                        alert("You must specify at least one metric to search for");
                        input_error = true;
                    }
                }
                else {
                    widget.query_data['metrics_count'] = 1;
                    if ($('input:text[name="metric' + widget_num + '-1"]').val().length < 1) {
                        $('input:text[name="metric' + widget_num + '-1"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
                        alert("You must specify a metric to search for");
                        input_error = true;
                    }
                    else if (widget.query_data.history_graph === "wow" && (end - start) / 60 > 10080) {
                        alert('Week-over-week history comparison searches are limited to 1 week or less of data');
                        $('input:text[name=start-time]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
                        input_error = true;
                    }
                    else if (widget.query_data.history_graph === "anomaly" && (end - start) > 86400) {
                        alert('Anomaly detection searches are limited to 1 day or less');
                        $('input:text[name=start-time]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
                        input_error = true;
                    }
                    else {
                        input_error = false;
                        var build_metric = {};
                        var metric_bits = $('input:text[name=metric' + widget_num + '-1]').val().split(' ');
                        build_metric.name = metric_bits.shift();
                        if (metric_bits.length > 0) {
                            build_metric.tags = metric_bits;
                        }
                        var agg_type = $('#active-aggregation-type' + widget_num + '-1').text().toLowerCase();
                        var ds_type = $('#active-downsample-type' + widget_num + '-1').text().toLowerCase();
                        build_metric.agg_type = methods[agg_type];
                        build_metric.ds_type = methods[ds_type];
                        build_metric.ds_interval = $('#active-downsample-interval' + widget_num + '-1').attr('data-value');
                        widget.query_data['downsample_master_interval'] = build_metric.ds_interval;

                        if ($('#rate-button' + widget_num + '-1').prop('checked')) {
                            build_metric.rate = true;
                        }

                        if ($('#lerp-button' + widget_num + '-1').prop('checked')) {
                            build_metric.lerp = true;
                        }

                        if ($('y2-button' + widget_num + '-1').prop('checked')) {
                            build_metric.y2 = true;
                        }
                        var search_key = md5(JSON.stringify(build_metric));
                        widget.query_data.metrics[search_key] = (build_metric);
                    }
                }

            }

            // If we made it this far without errors in the form input, then
            // we build us a graph
            if (input_error == false) {
                var graph_element = $('#graphdiv' + widget_num);
                // Make sure the graph display div is empty
                graph_element.empty();
                // Clear the graph legend
                $('#legend-hover' + widget_num).empty();
                // Load the waiting spinner
                graph_element.append('<div class="bowlG">' +
                    '<div class="bowl_ringG"><div class="ball_holderG">' +
                    '<div class="ballG"></div></div></div></div>');
                graph_element.append('<div id="status-box' + widget_num + '" style="width: 100%; text-align: center;">' +
                    '<p id="status-message' + widget_num + '"></p></div>');
                widget.init_query();
                setTimeout(function () {
                    widget.flip_to_front();
                }, 250);
            }
            else {
                if (!widget_element.children('.widget').hasClass('flipped')) {
                    widget.edit_params();
                }
            }


        }, init_query: function () {

            var widget = this;
            widget_num = widget.uuid;

            if (widget.options.datasource === "OpenTSDB") {

                var graph_type = 'line';
                // Start deferred query for metric data
                $.when(widget.opentsdb_search()).then(
                    // done: Send the data over to be parsed
                    function (data) {
                        $.when(widget.process_timeseries_data(data)).then(
                            // done: Build the graph
                            function (data) {
                                widget.build_graph(data, graph_type);
                            }
                            // fail: Show error image and error message
                            , function (status) {
                                $('#' + widget.element.attr('id') + ' .bowlG')
                                    .html('<img src="' + widget.options.sw_url + 'app/img/error.png" style="width: 60px; height: 30px;">');
                                $('#' + widget.element.attr('id') + ' .status-message').text(status[1]);
                            }
                        );
                    }
                    // fail: Show error image and error message
                    , function (status) {
                        widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('.bowlG')
                            .css({'padding-top': '5%', 'margin-top': '0', 'margin-bottom': '5px', 'width': '120px', 'height': '60px'})
                            .html('<img src="' + widget.options.sw_url + 'app/img/error.png" style="width: 120px; height: 60px;">');

                        widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).empty()
                            .append('<p>' + status.shift() + '</p>');

                        if (typeof status[0] === "string") {
                            if ((status[0].match("<!DOCTYPE")) || (status[0].match("<html>"))) {
                                var error_message = status.join(' ').replace(/'/g, "&#39");
                                widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num)
                                    .append('<iframe style="margin: 0 auto; color: rgb(205, 205, 205);" width="80%" height="90%" srcdoc=\'' + error_message + '\' seamless></iframe>');
                            }
                            else {
                                widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).text(status);
                            }
                        }
                    }
                );

            }

        }, opentsdb_search: function () {

            var widget = this;
            var query_object = new $.Deferred();
            var widget_num = widget.uuid;
            var status = widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).children('#status-message' + widget_num);

            // Generate (or find the cached) model data for the metric
            if (widget.query_data.history_graph == "anomaly") {
                status.html('<p>Fetching Metric Data</p>');
                $.when(widget.get_opentsdb_data_anomaly()
                    .done(function (data) {
                        query_object.resolve(data);
                    })
                    .fail(function (data) {
                        query_object.reject(data);
                    })
                );
            }
            // Search current and previous week for metric data
            else if (widget.query_data.history_graph == "wow") {
                status.html('<p>Fetching Metric Data</p>');
                $.when(widget.get_opentsdb_data_wow()
                    .done(function (data) {
                        query_object.resolve(data);
                    })
                    .fail(function (data) {
                        query_object.reject(data);
                    })
                );
            }
            else {
                status.html('<p>Fetching Metric Data</p>');
                $.when(widget.get_opentsdb_data()
                    .done(function (data) {
                        query_object.resolve(data);
                    })
                    .fail(function (data) {
                        query_object.reject(data);
                    })
                );
            }

            return query_object.promise();

        }, get_opentsdb_data: function () {
            var widget = this;

            if (typeof widget.ajax_request !== 'undefined') {
                widget.ajax_request.abort();
            }

            widget.ajax_object = new $.Deferred();

            widget.ajax_request = $.ajax({
                url: widget.options.sw_url + "api/search/OpenTSDB", type: 'POST', data: widget.query_data, dataType: 'json', timeout: 120000
            })
                , chain = widget.ajax_request.then(function (data) {
                return(data);
            });

            chain.done(function (data) {
                if (data[0] === "error") {
                    widget.ajax_request.abort();
                    widget.ajax_object.reject(data[1])
                }
                else if (Object.keys(data).length <= 4) {
                    widget.ajax_request.abort();
                    widget.ajax_object.reject(["0", "Query returned no data"]);
                }
                else {
                    delete widget.ajax_request;
                    widget.ajax_object.resolve(data);
                }
            });

            return widget.ajax_object.promise();

        }, get_opentsdb_data_wow: function () {

            var widget = this;
            var status = widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).children('#status-message' + widget_num);

            if (typeof widget.ajax_request !== 'undefined') {
                console.log('Previous request still in flight, aborting');
                widget.ajax_request.abort();
            }

            widget.ajax_object = new $.Deferred();

            var metric_data = {};

            widget.ajax_request = $.ajax({
                url: widget.options.sw_url + "api/search/OpenTSDB", type: 'POST', data: widget.query_data, dataType: 'json', timeout: 120000
            })
                , chained = widget.ajax_request.then(function (data) {
                current_data = data;
                if (current_data[0] === "error") {
                    widget.ajax_request.abort();
                    widget.ajax_object.reject(current_data[1])
                }
                else if (Object.keys(current_data).length <= 4) {
                    widget.ajax_request.abort();
                    widget.ajax_object.reject(["0", "Current week query returned no data"]);
                }
                else {
                    status.html('<p>Fetching Metric Data For Previous Week</p>');
                    metric_data.start = current_data.start;
                    delete current_data.start;
                    metric_data.end = current_data.end;
                    delete current_data.end;
                    metric_data.query_url = current_data.query_url;
                    delete current_data.query_url;
                    current_keys = Object.keys(current_data)
                    current_key = 'Current - ' + current_keys[0];
                    metric_data.legend = {};
                    metric_data.legend[current_key] = current_key;
                    metric_data[current_key] = current_data[current_keys[0]];
                    delete current_data;
                    var past_query = $.extend(true, {}, widget.query_data);
                    var query_span = parseInt(widget.query_data.end_time) - parseInt(widget.query_data.start_time);
                    past_query.end_time = parseInt(widget.query_data.end_time - 604800);
                    past_query.start_time = past_query.end_time - query_span;
                    past_query.previous = true;
                    return $.ajax({
                        url: widget.options.sw_url + "api/search/OpenTSDB", type: 'POST', data: past_query, dataType: 'json', timeout: 120000
                    });
                }
            });
            chained.done(function (data) {
                if (!data) {
                    widget.ajax_request.abort();
                    widget.ajax_object.reject();
                }
                else {
                    past_data = data;
                    if (past_data[0] === "error") {
                        widget.ajax_request.abort();
                        widget.ajax_object.reject(past_data[1])
                    }
                    else if (Object.keys(past_data).length <= 4) {
                        widget.ajax_request.abort();
                        widget.ajax_object.reject(["0", "Previous week query returned no data"]);
                    }
                    else {
                        delete past_data.start;
                        delete past_data.end;
                        delete past_data.query_url;
                        past_keys = Object.keys(past_data);
                        past_key = 'Previous - ' + past_keys[0];
                        metric_data.legend[past_key] = past_key;
                        metric_data[past_key] = past_data[past_keys[0]];
                        delete past_data;
                        $.each(metric_data[past_key], function (index, entry) {
                            entry.timestamp = parseInt(entry.timestamp + 604800);
                        });
                        delete widget.ajax_request;
                        widget.ajax_object.resolve(metric_data);
                    }
                }
            });

            return widget.ajax_object.promise();

        }, get_opentsdb_data_anomaly: function () {

            var widget = this;
            var status = widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).children('#status-message' + widget_num);

            if (typeof widget.ajax_request !== 'undefined') {
                console.log('Previous request still in flight, aborting');
                widget.ajax_request.abort();
            }

            widget.ajax_object = new $.Deferred();

            var metric_data = {};
            var data_for_detection = [];

            widget.ajax_request = $.ajax({
                url: widget.options.sw_url + "api/search/OpenTSDB", type: 'POST', data: widget.query_data, dataType: 'json', timeout: 120000
            })
                , chained = widget.ajax_request.then(function (data) {
                if (data[0] === "error") {
                    widget.ajax_request.abort();
                    widget.ajax_object.reject(data[1])
                }
                else if (Object.keys(data).length <= 4) {
                    widget.ajax_request.abort();
                    widget.ajax_object.reject(["0", "Query returned no data"]);
                }
                else {
                    metric_data = data;
                    data_for_detection = {query_data: widget.query_data, data: metric_data, data_source: widget.options.datasource};
                    status.html('<p>Calculating anomalies</p>');
                    return $.ajax({
                        url: widget.options.sw_url + "api/detect_timeseries_anomalies", type: 'POST', data: data_for_detection, dataType: 'json'
                    });
                }
            });

            chained.done(function (data) {
                if (!data) {
                    widget.ajax_request.abort();
                    widget.ajax_object.reject();
                }
                else {
                    if (metric_data[0] === "error") {
                        widget.ajax_request.abort();
                        widget.ajax_object.reject(metric_data[1])
                    }
                    else {
                        metric_data.anomalies = data;
                        widget.ajax_object.resolve(metric_data);
                    }
                }
            }).fail(function (data) {
                    widget.ajax_request.abort();
                    widget.ajax_object.reject([data.status, data.statusText]);
                });

            return widget.ajax_object.promise();

        }, process_timeseries_data: function (data) {

            var widget = this;

            var parse_object = new $.Deferred();
            var status = widget.sw_graphwidget_frontmain.children('#graphdiv' + widget_num).children('#status-box' + widget_num).children('#status-message' + widget_num);

            widget.query_url = data.query_url;
            delete data.start;
            delete data.end;
            delete data.query_url;
            if (widget.query_data.history_graph == "anomaly") {
                var anomalies = data.anomalies;
                delete data.anomalies;
            }

            status.html('<p>Parsing Metric Data</p>');

            var empty_value = null;
            if (typeof document._sw_conf.graphing.treat_null_as_zero !== "undefined" && document._sw_conf.graphing_treat_null_as_zero === 1) {
                empty_value = 0;
            }

            var graph_data = [];
            graph_data.legend_map = data.legend;
            delete data.legend;

            if (widget.query_data.history_graph !== "no") {
                $.each(data, function (series, series_data) {
                    graph_data.push({name: series, search_key: (Object.keys(widget.query_data.metrics))[0], axis: 'left', values: series_data});
                })
            }
            else {
                var tag_map = {};
                $.each(widget.query_data.metrics, function (search_key, search_data) {
                    if (typeof search_data.tags !== "undefined") {
                        tag_map[search_key] = {name: search_data.name, tags: [], tag_count: search_data.tags.length};
                        $.each(search_data.tags, function (i, tag) {
                            var tag_parts = tag.split('=');
                            if (tag_parts[1] === '*') {
                                tag_map[search_key].tags.push(tag_parts[0] + '=ALL');
                            }
                            else if (tag_parts[1].match(/\|/)) {
                                var matches = tag_parts[1].split('|');
                                $.each(matches, function (i, m) {
                                    tag_map[search_key].tags.push(tag_parts[0] + '=' + m);
                                });
                            }
                            else {
                                tag_map[search_key].tags.push(tag);
                            }
                        });
                    }
                    else {
                        tag_map[search_key] = {name: search_data.name, tags: ['NONE'], tag_count: 0};
                    }
                });
                $.each(data, function (series, series_data) {
                    var series_key = graph_data.legend_map[series].split(' ');
                    var series_metric = series_key.shift();
                    var key_map = '';
                    $.each(tag_map, function (search_key, search_data) {
                        if (series_metric === search_data.name) {
                            if (search_data.tags[0] === "NONE") {
                                key_map = search_key;
                            }
                            else {
                                var search_matched = 0;
                                $.each(search_data.tags, function (i, t) {
                                    if (t.match(/ALL/)) {
                                        tleft = (t.split('='))[0]
                                        $.each(series_key, function (i, k) {
                                            if (k.match(tleft)) {
                                                search_matched++;
                                            }
                                        })
                                    }
                                    else {
                                        $.each(series_key, function (i, k) {
                                            if (k === t) {
                                                search_matched++;
                                            }
                                        });
                                    }
                                });
                                if (search_matched == search_data.tag_count) {
                                    key_map = search_key;
                                }
                            }
                        }
                    });
                    var axis = 'left';
                    if (typeof key_map !== "undefined" && key_map.length > 0) {
                        if (typeof widget.query_data.metrics[key_map].y2 !== "undefined" && widget.query_data.metrics[key_map].y2 == true) {
                            axis = 'right';
                        }
                    }
                    graph_data.push({name: series, search_key: key_map, axis: axis, values: series_data});
                });
            }

            if (widget.query_data.history_graph == "anomaly") {
                graph_data.anomalies = anomalies;
            }

            var parsed_data = graph_data;

            parse_object.resolve(parsed_data);

            return parse_object.promise();

        }, build_graph: function (data, type) {

            var widget = this;

            widget.graph = {};
            widget.graph.legend_map = data.legend_map;
            delete(data.legend_map);
            if (widget.query_data['history_graph'] === "anomaly") {
                widget.graph.anomalies = data.anomalies;
                delete(data.anomalies);
            }
            widget.graph.autoupdate_interval = widget.query_data.downsample_master_interval > 1 ? widget.query_data.downsample_master_interval * 60 : 300;

            widget.graph.right_axis = false;
            var data_right = [];
            var data_left = [];
            $.each(data, function (s, d) {
                $.each(d.values, function (v) {
                    d.values[v]['date'] = new Date(d.values[v]['timestamp'] * 1000);
                });
                if (d.axis === "right") {
                    widget.graph.right_axis = true;
                    data_right.push(d);
                }
                else {
                    data_left.push(d);
                }
            });

//      widget.graph.data = data;
            widget.graph.data_left = data_left;
            widget.graph.margin = {top: 0, right: 5, bottom: 20, left: 45};
            delete(data_left);

            if (widget.graph.right_axis == true) {
                widget.graph.margin.right = 55;
                widget.graph.data_right = data_right;
                delete(data_right);
            }
            delete(data);

            var graphdiv = $('#' + widget.element.attr('id') + ' .graphdiv').empty();
            var graphdiv_offset = graphdiv.position().top;
            var legend_box = $('#' + widget.element.attr('id') + ' .legend');

            widget.sw_graphwidget_frontmain.css('height', widget.sw_graphwidget.innerHeight());
            widget.sw_graphwidget_frontmain.children('.graphdiv').css('height', (widget.sw_graphwidget_frontmain.innerHeight() - (widget.sw_graphwidget_frontmain.children('.legend-container').outerHeight(true) + graphdiv_offset)));
            widget.sw_graphwidget_frontmain.children('.legend-container').css('width', widget.sw_graphwidget_frontmain.innerWidth())
                .removeClass('hidden');

            widget.graph.width = (graphdiv.innerWidth() - widget.graph.margin.left - widget.graph.margin.right);
            widget.graph.height = (graphdiv.innerHeight() - widget.graph.margin.top - widget.graph.margin.bottom);

            widget.svg = d3.select('#' + graphdiv.attr('id')).append('svg')
                .attr('width', graphdiv.innerWidth())
                .attr('height', graphdiv.innerHeight());
            widget.svg.g = widget.svg.append('g')
                .attr('transform', 'translate(' + widget.graph.margin.left + ',' + widget.graph.margin.top + ')');

            if (type === "line") {
                widget.graph.x = d3.time.scale()
                    .range([0, widget.graph.width]);

                widget.graph.y = d3.scale.linear()
                    .range([widget.graph.height, 0]);

                widget.graph.x_axis = d3.svg.axis()
                    .scale(widget.graph.x)
                    .orient('bottom')
                    .tickSize(-widget.graph.height, 0)
                    .tickPadding(8)
                    .tickFormat(d3.time.format('%H:%M'));

                widget.graph.y_axis = d3.svg.axis()
                    .scale(widget.graph.y)
                    .orient('left')
                    .ticks(5)
                    .tickSize(-widget.graph.width, 0)
                    .tickPadding(5)
                    .tickFormat(d3.format('.3s'));

                if (widget.graph.right_axis == true) {
                    widget.graph.x.domain([
                        d3.min(widget.graph.data_left.concat(widget.graph.data_right), function (d) {
                            return d3.min(d.values, function (v) {
                                return v.date;
                            })
                        })
                        , d3.max(widget.graph.data_left.concat(widget.graph.data_right), function (d) {
                            return d3.max(d.values, function (v) {
                                return v.date;
                            })
                        })
                    ]);
                }
                else {
                    widget.graph.x.domain([
                        d3.min(widget.graph.data_left, function (d) {
                            return d3.min(d.values, function (v) {
                                return v.date;
                            })
                        })
                        , d3.max(widget.graph.data_left, function (d) {
                            return d3.max(d.values, function (v) {
                                return v.date;
                            })
                        })
                    ]);

                }

                widget.graph.x_master_domain = widget.graph.x.domain();

                widget.graph.y.domain([
                    0, (d3.max(widget.graph.data_left, function (d) {
                        return d3.max(d.values, function (v) {
                            return v.value;
                        })
                    }) * 1.05)
                ]);

                widget.graph.y_master_domain = widget.graph.y.domain();

                if (widget.graph.right_axis == true) {
                    widget.graph.y1 = d3.scale.linear()
                        .range([widget.graph.height, 0]);

                    widget.graph.y_axis_right = d3.svg.axis()
                        .scale(widget.graph.y1)
                        .orient('right')
                        .ticks(5)
                        .tickPadding(5)
                        .tickFormat(d3.format('.3s'));

                    widget.graph.y1.domain([
                        0, (d3.max(widget.graph.data_right, function (d) {
                            return d3.max(d.values, function (v) {
                                return v.value;
                            })
                        }) * 1.05)
                    ]);
                }

                widget.graph.color = d3.scale.ordinal()
                    .domain(function (d) {
                        return widget.graph.legend_map[d.name];
                    })
                    .range(swcolors.Wheel_DarkBG[5]);

                widget.graph.line = d3.svg.line()
                    .interpolate('linear')
                    .x(function (d) {
                        return widget.graph.x(d.date);
                    })
                    .y(function (d) {
                        return widget.graph.y(+d.value);
                    });

                widget.graph.line_right = d3.svg.line()
                    .interpolate('linear')
                    .x(function (d) {
                        return widget.graph.x(d.date);
                    })
                    .y(function (d) {
                        return widget.graph.y1(+d.value);
                    });

                widget.svg.g.append('defs').append('clipPath')
                    .attr('id', 'clip' + widget.uuid)
                    .append('rect')
                    .attr('width', widget.graph.width)
                    .attr('height', widget.graph.height);

                widget.svg.g.append('g')
                    .attr('class', 'x axis')
                    .attr('transform', 'translate(0,' + ($('#' + graphdiv.attr('id') + ' svg').innerHeight() - widget.graph.margin.bottom) + ')')
                    .call(widget.graph.x_axis)
                    .append('text')
                    .classed('graph-title', 1)
                    .classed('hidden', 1)
                    .attr('text-anchor', 'middle')
                    .text(widget.query_data.title);

                widget.format_graph_title();

                widget.svg.g.append('g')
                    .attr('class', 'y axis')
                    .call(widget.graph.y_axis);

                widget.svg.g.selectAll('.y.axis text').attr('dy', '0.75em');

                if (widget.graph.right_axis == true) {
                    widget.svg.g.append('g')
                        .attr('class', 'y1 axis')
                        .attr('transform', 'translate(' + widget.graph.width + ',0)')
                        .call(widget.graph.y_axis_right);

                    widget.svg.g.selectAll('.y1.axis text').attr('dy', '0.75em');

                }

                widget.svg.g.selectAll('.metric.left')
                    .data(widget.graph.data_left)
                    .enter().append('g')
                    .classed('metric', 1)
                    .classed('left', 1)
                    .attr('clip-path', 'url(#clip' + widget.uuid + ')')
                    .append('path')
                    .classed('line', 1)
                    .classed('left', 1)
                    .attr('d', function (d) {
                        return widget.graph.line(d.values);
                    })
                    .attr('data-name', function (d) {
                        return widget.graph.legend_map[d.name];
                    })
                    .style('stroke', function (d) {
                        return widget.graph.color(widget.graph.legend_map[d.name]);
                    });

                if (widget.graph.right_axis == true) {
                    widget.svg.g.selectAll('.metric.right')
                        .data(widget.graph.data_right)
                        .enter().append('g')
                        .classed('metric', 1)
                        .classed('right', 1)
                        .attr('clip-path', 'url(#clip' + widget.uuid + ')')
                        .append('path')
                        .classed('line', 1)
                        .classed('right', 1)
                        .attr('d', function (d) {
                            return widget.graph.line_right(d.values);
                        })
                        .attr('data-name', function (d) {
                            return widget.graph.legend_map[d.name];
                        })
                        .style('stroke', function (d) {
                            return widget.graph.color(widget.graph.legend_map[d.name]);
                        });

                }

                widget.graph.tooltip_format = d3.time.format('%X');

                widget.add_graph_dots();

                if (Object.keys(widget.graph.legend_map).length > 9) {
                    legend_box.css({
                        '-webkit-columns': 'auto 4', '-moz-columns': 'auto 4', columns: 'auto 4'
                    });
                }
                else if (Object.keys(widget.graph.legend_map).length > 6) {
                    legend_box.css({
                        '-webkit-columns': 'auto 2', '-moz-columns': 'auto 3', columns: 'auto 3'
                    });
                }
                else if (Object.keys(widget.graph.legend_map).length > 3) {
                    legend_box.css({
                        '-webkit-columns': 'auto 2', '-moz-columns': 'auto 2', columns: 'auto 2'
                    });
                }

                $.each(widget.graph.legend_map, function (series, label) {
                    var item_color = widget.graph.color(widget.graph.legend_map[series]);
                    legend_box.append('<span title="' + label + '" style="color: ' + item_color + '">' + label + '</span>');
                    var name_span = legend_box.children('span[title="' + label + '"]');
                    if (widget.graph.right_axis == true) {
                        $(name_span).prepend('<span class="iconic iconic-play" style="font-size: .75em"></span> ');
                        if (!d3.select('.metric path[data-name="' + label + '"]').classed('right')) {
                            $(name_span).children('.iconic').addClass('rotate-180');
                        }
                    }
                });

                legend_box.children('span')
                    .css('cursor', 'pointer')
                    .on('mouseover', function () {
                        $(this).css('font-weight', 'bold');
                        widget.svg.g.selectAll('.metric path').classed('fade', 1);
                        var moved_metric = widget.svg.g.select('.metric path[data-name="' + $(this).attr('title') + '"]');
                        var axis_position_class = 'left';
                        if (moved_metric.classed('right')) {
                            axis_position_class = 'right';
                        }
                        if (!moved_metric.classed('hidden')) {
                            var moved_metric_node = moved_metric.node();
                            var moved_metric_data = moved_metric.data();
                            $(moved_metric_node).parent().remove();
                            var new_metric = d3.select('#' + widget.element.attr('id') + ' svg>g').append('g', 'g.metric');
                            new_metric
                                .classed('metric', 1)
                                .classed(axis_position_class, 1)
                                .attr('clip-path', 'url(#clip' + widget.uuid + ')')
                                .data(moved_metric_data)
                                .append('path')
                                .classed('line', 1)
                                .classed(axis_position_class, 1)
                                .attr('d', function (d) {
                                    if (d.axis === "right") {
                                        return widget.graph.line_right(d.values);
                                    } else {
                                        return widget.graph.line(d.values);
                                    }
                                })
                                .attr('data-name', $(this).attr('title'))
                                .style('stroke', function (d) {
                                    return widget.graph.color(widget.graph.legend_map[d.name]);
                                })
                                .style('stroke-width', '3px')
                        }
                    })
                    .on('mouseout', function () {
                        $(this).css('font-weight', 'normal');
                        widget.svg.g.select('.widget path[data-name="' + $(this).attr('title') + '"]').style('stroke-width', '1.5px');
                        widget.svg.g.selectAll('.metric path').classed('fade', 0);
                    })
                    .on('click', function (e) {
                        var metric_line = widget.svg.g.select('.metric path[data-name="' + $(this).attr('title') + '"]');
                        var metric_dots = widget.svg.g.select('.dots[data-name="' + $(this).attr('title') + '"]');
                        if (e.altKey) {
                            legend_box.children('span').addClass('fade');
                            $(this).removeClass('fade');
                            widget.svg.g.selectAll('.metric path')
                                .classed('hidden', 1);
                            metric_line.classed('hidden', 0);
                            widget.svg.g.selectAll('.dots')
                                .classed('hidden', 1);
                            metric_dots.classed('hidden', 0);
                        }
                        else {
                            if (metric_line.classed('hidden')) {
                                $(this).removeClass('fade');
                                metric_line.classed('hidden', false);
                                metric_dots.classed('hidden', false);
                            }
                            else {
                                $(this).addClass('fade');
                                metric_line.classed('hidden', true);
                                metric_dots.classed('hidden', true);
                            }
                        }
                    });

                if (widget.query_data['history_graph'] === "anomaly") {
                    $.each(widget.graph.anomalies, function (i, anomaly) {
                        anomaly.start_time = new Date(parseInt(anomaly.start) * 1000);
                        anomaly.end_time = new Date(parseInt(anomaly.end) * 1000);
                    });
                    widget.svg.g.selectAll('.anomaly-bars')
                        .data(widget.graph.anomalies)
                        .enter().insert('g', ':first-child')
                        .classed('anomaly-bars', 1)
                        .append('rect')
                        .attr('y', 0)
                        .attr('height', widget.graph.height)
                        .attr('x', function (d) {
                            return widget.graph.x(d.start_time)
                        })
                        .attr('width', function (d) {
                            return (widget.graph.x(d.end_time) - widget.graph.x(d.start_time))
                        })
                        .attr('fill', 'red')
                        .attr('opacity', 0.25);
                }

                widget.graph.brush = d3.svg.brush()
                    .x(widget.graph.x)
                    .y(widget.graph.y)
                    .on('brushend', function () {
                        if (widget.graph.brush.empty()) {
                            widget.graph.x.domain(widget.graph.x_master_domain);
                            widget.graph.y.domain(widget.graph.y_master_domain);
                            widget.resize_graph();
                            widget.autoupdate_timer = setTimeout(function () {
                                widget.update_graph('line');
                            }, 300 * 1000);
                        }
                        else {
                            widget.graph.x.domain([widget.graph.brush.extent()[0][0], widget.graph.brush.extent()[1][0]]);
                            widget.graph.y.domain([widget.graph.brush.extent()[0][1], widget.graph.brush.extent()[1][1]]);
                            widget.resize_graph();
                            widget.graph.zoombox.select('rect.extent')
                                .attr('x', 0)
                                .attr('y', 0)
                                .attr('height', 0)
                                .attr('width', 0);
                            clearTimeout(widget.autoupdate_timer);
                        }
                    });

                widget.graph.zoombox = widget.svg.g.insert('g', '.metric')
                    .attr('class', 'brush');

                widget.graph.zoombox.call(widget.graph.brush)
                    .selectAll('rect.background')
                    .attr('height', widget.graph.height)
                    .attr('width', widget.graph.width);

                // Set the interval for adding new data if Auto Update is selected
                if (widget.query_data['auto_update']) {
                    console.log('setting auto-update timer to ' + widget.graph.autoupdate_interval / 60 + ' minutes for widget ' + widget.element.attr('id') + ' at ' + new Date.now().toTimeString());
                    widget.autoupdate_timer = setTimeout(function () {
                        widget.update_graph('line');
                    }, widget.graph.autoupdate_interval * 1000);
                }

            }
        }, update_graph: function (type) {
            var widget = this;
            if (widget.options.datasource = "OpenTSDB") {
                var last_point_bits = widget.graph.data_left[0].values.slice(-1);
                var last_point = last_point_bits.pop();
                var new_start = last_point.timestamp;
                var new_end = new Date.now().getTime();
                new_end = parseInt(new_end / 1000);
                if ((new_end - new_start) > widget.query_data.time_span) {
                    new_start = new_end - widget.query_data.time_span;
                }
                widget.query_data.start_time = new_start;
                widget.query_data.end_time = new_end;
                widget.query_data.new_query = false;
                var update_tracker = {};
                $.each(widget.graph.legend_map, function(name, lname) {
                    update_tracker[name] = 1;
                });
                $.when(widget.opentsdb_search()).then(function (incoming_new_data) {
                    $.when(widget.process_timeseries_data(incoming_new_data)).then(
                        function (incoming_new_data) {
                            if (type === "line") {
                                var new_data = incoming_new_data;
                                var new_point_count = new_data[0].values.length;
                                delete(incoming_new_data);
                                delete(new_data.legend_map);
                                if (widget.query_data['history_graph'] === "anomaly") {
                                    widget.graph.anomalies = widget.graph.anomalies.concat(new_data.anomalies);
                                    delete(new_data.anomalies);
                                }
                                $.each(new_data, function (s, d) {
                                    $.each(d.values, function (v) {
                                        d.values[v]['date'] = new Date(d.values[v]['timestamp'] * 1000);
                                    });
                                });

                                if (widget.graph.right_axis == true) {
                                    $.each(widget.graph.data_left.concat(widget.graph.data_right), function (i, d) {
                                        $.each(new_data, function (ni, nd) {
                                            if (nd.name === d.name) {
                                                d.values = d.values.concat(nd.values);
                                                d.values.splice(0, new_point_count);
                                                delete update_tracker[d.name];
                                            }
                                        });
                                    });
                                    if (Object.keys(update_tracker).length > 0) {
                                        $.each(widget.graph.data_left.concat(widget.graph.data_right), function (i, d) {
                                            if (typeof update_tracker[d.name] !== "undefined") {
                                                d.values.splice(0, new_point_count);
                                            }
                                        });
                                    }
                                    widget.graph.x.domain([
                                        d3.min(widget.graph.data_left.concat(widget.graph.data_right), function (d) {
                                            return d3.min(d.values, function (v) {
                                                return v.date;
                                            })
                                        })
                                        , d3.max(widget.graph.data_left.concat(widget.graph.data_right), function (d) {
                                            return d3.max(d.values, function (v) {
                                                return v.date;
                                            })
                                        })
                                    ]);
                                }
                                else {
                                    $.each(widget.graph.data_left, function (i, d) {
                                        $.each(new_data, function (ni, nd) {
                                            if (nd.name === d.name) {
                                                d.values = d.values.concat(nd.values);
                                                d.values.splice(0, new_point_count);
                                                delete update_tracker[d.name];
                                            }
                                        });
                                    });
                                    if (Object.keys(update_tracker).length > 0) {
                                        $.each(widget.graph.data_left, function (i, d) {
                                            if (typeof update_tracker[d.name] !== "undefined") {
                                                console.log('No new data received for ' + d.name + ', forcing trim');
                                                d.values.splice(0, new_point_count);
                                            }
                                        });
                                    }
                                    widget.graph.x.domain([
                                        d3.min(widget.graph.data_left, function (d) {
                                            return d3.min(d.values, function (v) {
                                                return v.date;
                                            })
                                        })
                                        , d3.max(widget.graph.data_left, function (d) {
                                            return d3.max(d.values, function (v) {
                                                return v.date;
                                            })
                                        })
                                    ]);
                                }
                                if (widget.graph.x.domain()[0] === "Invalid Date" || widget.graph.x.domain()[1] === "Invalid Date") {
                                    console.log('Invalid x domain, refreshing');
                                    widget.go_click_handler();
                                }
                                var current_time = new Date();
                                var nominal_start = new Date(current_time - (widget.query_data.time_span * 1000));
                                if ((current_time - widget.graph.x.domain()[1]) / 1000 > 3600) {
                                    console.log('Graph end is more than 1 hour off, refreshing');
                                    widget.go_click_handler();
                                }
                                if (Math.abs((nominal_start - widget.graph.x.domain()[0]) / 1000) > 3600) {
                                    console.log('Graph start is more than 1 hour off, refreshing');
                                    widget.go_click_handler();
                                }
                                widget.graph.y.domain([
                                    0, (d3.max(widget.graph.data_left, function (d) {
                                        return d3.max(d.values, function (v) {
                                            return v.value;
                                        })
                                    }) * 1.05)
                                ]);
                                widget.svg.g.select('.x.axis').call(widget.graph.x_axis);
                                widget.svg.g.select('.y.axis').call(widget.graph.y_axis);
                                widget.svg.g.selectAll('.y.axis text').attr('dy', '0.75em');
                                if (widget.graph.right_axis == true) {
                                    widget.graph.y1.domain([
                                        0, (d3.max(widget.graph.data_right, function (d) {
                                            return d3.max(d.values, function (v) {
                                                return v.value;
                                            })
                                        }) * 1.05)
                                    ]);
                                    widget.svg.g.selectAll('.y1.axis text').attr('dy', '0.75em');
                                }
                                widget.svg.g.selectAll('.metric path')
                                    .attr('d', function (d) {
                                        if (d.axis === "right") {
                                            return widget.graph.line_right(d.values);
                                        } else {
                                            return widget.graph.line(d.values);
                                        }
                                    });

                                if (widget.query_data['history_graph'] === "anomaly") {
                                    widget.svg.g.selectAll('.anomaly-bars').selectAll('rect')
                                        .attr('x', function (d) {
                                            return widget.graph.x(d.start_time)
                                        });
                                }
                                $(widget.sw_graphwidget_containerid + ' .dots').remove();
                                widget.add_graph_dots();
                            }
                        }
                    );
                });
            }
            console.log('Widget ' + widget.element.attr('id') + ' refreshed at ' + new Date.now().toTimeString() + ', next refresh in ' + widget.graph.autoupdate_interval / 60 + ' minutes');
            widget.autoupdate_timer = setTimeout(function () {
                widget.update_graph('line');
            }, widget.graph.autoupdate_interval * 1000);
        }, add_graph_dots: function () {
            var widget = this;

            var dots = widget.svg.g.selectAll('.dots')
                .data(widget.graph.right_axis ? widget.graph.data_left.concat(widget.graph.data_right) : widget.graph.data_left)
                .enter().append('g')
                .attr('class', 'dots')
                .attr('data-name', function (d) {
                    return widget.graph.legend_map[d.name];
                });
            dots.each(function (d, i) {
                var series = d.name;
                var axis = d.axis;
                d3.select(this).selectAll('.dot')
                    .data(d.values)
                    .enter().append('circle')
                    .classed('dot', 1)
                    .classed('transparent', 1)
                    .classed('info-tooltip-top', 1)
                    .attr('r', 3)
                    .attr('cx', function (d) {
                        return widget.graph.x(d.date);
                    })
                    .attr('cy', function (d) {
                        if (axis === "right") {
                            return widget.graph.y1(d.value);
                        } else {
                            return widget.graph.y(+d.value);
                        }
                    })
                    .attr('title', function (d) {
                        return widget.graph.tooltip_format(d.date) + ' - ' + d.value;
                    })
                    .attr('data-axis', function () {
                        if (axis === "right") {
                            return 'right';
                        } else {
                            return 'left';
                        }
                    })
                    .style('fill', 'none')
                    .style('stroke-width', '3px')
                    .style('stroke', function () {
                        return (widget.graph.color(widget.graph.legend_map[series]))
                    })
                    .on('mouseover', function () {
                        var e = d3.event;
                        d3.select(this).classed('transparent', 0);
                        var legend_item = $("span[title='" + $(this).parent().attr('data-name') + "']");
                        var legend_box = legend_item.parent();
                        legend_item.detach();
                        legend_box.prepend(legend_item);
                        legend_item.css('font-weight', 'bold');
                    })
                    .on('mouseout', function () {
                        d3.select(this).classed('transparent', 1);
                        $("span[title='" + $(this).parent().attr('data-name') + "']").css('font-weight', 'normal');
                    });
            });
            var graphdiv = $('#' + widget.element.attr('id') + ' .graphdiv');
            $('.dot.info-tooltip').tooltip({placement: 'bottom', container: graphdiv});
            $('.dot.info-tooltip-right').tooltip({placement: 'right', container: graphdiv});
            $('.dot.info-tooltip-left').tooltip({placement: 'left', container: graphdiv});
            $('.dot.info-tooltip-top').tooltip({placement: 'top', container: graphdiv});
        }, format_graph_title: function () {
            var widget = this;
            var graph_title = widget.svg.g.select('text.graph-title');
            graph_title
                .attr('x', widget.graph.x.range()[1] / 2)
                .attr('y', (widget.graph.margin.bottom - widget.graph.y.range()[0] / 2) - (graph_title.node().getBBox().height / 2));
            var svg_rect_node = widget.svg.g.select('defs').select('rect').node();
            var svg_rect_width;
            try {
                svg_rect_width = svg_rect_node.getBBox().width;
            } catch (err) {
                svg_rect_width = svg_rect_node.width.animVal.value;
            }
            var splits = parseInt(graph_title.node().getBBox().width / svg_rect_width);
            if (splits > 0) {
                var graph_title_atoms = widget.query_data.title.split(' ');
                var atom_count = graph_title_atoms.length;
                var target_length = widget.query_data.title.length / (splits + 1);
                var title_parts = [];
                for (i = 0; i <= splits; i++) {
                    var temp_string = '';
                    while ((graph_title_atoms.length > 0) && (graph_title_atoms < atom_count / 2 || temp_string.length < target_length)) {
                        temp_string += graph_title_atoms.shift() + ' ';
                    }
                    title_parts[i] = temp_string.trim();
                }
                graph_title.text('').selectAll('tspan')
                    .data(title_parts).enter()
                    .append('tspan')
                    .text(function (d) {
                        return d;
                    });
                widget.graph.title_height = graph_title.node().getBBox().height;
                var tspan_offset = 0;
                graph_title.selectAll('tspan').each(function () {
                    d3.select(this)
                        .attr('y', function () {
                            return parseInt(graph_title.attr('y')) + tspan_offset;
                        })
                        .attr('x', function () {
                            return graph_title.attr('x');
                        });
                    tspan_offset += widget.graph.title_height;
                });
                graph_title.attr('y', (widget.graph.margin.bottom - widget.graph.y.range()[0] / 2) - (graph_title.node().getBBox().height / 2));
            }
            graph_title.classed('hidden', 0);
        }
    })
}(jQuery));
