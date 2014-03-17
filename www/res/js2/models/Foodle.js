define(function(require, exports) {

	var
		$ = jQuery = require('jquery'),
		moment = require('moment-timezone'),

		Model = require('./Model');

	var Foodle = Model.extend({

		"type": function() {
			// console.log("I am a Foodle model");
		},
		"isOwner": function(user) {
			// console.log("Compare me ", this._.userid, " with ", this.owner);
			return (this._.userid === this.owner);
			// console.log("Owner of this foodle is " + this.owner);
		},
		"show": function() {
			// console.log("SHOW");
		},
		"setUser": function(userid) {
			this._.userid = userid;
		},
		"getUser": function() {
			if (this._.userid) return this._.userid;
			return null;
		},
		"hasMyResponse": function() {
			if (!this._.userid) return false;
			if (!this._.responses) return false;
			return (this._.responses.hasOwnProperty(this._.userid));
		},

		"getMyResponse": function() {
			
			// console.log("Foodle › attempting to load my response");
			// console.log("Userid", this._.userid);
			// console.log("Responses", this._.responses);

			if (!this._.userid) return null;
			if (!this._.responses) return null;

			var userid = this._.userid;
			if (!this._.responses[userid]) return null;
			return this._.responses[userid];

		},

		"hasResponseWithComment": function() {
			for(var userid in this._.responses) {
				if (this._.responses[userid].hasComment()) return true;
			}
			return false;
		},

		"locked": function() {

			if (!this.expire) return false;

			var now = moment();
			var deadline = moment.unix(parseInt(this.expire, 10));
			return (now > deadline);
		},

		"lockedRestriction": function() {
			if (!this.restrictions) return false;
			var x;
			if (this.restrictions.rows) {
				x = this.getRowCount(this.restrictions.rows);
				if (x.locked) return true;
			}
			if (this.restrictions.col) {
				x = this.getColCount(this.restrictions.col.col, this.restrictions.col.limit);
				if (x.locked) return true;
			}
			return false;

		},

		/**
		 * Get number of columns by column definition
		 * @return {[type]} [description]
		 */
		"getColNo": function() {
			var counter = 0;

			if (this.columntype && this.columntype === 'dates' && this.columns.hasOwnProperty('dates') && this.columns.hasOwnProperty('timeslots')) {
				return this.columns.dates.length * this.columns.timeslots.length;
			}
			if (this.columntype && this.columntype === 'dates2' && this.columns.hasOwnProperty('dates') && this.columns.hasOwnProperty('timeslots')) {
				for(var date in this.columns.timeslots) {
					counter += this.columns.timeslots[date].length;
				}
				return counter;

			}

			for(var i = 0; i < this.columns.length; i++) {
				if (this.columns[i].hasOwnProperty('children')) {
					counter += this.columns[i].children.length;
				} else {
					counter++;
				}
			}
			return counter;

		},

		"getColCount": function(col, limit) {
			var obj = {
				"locked": false,
				"count": 0,
				"left": limit
			};
			if (!this._.responses) return obj;
			obj.count = this.colCount(col);
			obj.locked = (obj.count >= limit);
			if (this.hasMyResponse()) {
				var myresponse = this.getMyResponse();
				if (myresponse.getResponse(col) === 1) obj.locked = false;
			}
			obj.left = limit - obj.count;
			return obj;

		},
		"colCount": function(i) {
			if (!this._.responses) return 0;
			var c = 0;
			for(var key in this._.responses) {
				if (this._.responses[key].getResponse(i) === 1) c++;
			}
			return c;
		},
		"rowCount": function() {
			if (!this._.responses) return 0;
			var c = 0;
			for(var key in this._.responses) {
				if (this._.responses.hasOwnProperty(key)) c++;

			}
			return c;
		},
		"getRowCount": function(limit) {
			var obj = {
				"locked": false,
				"count": 0,
				"left": limit
			};
			if (!this._.responses) return obj;
			obj.count = this.rowCount();
			obj.locked = (obj.count >= limit);
			if (this.hasMyResponse()) obj.locked = false;
			obj.left = limit - obj.count;
			return obj;
		},

		"setResponses": function(responses) {
			this._.responses = responses;
		},
		"getResponses": function() {
			if (this._.responses) return this._.responses;
			return null;
		}
	})

	return Foodle;

});