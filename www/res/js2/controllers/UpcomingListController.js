define(function(require, exports) {



	var 
		$ = require('jquery'),
		Class = require('lib/class'),
		hb = require('lib/handlebars'),

		pretty = require('lib/pretty'),

		Activity = require('models/Activity')
		;

	var t = require('lib/text!templates/upcoming.html');
	var ts = require('lib/text!templates/upcoming-slim.html');
	var template = hb.compile(t);
	var stemplate = hb.compile(ts);

	var UpcomingListController = Class.extend({
		"init": function(api, el, limit, style, hlFoodle) {

			this.api = api;
			this.el = el;
			this.style = style || 'basic';
			this.hlFoodle = hlFoodle || null;

			this.limit = limit || 0;

			this.api.getEvents(this.limit, $.proxy(this.processEvents, this));
		},
		
		"processEvents": function(events) {
			var that = this;

			// console.log("Received eventlist", events);
			// console.log("That element", that.el);

			that.el.empty();
			$.each(events, function(i, item) {
				var ne = null;
				var obj = $.extend({}, item, {"_": window._d});
				if (that.style === 'slim') {
					ne = stemplate(obj);
				} else {
					ne = template(obj);	
				}

				// console.log("Highlight", that.hlFoodle, item);
				if (that.hlFoodle && that.hlFoodle.identifier === item.foodle.id) {
					// console.log("Highlight", $(ne));
					ne = $(ne).addClass('active');
				}

				// console.log("Processing item", item);
				that.el.append(ne);
				
			});
			$("span.ts").prettyDate(); 
		}
	})

	return UpcomingListController;

});