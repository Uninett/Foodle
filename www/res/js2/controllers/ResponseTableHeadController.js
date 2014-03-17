define(function(require, exports) {



	var 
		$ = require('jquery'),
		Class = require('lib/class'),
		moment = require('moment-timezone'),

		Foodle = require('models/Foodle')
		;

	require('moment-timezone-data');

	/**
	 * Draws the thead of the response section for a specific Foodle.
	 */
	var ResponseTableHeadController = Class.extend({

		/**
		 * Constructor
		 * @param  {Jquery element} el    	Representing the already existing <thead> element
		 * @param  {[type]} foodle 			The Foodle to draw
		 * @param  {[type]} extra  			Additional colspan to allow more columns under the participant header...
		 * 
		 */
		"init": function(el, foodle, extra, timezone) {
			var that = this;

			this.el = el;
			this.foodle = foodle;
			this.extra = extra || 1;
			this.draw(timezone);

			this.timezone = timezone;
		},

		/**
		 * Update the headers with timezlots in a new timezone.
		 * 
		 * @param  {[type]} tz [description]
		 * @return {[type]}    [description]
		 */
		"setTimezone": function(tz) {
			if (tz === this.timezone) return;
			this.timezone = tz;
			this.draw(tz);
		},


		// "drawDates": function() {
		// 	var coldef = this.foodle.columns;

		// 	var firstrow = $('<tr></tr>');
		// 	var secondrow = $('<tr></tr>');


		// 	var dateno = coldef.dates.length;
		// 	var slotno = coldef.timeslots.length;

		// 	firstrow.append('<th rowspan="2" colspan="' + this.extra + '" style="width: 210px">Participant</th>');

		// 	var x1, x2, xs, xsfrom, xsto;

		// 	for(var i = 0; i < coldef.dates.length; i++) {

		// 		xs = moment(coldef.dates[i], 'YYYY-MM-DD');

		// 		firstrow.append('<th colspan="' + slotno + '">' + xs.format('DD. MMM, YYYY') + '</th>');

		// 		for (var j = 0; j < coldef.timeslots.length; j++) {
		// 			xsfrom = moment(coldef.dates[i] + ' ' + coldef.timeslots[j][0]);
		// 			xsto   = moment(coldef.dates[i] + ' ' + coldef.timeslots[j][1]);
		// 			x1 = moment(xsfrom);
		// 			x2 = moment(xsto);

		// 			secondrow.append('<th>' + x1.format('HH:mm') + '-' + x2.format('HH:mm') + '</th>');
		// 		};

		// 	}


		// 	// for(var i = 0; i < coldef.length; i++) {

		// 	// 	var colspan = 1;
		// 	// 	var rowspan = 1;

		// 	// 	if (coldef[i].hasOwnProperty('children')) {
		// 	// 		for(var j = 0; j < coldef[i].children.length; j++) {
		// 	// 			secondrow.append('<th>' + coldef[i].children[j].title + '</th>');
		// 	// 		}
		// 	// 		colspan = coldef[i].children.length;
		// 	// 	} else {
		// 	// 		rowspan = 2;
		// 	// 	}

		// 	// 	firstrow.append('<th colspan="' + colspan + '" rowspan="' + rowspan + '">' + coldef[i].title + '</th>');

		// 	// }
		// 	firstrow.append('<th rowspan="2">Updated</th>');

		// 	this.el.append(firstrow).append(secondrow);
		// },


		"convertDateColumns": function(input) {

			var coldef = [];
			var structuredDates = {};
			var date;

			for (var i = 0; i < input.length; i++) {
				date = input[i][0].format('YYYY-MM-DD');

				if (!structuredDates[date]) structuredDates[date] = [];
				structuredDates[date].push(input[i]);
			};

			var item, header;
			for(date in structuredDates) {
				header = {
					"title": structuredDates[date][0][0].format('DD. MMM, YYYY'),
					"children": []
				};
				for (var i = 0; i < structuredDates[date].length; i++) {
					item = {
						"title": structuredDates[date][i][0].format('HH:mm') + ' - ' + structuredDates[date][i][1].format('HH:mm')
					};
					header.children.push(item);

				};
				coldef.push(header);
			};

			return coldef;
		},

		"transformDateColumns": function(coldef, toTimezone) {

			var dateno = coldef.dates.length;
			var slotno = coldef.timeslots.length;

			var doTimezone = false;
			if (this.foodle.timezone && toTimezone) doTimezone = true;

			var x1, x2, xsfrom, xsto;

			var dateColumns = [];

			// console.error("about to interpret these dates", coldef);

			for(var i = 0; i < coldef.dates.length; i++) {

				for (var j = 0; j < coldef.timeslots.length; j++) {

					var timeslotFrom = coldef.timeslots[j][0].replace('.', ':');
					var timeslotTo   = coldef.timeslots[j][1].replace('.', ':');

					if (doTimezone) {
						// console.log("  › TIMEZONE › Perform translation from ", this.foodle.timezone, " to ", toTimezone);
						xsfrom = moment.tz(coldef.dates[i] + ' ' + timeslotFrom, this.foodle.timezone).tz(toTimezone);
						xsto   = moment.tz(coldef.dates[i] + ' ' + timeslotTo,   this.foodle.timezone).tz(toTimezone);

						// console.log("Convert " + coldef.dates[i] + ' ' + coldef.timeslots[j][0] + ' to ' + xsfrom.format('YYYY-MM-DD HH:mm'))
					} else {
						// console.log("  › TIMEZONE › DO NOT USE TIMEZONE");
						xsfrom = moment(coldef.dates[i] + ' ' + timeslotFrom);
						xsto   = moment(coldef.dates[i] + ' ' + timeslotTo);
					}
					dateColumns.push([xsfrom, xsto]);
				};

			}


			return this.convertDateColumns(dateColumns);

		},


		"transformDateTimeslotColumns": function(coldef, toTimezone) {

			// var dateno = coldef.dates.length;
			// var slotno = coldef.timeslots.length;

			var doTimezone = false;
			if (this.foodle.timezone && toTimezone) doTimezone = true;

			// console.error("doTimezone", this.foodle.timezone, doTimezone);

			var xsfrom, xsto;
			var dateColumns = [];

			// console.log("about to interpret these dates", coldef);

			for(var date in coldef.timeslots) {

				for(var i = 0; i < coldef.timeslots[date].length; i++) {

					// console.log(' › About to process list ', i, date, coldef.timeslots[date][i]);
					// console.log(coldef.timeslots[date][i]);
					// console.log(coldef.timeslots[date], i, coldef.timeslots[date][i]);

					var timeslotFrom = coldef.timeslots[date][i][0];
					var timeslotTo   = coldef.timeslots[date][i][1];

					var tsFrom = date + ' ' + timeslotFrom;
					var tsTo = date + ' ' + timeslotTo;

					if (doTimezone) {
						// console.log("  › TIMEZONE › Perform translation from ", this.foodle.timezone, " to ", toTimezone);
						xsfrom = moment.tz(tsFrom, this.foodle.timezone).tz(toTimezone);
						xsto   = moment.tz(tsTo,   this.foodle.timezone).tz(toTimezone);

						// console.log("Convert " + coldef.dates[i] + ' ' + coldef.timeslots[j][0] + ' to ' + xsfrom.format('YYYY-MM-DD HH:mm'))
					} else {
						// console.log("  › TIMEZONE › DO NOT USE TIMEZONE");
						xsfrom = moment(date + ' ' + timeslotFrom);
						xsto   = moment(date + ' ' + timeslotTo);
					}
					dateColumns.push([xsfrom, xsto]);
				}

			}
			// console.log("----- › Done");
			return this.convertDateColumns(dateColumns);

		},




		"interpretOldDateColumn": function(col, toTimezone) {
			var dateColumns = [];

			// console.error('interpretOldDateColumn', col, toTimezone, this.foodle);

			var doTimezone = false;
			if (this.foodle.timezone && toTimezone) doTimezone = true;

			// console.log("Interpreting old date column", col);

			for (var i = 0; i < col.length; i++) {
				var header = col[i].title;
				for(var j = 0; j < col[i].children.length; j++) {

					var item = col[i].children[j].title.replace(/\./g, ':');
					var itema = item.split('-');

					var strto = null;

					// Fix for timezlots starting with only one digit.  9:30 translated to 09:30
					if (itema[0].match(/^[0-9]:[0-9][0-9]/)) {
						itema[0] = '0' + itema[0];
					}
					if (itema[1]) {
						if (itema[1].match(/^[0-9]:[0-9][0-9]/)) {
							itema[1] = '0' + itema[1];
						}
					}

					var strfrom = header + ' ' + itema[0];
					if (itema.length > 1) {
						strto = header + ' ' + itema[1];
					}

					// console.error("Interpreting ..." + col[i].children[j].title + ' to ' + item,  strfrom, strto);

					if (doTimezone) {
						// console.log("  › TIMEZONE › Perform translation from ", this.foodle.timezone, " to ", toTimezone);

						xsfrom = moment.tz(strfrom, this.foodle.timezone).tz(toTimezone);
						if (strto !== null) {
							xsto   = moment.tz(strto, this.foodle.timezone).tz(toTimezone);	
						} else {
							xsto   = xsfrom.clone().add('hours', 1);
						}
						

						// console.log("Convert " +strfrom + ' to ' + xsfrom.format('YYYY-MM-DD HH:mm'))
					} else {
						// console.log("  › TIMEZONE › DO NOT USE TIMEZONE");
						xsfrom = moment(strfrom);
						if (strto !== null) {
							xsto   = moment(strto);
						} else {
							xsto   = xsfrom.clone().add('hours', 1);
						}
					}
					dateColumns.push([xsfrom, xsto]);
				}
			};
			return this.convertDateColumns(dateColumns);
		},


		"oldFormatAndContainsTimeslots": function() {

			if (this.foodle.columntype && this.foodle.columntype === 'dates' && this.foodle.columns.hasOwnProperty('length')) {

				for (var i = 0; i < this.foodle.columns.length; i++) {
					var header = this.foodle.columns[i].title;

					if (this.foodle.columns[i].children && this.foodle.columns[i].children.length > 0) {

					} else {
						return false;
					}

				}
				return true;
			}
			return false;
		},


		/**
		 * Walks through two levels of headers and injects content into the <thead> element.
		 * @return {[type]}           [description]
		 */
		"draw": function(tz) {

			console.error('DRAW ', this.foodle.columns, tz);

			var coldef;
			if (this.foodle.columntype && this.foodle.columntype === 'dates' && !this.foodle.columns.hasOwnProperty('length')) {
				coldef = this.transformDateColumns(this.foodle.columns, tz);	
			} else if (this.oldFormatAndContainsTimeslots()) {
				coldef = this.interpretOldDateColumn(this.foodle.columns, tz);
			} else if (this.foodle.columntype && this.foodle.columntype === 'dates2') {
				coldef = this.transformDateTimeslotColumns(this.foodle.columns, tz);	
			} else {
				coldef = this.foodle.columns;
			}

			var firstrow = $('<tr></tr>');
			var secondrow = $('<tr></tr>');


			firstrow.append('<th rowspan="2" colspan="' + this.extra + '" style="width: 210px">Participant</th>');

			for(var i = 0; i < coldef.length; i++) {

				var colspan = 1;
				var rowspan = 1;

				if (coldef[i].hasOwnProperty('children')) {
					for(var j = 0; j < coldef[i].children.length; j++) {
						secondrow.append('<th>' + coldef[i].children[j].title + '</th>');
					}
					colspan = coldef[i].children.length;
				} else {
					rowspan = 2;
				}

				firstrow.append('<th colspan="' + colspan + '" rowspan="' + rowspan + '">' + coldef[i].title + '</th>');

			}
			firstrow.append('<th rowspan="2">Updated</th>');

			this.el.empty().append(firstrow).append(secondrow);

		}

	})

	return ResponseTableHeadController;

});