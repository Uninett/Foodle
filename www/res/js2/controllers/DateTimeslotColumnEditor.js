define(function(require, exports) {



	var 
		$ = require('jquery'),
		Class = require('lib/class'),
		hb = require('lib/handlebars'),

		moment = require('moment-timezone'),

		Foodle = require('models/Foodle')
		;

	require('moment-timezone-data');
	require('lib/bootstrap3-typeahead');

	var t = require('lib/text!templates/datetimeslotcolumneditor.html');
	var template = hb.compile(t);

	// var tslot = require('lib/text!templates/datecolumn-timeslot.html');
	// var templateTimeslot = hb.compile(tslot);



	var showOnlyFuture = function(date) {
		var todaysDate = new Date();
		todaysDate.setHours(0, 0, 0, 0);
		// console.log("CHECK DATE OLD", date, todaysDate);
		if (date < todaysDate) {
			return false;
		}
		return true;
	};


	var DateTimeslotColumnEditor = Class.extend({
		"init": function(el, user) {
			var that = this;

			this.dates = [];
			this.timeslots = {};

			this.callbacks = {};
			// this.dates = [];

			this.user = user;
			this.el = el;
			this.el.empty().append(template({"_": _d}));


			// $('body').on('click', '#xx', function(e) {
			// 	e.preventDefault(); e.stopPropagation();
			// 	console.log("Get Coldef");
			// 	var x = that.getColDef();
			// 	console.log(x);

			// });


			// this.addTable();

			// this.el.on('click', '.actRemoveTimeslot', function(e) {
			// 	e.preventDefault(); e.stopPropagation();

			// 	var el = $(e.currentTarget).closest('div.row');
			// 	// console.log("Remove timeslot");
			// 	that.removeTimeslot(el);

			// });
			// this.el.on('click', '.actAddTimeslot', function(e) {
			// 	e.preventDefault(); e.stopPropagation();

			// 	var p = {};
			// 	// console.log("Add timeslot");
			// 	that.addTimeslot();
			// });

			this.el.on('blur', 'input.timeslot', function(e) {
				e.preventDefault(); e.stopPropagation();

				// console.log("Blur input element");

				that.fillTimeslotsIfNeeded($(e.currentTarget).closest('div.timeslotContainer').eq(0));

				// var p = {};
				// console.log("Add timeslot");
				// that.addTimeslot();
			});


			this.redraw();
			
			// this.addTimeslot('09:00', '11:00');
			// this.addTimeslot('13:00', '15:00');

			window.g = $.proxy(this.getColDef, this);

		},

		"on": function(evnt, callback) {
			this.callbacks[evnt] = callback;
		},

		"trigger": function(evnt) {
			var args = Array.prototype.slice.call(arguments, 1);
			if (this.callbacks && this.callbacks[evnt] && typeof this.callbacks[evnt] === 'function') {
				this.callbacks[evnt].apply(this, args);
			}
		},

		"timezoneOK": function(tz) {
			for(var i = 0; i < window.moment_zones.length; i++) {
				if (tz === window.moment_zones[i]) return true;
			}
			return false;
		},



		"validate": function() {

			var x = this.getColDef();
			this.el.find('.colerrors').empty();

			var hasError = false;

			if (x.dates.length === 0) {
				// console.error('No dates');
				this.el.find('.colerrors').append('<div class="alert alert-danger"><strong>No dates selected</strong>. Please select at least one date.</div>');
				hasError = true;
			}
			if (x.timeslots.length === 0) {
				// console.error('No timeslots');
				this.el.find('.colerrors').append('<div class="alert alert-danger"><strong>No timeslots selected</strong>. Please add at least one timeslot.</div>');
				hasError = true;
			}

			return !hasError;

		},



		// "addTimeslot": function(from, to) {

		// 	var p = {
		// 		from: from || '',
		// 		to: to || ''
		// 	}
		// 	var c = this.el.find('#timeslotrowcontainer');
		// 	c.append(templateTimeslot(p));

		// },

		// "removeTimeslot": function(el) {
		// 	el.remove();
		// },



		"drawDates": function() {

			var cc = this.el.find('#timeslotTableBody');
			var d;
			cc.empty();
			// var c = $('<tr> </tr>').appendTo(cc);

			this.dates.sort(function(a,b) {
				if (a > b) return 1;
				if (b > a) return -1;
				return 0;
			});

			for(var i = 0; i < this.dates.length; i++) {
				d = moment(this.dates[i]);
				var tba = $('<tr data-date="' + d.format('YYYY-MM-DD') + '">' + 
					'<td class="col-md-3">' + d.format('MMM Do, YYYY (ddd)') + '</td>' + 
					'<td class="datetimeslotCell col-md-9"><div class="row timeslotContainer">' + this.prepareDrawTimeslots(this.dates[i]) + '</div></td></tr>');
				tba.appendTo(cc);
				this.fillTimeslotsIfNeeded(tba.find('.timeslotContainer'));
			}


		},

		"prepareSingleTimeslot": function(f, t) {
			var def = '<div class="input-group timeslotGroup">' +
				'<span class="input-group-addon">From</span><input type="text"  placeholder="11:00" value="' + f + '" class="timeslot timeslotFrom form-control">' +
				'<span class="input-group-addon">to</span><input type="text"  placeholder="12:00" value="' + t + '" class="timeslot timeslotTo form-control">' +
			'</div>';
			return '<div class="col-xs-4">' + def + '</div>';
		},

		"prepareDrawTimeslots": function(date) {

			var ds = moment(date).format('YYYY-MM-DD');
			var t = [['', ''], ['', '']];
			if (this.timeslots[ds]) {
				t = this.timeslots[ds];
			}

			var html = '';
			for(var i = 0; i < t.length; i++) {
				html += this.prepareSingleTimeslot(t[i][0], t[i][1]);
			}

			// var def = '<div class="input-group"><input type="time" placeholder="10:00" /><span class="input-group-addon">to</span><input type="time" placeholder="10:00" /></div>';

			if (!this.timeslots[date]) return html;

		},


		/**
		 * Check that there always is empty timeslot groups to 
		 */
		"fillTimeslotsIfNeeded": function(el) {
			// console.log("Fill timelsotfontainer ", el);

			var tr = /[0-2][0-9]:[0-9][0-9]/;
			var counter = 0;
			el.find('.timeslotGroup').each(function(i, item) {
				var from = $(item).find('input.timeslotFrom').val();
				var to   = $(item).find('input.timeslotTo').val();
				
				// console.log("Checking ", from, to);

				if (!from.match(tr) || !to.match(tr)) {
					counter++;
				}
			});

			// console.log("Counter of available slots i s", counter);

			if (counter < 1) {
				el.append(this.prepareSingleTimeslot('', ''));	
			}
			
			
		},


		"getColNo": function(top, sub) {
			var count = 0;
			if (top > 0) {
				for(var i = 0; i < top; i++) {
					count += this.subcolumns[i];
				}				
			}
			count += sub;
			return count;
		},

		"setColDef": function(coldef) {

			// console.error("set col def to ", coldef);

			this.topcolumns = coldef.length;
			this.subcolumns = [];
			for(var i = 0; i < coldef.length; i++) {

				if (coldef[i].hasOwnProperty('children')) {
					this.subcolumns[i] = coldef[i].children.length;
				} else {
					this.subcolumns[i] = 0;
				}

			}

			this.redraw(coldef);

		},

		"timeIsValid": function(t) {
			var pattern = new RegExp('^([0-2])?[0-9]:[0-5][0-9]$');
			var tested = pattern.test(t);
			// console.log("Testing ", t, tested);
			return tested;
		},


		"getColDef": function() {

			var that = this;

			var dates = [];
			var timeslots = {};

			for(var i = 0; i < this.dates.length; i++) {
				dates.push(this.dates[i].format('YYYY-MM-DD'));
			}

			// console.log("timeslottable ", this.el.find('#timeslotTableBody tr'));

			this.el.find('#timeslotTableBody tr').each(function(i, row) {

				var date = $(row).data('date');
				// console.log("Found date []", date);

				$(row).find('div.timeslotGroup').each(function(j, slot) {

					var start = $(slot).find('.timeslotFrom').val();
					var end = $(slot).find('.timeslotTo').val();

					if (that.timeIsValid(start) && that.timeIsValid(end)) {
						if (!timeslots[date]) {
							timeslots[date] = [];
						}
						timeslots[date].push([start, end]);
						// console.log("Date [" + date + "] " + start + "  " + end);
					} else {
						// console.error("INVALID Date [" + date + "] " + start + "  " + end);
					}

					

				});


			});

			// this.el.find('.timeslotRow').each(function(i, row) {
			// 	var start = $(row).find('.inputTimeStart').val();
			// 	var end = $(row).find('.inputTimeEnd').val();

			// 	if (that.timeIsValid(start) && that.timeIsValid(end)) {
			// 		timeslots.push([start, end]);	
			// 	}

				
			// });

			// var timezone = this.el.find('#timezoneselect').val();

			// console.log("Timeslots", timeslots);

			var obj = {
				"dates": dates,
				"timeslots": timeslots
			};

			// if (this.timezoneOK(timezone)) {
			// 	obj.timezone = timezone;
			// } else {
			// 	// console.error("INVALID TIMEZONE", timezone);
			// }

			// console.error("Got this coldef object", obj);

			return obj;

		},

		"hasTwoLevels": function(coldef) {
			return true;
		},

		"redraw": function(setColdef) {
			var that = this;
			var coldef = setColdef;

			// console.error ("Redraw with this COLDEF", setColdef);

			if (!setColdef) {
				coldef = this.getColDef();

			}
			this.timeslots = coldef.timeslots;


			this.el.empty().append(template({"_": _d}));

			var datesDatepickerConfig = {
				"format": "yyyy-mm-dd",
				"todayBtn": true,
				"todayHighlight": true,
				"weekStart": 1,
				"autoclose": false,
				"beforeShowDay": showOnlyFuture,
				"multidate": true
			};


			// console.log("dpc", datesDatepickerConfig);

			this.datepicker = this.el.find('.dateSelector').datepicker(datesDatepickerConfig)
				.on('changeDate', function(data) {
					// console.log("›› ] Change date", data);
					that.dates = [];
					for(var i = 0; i < data.dates.length; i++) {
						that.dates.push(moment(data.dates[i]));
					}
					// that.dates = data.dates;
					that.drawDates();
				}
			);


			var tz = null;
			if (coldef.hasOwnProperty('timezone')) {
				tz = coldef.timezone;
			}

			// this.prepareTimezoneSelector(tz);

			if (!coldef.hasOwnProperty('dates')) {
				alert('This foodle was created using an old version of Foodle. You need to setup the dates again manually. Sorry about that.');
				return;
			}


			if (coldef.dates.length > 0) {
				var sd = [];
				for (var i = 0; i < coldef.dates.length; i++) {
					sd.push(moment(coldef.dates[i], 'YYYY-MM-DD').toDate());
				}
				this.datepicker.datepicker('setDates', sd);
			}



			if (coldef.timeslots.length > 0) {
				for (var i = 0; i < coldef.timeslots.length; i++) {
					this.addTimeslot(coldef.timeslots[i][0], coldef.timeslots[i][1]);
				}
			}


		},








		"getHeaderRow": function() {
			var row = $('<tr></tr>');

			var t;
			for(var i = 0; i < this.topcolumns; i++) {
				var rowspan = 1;
				if (this.subcolumns[i] === 0) {
					rowspan = 2;
				}
				t = '<td rowspan="' + rowspan + '" colspan="' + this.subcolumns[i] + '"><input style="width: 100%" class="coldef-header" type="text" placeholder="Header" /></td>';
				row.append(t);	
			}
			return row;
		},


		"getSuboptionsRow": function() {
			var row = $('<tr></tr>');

			var t;
			for(var i = 0; i < this.topcolumns; i++) {
				for(var j = 0; j < this.subcolumns[i]; j++) {
					t = '<td><input style="width: 100%" class="coldef-option" type="text" placeholder="Opt" /></td>';
					row.append(t);
				}
			}
			return row;
		},
		"getSuboptionsControllers": function() {
			var row = $('<tr></tr>');

			var t;
			for(var i = 0; i < this.topcolumns; i++) {

				t = '<td style="text-align: left" colspan="' + this.subcolumns[i] + '" data-col-l1="' + i + '">' + 
						'<button class="removeSubOpt" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-minus"></span></button>' + 
						'<button class="addSubOpt" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-plus"></span></button>' +
					'</td>';
				var td = $(t);
				row.append(td);

				if (this.subcolumns[i] < 1 ) {
					td.find('.removeSubOpt').attr('disabled', 'disabled');
					// td.find('.removeSubOpt').removeAttr('disabled');
				} else if (this.subcolumns[i] > 4 ) {
					// td.find('.addSubOpt').removeAttr('disabled');
					td.find('.addSubOpt').attr('disabled', 'disabled');
				}
			}
			
			return row;
		}


	})

	return DateTimeslotColumnEditor;

});