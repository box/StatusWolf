/**
 *
 * Author: Mark Troyer <disco@box.com>
 * Date Created: 15 July 2013
 *
 */


(function() {
	$.widget('custom.graph_widget', {
		version: "1.0"
		,defaultElement: "<div>"
		,options: {
			disabled: null
			,label: null
		}
		,_create: function() {

			var that = this;

			$(this.element).addClass('transparent');

			this.element_id = '#' + $(this.element).attr('id');

			this.graph_widget = $('<div>', {
				'class': 'widget'
			}).appendTo(this.element);

			// The graph widget has a front and back face
			this.widget_front = $('<div>', {
				'class': 'widget-front'
			}).appendTo(this.graph_widget);
			this.widget_back = $('<div>', {
				'class': 'widget-back'
			}).appendTo(this.graph_widget);

			// Each face has a title bar, a main content area,
			// and a footer bar
			$(this.widget_front).append('<div class="flexy widget-title">')
				.append('<div class="widget-main">')
				.append('<div class="flexy widget-footer">');
			$(this.widget_back).append('<div class="flexy widget-title">')
				.append('<div class="widget-main">')
				.append('<div class="flexy widget-footer">');

			// Define the standard buttons for the graph widget
			// Buttons that will go in the title bar:
			this.widget_close_button = $('<div>')
				.addClass('widget-title-button close-widget')
				.click(function(event) {
					event.preventDefault();
					$(that.element).addClass('transparent');
					var that_id = that.element.attr('id');
					setTimeout(function() {
						that.destroy(event);
						$('#' + that_id).remove();
					}, 600);
				});
			$(this.widget_close_button).append('<span class="iconic iconic-x">');
			this.saved_searches_menu = $('<div>', {
				'class': 'dropdown widget-title-dropdown saved-searches-menu'
			});
			$(this.saved_searches_menu).append('<span class="widget-title-button" data-toggle="dropdown">' +
				'<span class="widget-button-label">Saved Searches </span>' +
				'<span class="iconic iconic-play rotate-90"></span></span>' +
				'<ul class="dropdown-menu saved-searches-options" role="menu" aria-labelledby="dLabel"></ul></div>');
			this.datasource_menu = $('<div>', {
				'class': 'dropdown widget-title-dropdown datasource-menu'
			});
			$(this.datasource_menu).append('<span class="widget-title-button" data-toggle="dropdown">' +
				'<span class="widget-button-label active-datasource">OpenTSDB </span>' +
				'<span class="iconic iconic-play rotate-90"></span></span>' +
				'<ul class="dropdown-menu menu-left datasource-options" role="menu" aria-labelledby="dLabel">' +
				'<li><span>OpenTSDB</span></li></ul></div>');

			// Buttons that will go in the footer bar
			this.edit_params_button = $('<div>', {
				'class': 'widget-footer-btn'
				,'onClick': '$(this).parents(\'.widget\').addClass(\'flipped\')'
			});
			$(this.edit_params_button).append('<span class="iconic iconic-pen-alt2">').append(' Edit Params');
			this.maximize_button = $('<div>')
				.addClass('widget-footer-btn fullscreen-out')
				.append('<span class="maximize-me iconic iconic-fullscreen">')
				.click(function(event) {
					event.preventDefault();
					that.maximize_widget(that);
				});
			this.query_cancel_button = $('<div>', {
				'class': 'widget-footer-btn query_cancel'
				,'onClick': '$(this).parents(\'.widget\').removeClass(\'flipped\')'
			});
			$(this.query_cancel_button).append('<span class="iconic iconic-x-alt">').append(' Cancel');
			this.go_button = $('<div>', {
				'class': 'widget-footer-btn go-button'
				,'onClick': 'go_click_handler(event)'
			});
			$(this.go_button).append('<span class="iconic iconic-bolt">').append(' Go');

			$(this.widget_front).children('.widget-title')
				.append('<div class="glue1">')
				.append(this.widget_close_button);
			$(this.widget_front).children('.widget-main')
				.append('<div class="graphdiv">');
			$(this.widget_front).children('.widget-footer')
				.append(this.edit_params_button)
				.append('<div class="glue1">')
				.append(this.maximize_button);

			$(this.widget_back).children('.widget-title')
				.append(this.saved_searches_menu)
				.append('<div class="glue1">')
				.append(this.datasource_menu);
			$(this.widget_back).children('.widget-main')
				.append('<div class="graph-widget-search-form">');
			$(this.widget_back).children('.widget-footer')
				.append(this.query_cancel_button)
				.append('<div class="glue1">')
				.append(this.go_button);

        this.datasource = $.trim($(this.element_id + ' .active-datasource').text().toLowerCase()) + '_search';
		console.log(this.datasource);
        this.show_datasource_form(this.element_id, this.datasource);
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
