define(function(require, exports) {

	var
		$ = jQuery = require('jquery'),
		Model = require('./Model');

	var FoodleDiscussion = Model.extend({
		
		"setFoodle": function(foodle) {
			this._.foodle = foodle;
		},

		"getFoodle": function() {
			if (this._.foodle) return this._.foodle;
			return null;
		}

	});

	return FoodleDiscussion;

});