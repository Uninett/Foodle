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

			DJ.load();		

			if (data.authenticated) {

				var api = new API(data.token);
				
				var frc = $("#foodleResponse");
				var identifier = frc.data('foodleid');

				api.getFoodle(identifier, function(foodle) {
					foodle.setUser(data.user.userid);
					var cc = new FoodleResponseController(api, foodle, data.user, frc);
				});

			} else {

				var api = new API();

				var frc = $("#foodleResponse");
				var identifier = frc.data('foodleid');

				api.getFoodle(identifier, function(foodle) {
					var cc = new FoodleResponseController(api, foodle, null, frc);
				});
			}


		});

	});





});

