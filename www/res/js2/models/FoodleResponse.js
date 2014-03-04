define(function(require, exports) {

	var
		$ = jQuery = require('jquery'),
		Model = require('./Model');

	var FoodleResponse = Model.extend({
		"getResponse": function(i) {
			if (!this.response) return null;
			if (!this.response.data) return null;
			if (!this.response.data.hasOwnProperty(i)) return null;
			return parseInt(this.response.data[i], 10);
		},
		"setData": function(data) {
			this.response = {
				'type': 'manual',
				'data': data
			};
		},
		"setFoodle": function(foodle) {
			this._.foodle = foodle;
		},
		"getFoodle": function() {
			if (this._.foodle) return this._.foodle;
			return null;
		},
		"hasComment": function() {
			// console.log("HASCOMMENT ", this);
			// if (this.response.hasOwnProperty('notes')) console.error('HAS COMMENT', this);
			return (this.hasOwnProperty('notes'));
		}
	})

	return FoodleResponse;

});