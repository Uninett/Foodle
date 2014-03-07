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


			if(!this.token) throw new Error('UserToken was not set before API call was initiated at endpoint [' + endpoint + ']');

			var data = JSON.stringify(object);
			// data._userToken = this.token;

			url += '?userToken=' + this.token;

			// console.log("About to post to this url: " + url);

			$.ajax({
				type: "POST",
				url: url,
				data: data,
				processData : false,
				contentType: "application/json; charset=utf-8",
				dataType: "json",
				success: function(data) {
					// console.log("Successfully posted data. Received back ", data);
					callback(data);
				},
				failure: function(errMsg) {
					alert(errMsg);
				}
			});

		},

		"custom": function(url, method, callback) {

			if(!this.token) throw new Error('UserToken was not set before API call was initiated at endpoint [' + endpoint + ']');

			url += '?userToken=' + this.token;

			// console.log("About to post to this url: " + url);

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
			this.http('/api/foodle/' + foodle.identifier + '/myresponse', {
				"data": response.getView()
			}, callback);
		},


		"deleteFoodle": function(id, callback) {
			this.custom('/api/foodle/' + id, 'DELETE', callback);
		},

		"getFoodle": function(id, callback) {
			this.http('/api/f/' + id, {
				"constructor": Foodle,
				"auth": false
			}, callback);
		},

		"getFoodleResponses": function(id, callback) {
			this.http('/api/f/' + id + '/responders', {
				"constructor": FoodleResponse,
				"wrapper": "object",
				"auth": false
			}, callback);
			// this.getData('/api/foodle/' + id + '/responders', null, 'object', FoodleResponse, callback);
		},

		"getFoodleDiscussion": function(id, callback) {
			this.http('/api/f/' + id + '/discussion', {
				"constructor": FoodleDiscussion,
				"wrapper": "list",
				"auth": false
			}, callback);
			// this.getData('/api/foodle/' + id + '/discussion', null, 'array', FoodleDiscussion, callback);
		},

		"setTimezone": function(tz, callback) {
			this.postData('/api/user/timezone', tz, callback);		
		},


		"getActivities": function(callback) {
			this.getData('/api/activity', null, 'array', Activity, callback);
		},

		"getEvents": function(limit, callback) {
			this.getData('/api/events', {"limit": limit || 0}, 'array', Event, callback);	
		},




		"postData": function(url, object, callback) {
			console.error('Using deprecated api.postData() function to call ' + url);
			return this.http(url, {data: object}, callback);
		},


		"http": function(url, opts, callback) {


			var defaultSuccess = function(data) {
				callback(data);
			}
			var defaultFailure = function(errMsg) {
				alert(errMsg);
			}

			var ajaxConfig = {
				contentType: "application/json; charset=utf-8",
				dataType: "json",
				success: defaultSuccess,
				failure: defaultFailure
			}

			var defaultOptions = {
				"method": "get",
				"data": null,
				"auth": true,
				"constructor": null,
				"wrapper": "item"
			}

			var options = $.extend({}, defaultOptions, opts);


			ajaxConfig.url = url;
			ajaxConfig.method = options.method;


			if (options.auth) {
				if(!this.token) throw new Error('UserToken was not set before API call was initiated at endpoint [' + url + ']');
				url += '?userToken=' + this.token;
			}


			if (options.data !== null) {
				ajaxConfig.processData = false;
				ajaxConfig.data = JSON.stringify(options.data);
				ajaxConfig.method = "post";
			}

			if (options.constructor !== null) {

				if (typeof options.constructor !== 'function') throw "options.constructor is not a function";
				var wf = null;

				if (options.wrapper === 'item') {
					wf = function(data) {
						var processed = new options.constructor(data);
						if (callback && typeof callback === 'function') callback(processed);				
					};
				} else if (options.wrapper === 'list') {

					wf = function(data) {
						var processed = [];
						for(var i = 0; i < data.length; i++) {
							processed.push(new options.constructor(data[i]));
						}
						if (callback && typeof callback === 'function') callback(processed);						
					};

				} else if (options.wrapper === 'object') {
					wf = function(data) {
						var processed = {};
						for(i in data) {
							processed[i] = new options.constructor(data[i]);
						}
						if (callback && typeof callback === 'function') callback(processed);					
					};
				}

				ajaxConfig.success = wf;
			}

			
			// console.log('About to perform an http ' + options.method + ' to this url: ' + url);
			$.ajax(ajaxConfig);

		},



		"createAnonymousSession": function(name, email, callback) {
			this.http('/api/user/register', {
				"auth": false,
				"data": {
					"name": name,
					"email": email
				}
			}, callback);
		}

	});




	exports.API = API;

});