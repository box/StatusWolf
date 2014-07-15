/**
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 15 July 2013
 *
 */


(function($, undefined) {

    var sw_opentsdbwidget_classes = 'widget';
    var sw_opentsdbwidget_containerclasses = 'opentsdb-widget';

    $.widget('sw.opentsdbwidget', {
        version: "1.0",
        defaultElement: "<div>",
        options: {
            disabled: null,
            label: null,
            handles: '',
            legend: "on",
            nointerpolation: false,
            query_data: null
        },
        _create: function() {

            var that = this,
                options = this.options,
                sw_opentsdbwidget,
                sw_opentsdbwidget_container,
                sw_opentsdbwidget_containerid,
                sw_opentsdbwidget_front,
                sw_opentsdbwidget_back,
                sw_opentsdbwidget_close,
                sw_opentsdbwidget_backfooter,
                sw_opentsdbwidget_frontmain,
                sw_opentsdbwidget_backmain,
                sw_opentsdbwidget_action,
                sw_opentsdbwidget_draghandle,
                sw_opentsdbwidget_graphdiv,
                sw_opentsdbwidget_legendbox,
                sw_opentsdbwidget_savedsearchesmenu,
                sw_opentsdbwidget_searchform,
                sw_opentsdbwidget_querycancelbutton,
                sw_opentsdbwidget_gobutton;

            // If this is the first OpenTSDB widget added, load the
            // widget-specific CSS file
            if ($('link[href*="opentsdb"]').length === 0) {
                var widget_css = document.createElement('link');
                widget_css.setAttribute('rel', 'stylesheet');
                widget_css.setAttribute('href', window.location.origin + '/Widgets/OpenTSDBWidget/css/sw.opentsdbwidget.css');
                document.getElementsByTagName('head')[0].appendChild(widget_css);
            }

            this.sw_opentsdbwidget_timespanmap = {
                '600': '10 Minutes',
                '1800': '30 Minutes',
                '3600': '1 Hour',
                '7200': '2 Hours',
                '14400': '4 Hours',
                '28800': '8 Hours',
                '43200': '12 Hours',
                '86400': '1 Day',
                '604800': '1 Week',
                '1209600': '2 Weeks',
                '2592000': '1 Month'
            };

            sw_opentsdbwidget_container = (this.sw_opentsdbwidget_container = $(this.element));
            $(sw_opentsdbwidget_container)
                .addClass('transparent')
                .addClass(sw_opentsdbwidget_containerclasses);
            // @Todo - implement resizing and dragging
//                .resizable()
//                .draggable({
//                    'handle': '.widget-drag-handle',
//                    'cursor': 'move'
//                });

            sw_opentsdbwidget = (this.sw_opentsdbwidget = $('<div>'))
                .addClass(sw_opentsdbwidget_classes)
                .appendTo(this.element);

            sw_opentsdbwidget_containerid = (this.sw_opentsdbwidget_containerid = '#' + $(this.sw_opentsdbwidget_container).attr('id'));

            // The OpenTSDB widget has a front and back face
            sw_opentsdbwidget_front = (this.sw_opentsdbwidget_front = $('<div>'))
                .addClass('widget-front')
                .appendTo(sw_opentsdbwidget);
            sw_opentsdbwidget_back = (this.sw_opentsdbwidget_back = $('<div>'))
                .addClass('widget-back')
                .appendTo(sw_opentsdbwidget);

            // Each face has a title bar, a main content area,
            // and a footer bar
            sw_opentsdbwidget_frontmain = (this.sw_opentsdbwidget_frontmain = $('<div>'))
                .addClass('widget-main')
                .addClass('front')
                .appendTo(sw_opentsdbwidget_front);
            sw_opentsdbwidget_backmain = (this.sw_opentsdbwidget_backmain = $('<div>'))
                .addClass('widget-main')
                .addClass('back')
                .appendTo(sw_opentsdbwidget_back);
            sw_opentsdbwidget_backfooter = (this.sw_opentsdbwidget_backfooter = $('<div>'))
                .addClass('widget-footer')
                .appendTo(sw_opentsdbwidget_back);

            sw_opentsdbwidget_graphdiv = (this.sw_opentsdbwidget_graphdiv = $('<div>'))
                .attr('id', 'graphdiv' + this.uuid)
                .addClass('graphdiv')
                .css('width', '99%')
                .appendTo(sw_opentsdbwidget_frontmain);

            sw_opentsdbwidget_legendbox = (this.sw_opentsdbwidget_legendbox = $('<div>'))
                .attr('id', 'legend-container' + this.uuid)
                .addClass('legend-container hidden')
                .append('<button type="button" class="legend-toggle legend-hide">' +
                    '<span class="elegant-icons arrow-triangle-down"></span></button>')
                .append('<div id="legend' + this.uuid + '" class="legend"></div>')
                .appendTo(sw_opentsdbwidget_frontmain);

            if (this.options.legend === "off") {
                that.hide_legend();
            }

            this.sw_opentsdbwidget_frontmain.on('click', 'button.legend-hide', function() {
                that.hide_legend(this);
            });

            this.sw_opentsdbwidget_frontmain.on('click', 'button.legend-show', function() {
                that.show_legend(this);
            });

            sw_opentsdbwidget_action = (this.sw_opentsdbwidget_action = $('<div>'))
                .addClass('widget-action dropdown')
                .append('<span data-toggle="dropdown">' +
                    '<span class="elegant-icons icon-cog"></span></span>' +
                    '<ul class="dropdown-menu sub-menu-item widget-action-options" role="menu">' +
                    '<li data-menu-action="maximize_widget"><span class="maximize-me">Maximize</span></li>' +
                    '<li data-menu-action="edit_params"><span>Edit Parameters</span></li>' +
                    '<li data-menu-action="show_graph_data"><span>Show graph data</span></li>' +
                    '<li class="clone-widget" data-parent="' + that.element.attr('id') + '"><span>Clone Widget</span></li>' +
                    '<li class="dropdown"><span>Options</span><span class="elegant-icons arrow-triangle-right"></span>' +
                    '<ul class="dropdown-menu sub-menu opentsdbwidget-options-menu">' +
                    '<li data-menu-action="set_all_spans"><span>Use this time span for all OpenTSDB Widgets</span></li>' +
                    '<li data-menu-action="set_all_tags_form"><span>Set tags for all OpenTSDB Widgets</span></li>' +
                    '<li data-menu-action="add_tags_to_all_form"><span>Add tag(s) to all OpenTSDB Widgets</span></li></ul></li>' +
                    '<li data-menu-action="go_click_handler"><span>Refresh Graph</span></li></ul>')
                .appendTo(sw_opentsdbwidget_frontmain);
            $('li.clone-widget').click(function(event) {
                event.stopImmediatePropagation();
                that.clone_widget($(this).attr('data-parent'));
            });

            sw_opentsdbwidget_draghandle = (this.sw_opentsdbwidget_draghandle = $('<span>'))
                .addClass('widget-drag-handle')
                .appendTo(sw_opentsdbwidget_front);

            sw_opentsdbwidget_close = (this.sw_opentsdbwidget_close = $('<div>'))
                .addClass('widget-close')
                .click(function(event) {
                    event.preventDefault();
                    $(sw_opentsdbwidget_container).addClass('transparent');
                    var that_id = sw_opentsdbwidget_containerid;
                    setTimeout(function() {
                        that.destroy(event);
                        sw_opentsdbwidget_container.remove();
                    }, 600);
                })
                .append('<span class="elegant-icons icon-close">')
                .appendTo(sw_opentsdbwidget_frontmain);



            sw_opentsdbwidget_savedsearchesmenu = (this.sw_opentsdbwidget_savedsearchesmenu = $('<div>'))
                .addClass('dropdown saved-searches-menu')
                .append('<span data-toggle="dropdown"><span class="widget-button-label">Saved Searches </span>' +
                    '<span class="elegant-icons arrow-triangle-down"></span></span>' +
                    '<ul class="dropdown-menu saved-searches-options" role="menu" aria-labelledby="dLabel"></ul>')
                .appendTo(sw_opentsdbwidget_backmain);

            // Buttons that will go in the footer bar
            sw_opentsdbwidget_querycancelbutton = (this.sw_opentsdbwidget_querycancelbutton = $('<div>'))
                .addClass("widget-footer-button left-button query_cancel")
                .click(function() {
                    that.flip_to_front();
                })
                .append('<span class="elegant-icons arrow-back"></span><span> Cancel</span>')
                .appendTo(sw_opentsdbwidget_backfooter);

            sw_opentsdbwidget_gobutton = (this.sw_opentsdbwidget_gobutton = $('<div>'))
                .addClass("widget-footer-button right-button go-button")
                .click(function(event) {
                    that.go_click_handler(event);
                })
                .append('<span class="elegant-icons icon-check"></span><span> Go</span>')
                .appendTo(sw_opentsdbwidget_backfooter);

            // Define the div for the search form
            sw_opentsdbwidget_searchform = (this.sw_opentsdbwidget_searchform = $('<div>'))
                .addClass('widget-search-form')
                .appendTo(sw_opentsdbwidget_backmain);

            that.build_search_form();

            if (that.options.query_data) {
                that.populate_search_form(that.options.query_data);
            } else {
                setTimeout(function() {
                    that.edit_params();
                }, 250);
            }


            $(sw_opentsdbwidget).on('click', 'li[data-menu-action]', function(event) {
                event.preventDefault();
                var action = $(this).attr('data-menu-action');
                that[action](this, that.sw_opentsdbwidget_containerid);
            });

            $(sw_opentsdbwidget).on('click', 'li[data-action]', function() {
                that.dropdown_menu_handler(this);
            });

            $(window).resize(function() {
                that.resize_graph();
            });

            // @Todo - implement resizing and dragging
//            $(that.sw_opentsdbwidget_container).resize(function() {
//                console.log('widget container is resizing!');
//                if (typeof that.svg !== "undefined") {
//                    that.resize_graph();
//                }
//            });
//
//            $(that.sw_opentsdbwidget_container).bind("resizestop", function(event, ui) {
//                console.log('resize stopped');
//                if (typeof that.svg !== "undefined") {
//                    that.resize_graph();
//                }
//            });

        },
        _destroy: function() {
            console.log('_destroy called');
            if (typeof this.autoupdate_timer !== "undefined") {
                clearTimeout(this.autoupdate_timer);
            }
            if (typeof this.ajax_request !== "undefined") {
                this.ajax_request.abort();
            }
            this.sw_opentsdbwidget_gobutton.remove();
            this.sw_opentsdbwidget_querycancelbutton.remove();
            this.sw_opentsdbwidget_searchform.remove();
            this.sw_opentsdbwidget_savedsearchesmenu.remove();
            this.sw_opentsdbwidget_backfooter.remove();
            this.sw_opentsdbwidget_backmain.remove();
            this.sw_opentsdbwidget_action.remove();
            this.sw_opentsdbwidget_close.remove();
            this.sw_opentsdbwidget_frontmain.remove();
            this.sw_opentsdbwidget_front.remove();
            this.sw_opentsdbwidget_back.remove();
            this.sw_opentsdbwidget.remove();
        },
        dropdown_menu_handler: function(item) {
            var widget = this;
            var button = $(item).parent().parent().children('span');
            var action = $(item).attr('data-action');
            $(button).children('.widget-button-label').text($(item).text());
            $(button).children('div.ds-interval').attr('data-value', $(item).children('span').attr('data-value'));
            if ($(item).parent().attr('id') === "time-span-options" + widget.uuid) {
                $(button).children('div#time-span' + widget.uuid).attr('data-ms', $(item).children('span').attr('data-ms')).text();
            }
        },
        edit_params: function() {
            var widget = this;
            $(window).scrollTop(0);
            if ($('#spinner' + widget.uuid).length > 0) {
                widget.sw_opentsdbwidget_graphdiv.empty();
            }
            widget.start_height = widget.sw_opentsdbwidget_container.height();
            widget.start_width = widget.sw_opentsdbwidget_container.width();
            widget.widget_position = widget.sw_opentsdbwidget_container.position();
            widget.sw_opentsdbwidget.addClass('flipped');
            $('div.container').after('<div class="bodyshade transparent">');
            setTimeout(function() {
                widget.sw_opentsdbwidget_container.css({position: 'absolute', height: widget.start_height + 'px', width: widget.start_width + 'px', top: widget.widget_position.top, left: widget.widget_position.left, 'z-index': '500'})
                    .after('<div class="spacer-box" style="display: inline-block; width: ' + widget.start_width +
                        'px; height: ' + widget.start_height +
                        'px; margin: ' + widget.sw_opentsdbwidget.parent().css('margin') + '"></div>');
                widget.sw_opentsdbwidget_container.css({top: '15%', left: '15%', height: '70%', width: '65%'});
                $('div.bodyshade').removeClass('transparent');
            }, 700);
        },
        flip_to_front: function() {
            var widget = this;
            if ($('div.bodyshade').length > 0) {
                $('div.bodyshade').addClass('transparent');
                widget.sw_opentsdbwidget_container.css({width: widget.start_width, height: '', top: widget.widget_position.top, left: widget.widget_position.left});
                setTimeout(function() {
                    widget.sw_opentsdbwidget_container.css({position: '', top: '', left: '', 'z-index': '', height: '', width: ''})
                        .siblings('div.spacer-box').remove();
                    $('div.bodyshade').remove();
                }, 600);
                setTimeout(function() {
                    if (typeof widget.svg !== "undefined") {
                        widget.resize_graph();
                    }
                    widget.sw_opentsdbwidget.removeClass('flipped');
                }, 700);
            }
            else {
                widget.sw_opentsdbwidget.removeClass('flipped');
                if ($('#' + widget.element.attr('id')).hasClass('transparent')) {
                    $('#' + widget.element.attr('id')).removeClass('transparent');
                }
            }
        },
        maximize_widget: function() {
            var widget = this;
            if ($(widget.sw_opentsdbwidget_container).hasClass('maximize-widget')) {
                $(widget.sw_opentsdbwidget_container).removeClass('maximize-widget');
                $(widget.sw_opentsdbwidget_container).addClass('cols-' + document._session.dashboard_columns);
                $('body').removeClass('no-overflow');
                $('.menubar').removeClass('hidden');
                $(widget.sw_opentsdbwidget_containerid + ' span.maximize-me').text('Maximize');
                if (typeof widget.svg !== "undefined") {
                    setTimeout(function() {
                        widget.resize_graph();
                    }, 350);
                }
            }
            else {
                $(window).scrollTop(0);
                $(widget.sw_opentsdbwidget_container).removeClass('cols-' + document._session.dashboard_columns);
                $(widget.sw_opentsdbwidget_container).addClass('maximize-widget');
                $('.menubar').addClass('hidden');
                $('body').addClass('no-overflow');
                $(widget.sw_opentsdbwidget_containerid + ' span.maximize-me').text('Minimize');
                if (typeof widget.svg !== "undefined") {
                    setTimeout(function() {
                        widget.resize_graph();
                    }, 350);
                }
            }
        },
        resize_graph: function() {
            var widget = this;
            var widget_main = widget.sw_opentsdbwidget_frontmain,
                graph_div = widget.sw_opentsdbwidget_graphdiv,
                graph_div_offset = graph_div.position().top,
                graph_legend = widget.sw_opentsdbwidget_frontmain.children('div.legend-container');
            graph_legend.css('width', widget_main.innerWidth());
            widget_main.css('height', widget.sw_opentsdbwidget.innerHeight());
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
            $(widget.sw_opentsdbwidget_containerid + ' text.graph-title').remove();
            widget.svg.select('.x.axis')
                .append('text')
                .classed('graph-title', 1)
                .classed('hidden', 1)
                .attr('text-anchor', 'middle')
                .text(widget.query_data.title);
            widget.format_graph_title();
            if (widget.query_data['history_graph'] === "anomaly") {
                widget.svg.selectAll('.anomaly-bars').selectAll('rect')
                    .attr('x', function(d) {
                        return widget.graph.x(d.start_time);
                    })
                    .attr('height', widget.graph.height)
                    .attr('width', function(d) {
                        return (widget.graph.x(d.end_time) - widget.graph.x(d.start_time));
                    });
            }
            widget.graph.data_left = [];
            if (typeof widget.graph.data_right !== "undefined") {
                widget.graph.data_right = [];
            }
            $.each(widget.graph.raw_data, function(s, d) {
                var line_values;
                if (widget.query_data.metrics[d.search_key].ds_type) {
                    line_values = widget.downsample_series(d.values, widget.graph.width * 0.5);
                } else {
                    line_values = d.values;
                }
                $.each(line_values, function(v) {
                    line_values[v]['date'] = new Date(line_values[v]['timestamp'] * 1000);
                });
                if (d.axis === "right") {
                    widget.graph.right_axis = true;
                    widget.graph.data_right.push({axis: d.axis, name: d.name, search_key: d.search_key, values: line_values});
                }
                else {
                    widget.graph.data_left.push({axis: d.axis, name: d.name, search_key: d.search_key, values: line_values});
                }
                delete(line_values);
            });
            var metric = widget.svg.selectAll('.metric.left');
            metric.data(widget.graph.data_left);
            metric.selectAll('path')
                .attr('d', function(d) {
                    console.log('Points graphed: ' + d.values.length);
                    return widget.graph.line(d.values);
                });
            if (widget.graph.right_axis == true) {
                widget.svg.selectAll('.metric.right')
                    .data(widget.graph.data_right)
                    .selectAll('path')
                        .attr('d', function(d) {
                            return widget.graph.line_right(d.values);
                        });
            }
        },
        clone_widget: function(widget_id) {
            // Add a new widget as a duplicate of the selected widget
            var widget = $('#' + widget_id).data('sw-opentsdbwidget');
            var username = document._session.dashboard_username;
            var new_widget_id = "widget" + md5(username + new Date.now().getTime());
            var new_widget_options = widget.options;
            new_widget_options.query_data = null;
            $('#dashboard-container').append('<div id="' + new_widget_id + '" class="widget-container cols-' + document._session.dashboard_columns + '" data-widget-type="opentsdbwidget">');
            var new_widget = $('div#' + new_widget_id).opentsdbwidget(widget.options);
            setTimeout(function() {
                $(new_widget).removeClass('transparent');
            }, 100);
            var new_widget_object = $(new_widget).data('sw-opentsdbwidget');
            $('#search-title' + new_widget_object.uuid).css('width', $('#search-title' + widget.uuid).width());
            new_widget_object.populate_search_form(widget.query_data, 'clone');
        },
        set_all_spans: function(element, proto_widget_id) {
            var proto_widget = $(proto_widget_id).data('sw-opentsdbwidget');
            if (proto_widget.graph.brush.empty() !== true) {
                var proto_period = 'date-search';
                var proto_no_autoupdate = true;
            }
            else {
                var proto_period = proto_widget.query_data.period;
            }
            var proto_time_span = proto_period === "span-search" ? proto_widget.query_data.time_span : false;
            var proto_start_time = parseInt(proto_widget.graph.x.domain()[0].getTime() / 1000);
            var proto_end_time = parseInt(proto_widget.graph.x.domain()[1].getTime() / 1000);
            var widget_list = $('.widget-container.opentsdb-widget');
            $.each(widget_list, function(i, widget_element) {
                var widget_id = '#' + $(widget_element).attr('id');
                var widget = $(widget_id).data('sw-opentsdbwidget');
                widget.query_data.period = proto_period;
                if (proto_time_span) {
                    widget.query_data.time_span = proto_time_span;
                }
                widget.query_data.end_time = proto_end_time;
                widget.query_data.start_time = proto_start_time;
                if (typeof proto_no_autoupdate !== "undefined") {
                    widget.query_data.autoupdate = false;
                }
                widget.populate_search_form(widget.query_data);
            });
        },
        set_all_tags_form: function(element) {
            var widget = this;
            var widget_list = $('.widget-container[data-widget-type="opentsdbwidget"]');
            var tags_popup = '<div class="popup" id="set-all-tags-popup">' +
                '<div id="set-all-tags-box"><div id="set-all-tags-head"><h4>Set tags for all OpenTSDB Widgets</h4></div>' +
                '<div id="set-all-tags-info"><p>Replaces any existing tags for the searches in all OpenTSDB Widgets with the tags specified here.</p></div>' +
                '<div id="set-all-tags-form" style="margin-bottom: 25px;"><div class="popup-form-data"><form onsubmit="return false;">' +
                '<input type="text" class="input" id="new-tags" name="new-tags" value="" style="width: 500px;" onkeypress="if (event.which === 13) { $.magnificPopup.instance.items[0].data.set_all_tags(); }">' +
                '</form></div></div>' +
                '<div class="popup-footer" style="margin-top: 10px;">' +
                '<div class="popup-footer-button cancel-button" id="cancel-set-all-tags" onClick="$.magnificPopup.close()"><span class="elegant-icons icon-close"><span class="font-reset"> Cancel</span></span></div>' +
                '<div class="popup-footer-button save-button" id="set-all-tags-button" onClick="$.magnificPopup.instance.items[0].data.set_all_tags()">' +
                '<span class="elegant-icons icon-check"><span class="font-reset"> Save</span></span></div>' +
                '</div>';
            $.magnificPopup.open({
                items: {
                    src: tags_popup,
                    type: 'inline',
                    set_all_tags: function() {
                        var new_tag_string = $('input#new-tags').val();
                        if (new_tag_string.length > 0) {
                            var new_tags = [];
                            if (new_tag_string.match(',')) {
                                new_tags = new_tag_string.split(',');
                            }
                            else {
                                new_tags = new_tag_string.split(' ');
                            }
                            widget_list = $('.widget-container[data-widget-type="opentsdbwidget"]');
                            $.each(widget_list, function(i, widget_element) {
                                var widget_id = '#' + $(widget_element).attr('id');
                                var widget = $(widget_id).data('sw-opentsdbwidget');
                                $.each(widget.query_data.metrics, function(search_key, search_params) {
                                    search_params.tags = new_tags;
                                });
                                widget.populate_search_form(widget.query_data);
                            });
                        }
                        $.magnificPopup.close();
                    }
                }, preloader: false,
                removalDelay: 300,
                mainClass: 'popup-animate',
                callbacks: {
                    open: function() {
                        setTimeout(function() {
                            $('.container').addClass('blur');
                            $('.navbar').addClass('blur');
                        }, 150);
                    }, close: function() {
                        $('.container').removeClass('blur');
                        $('.navbar').addClass('blur');
                    }, afterClose: function() {
                        $('#set-all-tags-popup').remove();
                    }
                }
            });
        },
        add_tags_to_all_form: function(element) {
            var widget = this;
            var widget_list = $('.widget-container[data-widget-type="opentsdbwidget"]');
            var tags_popup = '<div class="popup" id="add-tags-popup">' +
                '<div id="add-tags-box"><div id="add-tags-head"><h4>Set tags for all Graph Widgets</h4></div>' +
                '<div id="add-tags-info"><p>Add tag(s) specified here to all Graph Widgets.</p></div>' +
                '<div id="add-tags-form" style="margin-bottom: 25px;"><div class="popup-form-data"><form onsubmit="return false;">' +
                '<input type="text" class="input" id="new-tags" name="new-tags" value="" style="width: 500px;" onkeypress="if (event.which === 13) { $.magnificPopup.instance.items[0].data.add_tag_to_all(); }">' +
                '</form></div></div>' +
                '<div class="popup-footer" style="margin-top: 10px;">' +
                '<div class="popup-footer-button cancel-button" id="cancel-add-tags" onClick="$.magnificPopup.close()"><span class="elegant-icons icon-close"></span><span> Cancel</span></div>' +
                '<div class="widget-footer-button save-button" id="add-tags-button" onClick="$.magnificPopup.instance.items[0].data.add_tag_to_all()">' +
                '<span class="elegant-icons icon-check"></span><span> Save</span></div>' +
                '</div>';
            $.magnificPopup.open({
                items: {
                    src: tags_popup,
                    type: 'inline',
                    add_tag_to_all: function() {
                        var new_tag_string = $('input#new-tags').val();
                        if (new_tag_string.length > 0) {
                            var new_tags = [];
                            if (new_tag_string.match(',')) {
                                new_tags = new_tag_string.split(',');
                            }
                            else {
                                new_tags = new_tag_string.split(' ');
                            }
                            widget_list = $('.widget-container[data-widget-type="opentsdbwidget"]');
                            $.each(widget_list, function(i, widget_element) {
                                var widget_id = '#' + $(widget_element).attr('id');
                                var widget = $(widget_id).data('sw-opentsdbwidget');
                                $.each(widget.query_data.metrics, function(search_key, search_params) {
                                    search_params.tags.push(new_tags);
                                });
                                widget.populate_search_form(widget.query_data);
                            });
                        }
                        $.magnificPopup.close();
                    }
                }, preloader: false,
                removalDelay: 300,
                mainClass: 'popup-animate',
                callbacks: {
                    open: function() {
                        setTimeout(function() {
                            $('.container').addClass('blur');
                            $('.navbar').addClass('blur');
                        }, 150);
                    }, close: function() {
                        $('.container').removeClass('blur');
                        $('.navbar').addClass('blur');
                    }, afterClose: function() {
                        $('#set-all-tags-popup').remove();
                    }
                }
            });
        },
        build_search_form: function() {

            var widget = this;
            var widget_num = widget.uuid,
                widget_element = widget.element;

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

            var form_div = widget.sw_opentsdbwidget_searchform;
            form_div.append('<table class="general-options-table" id="graph-search-general' + widget_num + '">');
            var form_table = form_div.children('table#graph-search-general' + widget_num);

            form_table.append('<tr><td colspan="2" class="search-title" id="search-title' + widget_num + '">' +
                '<h3 class="search-title-prompt">Click to set search title</h3>' +
                '<input type="text" id="search-title-input" name="search-title-input' + widget_num + '" class="nodisplay" size="50">' +
                '</td></tr>');

            form_table.append('<tr><td width="30%"><div class="toggle-button-group">' +
                '<div class="toggle-button section-toggle"><label>' +
                '<input type="radio" class="date-search" name="date-span" value="date-search" data-target="widget-dates' + widget_num + '">' +
                '<span>Date Range</span></label>' +
                '</div><div class="toggle-button section-toggle toggle-on"><label>' +
                '<input type="radio" class="span-search" name="date-span" value="span-search" checked="checked" data-target="widget-time-span' + widget_num + '">' +
                '<span>Time Span</span></label></div></div></td>' +
                '<td><div class="section section-off widget-dates" id="widget-dates' + widget_num + '">' +
                '<div class="widget-form-item menu-label" id="start-time' + widget_num + '">' +
                '<input size="25" type="text" class="input input-append date-input" data-format="yyyy/MM/dd hh:mm:ss" name="start-time" placeholder="Start">' +
                '<span class="input-addon-button"><span class="elegant-icons icon-calendar"></span></span></div>' +
                '<div class="widget-form-item menu-label" id="end-time' + widget_num + '">' +
                '<input size="25" type="text" class="input input-append date-input" data-format="yyyy/MM/dd hh:mm:ss" name="end-time" placeholder="End">' +
                '<span class="input-addon-button"><span class="elegant-icons icon-calendar"></span></span></div></div>' +
                '<div class="section section-on widget-time-span" id="widget-time-span' + widget_num + '">' +
                '<div class="widget-form-item menu-label" style="margin-right: 0;">' +
                '<h4>Show Me The Past</h4>' +
                '<div class="dropdown sw-button"><span data-toggle="dropdown">' +
                '<div class="widget-button-label" id="time-span' + widget_num + '" data-ms="14400">4 Hours</div>' +
                '<span class="dropdown-arrow-container"><span class="elegant-icons arrow-triangle-down"></span></span></span>' +
                '<ul class="dropdown-menu menu-left" id="time-span-options' + widget_num + '" role="menu" aria=labelledby="dLabel">' +
                long_span_menu + '</ul></div></div></div></td></tr>');

            form_table.append('<tr></td><td><div class="auto-update"><div class="push-button">' +
                '<input type="checkbox" name="auto-update"><label>' +
                '<span class="elegant-icons icon-close-alt red"></span><span> Auto Update</span></label></div></div></td>' +
                '<td><div class="toggle-button-group"><div class="toggle-button section-toggle toggle-on"><label>' +
                '<input type="radio" class="history-no" name="history-graph" checked="checked" data-target="history-no' + widget_num + '" value="no">' +
                '<span>No History</span></label>' +
                '</div><div class="toggle-button section-toggle"><label>' +
                '<input type="radio" class="history-anomaly" name="history-graph" data-target="history-anomaly' + widget_num + '" value="anomaly">' +
                '<span>Anomaly</span></label>' +
                '</div><div class="toggle-button section-toggle"><label>' +
                '<input type="radio" class="history-wow" name="history-graph" data-target="history-wow' + widget_num + '" value="wow">' +
                '<span>Week-Over-Week</span></label></div></div></td></tr>');

            $(widget.sw_opentsdbwidget_searchform).on('click', 'h3', function() {
                var search_title_text = $(this).text();
                $(this).addClass('nodisplay');
                var title_input = $(this).siblings('input');
                title_input.removeClass('nodisplay');
                if ($(this).hasClass('search-title-prompt')) {
                    title_input.attr('placeholder', 'Search Title');
                } else {
                    title_input.val(search_title_text);
                }
                title_input.css({
                    'font-size': $(this).css('font-size'), 'font-weight': $(this).css('font-weight')
                }).focus();
            });

            $(widget.sw_opentsdbwidget_searchform).on('blur', 'input#search-title-input', function() {
                var search_title_text = $(this).siblings('h3').text();
                var changed_title = $(this).val();
                $(this).addClass('nodisplay');
                $(this).siblings('h3').removeClass('nodisplay');
                if (changed_title.length > 1) {
                    $(this).siblings('h3').text(changed_title).removeClass('search-title-prompt');
                } else {
                    $(this).siblings('h3').text(search_title_text);
                }
            });

            // The enter keypress when the search name edit field has focus
            // does the same as above
            $(widget.sw_opentsdbwidget_backmain).on('keydown', 'input#search-title-input', function(event) {
                if (event.which === 13) {
                    $(this).blur();
                }
            });

            form_div.append('<div class="metrics-tabs-box" id="metrics-tabs-box' + widget_num + '">');
            var tabs_box = $('div#metrics-tabs-box' + widget_num);
            tabs_box.append('<div class="metric-input-tabs tabbable tab-below" style="width: 99%;">' +
                '<div class="tab-content" id="tab-content' + widget_num + '"></div><ul class="nav nav-tabs" id="tab-list' + widget_num + '"></ul></div>');

            var add_metric_button_id = 'add-metric-button' + widget_num;

            $('#tab-list' + widget_num).append('<li id="' + add_metric_button_id + '" class="add-tab"><span class="elegant-icons icon-plus"></span></li>');
            $('#' + add_metric_button_id)
                .click(function() {
                    widget.metric_count = widget.add_tab(widget.metric_count, widget_num);
                    $('input[name="metric' + widget_num + '-' + widget.metric_count + '"]').autocomplete({
                        minChars: 2,
                        serviceUrl: window.location.origin + '/api/datasource/opentsdb/suggest_metrics',
                        containerClass: 'autocomplete-suggestions dropdown-menu',
                        appendTo: '#metric-options' + widget_num + '-' + widget.metric_count + ' .metric-input-textbox',
                        zIndex: '',
                        maxHeight: '300',
                        width: 300
                    });
                    if (widget.metric_count == 6) {
                        $(this).addClass('hidden');
                    }
                });

            var auto_update = $(widget_element).find('div.auto-update').children('div.push-button');
            $(auto_update).children('input').attr('id', 'auto-update-button' + widget_num);
            $(auto_update).children('label').attr('for', 'auto-update-button' + widget_num);

            // Add the handler for the date/time picker and init the form objects
            $('#start-time' + widget_num).datetimepicker({collapse: false});
            $('#end-time' + widget_num).datetimepicker({collapse: false});

            widget.metric_count = widget.add_tab(widget.metric_count, widget_num);
            $('#metric-options' + widget_num + '-' + widget.metric_count + ' .metric-autocomplete').autocomplete({
                minChars: 2,
                serviceUrl: window.location.origin + '/api/datasource/opentsdb/suggest_metrics',
                containerClass: 'autocomplete-suggestions dropdown-menu',
                appendTo: '#metric-options' + widget_num + '-' + widget.metric_count + ' .metric-input-textbox',
                zIndex: '',
                maxHeight: 300,
                width: 300
            }).keyup(function(event) {
                widget.load_suggested_tags(this.value, this.parentNode, event.keyCode);
            });

            var main_height = $(widget_element).children('.widget').innerHeight();
            widget.sw_opentsdbwidget_frontmain.css('height', main_height);

            $.when(widget.build_saved_search_menu()).then(
                function(data) {
                    if (data[1] > data[0]) {
                        widget.sw_opentsdbwidget_savedsearchesmenu.children('ul.saved-searches-options')
                            .append('<li class="full-saved-search-list"><span>More...</span></li>');
                    }
                }
            );

            widget.sw_opentsdbwidget_savedsearchesmenu.on('click', 'li.full-saved-search-list', function() {
                $('body').append('<div id="saved-search-list-popup" class="popup mfp-hide"></div>');
                var search_popup = $('div#saved-search-list-popup');
                search_popup.append('<div id="my-searches-box" class="saved-search-box">' +
                    '<div id="my-searches-head"><h2>My Searches</h2></div>' +
                    '<div id="my-searches-list" class="saved-search-list">' +
                    '<table id="my-searches-list-table"><tr><th>Search Title</th></tr></table>' +
                    '</div></div>');

                $.each(widget.saved_searches.my_searches, function(i, search) {
                    $('table#my-searches-list-table').append('<tr class="search-item">' +
                        '<td><span data-search-id="' + search.id + '">' + search.title + '</span></td></tr>');
                });

                search_popup.append('<div id="shared-searches-box" class="saved-search-box">' +
                    '<div id="shared-searches-head"><h2>Shared Searches</h2></div>' +
                    '<div id="shared-searches-list" class="saved-search-list">' +
                    '<table id="shared-searches-list-table">' +
                    '<tr class="header-row"><th>Search Title</th><th>User</th></tr></table>' +
                    '</div></div>');

                $.each(widget.saved_searches.shared_searches, function(i, search) {
                    if (search.username === document._session.dashboard_username) {
                        $('table#shared-searches-list-table').children('tbody').children('tr.header-row')
                            .after('<tr class="search-item">' +
                                '<td><span data-search-id="' + search.id + '">' + search.title + '</span></td>' +
                                '<td>' + search.username + '</td></tr>')
                    } else {
                        $('table#shared-searches-list-table').append('<tr class="search-item">' +
                            '<td><span data-search-id="' + search.id + '">' + search.title + '</span></td>' +
                            '<td>' + search.username + '</td></tr>');
                    }
                });

                $.magnificPopup.open({
                    items: {
                        src: search_popup,
                        type: 'inline'
                    },
                    preloader: false,
                    removalDelay: 300,
                    callbacks: {
                        open: function() {
                            setTimeout(function() {
                                $('.container').addClass('blur');
                                $('.navbar').addClass('blur');
                            }, 150);
                        },
                        close: function() {
                            $('.container').removeClass('blur');
                            $('.navbar').removeClass('blur');
                        },
                        afterClose: function() {
                            search_popup.remove();
                        }
                    }
                });

                search_popup.on('click', 'tr.search-item', function() {
                    var saved_query = {};
                    if (search_id = $(this).children('td').children('span').attr('data-search-id')) {
                        $.ajax({
                            url: window.location.origin + "/" +
                                "/saved/OpenTSDB/" + search_id,
                            type: 'GET',
                            dataType: 'json',
                            success: function(data) {
                                saved_query = data;
                                delete(saved_query.private);
                                delete(saved_query.save_span);
                                delete(saved_query.user_id);
                                $.magnificPopup.close();
                                widget.populate_search_form(saved_query);
                            }
                        });
                    }
                });
            });

            $(form_div).on('click', '.toggle-button', function() {
                statuswolf_button(this);
                if ($(this).hasClass('section-toggle')) {
                    var data_target = $(this).children('label').children('input').attr('data-target');
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
                }
            });

            $(widget.sw_opentsdbwidget_backmain).on('click', '.tab-close', function() {
                widget.close_tab($(this).attr('data-tab-number'));
                if ($('#' + add_metric_button_id).hasClass('hidden')) {
                    $('#' + add_metric_button_id).removeClass('hidden');
                }
            });

            $(form_div).on('click', '.push-button', function() {
                statuswolf_button(this);
            });
        },
        add_tab: function(tab_num, widget_num) {
            var widget = this;
            tab_num++;

            var tab_content = $('div#tab-content' + widget_num)
                , tab_tag = widget_num + '-' + tab_num
                , tab_list = $('ul#tab-list' + widget_num);

            $(tab_content).append('<div class="tab-pane" id="tab' + tab_tag + '">');
            var tab_pane = $(tab_content).children('div#tab' + tab_tag);

            if (tab_num > 1) {
                tab_pane.append('<div class="tab-close" id="tab-close' + tab_tag + '" data-tab-number="' + tab_num + '">' +
                    '<span class="elegant-icons icon-close"></span></div>');
            }
            tab_pane.append('<table class="tab-table" id="metric-options' + tab_tag + '">');
            tab_table = $(tab_pane.children('table#metric-options' + tab_tag));
            tab_table.append('<tr><td colspan="4"><div class="metric-input-textbox" id="metric-input-textbox' + tab_tag + '">' +
                '<input type="text" class="metric-autocomplete" name="metric' + tab_tag + '" placeholder="Metric name and tags">' +
                '</div></td></tr>' +
                '<tr><td width="25%"><div class="widget-form-item menu-label" style="min-width: 155px;">' +
                '<div class="push-button" style="margin-top: 10px; width: 95%;">' +
                '<input type="checkbox" id="rate-button' + tab_tag + '" name="rate' + tab_tag + '">' +
                '<label for="rate-button' + tab_tag + '"><span class="elegant-icons icon-close-alt red"></span>' +
                '<span class="binary-label">Rate</span></label></div></div></td>' +
                '<td width="75%"><div class="widget-form-item menu-label" id="aggregation' + tab_tag + '" style="margin-right: 0;">' +
                '<h4>Aggregation</h4><div class="dropdown sw-button">' +
                '<span data-toggle="dropdown"><div class="widget-button-label" id="active-aggregation-type' + tab_tag + '">Sum</div>' +
                '<span class="dropdown-arrow-container"><span class="elegant-icons arrow-triangle-down"></span></span></span>' +
                '<ul class="dropdown-menu" id="aggregation-type=options' + tab_tag + '" role="menu" aria-labelledby="dLabel">' +
                '<li data-action="set-agg-type"><span>Sum</span></li>' +
                '<li data-action="set-agg-type"><span>Average</span></li>' +
                '<li data-action="set-agg-type"><span>Minimum Value</span></li>' +
                '<li data-action="set-agg-type"><span>Maximum Value</span></li>' +
                '<li data-action="set-agg-type"><span>Standard Deviation</span></li>' +
                '</ul></div></td></tr>' +
                '<tr><td><div class="widget-form-item menu-label" style="min-width: 155px;">' +
                '<div class="push-button" style="margin-top: 10px; width: 95%;">' +
                '<input type="checkbox" id="y2-button' + tab_tag + '" name="y2-' + tab_tag + '">' +
                '<label for="y2-button' + tab_tag + '"><span class="elegant-icons icon-close-alt red"></span>' +
                '<span class="binary-label">Right Axis</span></label></div></div></td>' +
                '<td><div class=""widget-form-item menu-label" style="min-width: 155px;">' +
                '<div class="push-button pushed" style="margin-top: 10px;">' +
                '<input type="checkbox" id="ds-button' + tab_tag + '" name="ds-' + tab_tag + '" checked="Checked">' +
                '<label for="ds-button' + tab_tag + '"><span class="elegant-icons icon-check-alt green"></span>' +
                '<span class="binary-label">Downsample</span></label></div></div></td></tr>' +
                '<tr><td><div class="widget-form-item menu-label" style="min-width: 155px;">' +
                '<div class="push-button info-tooltip-right" style="margin-top: 10px; width: 95%;" title="If selected, timestamp buckets with no data will be displayed as 0, otherwise they\'ll be skipped over.">' +
                '<input type="checkbox" id="null-zero-button' + tab_tag + '" name="null-zero' + tab_tag + '">' +
                '<label for="null-zero-button' + tab_tag + '"><span class="elegant-icons icon-close-alt red"></span>' +
                '<span class="binary-label">Treat Null As Zero</span></label></div></td><td></td></tr>' +
                '<tr><td><div id="lerp-button-container" class="hidden widget-form-item menu-label" style="min-width: 155px;">' +
                '<div class="push-button info-tooltip-right" style="margin-top: 10px; width: 95%;" title="Interpolation should be disabled unless you are absolutely sure that you need it.">' +
                '<input type="checkbox" id="lerp-button' + tab_tag + '" name="lerp' + tab_tag + '">' +
                '<label for="lerp-button' + tab_tag + '"><span class="elegant-icons icon-close-alt red"></span>' +
                '<span class="binary-label">Interpolation</span></label></div></div></td></tr>');
            if (widget.options.nointerpolation) {
                $('div#lerp-button-container').removeClass('hidden');
            } else {
                $('input#lerp-button' + tab_tag).prop('checked', true);
            }

            $('<li id="nav-tab' + tab_tag + '" class="nav-tab"><a href="#tab' + tab_tag + '" data-toggle="tab">Metric ' + tab_num + '</a></li>')
                .insertBefore($('#add-metric-button' + widget_num));
            $('#' + tab_list.attr('id') + ' a[href="#tab' + tab_tag + '"]').click(function(event) {
                event.preventDefault();
                $(this).tab('show');
            });

            $('#' + tab_list.attr('id') + ' a[href="#tab' + tab_tag + '"]').tab('show');

            $('.info-tooltip').tooltip({placement: 'bottom'});
            $('.info-tooltip-right').tooltip({placement: 'right'});
            $('.info-tooltip-left').tooltip({placement: 'left'});
            $('.info-tooltip-top').tooltip({placement: 'top'});

            return tab_num;
        },
        close_tab: function(tab_number) {
            var widget = this;

            $('li#nav-tab' + widget.uuid + '-' + tab_number).remove();
            $('#tab' + widget.uuid + '-' + tab_number).remove();
            var remaining_tabs = $('#tab-content' + widget.uuid + ' .tab-pane');
            var tab_count = 0;
            $.each(remaining_tabs, function(i, tab) {
                tab_count++;
                var tab_id = $(tab).attr('id');
                $(tab).attr('id', 'tab' + widget.uuid + '-' + tab_count);
                $(tab).children('.tab-close')
                    .attr('id', 'tab-close' + widget.uuid + '-' + tab_count)
                    .attr('data-tab-number', tab_count);
                $('li#nav-' + tab_id)
                    .attr('id', 'nav-tab' + widget.uuid + '-' + tab_count)
                    .children('a').attr('href', '#tab' + widget.uuid + '-' + tab_count).text('Metric ' + tab_count);
            });

            $('#tab-content' + widget.uuid).children('.tab-pane:last-child').addClass('active');
            $('#tab-list' + widget.uuid).children('li#nav-tab' + widget.uuid + '-' + tab_count).addClass('active');

            widget.metric_count = tab_count;

        },
        load_suggested_tags: function(search_string, dom_parent, keyCode) {
            var widget = this;

            if (window.GLOBAL_METRIC_TO_TAG_CACHE == undefined) {
                window.GLOBAL_METRIC_TO_TAG_CACHE = {};
            }
            var query_bits = search_string.split(" ");
            var metric = query_bits[0];
            var parent_node = $(dom_parent);

            // Check if we have a cached entry.
            if (window.GLOBAL_METRIC_TO_TAG_CACHE[metric]) {
                if (parent_node.children().length == 1 && window.GLOBAL_METRIC_TO_TAG_CACHE[metric] != 'pending') {
                    var display_string = "No tags found";
                    if (window.GLOBAL_METRIC_TO_TAG_CACHE[metric] > 0) {
                        display_string = window.GLOBAL_METRIC_TO_TAG_CACHE[metric].join(", ") ;
                    }
                    parent_node.append("<p class='tag-suggestions'><b>Suggested Tags:</b> " + display_string + "</p>");
                }
                return;
            }

            // If there was no cached entry, remove any suggested tag displays.
            parent_node = $(dom_parent);
            if (parent_node.children('.tag-suggestions').length > 0) {
                parent_node.children('.tag-suggestions').remove();
            }

            // If no cached entry and there is only one query bit,
            // don't worry about it - the full metric name may not have been typed yet.
            // If we have greater than 1, or if we explicitly hit "enter" to dismiss
            // the autocomplete on metric name, let's fetch the tags for the metric.
            if (query_bits.length > 1 || keyCode == 13) {
                // While fetching, immediately set a pending entry. This is so we don't continually
                // fire AJAX requests. On return, the AJAX request will set the cached entry.
                // Pending entries should not override real entries.
                window.GLOBAL_METRIC_TO_TAG_CACHE[metric] = 'pending';

                // Set up some AJAX that will fetch suggested tags and then render.
                $.ajax({
                    url: window.location.origin + '/api/datasource/opentsdb/suggest_tags?metric=' + metric
                    ,type: 'GET'
                    ,success: function(data) {
                        data = $.parseJSON(data);
                        window.GLOBAL_METRIC_TO_TAG_CACHE[data['metric']] = data['suggestions'];
                        if (parent_node.children('.tag-suggestions').length == 0) {
                            var display_string = "No tags found";
                            if (data['suggestions'].length > 0) {
                                display_string = data['suggestions'].join(", ") ;
                            }
                            parent_node.append("<p class='tag-suggestions'><b>Suggested Tags:</b> " + display_string + "</p>");
                        }

                    }
                });
            }
        },
        build_saved_search_menu: function() {
            var widget = this;
            var user_id = document._session.dashboard_userid;
            var api_url = window.location.origin + '/api/search/saved/OpenTSDB';
            var menu_length = 15;
            widget.saved_searches = {};

            var saved_search_menu = new $.Deferred();
            var saved_search_menu_query = $.ajax({
                    url: api_url,
                    type: 'GET',
                    dataType: 'json'
                }),
                chain = saved_search_menu_query.then( function(data) {
                    return(data)
                });
            chain.done( function(data) {
                var my_searches = data.user_searches;
                var shared_searches = data.shared_searches;
                var saved_search_list = widget.sw_opentsdbwidget_backmain.children('.saved-searches-menu').children('ul.saved-searches-options')
                saved_search_list.empty();
                var max = menu_length;
                saved_search_list.append('<li class="menu-section"><span>My Searches</span></li>');
                if (my_searches) {
                    widget.saved_searches.my_searches = my_searches;
                    max = (my_searches.length < menu_length ? my_searches.length : menu_length);
                    for (i = 0; i < max; i++) {
                        saved_search_list.append('<li><span data-name="search-' + my_searches[i].id + '">' + my_searches[i].title + '</span></li>');
                        menu_length--;
                    }
                }
                if (shared_searches) {
                    widget.saved_searches.shared_searches = shared_searches;
                    if (menu_length > 0) {
                        max = (shared_searches.length < menu_length ? shared_searches.length : menu_length);
                        saved_search_list.append('<li class="menu-section"><span class="divider"></span></li>');
                        saved_search_list.append('<li class="menu-section"><span>Shared Searches</span></li>');
                        for (i = 0; i < max; i++) {
                            if (shared_searches[i].user_id === document._session.dashboard_userid) {
                                saved_search_list.children('li.menu-section:last').after('<li><span data-name="search-' + shared_searches[i].id + '">' + shared_searches[i].title + '</span></li>');
                            } else {
                                saved_search_list.append('<li><span data-name="search-' + shared_searches[i].id + '">' + shared_searches[i].title + '</span></li>');
                            }
                        }
                    }
                }

                widget.sw_opentsdbwidget_backmain.children('.saved-searches-menu').children('ul.saved-searches-options').on('click', 'li', function() {
                    var saved_query = {};
                    if (search_name = $(this).children('span').attr('data-name')) {
                        var search_bits = search_name.split('-');
                        var search_id = search_bits[1];
                        $.ajax({
                            url: window.location.origin + "/api/search/saved/OpenTSDB/" + search_id,
                            type: 'GET',
                            dataType: 'json',
                            success: function(data) {
                                saved_query = data;
                                delete(saved_query.private);
                                delete(saved_query.save_span);
                                delete(saved_query.user_id);
                                widget.populate_search_form(saved_query, true);
                            }
                        });
                    }

                });

                var my_search_count = (typeof my_searches !== "undefined" ? my_searches.length : 0);
                var shared_search_count = (typeof shared_searches !== "undefined" ? shared_searches.length : 0);
                var item_length = my_search_count + shared_search_count;
                saved_search_menu.resolve([menu_length, item_length]);
            });

            return saved_search_menu.promise();
        },
        populate_search_form: function(query_data, force_prompt_user) {

            var widget = this;
            var prompt_user = false;
            var widget_num = widget.uuid;
            $(widget.sw_opentsdbwidget_containerid + ' ul.nav-tabs').children('li.nav-tab').remove();
            $(widget.sw_opentsdbwidget_containerid + ' div.tab-content').empty();
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
                $('#search-title' + widget.uuid + ' h3').text(query_data.title).removeClass('search-title-prompt')
                    .siblings('input').val(query_data.title);
            }

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

            var method_map = {
                sum: 'Sum',
                avg: 'Average',
                min: 'Minimum Value',
                max: 'Maximum Value',
                dev: 'Standard Deviation',
                none: 'None'
            };

            var auto_update_input = $('input#auto-update-button' + widget_num);
            if (query_data.auto_update === "true") {
                auto_update_input.parent('.push-button').addClass('pushed');
                if (!auto_update_input.prop('checked')) {
                    auto_update_input.attr('checked', 'Checked').prop('checked', true);
                    auto_update_input.siblings('label').children('span.elegant-icons').removeClass('icon-close-alt red').addClass('icon-check-alt green');
                }
            }
            else {
                auto_update_input.parent('.push-button').removeClass('pushed');
                if (auto_update_input.prop('checked')) {
                    auto_update_input.attr('checked', null).prop('checked', false);
                    auto_update_input.siblings('label').children('span.elegant-icons').removeClass('icon-check-alt green').addClass('icon-close-alt red');
                }
            }

            if (query_data.history_graph.match(/anomaly/)) {
                var el = $('input[data-target="history-anomaly' + widget_num + '"]').parent('label');
                $(el).parent('div.toggle-button').addClass('toggle-on');
                $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
                $(el).children('input').attr('checked', 'Checked');
                $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
                $('ul#time-span-options' + widget_num).html(anomaly_span_menu);
            }
            else if (query_data.history_graph.match(/wow/)) {
                var el = $('input[data-target="history-wow' + widget_num + '"]').parent('label');
                $(el).parent('div.toggle-button').addClass('toggle-on');
                $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
                $(el).children('input').attr('checked', 'Checked');
                $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
                $('ul#time-span-options' + widget_num).html(wow_span_menu);
            }
            else {
                var el = $('input[data-target="history-no' + widget_num + '"]').parent('label');
                $(el).parent('div.toggle-button').addClass('toggle-on');
                $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
                $(el).children('input').attr('checked', 'Checked');
                $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
                $('ul#time-span-options' + widget_num).html(long_span_menu);
            }

            if (query_data.period === "span-search") {
                var el = $('input[data-target="widget-time-span' + widget_num + '"]').parent('label');
                $(el).parent('div.toggle-button').addClass('toggle-on');
                $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
                $(el).children('input').attr('checked', 'Checked');
                $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
                var span = query_data['time_span'];
                var data_target = $(el).children('input').attr('data-target');
                var section = $('#' + data_target);
                section.removeClass('section-off').addClass('section-on').siblings('.section').addClass('section-off').removeClass('section-on');
                $('#time-span' + widget_num).attr('data-ms', span).text(widget.sw_opentsdbwidget_timespanmap[span]);
            }
            else {
                var el = $('input[data-target="widget-dates' + widget_num + '"]').parent('label');
                $(el).parent('div.toggle-button').addClass('toggle-on');
                $(el).parent('div.toggle-button').siblings('div.toggle-button').removeClass('toggle-on');
                $(el).children('input').attr('checked', 'Checked');
                $(el).parent('.toggle-button').siblings('.toggle-button').children('label').children('input').attr('checked', null);
                if ((start_in = parseInt(query_data.start_time)) && (end_in = parseInt(query_data.end_time))) {
                    $('div#start-time' + widget_num).children('input').val(new Date(start_in * 1000).toString('yyyy/MM/dd HH:mm:ss'));
                    $('div#end-time' + widget_num).children('input').val(new Date(end_in * 1000).toString('yyyy/MM/dd HH:mm:ss'));
                }
                else {
                    prompt_user = true;
                }
                var data_target = $(el).children('input').attr('data-target');
                var section = $('#' + data_target);
                section.removeClass('section-off').addClass('section-on').siblings('.section').addClass('section-off').removeClass('section-on');
            }

            var metric_num = 0;
            $.each(query_data.metrics, function(search_key, metric) {
                metric_num++;
                metric_string = metric.name;
                if (metric_num > 1) {
                    var metric_tab = $('div#tab' + widget.uuid + '-' + metric_num);
                    if (metric_tab.length == 0) {
                        widget.metric_count = widget.add_tab(metric_num - 1, widget.uuid);
                        $('input[name="metric' + widget_num + '-' + widget.metric_count + '"]').autocomplete({
                            minChars: 2,
                            serviceUrl: window.location.origin + '/api/datasource/opentsdb/suggest_metrics',
                            containerClass: 'autocomplete-suggestions dropdown-menu',
                            appendTo: '#metric-options' + widget_num + '-' + widget.metric_count + ' .metric-input-textbox',
                            zIndex: '',
                            maxHeight: 300,
                            width: 300
                        });
                    }
                }

                if (metric.tags) {
                    $.each(metric.tags, function(i, tag) {
                        metric_string += ' ' + tag;
                    });
                }
                $('input[name="metric' + widget_num + '-' + metric_num + '"]').val(metric_string);
                $('#active-aggregation-type' + widget_num + '-' + metric_num).text(method_map[metric.agg_type]);

                var lerp_input = $('input#lerp-button' + widget_num + '-' + metric_num);
                if (metric.lerp && metric.lerp !== "false") {
                    lerp_input.parent('.push-button').addClass('pushed');
                    lerp_input.siblings('label').children('span.elegant-icons').addClass('icon-check-alt green').removeClass('icon-check-alt red');
                    if (!lerp_input.prop('checked')) {
                        lerp_input.prop('checked', true).attr('checked', 'Checked');
                    }
                }
                else {
                    lerp_input.parent('.push-button').removeClass('pushed');
                    lerp_input.siblings('label').children('span.elegant-icons').addClass('icon-close-alt red').removeClass('icon-check-alt green');
                    if (lerp_input.prop('checked')) {
                        lerp_input.prop('checked', false).attr('checked', null);
                    }
                }

                var rate_input = $('input#rate-button' + widget_num + '-' + metric_num);
                if (metric.rate && metric.rate !== "false") {
                    rate_input.parent('.push-button').addClass('pushed');
                    rate_input.siblings('label').children('span.elegant-icons').addClass('icon-check-alt green').removeClass('icon-close-alt red');
                    if (!rate_input.prop('checked')) {
                        rate_input.prop('checked', true).attr('checked', 'Checked');
                    }
                }
                else {
                    rate_input.parent('.push-button').removeClass('pushed');
                    rate_input.siblings('label').children('span.elegant-icons').addClass('icon-close-alt red').removeClass('icon-check-alt green');
                    if (rate_input.prop('checked')) {
                        rate_input.prop('checked', false).attr('checked', null);
                    }
                }

                var y2_input = $('input#y2-button' + widget_num + '-' + metric_num);
                if (metric.y2 && metric.y2 !== "false") {
                    y2_input.parent('.push-button').addClass('pushed');
                    y2_input.siblings('label').children('span.elegant-icons').addClass('icon-check-alt green').removeClass('icon-close-alt red');
                    if (!y2_input.prop('checked')) {
                        y2_input.prop('checked', true).attr('checked', 'Checked');
                    }
                }
                else {
                    y2_input.parent('.push-button').removeClass('pushed');
                    y2_input.siblings('label').children('span.elegant-icons').addClass('icon-close-alt red').removeClass('icon-check-alt green');
                    if (y2_input.prop('checked')) {
                        y2_input.prop('checked', false).attr('checked', null);
                    }
                }

                var null_zero_input = $('input#null-zero-button' + widget_num + '-' + metric_num);
                if (metric.null_zero && metric.null_zero !== "false") {
                    null_zero_input.parent('.push-button').addClass('pushed');
                    null_zero_input.siblings('label').children('span.elegant-icons').addClass('icon-check-alt green').removeClass('icon-close-alt red');
                    if (!null_zero_input.prop('checked')) {
                        null_zero_input.prop('checked', true).attr('checked', 'Checked');
                    }
                } else {
                    null_zero_input.parent('.push-button').removeClass('pushed');
                    null_zero_input.siblings('label').children('span.elegant-icons').addClass('icon-close-alt red').removeClass('icon-check-alt green');
                    if (null_zero_input.prop('checked')) {
                        null_zero_input.prop('checked', false).attr('checked', null);
                    }
                }

                var ds_input = $('input#ds-button' + widget_num + '-' + metric_num);
                if (metric.ds_type && metric.ds_type !== "false") {
                    ds_input.parent('.push-button').addClass('pushed');
                    ds_input.siblings('label').children('span.elegant-icons').addClass('icon-check-alt green').removeClass('icon-close-alt red');
                    if (!ds_input.prop('checked')) {
                        ds_input.prop('checked', true).attr('checked', 'Checked');
                    }
                } else {
                    ds_input.parent('.push_button').removeClass('pushed');
                    ds_input.siblings('label').children('span.elegant-icons').addClass('icon-close-alt red').removeClass('icon-check-alt green');
                    if (ds_input.prop('checked')) {
                        ds_input.prop('checked', false).attr('checked', null);
                    }
                }
            });

            if (prompt_user) {
                setTimeout(function() {
                    if (widget.sw_opentsdbwidget_container.hasClass('transparent')) {
                        widget.sw_opentsdbwidget_container.removeClass('transparent');
                    }
                }, 500);
            } else {
                widget.go_click_handler();
            }

        },
        go_click_handler: function(event) {

            var widget = this;
            var widget_num = widget.uuid;
            var widget_element = $(widget.element);
            widget.query_data = {};
            widget.query_data.downsample_master_interval = 0;
            widget.query_data.new_query = true;
            var input_error = false;

            $('#graph-title' + widget_num).empty();
            $('#legend' + widget_num).empty();
            if (typeof widget.svg !== "undefined") {
                delete(widget.svg);
            }
            widget.sw_opentsdbwidget_graphdiv.empty();

            if (typeof widget.autoupdate_timer !== "undefined") {
                clearTimeout(widget.autoupdate_timer);
                delete widget.autoupdate_timer;
            }

            var methods = {
                'sum': 'sum',
                'average': 'avg',
                'minimum value': 'min',
                'maximum value': 'max',
                'standard deviation': 'dev',
                'none': 'none'
            };
            // Map out all the elements we need to check
            var input_dates = $('div#widget-dates' + widget_num);
            var input_time_span = $('div#time-span' + widget_num);
            var input_autoupdate = $('input#auto-update-button' + widget_num);
            var history_buttons = widget_element.find('input:radio[name="history-graph"]');
            widget.query_data.title = $('input[name="search-title-input' + widget_num + '"]').val();
            $.each(history_buttons, function(i, history_type) {
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
                if ($(input_dates).children('div#start-time' + widget_num).children('input[name="start-time"]').val().length < 1) {
                    $(input_dates).children('div#start-time' + widget_num).children('input[name="start-time"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
                    alert("You must specify a start time");
                } else if ($(input_dates).children('div#end-time' + widget_num).children('input[name="end-time"]').val().length < 1) {
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
            } else {
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
            widget.query_data['auto_update'] = input_autoupdate.prop('checked') ? true : false;

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
                        metric_bits = metric_search_string.split('{');
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
//                    var ds_type = $('#active-downsample-type' + widget_num + '-' + i).text().toLowerCase();
                    build_metric.agg_type = methods[agg_type];
//                    build_metric.ds_type = methods[ds_type];
//                    build_metric.ds_interval = $('#active-downsample-interval' + widget_num + '-' + i).attr('data-value');
//                    if ((widget.query_data['downsample_master_interval'] < 1) || (build_metric.ds_interval > widget.query_data['downsample_master_interval'])) {
//                        widget.query_data['downsample_master_interval'] = build_metric.ds_interval;
//                    }
//
                    if ($('#rate-button' + widget_num + '-' + i).prop('checked')) {
                        build_metric.rate = true;
                    }

                    if ($('#lerp-button' + widget_num + '-' + i).prop('checked')) {
                        build_metric.lerp = true;
                    }
                    if ($('#y2-button' + widget_num + '-' + i).prop('checked')) {
                        build_metric.y2 = true;
                    }
                    if ($('#null-zero-button' + widget_num + '-' + i).prop('checked')) {
                        build_metric.null_zero = true;
                    }
                    if ($('#ds-button' + widget_num + '-' + i).prop('checked')) {
                        build_metric.ds_type = true;
                    }

                    var search_key = md5(JSON.stringify(build_metric));
                    widget.query_data.metrics[search_key] = (build_metric);
                }
                if (widget.query_data['metrics'].length < 1) {
                    widget.sw_opentsdbwidget_searchform.find('ul#tab-list' + widget_num + ' a[href="#tab' + widget_num + '-1"]').click();
                    $('input:text[name="metric' + widget_num + '-1"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
                    alert("You must specify at least one metric to search for");
                    input_error = true;
                }
            } else {
                widget.query_data['metrics_count'] = 1;
                if ($('input:text[name="metric' + widget_num + '-1"]').val().length < 1) {
                    $('input:text[name="metric' + widget_num + '-1"]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
                    alert("You must specify a metric to search for");
                    input_error = true;
                } else if (widget.query_data.history_graph === "wow" && (end - start) / 60 > 10080) {
                    alert('Week-over-week history comparison searches are limited to 1 week or less of data');
                    $('input:text[name=start-time]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
                    input_error = true;
                } else if (widget.query_data.history_graph === "anomaly" && (end - start) > 86400) {
                    alert('Anomaly detection searches are limited to 1 day or less');
                    $('input:text[name=start-time]').css('border-color', 'red').css('background-color', 'rgb(255, 200, 200)').focus();
                    input_error = true;
                } else {
                    input_error = false;
                    var build_metric = {};
                    var metric_bits = $('input:text[name=metric' + widget_num + '-1]').val().split(' ');
                    build_metric.name = metric_bits.shift();
                    if (metric_bits.length > 0) {
                        build_metric.tags = metric_bits;
                    }
                    var agg_type = $('#active-aggregation-type' + widget_num + '-1').text().toLowerCase();
                    build_metric.agg_type = methods[agg_type];

                    if ($('#rate-button' + widget_num + '-1').prop('checked')) {
                        build_metric.rate = true;
                    }

                    if ($('#lerp-button' + widget_num + '-1').prop('checked')) {
                        build_metric.lerp = true;
                    }

                    if ($('#y2-button' + widget_num + '-1').prop('checked')) {
                        build_metric.y2 = true;
                    }

                    if ($('#null-zero-button' + widget_num + '-1').prop('checked')) {
                        build_metric.null_zero = true;
                    }

                    if ($('#ds-button' + widget_num + '-1').prop('checked')) {
                        build_metric.ds_type = true;
                    }

                    var search_key = md5(JSON.stringify(build_metric));
                    widget.query_data.metrics[search_key] = (build_metric);
                }
            }


            // If we made it this far without errors in the form input, then
            // we build us a graph
            if (input_error == false) {
                // Load the waiting spinner
                widget.sw_opentsdbwidget_graphdiv.append('<div class="spinner" id="spinner' + widget_num + '">' +
                    '<div class="spinner-holder elegant-icons icon-loading"></div></div>');
                widget.sw_opentsdbwidget_graphdiv.append('<div id="status-box' + widget_num + '" style="width: 100%; text-align: center;">' +
                    '<p id="status-message' + widget_num + '"></p></div>');
                widget.init_query();
                setTimeout(function() {
                    widget.flip_to_front();
                }, 250);
            }
            else {
                if (!widget_element.children('.widget').hasClass('flipped')) {
                    widget.edit_params();
                }
            }

        },
        init_query: function() {

            var widget = this;
            widget_num = widget.uuid;

            var graph_type = 'line';
            // Start deferred query for metric data
            $.when(widget.opentsdb_search()).then(
                // done: Send the data over to be parsed
                function(data) {
                    $.when(widget.process_timeseries_data(data)).then(
                        // done: Build the graph
                        function(data) {
                            widget.build_graph(data, graph_type);
                        }
                        // fail: Show error image and error message
                        , function(status) {
                            $('#spinner' + widget_num).empty()
                                .append('<div class="spinner-error elegant-icons icon-error-triangle red"></div>');
                            $('#' + widget.element.attr('id') + ' .status-message').empty().append('<p>' + status[1] + '</p>');
                        }
                    );
                }
                // fail: Show error image and error message
                , function(status) {
                    $('#spinner' + widget_num).empty()
                        .append('<div class="spinner-error elegant-icons icon-error-triangle red"></div>');

                    widget.sw_opentsdbwidget_graphdiv.children('#status-box' + widget_num).empty()
                        .append('<p>' + status.shift() + '</p>');

                    if (typeof status[0] === "string") {
                        if ((status[0].match("<!DOCTYPE")) || (status[0].match("<html>"))) {
                            var error_message = status.join(' ').replace(/'/g, "&#39");
                            widget.sw_opentsdbwidget_graphdiv.children('#status-box' + widget_num)
                                .append('<iframe style="margin: 0 auto; color: rgb(205, 205, 205);" width="80%" height="90%" srcdoc=\'' + error_message + '\' seamless></iframe>');
                        }
                        else {
                            widget.sw_opentsdbwidget_graphdiv.children('#status-box' + widget_num).empty().append('<p>' + status + '</p>');
                        }
                    }
                }
            );
        },
        opentsdb_search: function() {

            var widget = this;
            var query_object = new $.Deferred();
            var widget_num = widget.uuid;
            var status = widget.sw_opentsdbwidget_graphdiv.children('#status-box' + widget_num).children('#status-message' + widget_num);

            // Generate model data for the metric
            if (widget.query_data.history_graph == "anomaly") {
                status.html('<p>Fetching Metric Data</p>');
                $.when(widget.get_opentsdb_data_anomaly()
                    .done(function(data) {
                        query_object.resolve(data);
                    })
                    .fail(function(data) {
                        query_object.reject(data);
                    })
                );
            }
            // Search current and previous week for metric data
            else if (widget.query_data.history_graph == "wow") {
                status.html('<p>Fetching Metric Data</p>');
                $.when(widget.get_opentsdb_data_wow()
                    .done(function(data) {
                        query_object.resolve(data);
                    })
                    .fail(function(data) {
                        query_object.reject(data);
                    })
                );
            }
            else {
                status.html('<p>Fetching Metric Data</p>');
                $.when(widget.get_opentsdb_data()
                    .done(function(data) {
                        query_object.resolve(data);
                    })
                    .fail(function(data) {
                        query_object.reject(data);
                    })
                );
            }

            return query_object.promise();

        },
        get_opentsdb_data: function() {
            var widget = this;

            if (typeof widget.ajax_request !== 'undefined') {
                widget.ajax_request.abort();
            }

            widget.ajax_object = new $.Deferred();

            var query_data = {};
            query_data['query_data'] = widget.query_data;

            widget.ajax_request = $.ajax({
                url: window.location.origin + "/api/datasource/opentsdb/search",
                type: 'POST',
                data: query_data,
                dataType: 'json',
                timeout: 120000
            }),
            chain = widget.ajax_request.then(function(data) {
                return(data);
            });

            chain.done(function(data) {
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

        },
        get_opentsdb_data_wow: function() {

            var widget = this;
            var status = widget.sw_opentsdbwidget_graphdiv.children('#status-box' + widget_num).children('#status-message' + widget_num);

            if (typeof widget.ajax_request !== 'undefined') {
                console.log('Previous request still in flight, aborting');
                widget.ajax_request.abort();
            }

            widget.ajax_object = new $.Deferred();

            var query_data = {};
            query_data['query_data'] = widget.query_data;
            var metric_data = {};

            widget.ajax_request = $.ajax({
                url: window.location.origin + "/api/datasource/opentsdb/search",
                type: 'POST',
                data: query_data,
                dataType: 'json',
                timeout: 120000
            }),
            chained = widget.ajax_request.then(function(data) {
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
                    current_keys = Object.keys(current_data);
                    current_key = 'Current - ' + current_keys[0];
                    metric_data.legend = {};
                    metric_data.legend[current_key] = current_key;
                    metric_data[current_key] = current_data[current_keys[0]];
                    delete current_data;
                    var past_query = {};
                    past_query['query_data'] = $.extend(true, {}, widget.query_data);
                    var query_span = parseInt(widget.query_data.end_time) - parseInt(widget.query_data.start_time);
                    past_query.query_data.end_time = parseInt(widget.query_data.end_time - 604800);
                    past_query.query_data.start_time = past_query.query_data.end_time - query_span;
                    past_query.query_data.previous = true;
                    return $.ajax({
                        url: window.location.origin + "/api/datasource/opentsdb/search",
                        type: 'POST',
                        data: past_query,
                        dataType: 'json',
                        timeout: 120000
                    });
                }
            });
            chained.done(function(data) {
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
                        $.each(metric_data[past_key], function(index, entry) {
                            entry.timestamp = parseInt(entry.timestamp) + 604800;
                        });
                        delete widget.ajax_request;
                        widget.ajax_object.resolve(metric_data);
                    }
                }
            });

            return widget.ajax_object.promise();

        },
        get_opentsdb_data_anomaly: function() {

            var widget = this;
            var status = widget.sw_opentsdbwidget_graphdiv.children('#status-box' + widget_num).children('#status-message' + widget_num);

            if (typeof widget.ajax_request !== 'undefined') {
                console.log('Previous request still in flight, aborting');
                widget.ajax_request.abort();
            }

            widget.ajax_object = new $.Deferred();

            var query_data = {};
            query_data['query_data'] = widget.query_data;
            var metric_data = {};
            var data_for_detection = [];

            widget.ajax_request = $.ajax({
                url: window.location.origin + "/api/datasource/opentsdb/search",
                type: 'POST',
                data: query_data,
                dataType: 'json',
                timeout: 120000
            }),
            chained = widget.ajax_request.then(function(data) {
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
                    data_for_detection = {query_data: widget.query_data, ts_data: metric_data};
                    status.html('<p>Calculating anomalies</p>');
//                    return ['error', ['0', 'testing']];
                    return $.ajax({
                        url: window.location.origin + "/api/anomaly/time-series",
                        type: 'POST',
                        data: data_for_detection,
                        dataType: 'json'
                    });
                }
            });

            chained.done(function(data) {
                if (!data) {
                    widget.ajax_request.abort();
                    widget.ajax_object.reject();
                }
                else {
                    if (data[0] === "error") {
                        widget.ajax_request.abort();
                        widget.ajax_object.reject(data[1])
                    }
                    else {
                        metric_data.anomalies = data;
                        widget.ajax_object.resolve(metric_data);
                    }
                }
            }).fail(function(data) {
                    widget.ajax_request.abort();
                    widget.ajax_object.reject([data.status, data.statusText]);
                });

            return widget.ajax_object.promise();

        },
        process_timeseries_data: function(data) {

            var widget = this;

            var parse_object = new $.Deferred();
            var status = widget.sw_opentsdbwidget_graphdiv.children('#status-box' + widget_num).children('#status-message' + widget_num);

            widget.query_url = data.query_url;
            delete data.start;
            delete data.end;
            delete data.query_url;
            if (widget.query_data.history_graph == "anomaly") {
                var anomalies = data.anomalies;
                delete data.anomalies;
            }

            status.html('<p>Parsing Metric Data</p>');

            var graph_data = [];
            graph_data.legend_map = data.legend;
            delete data.legend;

            if (widget.query_data.history_graph !== "no") {
                $.each(data, function(series, series_data) {
                    graph_data.push({name: series, search_key: (Object.keys(widget.query_data.metrics))[0], axis: 'left', values: series_data});
                })
            }
            else {
                var tag_map = {};
                $.each(widget.query_data.metrics, function(search_key, search_data) {
                    if (typeof search_data.tags !== "undefined") {
                        tag_map[search_key] = {name: search_data.name, tags: [], tag_count: search_data.tags.length};
                        $.each(search_data.tags, function(i, tag) {
                            var tag_parts = tag.split('=');
                            if (tag_parts[1] === '*') {
                                tag_map[search_key].tags.push(tag_parts[0] + '=ALL');
                            }
                            else if (tag_parts[1].match(/\|/)) {
                                var matches = tag_parts[1].split('|');
                                $.each(matches, function(i, m) {
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
                $.each(data, function(series, series_data) {
                    var series_key = graph_data.legend_map[series].split(' ');
                    var series_metric = series_key.shift();
                    var key_map = '';
                    $.each(tag_map, function(search_key, search_data) {
                        if (series_metric === search_data.name) {
                            if (search_data.tags[0] === "NONE") {
                                key_map = search_key;
                            }
                            else {
                                var search_matched = 0;
                                $.each(search_data.tags, function(i, t) {
                                    if (t.match(/ALL/)) {
                                        tleft = (t.split('='))[0]
                                        $.each(series_key, function(i, k) {
                                            if (k.match(tleft)) {
                                                search_matched++;
                                            }
                                        })
                                    }
                                    else {
                                        $.each(series_key, function(i, k) {
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

        },
        /**
         * Auto Downsampling in StatusWolf is accomplished using an algorithm
         * called Largest-Triangle-Three-Buckets, as described in a Master's
         * thesis by Sveinn Steinarsson at the University of Iceland.
         * See http://hdl.handle.net/1946/15343 and
         * https://github.com/sveinn-steinarsson/flot-downsample
         *
         */
        downsample_series: function(data, threshold) {

            console.log('downsampling');

            var data_length = data.length;
            console.log('data_length: ' + data_length);
            console.log('threshold: ' + threshold);

            if (threshold >= data_length || threshold === 0) {
                return data;
            }

            var downsampled = [],
                downsampled_index = 0;

            var bucket = (data_length - 2) / (threshold - 2);
            console.log('bucket: ' + bucket);

            var point = 0,
                max_area_point,
                max_area,
                area,
                next_point;

            downsampled[downsampled_index++] = data[point];

            for (var i = 0; i < threshold - 2; i++) {

                var avg_x = 0,
                    avg_y = 0,
                    avg_range_start = Math.floor((i + 1) * bucket) + 1,
                    avg_range_end = Math.floor((i + 2) * bucket) + 1;

                avg_range_end = avg_range_end < data_length ? avg_range_end : data_length;

                var avg_range_length = avg_range_end - avg_range_start;

                for (; avg_range_start < avg_range_end; avg_range_start++) {
                    avg_x += data[avg_range_start].timestamp * 1;
                    avg_y += data[avg_range_start].value * 1;
                }
                avg_x /= avg_range_length;
                avg_y /= avg_range_length;

                var range_offs = Math.floor((i + 0) * bucket) + 1,
                    range_to = Math.floor((i + 1) * bucket) + 1;
                    range_to = range_to < data_length ? range_to : data.length;

                var point_x = data[point].timestamp * 1,
                    point_y = data[point].value;

                max_area = area = -1;

                for (; range_offs < range_to ; range_offs++) {
                    area = Math.abs((point_x - avg_x) * (data[range_offs].value - point_y) -
                        (point_x - data[range_offs].timestamp) * (avg_y - point_y) * 0.5);
                    if (area > max_area) {
                        max_area = area;
                        max_area_point = data[range_offs];
                        next_point = range_offs;
                    }
                }

                downsampled[downsampled_index++] = max_area_point;
                point = next_point;
            }

            downsampled[downsampled_index++] = data[data_length - 1];

            return downsampled;

//            console.log(downsampled);

        },
        build_graph: function(data, type) {

            var widget = this;

            widget.graph = {};
            widget.graph.legend_map = data.legend_map;
            delete(data.legend_map);
            if (widget.query_data['history_graph'] === "anomaly") {
                widget.graph.anomalies = data.anomalies;
                delete(data.anomalies);
            }
            widget.graph.autoupdate_interval = widget.query_data.downsample_master_interval > 1 ? widget.query_data.downsample_master_interval * 60 : 300;

            var graphdiv = widget.sw_opentsdbwidget_graphdiv.empty();
            var graphdiv_offset = graphdiv.position().top;
            var legend = widget.sw_opentsdbwidget_legendbox.children('div.legend');
            widget.sw_opentsdbwidget_frontmain.css('height', widget.sw_opentsdbwidget.innerHeight());
            graphdiv.css('height', (widget.sw_opentsdbwidget_frontmain.innerHeight() - (widget.sw_opentsdbwidget_frontmain.children('.legend-container').outerHeight(true) + graphdiv_offset)));
            widget.sw_opentsdbwidget_legendbox.css('width', widget.sw_opentsdbwidget_frontmain.innerWidth())
                .removeClass('hidden');

            widget.graph.margin = {top: 0, right: 5, bottom: 20, left: 45};
            widget.graph.width = (graphdiv.innerWidth() - widget.graph.margin.left - widget.graph.margin.right);
            widget.graph.height = (graphdiv.innerHeight() - widget.graph.margin.top - widget.graph.margin.bottom);

            widget.graph.raw_data = data;
            delete(data);

            widget.graph.data_left = [];
            widget.graph.data_right = [];
            $.each(widget.graph.raw_data, function(s, d) {
                var line_values;
                if (widget.query_data.metrics[d.search_key].ds_type) {
                    line_values = widget.downsample_series(d.values, widget.graph.width * 0.5);
                } else {
                    line_values = d.values;
                }
                $.each(line_values, function(v) {
                    line_values[v]['date'] = new Date(line_values[v]['timestamp'] * 1000);
                });
                if (d.axis === "right") {
                    widget.graph.right_axis = true;
                    widget.graph.data_right.push({axis: d.axis, name: d.name, search_key: d.search_key, values: line_values});
                }
                else {
                    widget.graph.data_left.push({axis: d.axis, name: d.name, search_key: d.search_key, values: line_values});
                }
            });

            if (widget.graph.right_axis == true) {
                widget.graph.margin.right = 55;
                widget.graph.width = (graphdiv.innerWidth() - widget.graph.margin.left - widget.graph.margin.right);
            } else {
                delete(widget.graph.data_right);
            }

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
                        d3.min(widget.graph.data_left.concat(widget.graph.data_right), function(d) {
                            return d3.min(d.values, function(v) {
                                return v.date;
                            })
                        })
                        , d3.max(widget.graph.data_left.concat(widget.graph.data_right), function(d) {
                            return d3.max(d.values, function(v) {
                                return v.date;
                            })
                        })
                    ]);
                }
                else {
                    widget.graph.x.domain([
                        d3.min(widget.graph.data_left, function(d) {
                            return d3.min(d.values, function(v) {
                                return v.date;
                            })
                        })
                        , d3.max(widget.graph.data_left, function(d) {
                            return d3.max(d.values, function(v) {
                                return v.date;
                            })
                        })
                    ]);

                }

                widget.graph.x_master_domain = widget.graph.x.domain();

                widget.graph.y.domain([
                    0, (d3.max(widget.graph.data_left, function(d) {
                        return d3.max(d.values, function(v) {
                            return parseFloat(v.value);
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
                        0, (d3.max(widget.graph.data_right, function(d) {
                            return d3.max(d.values, function(v) {
                                return parseFloat(v.value);
                            })
                        }) * 1.05)
                    ]);
                }

                widget.graph.color = d3.scale.ordinal()
                    .domain(function(d) {
                        return widget.graph.legend_map[d.name];
                    })
                    .range(swcolors.Wheel_DarkBG[5]);

                widget.graph.line = d3.svg.line()
                    .interpolate('linear')
                    .x(function(d) {
                        return widget.graph.x(d.date);
                    })
                    .y(function(d) {
                        return widget.graph.y(+d.value);
                    });

                widget.graph.line_right = d3.svg.line()
                    .interpolate('linear')
                    .x(function(d) {
                        return widget.graph.x(d.date);
                    })
                    .y(function(d) {
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
                    .attr('d', function(d) {
                        return widget.graph.line(d.values);
                    })
                    .attr('data-name', function(d) {
                        return widget.graph.legend_map[d.name];
                    })
                    .style('stroke', function(d) {
                        return widget.graph.color(widget.graph.legend_map[d.name]);
                    })
                    .style('pointer-events', 'none');

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
                        .attr('d', function(d) {
                            return widget.graph.line_right(d.values);
                        })
                        .attr('data-name', function(d) {
                            return widget.graph.legend_map[d.name];
                        })
                        .style('stroke', function(d) {
                            return widget.graph.color(widget.graph.legend_map[d.name]);
                        })
                        .style('pointer-events', 'none');
                }

                var point_format = d3.format('.2f');

                var dot_data = widget.graph.right_axis ? widget.graph.data_left.concat(widget.graph.data_right) : widget.graph.data_left;
                widget.graph.dots = widget.svg.g.append('g')
                    .attr('class', 'dot-container')
                    .selectAll('g')
                    .data(dot_data)
                    .enter().append('g')
                        .attr('opacity', 0)
                        .attr('data-name', function(d) {
                            return widget.graph.legend_map[d.name];
                        });

                widget.graph.dots
                    .append('circle')
                        .attr('cx', widget.graph.x(dot_data[0].values[0].date))
                        .attr('cy', function(d) {
                            if (d.axis === "right") {
                                return widget.graph.y1(d.values[0].value);
                            } else {
                                return widget.graph.y(d.values[0].value);
                            }
                        })
                        .attr('r', 4)
                        .style('fill', 'none')
                        .style('stroke-width', '1.5px')
                        .style('stroke', function(d) {
                            return (widget.graph.color(widget.graph.legend_map[d.name]));
                        })
                        .classed('dot', 1);
                widget.graph.dots.append('text')
                    .attr('x', 0)
                    .attr('y', 0)
                    .style('fill', function(d) {
                        return (widget.graph.color(widget.graph.legend_map[d.name]));
                    })
                    .style('font-size', '.71em')
                    .style('text-shadow', '2px 2px 2px #000');

                var bisect = d3.bisector(function(d) { return d.date; }).left;

                if (Object.keys(widget.graph.legend_map).length > 9) {
                    legend.css({
                        '-webkit-columns': 'auto 4', '-moz-columns': 'auto 4', columns: 'auto 4'
                    });
                }
                else if (Object.keys(widget.graph.legend_map).length > 6) {
                    legend.css({
                        '-webkit-columns': 'auto 2', '-moz-columns': 'auto 3', columns: 'auto 3'
                    });
                }
                else if (Object.keys(widget.graph.legend_map).length > 3) {
                    legend.css({
                        '-webkit-columns': 'auto 2', '-moz-columns': 'auto 2', columns: 'auto 2'
                    });
                }

                $.each(widget.graph.legend_map, function(series, label) {
                    var item_color = widget.graph.color(widget.graph.legend_map[series]);
                    legend.append('<span title="' + label + '" class="legend-title" style="color: ' + item_color + '">' + label + '</span>');
                    var name_span = legend.children('span[title="' + label + '"]');
                    if (widget.graph.right_axis == true) {
                        if (!d3.select('.metric path[data-name="' + label + '"]').classed('right')) {
                            $(name_span).prepend('<span class="elegant-icons arrow-triangle-left" style="font-size: .75em"></span>');
                        } else {
                            $(name_span).prepend('<span class="elegant-icons arrow-triangle-right" style="font-size: .75em"></span>');
                        }
                    }
                });

                legend.children('span')
                    .css('cursor', 'pointer')
                    .on('mouseover', function() {
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
                            var new_metric = d3.select('#' + widget.element.attr('id') + ' svg>g').insert('g', 'g.dot-container');
                            new_metric
                                .classed('metric', 1)
                                .classed(axis_position_class, 1)
                                .attr('clip-path', 'url(#clip' + widget.uuid + ')')
                                .data(moved_metric_data)
                                .append('path')
                                .classed('line', 1)
                                .classed(axis_position_class, 1)
                                .attr('d', function(d) {
                                    if (d.axis === "right") {
                                        return widget.graph.line_right(d.values);
                                    } else {
                                        return widget.graph.line(d.values);
                                    }
                                })
                                .attr('data-name', $(this).attr('title'))
                                .style('stroke', function(d) {
                                    return widget.graph.color(widget.graph.legend_map[d.name]);
                                })
                                .style('stroke-width', '3px')
                                .style('pointer-events', 'none');
                        }
                    })
                    .on('mouseout', function() {
                        $(this).css('font-weight', 'normal');
                        widget.svg.g.select('.widget path[data-name="' + $(this).attr('title') + '"]').style('stroke-width', '1.5px');
                        widget.svg.g.selectAll('.metric path').classed('fade', 0);
                    })
                    .on('click', function(e) {
                        var series_title = $(this).attr('title'),
                            metric_line = widget.svg.g.select('.metric path[data-name="' + series_title + '"]');
                        if (e.altKey) {
                            legend.children('span').addClass('fade');
                            $(this).removeClass('fade');
                            widget.svg.g.selectAll('.metric path')
                                .classed('hidden', 1);
                            metric_line.classed('hidden', 0);
                            widget.graph.dots.each(function() {
                                if ($(this).attr('data-name') === series_title) {
                                    d3.select(this).classed('hidden', 0);
                                } else {
                                    d3.select(this).classed('hidden', 1);
                                }
                            })
                        }
                        else {
                            if (metric_line.classed('hidden')) {
                                $(this).removeClass('fade');
                                metric_line.classed('hidden', 0);
                                widget.graph.dots.each(function() {
                                    if ($(this).attr('data-name') === series_title) {
                                        d3.select(this).classed('hidden', 0);
                                    }
                                });
                            }
                            else {
                                $(this).addClass('fade');
                                metric_line.classed('hidden', 1);
                                widget.graph.dots.each(function() {
                                    if ($(this).attr('data-name') === series_title) {
                                        d3.select(this).classed('hidden', 1);
                                    }
                                });
                            }
                        }
                    });

                if (widget.query_data['history_graph'] === "anomaly") {
                    $.each(widget.graph.anomalies, function(i, anomaly) {
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
                        .attr('x', function(d) {
                            return widget.graph.x(d.start_time)
                        })
                        .attr('width', function(d) {
                            return (widget.graph.x(d.end_time) - widget.graph.x(d.start_time))
                        })
                        .attr('fill', 'red')
                        .attr('opacity', 0.25);
                }

                widget.graph.brush = d3.svg.brush()
                    .x(widget.graph.x)
                    .y(widget.graph.y)
                    .on('brushend', function() {
                        if (widget.graph.brush.empty()) {
                            widget.graph.x.domain(widget.graph.x_master_domain);
                            widget.graph.y.domain(widget.graph.y_master_domain);
                            widget.resize_graph();
                            if (widget.query_data['auto_update']) {
                                widget.autoupdate_timer = setTimeout(function() {
                                    widget.update_graph('line');
                                }, 300 * 1000);
                            }
                        }
                        else {
                            widget.graph.x.domain([widget.graph.brush.extent()[0][0], widget.graph.brush.extent()[1][0]]);
                            widget.graph.y.domain([widget.graph.brush.extent()[0][1], widget.graph.brush.extent()[1][1]]);
                            widget.resize_graph();
                            widget.graph.zoombox.select('rect.extent')
                                .attr('x', 0)
                                .attr('y', 0)
                                .attr('height', 0)
                                .attr('width', 0)
                                .style('pointer-events', 'none');
                            widget.graph.zoombox.selectAll('g')
                                .style('display', 'none');
                            if (typeof widget.autoupdate_timer !== "undefined") {
                                clearTimeout(widget.autoupdate_timer);
                            }
                        }
                    });

                widget.graph.zoombox = widget.svg.g.append('g')
                    .attr('class', 'brush');

                widget.graph.zoombox.call(widget.graph.brush)
                    .selectAll('rect.background')
                        .attr('height', widget.graph.height)
                        .attr('width', widget.graph.width)
                        .on('mouseover', function() {
                            widget.graph.dots.attr('opacity', 1);
                            widget.svg.g.selectAll('.metric path').attr('opacity', .5);
                        })
                        .on('mouseout', function() {
                            widget.graph.dots.attr('opacity', 0);
                            widget.svg.g.selectAll('.metric path').attr('opacity', 1);
                        })
                        .on('mousemove', function() {
                            var x_position = widget.graph.x.invert(d3.mouse(this)[0]),
                                x_position_index = bisect(dot_data[0].values, x_position),
                                closest_timestamp = dot_data[0].values[x_position_index].date;
                            widget.graph.dots.select('circle')
                                .attr('cx', widget.graph.x(closest_timestamp))
                                .attr('cy', function(d) {
                                    if (d.axis === "right") {
                                        return d.values[x_position_index].value ? widget.graph.y1(d.values[x_position_index].value) : widget.graph.y1(0);
                                    } else {
                                        return d.values[x_position_index].value ? widget.graph.y(d.values[x_position_index].value) : widget.graph.y(0);
                                    }
                                });
                            widget.graph.dots.select('text')
                                .text(function(d) { return point_format(d.values[x_position_index].value); })
                                .attr('x', function(d) {
                                    if ((x_position_index / d.values.length) > .85) {
                                        return widget.graph.x(closest_timestamp) - 5;
                                    } else {
                                        return widget.graph.x(closest_timestamp) + 5;
                                    }
                                })
                                .attr('text-anchor', function(d) {
                                    if ((x_position_index / d.values.length) > .85) {
                                        return 'end';
                                    } else {
                                        return 'start';
                                    }
                                })
                                .attr('y', function(d) {
                                    if (d.axis === "right") {
                                        return widget.graph.y1(d.values[x_position_index].value) - 2.5;
                                    } else {
                                        return widget.graph.y(d.values[x_position_index].value) - 2.5;
                                    }
                                });
                        });

                // Set the interval for adding new data if Auto Update is selected
                if (widget.query_data['auto_update']) {
                    var widget_string = widget.sw_opentsdbwidget_containerid;
                    if (widget.query_data.title.length > 0) {
                        widget_string = widget.query_data.title;
                    }
                    console.log('setting auto-update timer to ' + widget.graph.autoupdate_interval / 60 + ' minutes for widget ' + widget_string + ' at ' + new Date.now().toTimeString());
                    widget.autoupdate_timer = setTimeout(function() {
                        widget.update_graph('line');
                    }, widget.graph.autoupdate_interval * 1000);
                }

            }
        },
        update_graph: function(type) {
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
                $.when(widget.opentsdb_search()).then(function(incoming_new_data) {
                    $.when(widget.process_timeseries_data(incoming_new_data)).then(
                        function(incoming_new_data) {
                            if (type === "line") {
                                var new_data = incoming_new_data;
                                var new_point_count = new_data[0].values.length;
                                delete(incoming_new_data);
                                delete(new_data.legend_map);
                                if (widget.query_data['history_graph'] === "anomaly") {
                                    widget.graph.anomalies = widget.graph.anomalies.concat(new_data.anomalies);
                                    delete(new_data.anomalies);
                                }
                                $.each(new_data, function(s, d) {
                                    $.each(d.values, function(v) {
                                        d.values[v]['date'] = new Date(d.values[v]['timestamp'] * 1000);
                                    });
                                });

                                if (widget.graph.right_axis == true) {
                                    $.each(widget.graph.data_left.concat(widget.graph.data_right), function(i, d) {
                                        $.each(new_data, function(ni, nd) {
                                            if (nd.name === d.name) {
                                                d.values = d.values.concat(nd.values);
                                                d.values.splice(0, new_point_count);
                                                delete update_tracker[d.name];
                                            }
                                        });
                                    });
                                    if (Object.keys(update_tracker).length > 0) {
                                        $.each(widget.graph.data_left.concat(widget.graph.data_right), function(i, d) {
                                            if (typeof update_tracker[d.name] !== "undefined") {
                                                d.values.splice(0, new_point_count);
                                            }
                                        });
                                    }
                                    widget.graph.x.domain([
                                        d3.min(widget.graph.data_left.concat(widget.graph.data_right), function(d) {
                                            return d3.min(d.values, function(v) {
                                                return v.date;
                                            })
                                        })
                                        , d3.max(widget.graph.data_left.concat(widget.graph.data_right), function(d) {
                                            return d3.max(d.values, function(v) {
                                                return v.date;
                                            })
                                        })
                                    ]);
                                }
                                else {
                                    $.each(widget.graph.data_left, function(i, d) {
                                        $.each(new_data, function(ni, nd) {
                                            if (nd.name === d.name) {
                                                d.values = d.values.concat(nd.values);
                                                d.values.splice(0, new_point_count);
                                                delete update_tracker[d.name];
                                            }
                                        });
                                    });
                                    if (Object.keys(update_tracker).length > 0) {
                                        $.each(widget.graph.data_left, function(i, d) {
                                            if (typeof update_tracker[d.name] !== "undefined") {
                                                console.log('No new data received for ' + d.name + ', forcing trim');
                                                d.values.splice(0, new_point_count);
                                            }
                                        });
                                    }
                                    widget.graph.x.domain([
                                        d3.min(widget.graph.data_left, function(d) {
                                            return d3.min(d.values, function(v) {
                                                return v.date;
                                            })
                                        })
                                        , d3.max(widget.graph.data_left, function(d) {
                                            return d3.max(d.values, function(v) {
                                                return v.date;
                                            })
                                        })
                                    ]);
                                }
                                if (widget.graph.x.domain()[0] == "Invalid Date" || widget.graph.x.domain()[1] == "Invalid Date") {
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
                                    0, (d3.max(widget.graph.data_left, function(d) {
                                        return d3.max(d.values, function(v) {
                                            return parseFloat(v.value);
                                        })
                                    }) * 1.05)
                                ]);
                                widget.svg.g.select('.x.axis').call(widget.graph.x_axis);
                                widget.svg.g.select('.y.axis').call(widget.graph.y_axis);
                                widget.svg.g.selectAll('.y.axis text').attr('dy', '0.75em');
                                if (widget.graph.right_axis == true) {
                                    widget.graph.y1.domain([
                                        0, (d3.max(widget.graph.data_right, function(d) {
                                            return d3.max(d.values, function(v) {
                                                return parseFloat(v.value);
                                            })
                                        }) * 1.05)
                                    ]);
                                    widget.svg.g.selectAll('.y1.axis text').attr('dy', '0.75em');
                                }
                                widget.svg.g.selectAll('.metric path')
                                    .attr('d', function(d) {
                                        if (d.axis === "right") {
                                            return widget.graph.line_right(d.values);
                                        } else {
                                            return widget.graph.line(d.values);
                                        }
                                    });

                                if (widget.query_data['history_graph'] === "anomaly") {
                                    widget.svg.g.selectAll('.anomaly-bars').selectAll('rect')
                                        .attr('x', function(d) {
                                            return widget.graph.x(d.start_time)
                                        });
                                }
                                $(widget.sw_opentsdbwidget_containerid + ' .dots').remove();
//                                widget.add_graph_dots();
                            }
                        }
                    );
                });
            }
            var widget_string = widget.query_data.title.length > 0 ? widget.query_data.title : widget.sw_opentsdbwidget_container;
            console.log('Widget ' + widget_string + ' refreshed at ' + new Date.now().toTimeString() + ', next refresh in ' + widget.graph.autoupdate_interval / 60 + ' minutes');
            widget.autoupdate_timer = setTimeout(function() {
                widget.update_graph('line');
            }, widget.graph.autoupdate_interval * 1000);
        },
        format_graph_title: function() {
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
                    .text(function(d) {
                        return d;
                    });
                widget.graph.title_height = graph_title.node().getBBox().height;
                var tspan_offset = 0;
                graph_title.selectAll('tspan').each(function() {
                    d3.select(this)
                        .attr('y', function() {
                            return parseInt(graph_title.attr('y')) + tspan_offset;
                        })
                        .attr('x', function() {
                            return graph_title.attr('x');
                        });
                    tspan_offset += widget.graph.title_height;
                });
                graph_title.attr('y', (widget.graph.margin.bottom - widget.graph.y.range()[0] / 2) - (graph_title.node().getBBox().height / 2));
            }
            graph_title.classed('hidden', 0);
        },
        hide_legend: function(button) {
            var widget = this;
            if (typeof button === "undefined") {
                button = widget.sw_opentsdbwidget_frontmain.children('div.legend-container').children('button.legend-toggle');
            }
            var differential = $(button).parents('div.legend-container').outerHeight(true) - $(button).parents('div.legend-container').height();
            $(button).removeClass('legend-hide');
            $(button).addClass('legend-show');
            $(button).siblings('div.legend').addClass('nodisplay');
            $(button).parents('div.legend-container').addClass('hidden-legend');
            $(button).parents('div.legend-container').siblings('div.graphdiv')
                .css('height', widget.sw_opentsdbwidget_frontmain.innerHeight() - differential);
            $(button).children('span.elegant-icons').removeClass('arrow-triangle-down').addClass('arrow-triangle-up');
            if (typeof widget.svg !== "undefined") {
                widget.resize_graph();
            }
            widget.options.legend = 'off';
        },
        show_legend: function(button) {
            var widget = this;
            if (typeof button === "undefined") {
                button = widget.sw_opentsdbwidget_frontmain.children('div.legend-container').children('button.legend-toggle');
            }
            $(button).removeClass('legend-show');
            $(button).addClass('legend-hide');
            $(button).parents('div.legend-container').removeClass('hidden-legend');
            $(button).siblings('div.legend').removeClass('nodisplay');
            $(button).parents('div.legend-container').siblings('div.graphdiv')
                .css('height', widget.sw_opentsdbwidget_frontmain.innerHeight() - $(button).parents('div.legend-container').outerHeight(true));
            $(button).children('span.elegant-icons').removeClass('arrow-triangle-up').addClass('arrow-triangle-down');
            if (typeof widget.svg !== "undefined") {
                widget.resize_graph();
            }
            widget.options.legend = 'on'
        },
        show_graph_data: function() {
            var widget = this;
            var table_data = [];
            $('body').append('<div id="raw-data" class="popup mfp-hide">' +
                '<table id="raw-data-table" class="table"></table></div>');
            if (widget.graph.raw_data.length > 0) {
                $.each(widget.graph.raw_data, function(i, metric) {
                    $.each(metric.values, function(i, point_data) {
                        table_data.push([metric.name, point_data.timestamp, point_data.value, metric.axis]);
                    })
                })
            }
            $('#raw-data-table').dataTable({
                'aaData': table_data,
                'aoColumns': [
                    {'sTitle': 'Metric Name'},
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
                            var raw_data_csv = 'Metric Name, Timestamp, Value, Display Axis\n';
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
        },
        reset_data_table: function() {
            $('#raw-data-table').dataTable().fnDestroy();
            $('#raw-data').remove();

        }
    })
}(jQuery));
