define(function(require, exports) {
	var 
		$ = require('jquery'),
		Class = require('lib/class');

	var Trail = Class.extend({
		"init": function(limit) {

			this.counter = 0;

			this.limit = limit;
			this.current = 0;
			this.history = {};
		},
		"check": function(i) {

			if (!this.history.hasOwnProperty(i)) {
				this.current++;
			}
			this.history[i] = ++this.counter;

			// console.log(" ===> Check", i, this);
		},
		"uncheck": function(i) {

			if (this.history.hasOwnProperty(i)) {
				delete this.history[i];
				this.current--;

			}


			// console.log(" ===> Uncheck", i, this);
		},

		"getOldest": function() {
			var lowest = 999999;
			var item = null;

			for(var key in this.history) {
				if (this.history[key] < lowest) {
					item = key;
					lowest = this.history[key];
				}
			}
			this.uncheck(item);
			return item;
		},


		"wipeOld": function() {
			if (this.current > this.limit) {
				return this.getOldest();
			}
			return null;
		}

	});

	return Trail;

});