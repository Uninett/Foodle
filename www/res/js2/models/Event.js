define(function(require, exports) {

	var
		$ = jQuery = require('jquery'),
		Model = require('./Model'),
		moment = require('moment-timezone')
		;

	var Event = Model.extend({
		"init": function(props) {
			this._super(props);
			this.typeI = {};
			if  (this.type === 'tentative') {
				this.typeI.tentative = 1;
			} else if (this.type === 'expire') {
				this.typeI.expire = 1;
			} else {
				this.typeI.standard = 1;
			}
		},

		"getFromNow": function() {
			var u = moment.unix(this.unix);
			return u.fromNow();
		}

	})

	return Event;

});