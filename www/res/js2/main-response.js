define(function(require, exports, module) {

	var 
		
		$ = jQuery = require('jquery'),

		API = require('api/API').API,
		FoodleResponseController = require('controllers/FoodleResponseController'),

		DJ = require('misc/discojuice')
		;


		
	require('bootstrap/bootstrap');
	require('bootstrap-datepicker');


	$.getJSON('/api/user', function(data) {

		$(document).ready(function() {

			// console.log("Received data", data);
			DJ.load();		

			if (data.authenticated) {

				var api = new API(data.token);
				
				var frc = $("#foodleResponse");
				var identifier = frc.data('foodleid');
				// console.log("Loaded with identifier", identifier);

				api.getFoodle(identifier, function(foodle) {
					foodle.setUser(data.user.userid);
					var cc = new FoodleResponseController(api, foodle, data.user, frc);
				});

			} else {

				var api = new API();

				// api.createAnonymousSession('Andreas Anonymous', 'andreas@uninett.no', function(r) {
				// 	console.log("Successfully registered", r);
				// 	$.getJSON('/api/user', function(data) {
				// 		console.log("Then what", data);
				// 	});
				// });

				
				var frc = $("#foodleResponse");
				var identifier = frc.data('foodleid');
				// console.log("Loaded with identifier", identifier);

				api.getFoodle(identifier, function(foodle) {
					// foodle.setUser(data.user.userid);
					var cc = new FoodleResponseController(api, foodle, null, frc);
				});
			}


		});

	});





});

