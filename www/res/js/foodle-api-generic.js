/**
 * Generic Foodle API, that may be used to perform all ajax calls towards Foodle
 * Handles the usertoken that is required, error handling and custom callabacks.
 */
var Foodle_API = function() {
	
	var 
		userToken;
	
	/*
	 * The Foodle_API library needs to be initialized with the user token 
	 * before it can be used
	 */
	function init(setUserToken) {
		userToken = setUserToken;
	}
	
	/*
	 * The processResponse function returns a closure with a specific callback() function
	 */
	function processResponse(constructor, callback) {
		return function(data) {
			
			var 
				i, 
				processed = [];
			
			if (data.status == 'ok' && data.data) {
			
				//console.log('got data');
				//console.log(data.data);
			
				if (constructor && typeof constructor === 'function') {
					for(i = 0; i < data.data.length; i++) {
						processed.push(new constructor(data.data[i]));
					}
					if (callback && typeof callback === 'function') callback(processed);
				} else {
					if (callback && typeof callback === 'function') callback(data.data);
				}
				
			} else {
				throw new Error('Error response on Foodle API: ' + data.message);
			}
		}
	}

	// Perform a API call.
	function getData(endpoint, params, constructor, callback) {
	
		if(!userToken) throw new Error('UserToken was not set before API call was initiated at endpoint [' + endpoint + ']');
	
		var key;
		var parameters = {userToken: userToken};
		
		for(key in params) {
			if (params.hasOwnProperty(key)) {
				parameters[key] = params[key];
			}
		}
	
		$.getJSON(endpoint, parameters, processResponse(constructor, callback) );

	}
	
	return {
		init: init,
		getData: getData
	};
	
}();