define(function(require, exports) {



	var 
		$ = require('jquery'),
		hb = require('lib/handlebars'),
		Class = require('lib/class')
		;

	var t = require('lib/text!templates/headerbar.html');
	var template = hb.compile(t);

	var HeaderBar = Class.extend({
		"init": function(data) {


			this.el = $("#headerbar");


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
			console.log("Template objet", obj);
			this.el.append(template(obj));
			
		}
	})

	return HeaderBar;

});