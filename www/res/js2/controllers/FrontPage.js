define(function(require, exports) {



	var 
		$ = require('jquery'),
		hb = require('lib/handlebars'),
		ActivityListController = require('controllers/ActivityListController'),
		UpcomingListController = require('controllers/UpcomingListController'),
		UpcomingFeedController = require('controllers/UpcomingFeedController')
		;

	var t = require('lib/text!templates/frontpage.html');
	var template = hb.compile(t);

	var FrontPage = Class.extend({
		"init": function(api, data) {
			this.api = api;
			this.el = $("#frontpage");

			console.log("FRONTPAGE");

			var obj = {
				"_": window._d,
				"authenticated": data.authenticated
			};

			if (data.logouturl) {
				obj.logouturl = data.logouturl;
			}
			if (data.user) {
				obj.user = data.user;
			}
			console.log("Template objet", obj, data);
			this.el.append(template(obj));

			if (api) {
				var al = new ActivityListController(api, $("#activities"));
				var il = new UpcomingListController(api, $("#upcoming"));
				$('.showIfAuthenticated').show();
				$('.hideIfAuthenticated').hide();
			}

			
		}
	})

	return FrontPage;

});