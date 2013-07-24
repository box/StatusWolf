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
		}
		,_create: function() {

			var that = this
				,options = this.options
				,sw_graphwidget
				,sw_graphwidget_front
				,sw_graphwidget_back
				,sw_graphwidget_close
				,sw_graphwidget_fronttitle
				,sw_graphwidget_backtitle
				,sw_graphwidget_frontfooter
				,sw_graphwidget_backfooter
				,sw_graphwidget_frontmain
				,sw_graphwidget_backmain
				,sw_graphwidget_savedsearchesmenu
				,sw_graphwidget_datasourcemenu
				,sw_graphwidget_editparamsbutton
				,sw_graphwidget_maximizebutton
				,sw_graphwidget_querycancelbutton
				,sw_graphwidget_gobutton;

			$(this.element).addClass('transparent');


			sw_graphwidget = (this.graph_widget = $('<div>'))
				.addClass(sw_graphwidget_classes)
				.appendTo(this.element);

			this.element_id = '#' + $(this.element).attr('id');

			// The graph widget has a front and back face
			sw_graphwidget_front = (this.sw_widget_front = $('<div>'))
				.addClass('widget-front')
				.appendTo(sw_graphwidget);
			sw_graphwidget_back = (this.sw_graphwidget_back = $('<div>'))
				.addClass('widget-back')
				.appendTo(sw_graphwidget);

			// Each face has a title bar, a main content area,
			// and a footer bar
			sw_graphwidget_fronttitle = (this.sw_graphwidget_fronttitle = $('<div>'))
				.addClass('flexy widget-title')
				.append('<div class="glue4">')
				.appendTo(sw_graphwidget_front);
			sw_graphwidget_frontmain = (this.sw_graphwidget_frontmain = $('<div>'))
				.addClass('widget-main')
				.appendTo(sw_graphwidget_front);
			sw_graphwidget_frontfooter = (this.sw_graphwidget_frontfooter = $('<div>'))
				.addClass('flexy widget-footer')
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
			sw_graphwidget_close = (this.sw_graphwidget_close = $('<div>'))
				.addClass('widget-title-button close-widget')
				.click(function(event) {
					event.preventDefault();
					$(that.element).addClass('transparent');
					var that_id = that.element.attr('id');
					setTimeout(function() {
						that.destroy(event);
						$('#' + that_id).remove();
					}, 600);
				})
				.append('<span class="iconic iconic-x">');

			sw_graphwidget_savedsearchesmenu = (this.sw_graphwidget_savedsearchesmenu = $('<div>'))
				.addClass('dropdown widget-title-dropdown saved-searches-menu')
				.append('<span class="widget-title-button" data-toggle="dropdown">' +
				'<span class="widget-button-label">Saved Searches </span>' +
				'<span class="iconic iconic-play rotate-90"></span></span>' +
				'<ul class="dropdown-menu saved-searches-options" role="menu" aria-labelledby="dLabel"></ul>')
				.appendTo(sw_graphwidget_backtitle);

			sw_graphwidget_backtitle.append('<div class="glue1">');

			sw_graphwidget_datasourcemenu = (this.sw_graphwidget_datasourcemenu = $('<div>'))
				.addClass('dropdown widget-title-dropdown datasource-menu')
				.append('<span class="widget-title-button" data-toggle="dropdown">' +
					'<span class="widget-button-label active-datasource">OpenTSDB </span>' +
					'<span class="iconic iconic-play rotate-90"></span></span>' +
					'<ul class="dropdown-menu menu-left datasource-options" role="menu" aria-labelledby="dLabel">' +
					'<li><span>OpenTSDB</span></li></ul>')
				.appendTo(sw_graphwidget_backtitle);



			// Buttons that will go in the footer bar
			sw_graphwidget_editparamsbutton = (this.sw_graphwidget_editparamsbutton = $('<div>'))
				.addClass("widget-footer-btn")
				.click(function() {
					sw_graphwidget.addClass("flipped");
				})
				.append('<span class="iconic iconic-pen-alt2"> Edit Params</span>')
				.appendTo(sw_graphwidget_frontfooter);

			sw_graphwidget_frontfooter.append('<div class="glue1">');

			sw_graphwidget_maximizebutton = (this.sw_graphwidget_maximize_button = $('<div>'))
				.addClass("widget-footer-btn fullscreen-out")
				.append('<span class="maximize-me iconic iconic-fullscreen">')
				.click(function(event) {
					event.preventDefault();
					that.maximize_widget(that);
				})
				.appendTo(sw_graphwidget_frontfooter);

			sw_graphwidget_querycancelbutton = (this.sw_graphwidget_querycancelbutton = $('<div>'))
				.addClass("widget-footer-btn query_cancel")
				.click(function() {
					sw_graphwidget.removeClass("flipped")
				})
				.append('<span class="iconic iconic-x-alt"> Cancel</span>')
				.appendTo(sw_graphwidget_backfooter);

			sw_graphwidget_backfooter.append('<div class="glue1">');

			sw_graphwidget_gobutton = (this.sw_graphwidget_gobutton = $('<div>'))
				.addClass("widget-footer-btn go-button")
//				.click(go_click_handler(event))
				.append('<span class="iconic iconic-bolt"> Go</span>')
				.appendTo(sw_graphwidget_backfooter);

//        this.datasource = $.trim($(this.element_id + ' .active-datasource').text().toLowerCase()) + '_search';
//		console.log(this.datasource);
//        this.show_datasource_form(this.element_id, this.datasource);
		}

		,_destroy: function() {
			console.log('_destroy called');
			this.go_button.remove();
			this.query_cancel_button.remove();
			this.edit_params_button.remove();
			this.maximize_button.remove();
			this.datasource_menu.remove();
			this.saved_searches_menu.remove();
			this.widget_close_button.remove();
			this.widget_back.remove();
			this.widget_front.remove();
			this.graph_widget.remove();
		}

		,show_datasource_form: function(element_id, datasource) {
			var view_url = this.option('widget_url') + 'GraphWidget/api/datasource_form/' + datasource;
			console.log(view_url);
			$.ajax({
				url: view_url
				,method: 'GET'
				,dataType: 'json'
				,success: function(data) {
					$(element_id + ' .graph-widget-search-form').addClass('hidden');
					$(element_id + ' .graph-widget-search-form').empty();
					$(element_id + ' .graph-widget-search-form').html(data['form_source']);
					$(element_id + ' .graph-widget-search-form').removeClass('hidden');
				}
			});
		}

		,maximize_widget: function(big_widget) {
			if ($(big_widget.element).hasClass('maximize-widget'))
			{
				$(big_widget.element).removeClass('maximize-widget');
				$('body').removeClass('no-overflow');
				$('.navbar').removeClass('hidden');
				$(big_widget.element).children('span.maximize-me').removeClass('iconic-fullscreen-exit').addClass('iconic-fullscreen');
				big_widget.resize_graph();
			}
			else
			{
				$(big_widget.element).addClass('maximize-widget');
				$('.navbar').addClass('hidden');
				$('body').addClass('no-overflow');
				$(big_widget.element).children('span.maximize-me').removeClass('iconic-fullscreen').addClass('iconic-fullscreen-exit');
				big_widget.resize_graph();
			}
		}

		,resize_graph: function() {
			var evt = document.createEvent('UIEvents');
			evt.initUIEvent('resize', true, false,window,0);
			window.dispatchEvent(evt);
		}

	})
}(jQuery));
