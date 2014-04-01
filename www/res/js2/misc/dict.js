define(function(require) {

	var
		// dict = require('../dictionaries/foodle.en'),
		$ = require('jquery')
		;

	var d = {};

	d.load = function(callback) {
		// console.log("load()O");
		var x = {};

		$.getJSON('/api/dict', function(data) {
			// console.log("Got data ", data);
			for(var i in data) {
				x[i] = data[i];
			}
			d.dict = x;
			// console.log("callback()");
			callback();
		});

	};

	d.get = function() {
		return d.dict;
	}




	return d;

});