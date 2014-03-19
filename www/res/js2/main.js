define(function(require, exports, module) {

	var 
		
		$ = jQuery = require('jquery'),

		API = require('api/API').API,
		FoodleResponseController = require('controllers/FoodleResponseController'),

		ActivityListController = require('controllers/ActivityListController'),
		UpcomingListController = require('controllers/UpcomingListController'),
		UpcomingFeedController = require('controllers/UpcomingFeedController')

		EditFoodleController = require('controllers/EditFoodleController'),


		DJ = require('misc/discojuice')
		;

	require('bootstrap/bootstrap');
	require('bootstrap-datepicker');


	var route = function(path, strict) {

		if (strict) {
			
			if (window.location.pathname === path) {
				// console.log("› Matching route [" + path + "] STRICT");
				return true;
			}
			return false;
		}

		// console.log('Search route(' + path + ')', window.location.pathname, window.location.pathname.indexOf(path));
		if (window.location.pathname.indexOf(path) === 0) {
			// console.log("› Matching route [" + path + "] loose matching");
			return true;
		}
		return false;
	}



	// var loc = window.location.href;
	// console.log("Parse url", window.location.pathname, loc, parseUri(loc));


	var getPath = function() {

	
		var hash = window.location.hash;
		if (hash.length < 3) return null;
		hash = hash.substring(3);


		hashparams = hash.split('/');
		if (hashparams.length < 2) return null;

		return hashparams;
	}


	
	DJ.load();	

	$.getJSON('/api/user', function(data) {

		$(document).ready(function() {




			/* Foodle frontpage */
			if (route('/', true)) {






					if (data.authenticated) {

						$('.showIfAuthenticated').show();
						$('.hideIfAuthenticated').hide();

						var api = new API(data.token);
						var al = new ActivityListController(api, $("#activities"));
						var il = new UpcomingListController(api, $("#upcoming"));

					} 


			/* Foodle repsonse page */
			} else if (route('/foodle/', false)) {



				if (data.user) {

					var api = new API(data.token);
					
					var frc = $("#foodleResponse");
					var identifier = frc.data('foodleid');

					api.getFoodle(identifier, function(foodle) {
						// console.log("Reveiced foodle object with getfoodle: ", foodle);
						foodle.type();
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

			/* Foodle repsonse page */
			} else if (route('/widget', true)) {

				var hash = window.location.hash;
				if (hash.length < 3) throw "Parameters not provided";
				hash = hash.substring(3);


				hashparams = hash.split('/');
				if (hashparams.length < 2) throw "Parameters not provided";
				if (hashparams[0] !== 'feed') throw "Only [feed] widgets supported, yet.";

				var style = 'slim';
				if (hashparams.length > 2) {
					style = hashparams[2];
				}

				// console.log("Hash", hash, hashparams);

				var api = new API();
				// var al = new ActivityListController(api, $("#activities"));
				var il = new UpcomingFeedController(api, $("#widget"), hashparams[1], style);


			} else if (route('/create', true) || route('/edit/', false)) {


				var params = getPath();

				if (params && params.length >= 2 && params[0] === 'create' && data.authenticated) {

					var api = new API(data.token);
					var templateFoodle = params[1];

					console.log("About to create a new Foodle from a template [" + templateFoodle + "]");


					api.getFoodleAuth(templateFoodle, function(foodle) {
						// foodle.setUser(userid);
						
						delete foodle.identifier;
						console.log("Template is ", foodle);

						var cc = new EditFoodleController(api, $("#editfoodle"), data.user, foodle);
					});



				} else if (data.authenticated) {

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


				}


			} else {

				console.log("› No routes matches")

			}


		});
	});






	// console.error("Foo 2");




});

