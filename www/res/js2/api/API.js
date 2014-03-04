define(function(require, exports, module) {


	var 
		$ = require('jquery'),
		Class = require('lib/class'),
		Activity = require('models/Activity'),
		Event = require('models/Event'),
		Foodle = require('models/Foodle'),
		FoodleResponse = require('models/FoodleResponse'),
		FoodleDiscussion = require('models/FoodleDiscussion')
	;


	/**
	 * Generic Foodle API, that may be used to perform all ajax calls towards Foodle
	 * Handles the usertoken that is required, error handling and custom callabacks.
	 */
	var API = Class.extend({

		"init": function(token) {
			this.token = token;
		},

		"getResponseListProcessor": function (constructor, callback) {
			return function(data) {
				
				var 
					i, 
					processed = [];

				if (constructor && typeof constructor === 'function') {
					for(var i = 0; i < data.length; i++) {
						processed.push(new constructor(data[i]));
					}
					if (callback && typeof callback === 'function') callback(processed);
				} else {
					if (callback && typeof callback === 'function') callback(data);
				}

			}

		},

		"getResponseIndexedListProcessor": function (constructor, callback) {
			return function(data) {
				
				var 
					i, 
					processed = {};

				if (constructor && typeof constructor === 'function') {
					for(i in data) {
						processed[i] = new constructor(data[i]);
					}
					if (callback && typeof callback === 'function') callback(processed);
				} else {
					if (callback && typeof callback === 'function') callback(data);
				}

			}

		},

		"getResponseItemProcessor": function (constructor, callback) {
			return function(data) {
				
				var processed;

				if (constructor && typeof constructor === 'function') {
					processed = new constructor(data);
					if (callback && typeof callback === 'function') callback(processed);
				} else {
					if (callback && typeof callback === 'function') callback(data);
				}

			}
			
		},

		"getData": function (endpoint, params, list, constructor, callback) {
		
			if(!this.token) throw new Error('UserToken was not set before API call was initiated at endpoint [' + endpoint + ']');
		
			var key;
			var parameters = {userToken: this.token};
			
			for(key in params) {
				if (params.hasOwnProperty(key)) {
					parameters[key] = params[key];
				}
			}
			
			if (list === 'array') {
				$.getJSON(endpoint, parameters, this.getResponseListProcessor(constructor, callback));
			} else if (list === 'object') {
				$.getJSON(endpoint, parameters, this.getResponseIndexedListProcessor(constructor, callback));
			} else if (list === 'item') {
				$.getJSON(endpoint, parameters, this.getResponseItemProcessor(constructor, callback));
			}
			

		},

		"postData": function(url, object, callback) {

			var data = JSON.stringify(object);
			// data._userToken = this.token;

			url += '?userToken=' + this.token;

			console.log("About to post to this url: " + url);

			$.ajax({
				type: "POST",
				url: url,
				data: data,
				processData : false,
				contentType: "application/json; charset=utf-8",
				dataType: "json",
				success: function(data) {
					console.log("Successfully posted data. Received back ", data);
					callback(data);
				},
				failure: function(errMsg) {
					alert(errMsg);
				}
			});

		},

		"custom": function(url, method, callback) {

			url += '?userToken=' + this.token;

			console.log("About to post to this url: " + url);

			$.ajax({
				type: method,
				url: url,
				contentType: "application/json; charset=utf-8",
				dataType: "json",
				success: function() {
					// console.log("Successfully posted data. Received back ", data);
					callback();
				},
				failure: function(errMsg) {
					alert(errMsg);
				}
			});

		},

		"updateFoodle": function(foodle, callback) {

			if (!foodle instanceof Foodle) throw new {"message": "invalid Foodle"};
			this.postData('/api/foodle/' + foodle.identifier, foodle.getView(), callback);

		},
		"createNewFoodle": function(foodle, callback) {

			if (!foodle instanceof Foodle) throw new {"message": "invalid Foodle"};
			this.postData('/api/foodle', foodle.getView(), callback);

		},

		"addComment": function(foodle, comment, callback) {
			if (!foodle instanceof Foodle) throw new {"message": "invalid Foodle"};
			this.postData('/api/foodle/' + foodle.identifier + '/discussion', comment, callback);		
		},

		"postResponse": function(response, callback) {
			if (!response instanceof FoodleResponse) throw new {"message": "invalid FoodleResponse"};
			var foodle = response.getFoodle();
			this.postData('/api/foodle/' + foodle.identifier + '/myresponse', response.getView(), callback);
		},


		"deleteFoodle": function(id, callback) {
			this.custom('/api/foodle/' + id, 'DELETE', callback);
		},

		"getFoodle": function(id, callback) {
			this.getData('/api/foodle/' + id, null, 'item', Foodle, callback);
		},

		"getFoodleResponses": function(id, callback) {
			this.getData('/api/foodle/' + id + '/responders', null, 'object', FoodleResponse, callback);
		},

		"getFoodleDiscussion": function(id, callback) {
			this.getData('/api/foodle/' + id + '/discussion', null, 'array', FoodleDiscussion, callback);
		},

		"setTimezone": function(tz, callback) {
			this.postData('/api/user/timezone', tz, callback);		
		},


		"getActivities": function(callback) {
			this.getData('/api/activity', null, 'array', Activity, callback);
		},

		"getEvents": function(limit, callback) {
			this.getData('/api/events', {"limit": limit || 0}, 'array', Event, callback);	
		}

	});






	exports.API = API;

});