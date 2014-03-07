define(function(require, exports, module) {

	var 
		
		$ = jQuery = require('jquery'),

		API = require('api/API').API,
		ActivityListController = require('controllers/ActivityListController'),
		UpcomingListController = require('controllers/UpcomingListController'),

		DJ = require('misc/discojuice')
		;

	require('bootstrap/bootstrap');

	$.getJSON('/api/user', function(data) {

		$(document).ready(function() {

			console.log("Received data", data);

			if (data.authenticated) {

				$('.showIfAuthenticated').show();
				$('.hideIfAuthenticated').hide();

				var api = new API(data.token);
				var al = new ActivityListController(api, $("#activities"));
				var il = new UpcomingListController(api, $("#upcoming"));

			} else {
				DJ.load();				
			}


		});

	});







});

