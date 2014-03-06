define(function(require, exports) {

	var
		$ = jQuery = require('jquery'),
		Model = require('./Model');

	var Activity = Model.extend({
		"show": function() {
			// console.log("SHOW");
		}
	})

	return Activity;

});