define(function(require, exports, module) {

	var 
		
		$ = jQuery = require('jquery'),

		API = require('api/API').API,
		EditFoodleController = require('controllers/EditFoodleController'),

		DJ = require('misc/discojuice')
		;


		
	require('bootstrap/bootstrap');
	require('bootstrap-datepicker');



	$.getJSON('/api/user', function(data) {

		$(document).ready(function() {

			// console.log("Received data", data);

			if (data.authenticated) {

				var api = new API(data.token);
				
				if (window.foodle_id) {
					// console.log("Foodle is to load is ", foodle_id);
					api.getFoodleAuth(window.foodle_id, function(foodle) {
						// foodle.setUser(userid);
						var cc = new EditFoodleController(api, $("#editfoodle"), data.user, foodle);
					});

				} else {
					
					var cc = new EditFoodleController(api, $("#editfoodle"), data.user);

				}

			} else {
				DJ.load();				
			}


		});

	});






});

