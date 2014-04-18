/**
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 15 July 2013
 *
 */


(function($, undefined) {

    var sw_examplewidget_classes = 'widget';

    $.widget('sw.examplewidget', {
        version: "1.0",
        defaultElement: "<div>",
        options: {
            disabled: null,
            label: null,
        }, _create: function() {

            var that = this,
                options = this.options,
                sw_examplewidget,
                sw_examplewidget_container,
                sw_examplewidget_containerid,
                sw_examplewidget_front,
                sw_examplewidget_back,
                sw_examplewidget_close,
                sw_examplewidget_backtitle,
                sw_examplewidget_backfooter,
                sw_examplewidget_frontmain,
                sw_examplewidget_backmain,
                sw_examplewidget_form,
                sw_examplewidget_action,
                sw_examplewidget_querycancelbutton,
                sw_examplewidget_gobutton;

            // If this is the first OpenTSDB widget added, load the
            // widget-specific CSS file
            if ($('link[href*="opentsdb"]').length === 0) {
                var widget_css = document.createElement('link');
                widget_css.setAttribute('rel', 'stylesheet');
                widget_css.setAttribute('href', window.location.origin + '/Widgets/OpenTSDBWidget/css/sw.examplewidget.css');
                document.getElementsByTagName('head')[0].appendChild(widget_css);
            }

            sw_examplewidget_container = (this.sw_examplewidget_container = $(this.element));
            $(sw_examplewidget_container).addClass('transparent');

            sw_examplewidget = (this.sw_examplewidget = $('<div>'))
                .addClass(sw_examplewidget_classes)
                .appendTo(this.element);

            sw_examplewidget_containerid = (this.sw_examplewidget_containerid = '#' + $(this.sw_examplewidget_container).attr('id'));

            // The Example widget has a front and back face
            sw_examplewidget_front = (this.sw_examplewidget_front = $('<div>'))
                .addClass('widget-front')
                .appendTo(sw_examplewidget);
            sw_examplewidget_back = (this.sw_examplewidget_back = $('<div>'))
                .addClass('widget-back')
                .appendTo(sw_examplewidget);

            // Each face has a title bar, a main content area,
            // and a footer bar
            sw_examplewidget_frontmain = (this.sw_examplewidget_frontmain = $('<div>'))
                .addClass('widget-main')
                .addClass('front')
                .appendTo(sw_examplewidget_front);
            sw_examplewidget_backtitle = (this.sw_examplewidget_backtitle = $('<div>'))
                .addClass('widget-title')
                .appendTo(sw_examplewidget_back);
            sw_examplewidget_backmain = (this.sw_examplewidget_backmain = $('<div>'))
                .addClass('widget-main')
                .addClass('back')
                .appendTo(sw_examplewidget_back);
            sw_examplewidget_backfooter = (this.sw_examplewidget_backfooter = $('<div>'))
                .addClass('widget-footer')
                .appendTo(sw_examplewidget_back);

            sw_examplewidget_action = (this.sw_examplewidget_action = $('<div>'))
                .addClass('widget-action dropdown')
                .append('<span data-toggle="dropdown">' +
                    '<span class="elegant-icons icon-cog"></span></span>' +
                    '<ul class="dropdown-menu sub-menu-item widget-action-options" role="menu">' +
                    '<li data-menu-action="maximize_widget"><span class="maximize-me">Maximize</span></li>' +
                    '<li data-menu-action="edit_params"><span>Edit Parameters</span></li>' +
                    '<li class="clone-widget" data-parent="' + that.element.attr('id') + '"><span>Clone Widget</span></li>' +
                    '<li class="dropdown"><span>Options</span><span class="elegant-icons arrow-triangle-right"></span>' +
                    '<ul class="dropdown-menu sub-menu examplewidget-options-menu">' +
                    '<li data-menu-action="example_action"><span>Sub Menu Option Choice</span></li></ul>')
                .appendTo(sw_examplewidget_frontmain);
            $('li.clone-widget').click(function(event) {
                event.stopImmediatePropagation();
                that.clone_widget($(this).attr('data-parent'));
            });

            sw_examplewidget_close = (this.sw_examplewidget_close = $('<div>'))
                .addClass('widget-close')
                .click(function(event) {
                    event.preventDefault();
                    $(sw_examplewidget_container).addClass('transparent');
                    var that_id = sw_examplewidget_containerid;
                    setTimeout(function() {
                        that.destroy(event);
                        sw_examplewidget_container.remove();
                    }, 600);
                })
                .append('<span class="elegant-icons icon-close">')
                .appendTo(sw_examplewidget_frontmain);

            // Buttons that will go in the footer bar
            sw_examplewidget_querycancelbutton = (this.sw_examplewidget_querycancelbutton = $('<div>'))
                .addClass("widget-footer-button left-button query_cancel")
                .click(function() {
                    that.flip_to_front();
                })
                .append('<span class="iconic iconic-x-alt"><span class="font-reset"> Cancel</span></span>')
                .appendTo(sw_examplewidget_backfooter);

            sw_examplewidget_gobutton = (this.sw_examplewidget_gobutton = $('<div>'))
                .addClass("widget-footer-button right-button go-button")
                .click(function(event) {
                    that.go_click_handler(event);
                })
                .append('<span class="iconic iconic-bolt"><span class="font-reset"> Go</span></span>')
                .appendTo(sw_examplewidget_backfooter);

            // Define the div for the search form
            sw_examplebwidget_form = (this.sw_examplewidget_form = $('<div>'))
                .addClass('example-widget-form')
                .appendTo(sw_examplewidget_backmain);

            setTimeout(function() {
                that.edit_params();
            }, 250);


            $(sw_examplewidget).on('click', 'li[data-menu-action]', function(event) {
                event.preventDefault();
                var action = $(this).attr('data-menu-action');
                that[action](this, that);
            });
        }, _destroy: function() {
            console.log('_destroy called');
            if (typeof this.autoupdate_timer !== "undefined") {
                clearTimeout(this.autoupdate_timer);
            }
            if (typeof this.ajax_request !== "undefined") {
                this.ajax_request.abort();
            }
            this.sw_examplewidget_gobutton.remove();
            this.sw_examplewidget_querycancelbutton.remove();
            this.sw_examplewidget_form.remove();
            this.sw_examplewidget_backfooter.remove();
            this.sw_examplewidget_backmain.remove();
            this.sw_examplewidget_backtitle.remove();
            this.sw_examplewidget_action.remove();
            this.sw_examplewidget_close.remove();
            this.sw_examplewidget_frontmain.remove();
            this.sw_examplewidget_front.remove();
            this.sw_examplewidget_back.remove();
            this.sw_examplewidget.remove();
        }, dropdown_menu_handler: function(item) {
            var widget = this;
            var button = $(item).parent().parent().children('span');
            var action = $(item).attr('data-action');
            $(button).children('.graph-widget-button-label').text($(item).text());
            $(button).children('div.ds-interval').attr('data-value', $(item).children('span').attr('data-value'));
            if ($(item).parent().attr('id') === "time-span-options" + widget.uuid) {
                $(button).children('div#time-span' + widget.uuid).attr('data-ms', $(item).children('span').attr('data-ms')).text();
            }
        }, edit_params: function() {
            var widget = this;
            $(window).scrollTop(0);
            widget.start_height = widget.sw_examplewidget_container.height();
            widget.start_width = widget.sw_examplewidget_container.width();
            widget.widget_position = widget.sw_examplewidget_container.position();
            widget.sw_examplewidget.addClass('flipped');
            $('div.container').after('<div class="bodyshade transparent">');
            setTimeout(function() {
                widget.sw_examplewidget_container.css({position: 'absolute', height: widget.start_height + 'px', width: widget.start_width + 'px', top: widget.widget_position.top, left: widget.widget_position.left, 'z-index': '500'})
                    .after('<div class="spacer-box" style="display: inline-block; width: ' + widget.start_width +
                        'px; height: ' + widget.start_height +
                        'px; margin: ' + widget.sw_examplewidget.parent().css('margin') + '"></div>');
                widget.sw_examplewidget_container.css({top: '15%', left: '15%', height: '70%', width: '65%'});
                $('div.bodyshade').removeClass('transparent');
            }, 700);
        }, flip_to_front: function() {
            var widget = this;
            if ($('div.bodyshade').length > 0) {
                $('div.bodyshade').addClass('transparent');
                widget.sw_examplewidget_container.css({width: widget.start_width, height: '', top: widget.widget_position.top, left: widget.widget_position.left});
                setTimeout(function() {
                    widget.sw_examplewidget_container.css({position: '', top: '', left: '', 'z-index': '', height: '', width: ''})
                        .siblings('div.spacer-box').remove();
                    $('div.bodyshade').remove();
                }, 600);
                setTimeout(function() {
                    if (typeof widget.svg !== "undefined") {
                        widget.resize_graph();
                    }
                    widget.sw_examplewidget.removeClass('flipped');
                }, 700);
            }
            else {
                widget.sw_examplewidget.removeClass('flipped');
                if ($('#' + widget.element.attr('id')).hasClass('transparent')) {
                    $('#' + widget.element.attr('id')).removeClass('transparent');
                }
            }
        }, maximize_widget: function() {
            var widget = this;
            if ($(widget.sw_examplewidget_container).hasClass('maximize-widget')) {
                $(widget.sw_examplewidget_container).removeClass('maximize-widget');
                $(widget.sw_examplewidget_container).addClass('cols-' + document._session.dashboard_columns);
                $('body').removeClass('no-overflow');
                $('.menubar').removeClass('hidden');
                $(widget.sw_examplewidget_containerid + ' span.maximize-me').text('Maximize');
            }
            else {
                $(window).scrollTop(0);
                $(widget.sw_examplewidget_container).removeClass('cols-' + document._session.dashboard_columns);
                $(widget.sw_examplewidget_container).addClass('maximize-widget');
                $('.menubar').addClass('hidden');
                $('body').addClass('no-overflow');
                $(widget.sw_examplewidget_containerid + ' span.maximize-me').text('Minimize');
            }
        }, clone_widget: function(widget_id) {
            // Add a new widget as a duplicate of the selected widget
            var widget = $('#' + widget_id).data('sw-opentsdbwidget');
            console.log('Cloning widget ' + widget.element.attr('id'));
            var username = document._session.dashboard_username;
            var new_widget_id = "widget" + md5(username + new Date.now().getTime());
            console.log('Adding new widget ' + new_widget_id);
            $('#dash-container').append('<div class="widget-container" id="' + new_widget_id + '" data-widget-type="opentsdbwidget">');
            var new_widget = $('div#' + new_widget_id).opentsdbwidget(widget.options);
            new_widget.addClass('cols-' + document._session.dashboard_columns);
            new_widget_object = $(new_widget).data('sw-opentsdbwidget');
            new_widget_object.populate_search_form(widget.query_data, 'clone');
            $('#search-title' + new_widget_object.uuid).css('width', $('#search-title' + widget.uuid).width());
            $('#' + new_widget_object.element.attr('id')).removeClass('transparent');
        }
    })
}(jQuery));
