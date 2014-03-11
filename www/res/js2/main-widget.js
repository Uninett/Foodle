define(function(require, exports, module) {

	var 
		
		$ = jQuery = require('jquery'),

		API = require('api/API').API,
		UpcomingFeedController = require('controllers/UpcomingFeedController')
		;

	require('bootstrap/bootstrap');


	$(document).ready(function() {

		console.log("Initialized feed");


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

		console.log("Hash", hash, hashparams);

		var api = new API();
		// var al = new ActivityListController(api, $("#activities"));
		var il = new UpcomingFeedController(api, $("#widget"), hashparams[1], style);



	});



});

