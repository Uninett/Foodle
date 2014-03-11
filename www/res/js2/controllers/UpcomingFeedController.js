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

	var UpcomingFeedController = Class.extend({
		"init": function(api, el, feed, style, hlFoodle) {

			this.api = api;
			this.el = el;
			this.style = style || 'slim';
			this.hlFoodle = hlFoodle || null;

			if (!feed) {
				throw "Feed not provided to UpcomingFeedController";
			}
			
			this.feed = feed;
			this.api.getFeedEvents(this.feed, $.proxy(this.processEvents, this));
		},
		
		"processEvents": function(events) {
			var that = this;

			console.log("Received eventlist", events);
			console.log("That element", that.el);

			that.el.empty();
			$.each(events, function(i, item) {
				var ne = null;

				item.target = ' target="blank" ';
				if (that.style === 'slim') {
					ne = stemplate(item);
				} else {
					ne = template(item);	
				}

				// console.log("Highlight", that.hlFoodle, item);
				if (that.hlFoodle && that.hlFoodle.identifier === item.foodle.id) {
					console.log("Highlight", $(ne));
					ne = $(ne).addClass('active');
				}

				// console.log("Processing item", item);
				that.el.append(ne);
				
			});
			$("span.ts").prettyDate(); 
		}
	})

	return UpcomingFeedController;

});