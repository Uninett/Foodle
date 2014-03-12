define(function(require, exports) {

	var 
		Class = require('../lib/class');


	var Model = Class.extend({
		"init": function(props) {
			this._ = {};
			for (var key in props) {
				this[key] = props[key];
			}
		},
		"type": function() {
			console.log("I am a Model");
		},
		"get": function(key) {
			return this[key];
		},
		"getView": function() {
			var obj = {};
			for(var key in this) {
				if (key === '_') continue;
				if (typeof this[key] === 'function') continue;
				obj[key] = this[key];
			}
			return obj;
		}
	});

	return Model;
});