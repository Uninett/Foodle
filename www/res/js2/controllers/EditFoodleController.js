define(function(require, exports) {



	var 
		$ = require('jquery'),
		Class = require('lib/class'),
		hb = require('lib/handlebars'),
		pretty = require('lib/pretty'),
		moment = require('moment-timezone'),

		ColumnEditor = require('./ColumnEditor'),

		Foodle = require('models/Foodle')
		;

	require('moment-timezone-data');
	require('lib/bootstrap3-typeahead');

	var showOnlyFuture = function(date) {
		var todaysDate = new Date();
		todaysDate.setHours(0, 0, 0, 0);
		// console.log("CHECK DATE OLD", date, todaysDate);
		if (date < todaysDate) {
			return false;
		}
		return true;
	};

	var stdDatepickerConfig = {
		"format": "yyyy-mm-dd",
		"todayBtn": true,
		"todayHighlight": true,
		"weekStart": 1,
		"autoclose": true,
		"beforeShowDay": showOnlyFuture
	};


	var showOnlyAfterStart = function(date) {

		// Default is today.
		var startDate = moment();
		var startDateStr = $("#inputDateStart").val();
		if (startDateStr !== '') {
			startDate = new moment(startDateStr);
			startDate.add('days', 1);
		}

		var toCheckDate = moment(date);

		// console.log("CHECK DATE", toCheckDate, startDate);

		if (toCheckDate < startDate) {
			return false;
		}
		return true;
	}
	var endDatepickerConfig = $.extend({}, stdDatepickerConfig);
	endDatepickerConfig.beforeShowDay = showOnlyAfterStart;
	endDatepickerConfig.todayHighlight = false;

	console.log("endDatepickerConfig", endDatepickerConfig);


	var t = require('lib/text!templates/editfoodle.html');
	var template = hb.compile(t);

	var EditFoodleController = Class.extend({
		"init": function(api, el, user, foodle) {

			console.log("init EditFoodleController");
			var that = this;

			this.user = user;
			this.foodle = foodle || {};

			console.log("Editing this FOODLE", foodle);

			this.api = api;
			this.el = el;

			this.el.empty().append(template(this.foodle));

			this.setupMap();
			this.prepareTimezoneSelector();

			$('#title').focus();

			$('.ttpd').tooltip({});


			this.columneditor = new ColumnEditor(this.el.find('#columneditor'), this.user);
			this.columneditor.on('changeType', function() {
				that.updateDynamics();
			});

			// this.columneditor.on('update', function() {
			// 	console.log("UPDATE COLUMNS");
			// });

			if (this.foodle.identifier) {
				this.updateUI();
				this.updateColSelector();
			}


			this.updateDynamics();


			var dptimeStart = $('#inputDateStart').datepicker(stdDatepickerConfig);
			var dptimeEnd   = $('#inputDateEnd'  ).datepicker(endDatepickerConfig);

			dptimeStart.on('changeDate', function(e) {
				console.log("Change date event on START", e);
				dptimeEnd.datepicker('update');
				that.updateDynamics();
			});
			dptimeEnd.on('changeDate', function(e) {
				that.updateDynamics();
			});


			var dpdeadline = $('#inputDeadlineDate').datepicker(stdDatepickerConfig);
			dpdeadline.on('changeDate', function(e) {
				console.log("Change date event", e);
				$("#inputDeadlineCheck").prop('checked', true);
				that.updateDynamics();
			});




			$("#inputDeadlineCheck").on('change', function(e) {
				
				var checked = $(e.currentTarget).prop('checked');
				console.log("Change click event", checked);

				if (checked) {
					console.log("datepicker show");
					dpdeadline.datepicker('show');

				} else if (!checked) {
					$('#inputDeadlineDate').val('');
					console.log("set value empty");
				}

			});

			this.el.on('change', '#enableLocation', function(e) {
				that.updateDynamics(e);
			});
			this.el.on('change', '#enableTime', function(e) {
				that.updateDynamics(e);
			});
			this.el.on('change', '#enableDeadline', function(e) {
				that.updateDynamics(e);
			});
			this.el.on('change', '#enableRestrictions', function(e) {
				that.updateDynamics(e);

				that.updateColSelector();
			});
			this.el.on('change', '#inputTimeAllDay', function(e) {
				that.updateDynamics(e);
			});
			this.el.on('change', '#inputTimeMultipleDays', function(e) {
				that.updateDynamics(e);
			});

			this.el.on('change', '#inputTimeStart', function(e) {
				that.updateDynamics(e);
			});
			this.el.on('change', '#inputTimeEnd', function(e) {
				that.updateDynamics(e);
			});


			$("#submitFoodle").on('click', function(e) {
				e.preventDefault(); e.stopPropagation();
				that.prepareSubmit();
			});


			that.marker = null;

			this.el.on('click', '#actLookupMap', function(e) {
				console.log("Input address chaneged.");
				that.el.find('#map-canvas').show();
				that.codeAddress();
			});


		},

		"prepareSubmit": function() {
			var that = this;

			var valid = this.validate();

			console.log("Is Valid", valid);

			if (!valid) return;

			if (that.foodle.hasOwnProperty('identifier')) {
				console.log("Update existing foodle " + that.foodle.identifier);

				that.updateFoodle(that.foodle.identifier);
			} else {
				console.log("Create new foodle");
				that.submitFoodle();	
			}
		},

		"validate": function() {

			// var obj = this.getObject();

			var hasError = false;
			var title = $('#inputTitle').val();

			if (title === '') {
				$('#form-group-title').addClass('has-error');
				hasError = true;
			} else {
				$('#form-group-title').removeClass('has-error');
			}

			if (!this.columneditor.validate()) hasError = true;


			return !hasError;

		},


		"timezoneOK": function(tz) {
			for(var i = 0; i < window.moment_zones.length; i++) {
				if (tz === window.moment_zones[i]) return true;
			}
			return false;
		},

		"prepareTimezoneSelector": function(tz) {
			var c = this.el.find('#timezoneselector');

			var s = $('<input id="timezoneselect" class="form-control" autocomplete="off" type="text" data-provide="typeahead" />').appendTo(c);

			console.log("TIMEZONE");
			console.log(this.user);

			s.typeahead({
				"source": window.moment_zones
			});

			if (this.timezoneOK(tz)) { 
				s.val(tz);
			} else if (this.timezoneOK(this.user.timezone)) {
				s.val(this.user.timezone);
			}

			for(var i = 0; i < window.moment_zones.length; i++) {
				s.append('<option>' + moment_zones[i] + '</option>');
			}


		},

		"setupMap": function() {
			var that = this;
			this.geocoder = new google.maps.Geocoder();
			google.maps.visualRefresh = true;
			var mapOptions = {
				center: new google.maps.LatLng(-34.397, 150.644),
				zoom: 8
			};
			this.map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);
			

			// Try W3C Geolocation (Preferred)
			if(navigator.geolocation) {
				browserSupportFlag = true;
				navigator.geolocation.getCurrentPosition(function(position) {
					initialLocation = new google.maps.LatLng(position.coords.latitude,position.coords.longitude);
					that.map.setCenter(initialLocation);
				}, function() {
					// handleNoGeolocation(browserSupportFlag);
				});
				// Browser does not support geo location.
			} else {
			  	browserSupportFlag = false;
			  	handleNoGeolocation(browserSupportFlag);
			}
		},

		"updateColSelector": function() {

			if (!$('#enableRestrictions').prop('checked')) return;

			$('#inputRestrictionColSelector').empty();
			var coldef = this.columneditor.getColDef();
			var collist = this.getColList(coldef);

			for(var i = 0; i < collist.length; i++) {
				var x = $('<option value="' + i + '"></option>');
				x.text(collist[i]);
				$('#inputRestrictionColselector').append(x);
			}

		},

		/*
		 * Transform an hierarchical list to a flat list.
		 */
		"getColList": function(coldef) {
			var list = [];

			for(var i = 0; i < coldef.length; i++) {
				if (coldef[i].hasOwnProperty('children')) {
					for(var j = 0; j < coldef[i].children.length; j++) {
						list.push(coldef[i].children[j].title + ' (' + coldef[i].title + ')')
					}
				} else {
					list.push(coldef[i].title);
				}
			}

			return list;

		},

		"updateUI": function() {

			// console.error("update ui with ", this.foodle.columns);

			if (this.foodle.columns) {
				// console.log("Updating UI with columns ", this.foodle.columntype)
				this.columneditor.redraw(this.foodle.columns, this.foodle.columntype);

			}



			if (this.foodle.location) {
				this.el.find('#enableLocation').prop('checked', true);

				if (this.foodle.location.local) {
					this.el.find('#inputLocationLocal').val(this.foodle.location.local);
				}
				if (this.foodle.location.address) {
					this.el.find('#inputAddress').val(this.foodle.location.address);
					this.codeAddress();
				}

			} else {
				this.el.find('#enableLocation').prop('checked', false);

			}



			// 	var enabledCollimit = this.el.find('#enableRestrictionColLimit').prop('checked');
			// 	if (enabledCollimit) {
			// 		var no = parseInt(this.el.find('#inputRestrictionColLimit').val(), 10);
			// 		var colno = parseInt(this.el.find('#inputRestrictionColselector').val());

			// 		if (no !== null && colno !== null) {
			// 			restr.col = {
			// 				'col': colno,
			// 				'limit': no
			// 			};

			// 		}
			// 	}

	

			if (this.foodle.restrictions) {


				this.el.find('#enableRestrictions').prop('checked', true);
				// this.updateColSelector();

				if (this.foodle.restrictions.rows) {
					this.el.find('#enableRestrictionRowlimit').prop('checked', true);
					this.el.find('#inputRestrictionRowlimit').val(this.foodle.restrictions.rows)

				} else {
					this.el.find('#enableRestrictionRowlimit').prop('checked', false);
				}

				if (this.foodle.restrictions.col) {
					this.el.find('#enableRestrictionColLimit').prop('checked', true);

					if (this.foodle.restrictions.col.limit) {
						this.el.find('#inputRestrictionColLimit').val(this.foodle.restrictions.col.limit);	
					}
					if (this.foodle.restrictions.col.col) {
						this.el.find('#inputRestrictionColselector').val(this.foodle.restrictions.col.col);
					}
					
					

				} else {
					this.el.find('#enableRestrictionColLimit').prop('checked', false);
				}

				if (this.foodle.restrictions.checklimit) {
					this.el.find('#enableRestrictionCheckLimit').prop('checked', true);
					this.el.find('#inputRestrictionCheckLimit').val(this.foodle.restrictions.checklimit)

				} else {
					this.el.find('#enableRestrictionCheckLimit').prop('checked', false);
				}

			} else {

				this.el.find('#enableRestrictions').prop('checked', false);

			}


			if (this.foodle.expire) {
				var em = moment.unix(parseInt(this.foodle.expire, 10));
				console.log("Expiration ", this.foodle.expire, em);
				this.el.find('#inputDeadlineDate').val(em.format('YYYY-MM-DD'));
				this.el.find('#inputDeadlineTime').val(em.format('HH:mm'));
				this.el.find('#enableDeadline').prop('checked', true);
			} else {
				this.el.find('#enableDeadline').prop('checked', false);
			}



			if (this.foodle.allowanonymous) {
				$('#inputRequireLogin').prop('checked', false);
			} else {
				$('#inputRequireLogin').prop('checked', true);
			}

			if (this.foodle.responsetype && this.foodle.responsetype === 'yesnomaybe') {
				$('#inputResponseTypeMaybe').prop('checked', true);
			} else {
				$('#inputResponseTypeMaybe').prop('checked', false);
			}


			if (this.foodle.columntype && this.foodle.columntype === 'dates' ) {
				console.error('Not implemented yet');
			}


			if (this.foodle.datetime) {
				this.el.find('#enableTime').prop('checked', true);

				if (this.foodle.datetime.datefrom) 	this.el.find('#inputDateStart').val(this.foodle.datetime.datefrom);
				if (this.foodle.datetime.dateto) 	this.el.find('#inputDateEnd').val(this.foodle.datetime.dateto);
				if (this.foodle.datetime.timefrom) 	this.el.find('#inputTimeStart').val(this.foodle.datetime.timefrom);
				if (this.foodle.datetime.timeto) 	this.el.find('#inputTimeEnd').val(this.foodle.datetime.timeto);

				this.el.find('#inputTimeMultipleDays').prop('checked', this.foodle.datetime.hasOwnProperty('datetto'));
				this.el.find('#inputTimeAllDay').prop('checked', !this.foodle.datetime.hasOwnProperty('timefrom'));

			} else {
				this.el.find('#enableTime').prop('checked', false);
			}


			if (this.foodle.timezone) {
				this.el.find('#timezoneselect').val(this.foodle.timezone);
			}



		},

		"updateFoodle": function(identifier) {

			
			// console.log(def);
			// $("#debug").empty().append(JSON.stringify(def, undefined, 4) );

			var def = this.getObject();
			var foodle = new Foodle(def);
			foodle.identifier = identifier;

			this.api.updateFoodle(foodle, function(response) {
				console.log("Successfully updated foodle", response);
				window.location.href = '/foodle/' + identifier;

			});



		},

		"submitFoodle": function() {

			// console.log(def);
			// $("#debug").empty().append(JSON.stringify(def, undefined, 4) );

			
			var def = this.getObject();
			var foodle = new Foodle(def);


			this.api.createNewFoodle(foodle, function(response) {
				console.log("Successfully created new foodle", response);
				var identifier = response.identifier;

				$('#submitFoodle').addClass('disabled');
				$('#modalSuccess').modal({
					'backdrop': true,
					'show': true,
					'keyboard': false
				});
				$('#modalSuccess').on('click', '.actContinue', function() {
					console.log('REDIRECT TO ');
					window.location.href = '/foodle/' + identifier;
				});


				$('#shareURL').attr('value', 'http://foodl.org/foodle/' + identifier);

			});

		},


		"codeAddress": function() {
			var that = this;
			var address = $('#inputAddress').val();

			that.geocoder.geocode( { 'address': address}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					that.map.setCenter(results[0].geometry.location);

					if (that.marker === null) {
						that.marker = new google.maps.Marker({
							map: that.map,
							position: results[0].geometry.location
						});

					} else {
						that.marker.setPosition( results[0].geometry.location);
						that.map.panTo( results[0].geometry.location );
					}
					that.map.setZoom(11);
					console.log("Successfully got geo location of address", address);
				} else {
					console.error('Geocode was not successful for the following reason: ' + status);
				}
			});
		},


		"getObject": function() {
			var obj = {};
			obj.title = $('#inputTitle').val();
			obj.descr = $('#inputDescr').val();
			obj.coldef = this.columneditor.getColDef();
			obj.columntype = this.columneditor.getColumntype();



			// 'text'; // Or 'dates'


			var enableLocation = this.el.find('#enableLocation').prop('checked');
			if (enableLocation) {
				var l = {};
				var loc = this.el.find('#inputLocationLocal').val();
				if (loc !== '') {
					l.local = loc;
				}
				var adr = this.el.find('#inputAddress').val();
				if (adr !== '') {
					l.address = adr;
				}

				if (loc !== '' || adr !== '') {
					obj.location = l;
				}
			} 


			var enableRestrictions = this.el.find('#enableRestrictions').prop('checked');
			console.log("enableRestrictions", enableRestrictions);

			if (enableRestrictions) {
				
				var restr = {};

				var enabledRowlimit = this.el.find('#enableRestrictionRowlimit').prop('checked');
				if (enabledRowlimit) {
					var no = parseInt(this.el.find('#inputRestrictionRowlimit').val(), 10);
					if (no !== null) {
						restr.rows = no;
					}
					
				}

				var enabledCollimit = this.el.find('#enableRestrictionColLimit').prop('checked');
				if (enabledCollimit) {
					var no = parseInt(this.el.find('#inputRestrictionColLimit').val(), 10);
					var colno = parseInt(this.el.find('#inputRestrictionColselector').val());

					if (no !== null && colno !== null) {
						restr.col = {
							'col': colno,
							'limit': no
						};

					}
				}

				var enabledCheckLimit = this.el.find('#enableRestrictionCheckLimit').prop('checked');
				if (enabledCheckLimit) {
					var no = parseInt(this.el.find('#inputRestrictionCheckLimit').val(), 10);

					if (no !== null) {
						restr.checklimit = no;
					}
					
				}


				obj.restrictions = restr;

			} else {
				this.el.find('#sectionRestrictions').hide();
			}



			var enableDeadline = this.el.find('#enableDeadline').prop('checked');
			if (enableDeadline) {

				var dldate = this.el.find('#inputDeadlineDate').val();
				var dltime = this.el.find('#inputDeadlineTime').val();

				if (dldate !== '' && dltime !== '') {
					var dl = moment(dldate + ' ' + dltime, 'YYYY-MM-DD HH:mm');
					console.log("deadline date ", dldate + ' ' + dltime, dl);
					obj.expire = dl.unix();
				}


			} 


			var reqlogin = $('#inputRequireLogin').prop('checked');
			obj.allowanonymous = !reqlogin;
			

			var maybe = $('#inputResponseTypeMaybe').prop('checked');
			obj.responsetype = (maybe ? 'yesnomaybe' : 'yesno');

			



			var enableTime = this.el.find('#enableTime').prop('checked');
			console.log("enableTime", enableTime);

			if (enableTime) {

				var datetime = {};

				var datefrom = this.el.find('#inputDateStart').val();
				var dateto   = this.el.find('#inputDateEnd').val();
				var timefrom = this.el.find('#inputTimeStart').val();
				var timeto   = this.el.find('#inputTimeEnd').val();


				// if (datefrom !== '') datetime.datefrom = datefrom;
				// if (dateto !== '')   datetime.dateto = dateto;
				// if (timefrom !== '') datetime.timefrom = timefrom;
				// if (timeto !== '')   datetime.timeto = timeto;


				var inputTimeAllDay = this.el.find('#inputTimeAllDay').prop('checked');
				var inputTimeMultipleDays = this.el.find('#inputTimeMultipleDays').prop('checked');


				if (inputTimeAllDay && inputTimeMultipleDays) {
					
					if (datefrom !== '') datetime.datefrom = datefrom;
					if (dateto !== '')   datetime.dateto = dateto;


				} else if (inputTimeAllDay && !inputTimeMultipleDays) {

					if (datefrom !== '') datetime.datefrom = datefrom;


				} else if (!inputTimeAllDay && inputTimeMultipleDays) {

					if (datefrom !== '') datetime.datefrom = datefrom;
					if (dateto !== '')   datetime.dateto = dateto;
					if (timefrom !== '') datetime.timefrom = timefrom;
					if (timeto !== '')   datetime.timeto = timeto;


				} else if (!inputTimeAllDay && !inputTimeMultipleDays) {

					if (datefrom !== '') datetime.datefrom = datefrom;
					if (timefrom !== '') datetime.timefrom = timefrom;
					if (timeto !== '')   datetime.timeto = timeto;					

				}

				obj.datetime = datetime;

			}


			if (enableDeadline || enableTime || 
				(this.columneditor.getColumntype() === 'dates')) {


				var tz = this.el.find('#timezoneselect').val();
				if (this.timezoneOK(tz)) {
					obj.timezone = tz;
				}

			}




			return obj;
		},

		"getDateStart": function() {
			var inputTimeAllDay = this.el.find('#inputTimeAllDay').prop('checked');
			var inputTimeMultipleDays = this.el.find('#inputTimeMultipleDays').prop('checked');

			var startDateStr = $("#inputDateStart").val();
			var startTimeStr = $("#inputTimeStart").val();

			if (startDateStr === '') return null;
			if (inputTimeAllDay) {
				startTimeStr = '00:00';
			} else if (startTimeStr === '') {
				return null;
			}

			var fullstr = startDateStr + ' ' + startTimeStr;

			return moment(fullstr, "YYYY-MM-DD HH:mm");
		},

		"getDateEnd": function() {
			var inputTimeAllDay = this.el.find('#inputTimeAllDay').prop('checked');
			var inputTimeMultipleDays = this.el.find('#inputTimeMultipleDays').prop('checked');

			var startDateStr = $("#inputDateStart").val();
			var endDateStr = $("#inputDateEnd").val();
			var endTimeStr = $("#inputTimeEnd").val();

			if (startDateStr === '') return null;
			
			if (inputTimeMultipleDays) {
				if (endDateStr === '') return null;
			} else {
				endDateStr = startDateStr;
			}

			if (inputTimeAllDay) {
				endTimeStr = '00:00';

			} else if (endTimeStr === '') {
				return null;
			}

			var fullstr = endDateStr + ' ' + endTimeStr;

			var x = moment(fullstr, "YYYY-MM-DD HH:mm");
			if (inputTimeAllDay) {
				x.add('days', 1);
			}
			return x;
		},


		"updateDynamics": function(e) {
			if (e) {
				e.stopPropagation(); e.preventDefault();
			}
			var enableLocation = this.el.find('#enableLocation').prop('checked');
			console.log("Location enabled", enableLocation);

			if (enableLocation) {
				this.el.find('#sectionLocationDetails').show();
				google.maps.event.trigger(this.map, 'resize');
				// this.el.find('#map-canvas').show();
			} else {
				this.el.find('#sectionLocationDetails').hide();
				// this.el.find('#map-canvas').hide();
			}


			var enableRestrictions = this.el.find('#enableRestrictions').prop('checked');
			console.log("enableRestrictions", enableRestrictions);

			if (enableRestrictions) {
				this.el.find('#sectionRestrictions').show();
			} else {
				this.el.find('#sectionRestrictions').hide();
			}



			var enableDeadline = this.el.find('#enableDeadline').prop('checked');
			console.log("enableDeadline", enableDeadline);

			if (enableDeadline) {
				this.el.find('#sectionDeadline').show();
			} else {
				this.el.find('#sectionDeadline').hide();
			}


			var enableTime = this.el.find('#enableTime').prop('checked');
			console.log("enableTime", enableTime);

			if (enableTime) {
				this.el.find('#sectionTime').show();
				this.el.find('#sectionTimeDetails').show();
				
			} else {
				this.el.find('#sectionTime').hide();
				this.el.find('#sectionTimeDetails').hide();
			}


			var inputTimeAllDay = this.el.find('#inputTimeAllDay').prop('checked');
			var inputTimeMultipleDays = this.el.find('#inputTimeMultipleDays').prop('checked');

			if (inputTimeAllDay) {
				this.el.find('#sectioninputTimeStart').hide();
				// this.el.find('#sectioninputDateEnd').s();
				this.el.find('#sectioninputTimeEnd').hide();
			} else {
				this.el.find('#sectioninputTimeStart').show();
				// this.el.find('#sectioninputDateEnd').hide();
				this.el.find('#sectioninputTimeEnd').show();
			}

			if (inputTimeMultipleDays) {
				// this.el.find('#sectioninputTimeStart').hide();
				this.el.find('#sectioninputDateEnd').show();
				// this.el.find('#sectioninputTimeEnd').show();
			} else {
				// this.el.find('#sectioninputTimeStart').hide();
				this.el.find('#sectioninputDateEnd').hide();
				// this.el.find('#sectioninputTimeEnd').show();
			}

			if (inputTimeAllDay && !inputTimeMultipleDays) {
				this.el.find('#sectioninputUntil').hide();
			} else {
				this.el.find('#sectioninputUntil').show();
			}

			var start = this.getDateStart();
			var end = this.getDateEnd();

			$("#timeDetails").empty();
			if (start !== null && end !== null && enableTime) {
				var str = 'Event starts in <i>' + start.fromNow() + '</i>';
				str += ' and last for <i>' + start.from(end, true) + '</i>';
				$("#timeDetails").append(str );
					
			}

			if (enableDeadline || enableTime || 
				(this.columneditor.getColumntype() === 'dates')) {

				this.el.find('#sectionTimezone').show();
			} else {
				this.el.find('#sectionTimezone').hide();
			}
			console.log('enableDeadline', enableDeadline);
			console.log(' enableTime',  enableTime);
			console.log('this.columneditor.getColumntype()', this.columneditor.getColumntype());

			console.log("start", start);
			console.log("end", end);


		}
	})

	return EditFoodleController;

});