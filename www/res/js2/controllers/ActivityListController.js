define(function(require, exports) {


    /**
     * dlkfjdfkjg
     */
	var 
		$ = require('jquery'),
		Class = require('lib/class'),
		hb = require('lib/handlebars'),

		pretty = require('lib/pretty'),

		Activity = require('models/Activity')
		;

	var t = require('lib/text!templates/activity.html');
	var template = hb.compile(t);

	var ActivityListController = Class.extend({
		"init": function(api, el) {

			this.api = api;
			this.el = el;

			this.api.getActivities($.proxy(this.processActivities, this));
		},
		"processActivities": function(activities) {
			var that = this;

			console.log("Received ActivityList", activities);
			console.log("That element", that.el);

			that.el.empty();
			$.each(activities, function(i, item) {
				that.el.append(template(item));
			});
			$("span.ts").prettyDate(); 
		}
	})

	return ActivityListController;

});