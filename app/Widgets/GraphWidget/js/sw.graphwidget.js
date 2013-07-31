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
				,sw_graphwidget_container
				,sw_graphwidget_containerid
				,sw_graphwidget_front
				,sw_graphwidget_back
				,sw_graphwidget_close
				,sw_graphwidget_fronttitle
				,sw_graphwidget_backtitle
				,sw_graphwidget_frontfooter
				,sw_graphwidget_backfooter
				,sw_graphwidget_frontmain
				,sw_graphwidget_backmain
				,sw_graphwidget_legend
				,sw_graphwidget_savedsearchesmenu
				,sw_graphwidget_datasourcemenu
				,sw_graphwidget_searchform
				,sw_graphwidget_editparamsbutton
				,sw_graphwidget_maximizebutton
				,sw_graphwidget_querycancelbutton
				,sw_graphwidget_gobutton
				,sw_graphwidget_datasource;

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
			sw_graphwidget_legend = (this.sw_graphwidget_legend = $('<div>'))
				.addClass('graph-widget-legend glue4')
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
					'<li><span>OpenTSDB</span></li></ul>')
				.appendTo(sw_graphwidget_backtitle);

			// Buttons that will go in the footer bar
			sw_graphwidget_editparamsbutton = (this.sw_graphwidget_editparamsbutton = $('<div>'))
				.addClass("widget-footer-button left-button")
				.click(function() {
					sw_graphwidget.addClass("flipped");
				})
				.append('<span class="iconic iconic-pen-alt2"> Edit Params</span>')
				.appendTo(sw_graphwidget_frontfooter);

			sw_graphwidget_frontfooter.append('<div class="glue1">');

			sw_graphwidget_maximizebutton = (this.sw_graphwidget_maximize_button = $('<div>'))
				.addClass("widget-footer-button right-button fullscreen-out")
				.append('<span class="maximize-me iconic iconic-fullscreen">')
				.click(function(event) {
					event.preventDefault();
					that.maximize_widget();
				})
				.appendTo(sw_graphwidget_frontfooter);

			sw_graphwidget_querycancelbutton = (this.sw_graphwidget_querycancelbutton = $('<div>'))
				.addClass("widget-footer-button left-button query_cancel")
				.click(function() {
					sw_graphwidget.removeClass("flipped")
				})
				.append('<span class="iconic iconic-x-alt"> Cancel</span>')
				.appendTo(sw_graphwidget_backfooter);

			sw_graphwidget_backfooter.append('<div class="glue1">');

			sw_graphwidget_gobutton = (this.sw_graphwidget_gobutton = $('<div>'))
				.addClass("widget-footer-button right-button go-button")
				.click(function() {
					go_click_handler(event, that);
				})
				.append('<span class="iconic iconic-bolt"> Go</span>')
				.appendTo(sw_graphwidget_backfooter);

			// Define the div for the search form
			sw_graphwidget_searchform = (this.sw_graphwidget_searchform = $('<div>'))
				.addClass('graph-widget-search-form')
				.appendTo(sw_graphwidget_backmain);

			sw_graphwidget_datasource = $.trim($(sw_graphwidget_datasourcemenu).children('span.widget-title-button').children('span.active-datasource').text().toLowerCase());
			this.show_datasource_form(sw_graphwidget_datasource + '_search');
		}

		,_destroy: function() {
			console.log('_destroy called');
			this.sw_graphwidget_gobutton.remove();
			this.sw_graphwidget_querycancelbutton.remove();
			this.sw_graphwidget_searchform.remove();
			this.sw_graphwidget_datasourcemenu.remove();
			this.sw_graphwidget_savedsearchesmenu.remove();
			this.sw_graphwidget_backfooter.remove();
			this.sw_graphwidget_backmain.remove();
			this.sw_graphwidget_backtitle.remove();
			this.sw_graphwidget_maximize_button.remove();
			this.sw_graphwidget_editparamsbutton.remove();
			this.sw_graphwidget_close.remove();
			this.sw_graphwidget_frontfooter.remove();
			this.sw_graphwidget_frontmain.remove();
			this.sw_graphwidget_fronttitle.remove();
			this.sw_graphwidget_front.remove();
			this.sw_graphwidget_back.remove();
			this.sw_graphwidget.remove();
		}

		,show_datasource_form: function(datasource) {
			var form_div = this.sw_graphwidget_searchform;
			var view_url = this.options['widget_url'] + 'GraphWidget/api/datasource_form/' + datasource;
			$.ajax({
				url: view_url
				,method: 'GET'
				,dataType: 'json'
				,success: function(data) {
					$(form_div).addClass('hidden');
					$(form_div).empty();
					$(form_div).html(data['form_source']);
					$(form_div).removeClass('hidden');
				}
			});
		}

		,maximize_widget: function() {
			if ($(this.sw_graphwidget_container).hasClass('maximize-widget'))
			{
				$(this.sw_graphwidget_container).removeClass('maximize-widget');
				$('body').removeClass('no-overflow');
				$('.navbar').removeClass('hidden');
				$(this.sw_graphwidget_container).children('span.maximize-me').removeClass('iconic-fullscreen-exit').addClass('iconic-fullscreen');
				this.resize_graph();
			}
			else
			{
				$(this.sw_graphwidget_container).addClass('maximize-widget');
				$('.navbar').addClass('hidden');
				$('body').addClass('no-overflow');
				$(this.sw_graphwidget_container).children('span.maximize-me').removeClass('iconic-fullscreen').addClass('iconic-fullscreen-exit');
				this.resize_graph();
			}
		}

		,resize_graph: function() {
			var evt = document.createEvent('UIEvents');
			evt.initUIEvent('resize', true, false,window,0);
			window.dispatchEvent(evt);
		}

	})
}(jQuery));
