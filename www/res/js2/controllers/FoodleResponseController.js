define(function(require, exports) {



	var 
		$ = require('jquery'),
		Class = require('lib/class'),
		hb = require('lib/handlebars'),
		pretty = require('lib/pretty'),
		moment = require('moment-timezone'),

		// ColumnEditor = require('./ColumnEditor'),

		Foodle = require('models/Foodle'),

		UpcomingListController = require('./UpcomingListController'),
		ResponseTableHeadController = require('./ResponseTableHeadController'),
		MyResponseController = require('./MyResponseController')
		;

	require('moment-timezone-data');
	require('lib/bootstrap3-typeahead');

	var t = require('lib/text!templates/foodleresponse.html');
	var template = hb.compile(t);



	/**
	 * The FoodleResponseController is the main controller of the /foodle/identifier response page. 
	 * 	It invokes subcontrollers for parts of the page
	 * 	It requires the be loaded with a Foodle, but it is optional to provide an authenticated user.
	 * 
	 * @param  {[type]} api
	 * @param  {[type]} foodle
	 * @param  {[type]} user 	(May be null if user is not authenticated yet)
	 * @param  {[type]} el 		
	 * @return {[type]}
	 */
	var FoodleResponseController = Class.extend({
		"init": function(api, foodle, user, el) {

			var that = this;

			this.user = user;
			this.api = api;
			this.foodle = foodle;
			this.el = el;

			this.geocoder = new google.maps.Geocoder();

			// console.log("›› Foodle object", foodle);


			this.loadResponses();

			this.el.on('click', '#responsenav #navMyResponse', function(e) {
				e.stopPropagation(); e.preventDefault();
				$('#myResponseTable').show();
				$('#responseTable').hide();
				$('#commentPane').hide();
				$('#responsenav li').removeClass('active');
				$('#responsenav li#navMyResponse').addClass('active');
			});
			this.el.on('click', '#responsenav #navAllResponses', function(e) {
				e.stopPropagation(); e.preventDefault();
				$('#myResponseTable').hide();
				$('#responseTable').show();
				$('#commentPane').hide();
				$('#responsenav li').removeClass('active');
				$('#responsenav li#navAllResponses').addClass('active');
			});
			this.el.on('click', '#responsenav #navComments', function(e) {
				e.stopPropagation(); e.preventDefault();
				$('#myResponseTable').hide();
				$('#responseTable').hide();
				$('#commentPane').show();

				$('#responsenav li').removeClass('active');
				$('#responsenav li#navComments').addClass('active');
			});

			this.el.on('click', '#enableComments', function(e) {
				e.stopPropagation();

				// console.log("CLICK on enableComments");
				var enableComments = $('#enableComments').prop('checked');
				if (enableComments) {
					$('.noterow').show();
				} else {
					$('.noterow').hide();
				}
			});


			this.el.on('change', '#timezoneselect', function(e) {
				e.stopPropagation(); e.preventDefault();
				that.setTimezone();
			});

			this.el.on('click', '#submitComment', function(e) {
				e.stopPropagation(); e.preventDefault();
				

				var comment = $('#commentText').val();
				$('#commentText').val('');

				if (comment === '') return;

				// console.error('Add comment', comment);

				that.api.addComment(that.foodle, comment, function(res) {
					// console.log("Successfully saved comment")
					that.loadDiscussion();
				});

			});


			this.el.on('click', '#actDelete', function(e) {
				e.stopPropagation(); e.preventDefault();
				// console.error('Delete');



				$('#modalDelete').find('#foodleID').val(that.foodle.identifier);

				$('#modalDelete').modal({
					'backdrop': true,
					'show': true,
					'keyboard': false
				});
				$('#modalDelete').on('click', '.actContinue', function() {
					// console.log('REDIRECT TO ');

					that.api.deleteFoodle(that.foodle.identifier, function() {
						// setTimeout(function() {
							window.location.href = '/';	
						// }, 10000);

					});

					
				});

			});
			


		},



		"onLoadComplete": function() {

			var that = this;
			if (this.loaded) return;
			this.loaded = true;


			this.draw();

		
			this.myresponse = new MyResponseController(this.api, this.foodle, this.user, null, this.el.find("#mytablebody"));
			this.myresponse.on('response', $.proxy(this.submitResponse, this));		
			this.myresponse.on('register', function(user) {
				// console.error('FoodleResponseController is now registerig user as authenticated', user);
				that.user = user;
				that.foodle.setUser(user.userid);
			});				


			$('#responseTable').hide();
			$('#commentPane').hide();

			if (this.foodle.hasResponseWithComment()) {
				$('#showComments').show();
			} else {
				$('#showComments').hide();
			}


			this.setupTimezoneController();
			this.loadDiscussion();
		},


		"submitResponse": function(response) {
			// console.log("About to submit response through API", response);
			var that = this;
			$('#cellSave').addClass('loading');
			this.api.postResponse(response, function(d) {
				// console.log("Yah! back", d);
				
				that.loadResponses();
			});
		},

		"loadResponses": function() {
			var that = this;
			$('#cellSave').removeClass('loading');
			this.api.getFoodleResponses(this.foodle.identifier, function(responses) {
				// console.log(" › Got respones", responses);
				// that.responses = responses;

				// for(var key in that.foodle) {
				// 	console.log("debug foodle ", key, " = ", that.foodle[key]);
				// }

				console.log("Check type", that.foodle); that.foodle.type();

				that.foodle.setResponses(responses);

				that.onLoadComplete();
				that.drawResponses();

				// if (this.user) {
					var mr = that.foodle.getMyResponse();

					// console.log("We are not completed loading responses. This is myresponse:", mr);

					that.myresponse.setMyResponse(mr);

				// }
				
			});
		},

		"loadDiscussion": function() {
			var that = this;

			// console.log('---about to load discussion', "getFoodleDiscussion");

			this.api.getFoodleDiscussion(this.foodle.identifier, function(comments) {
				// console.log("Got comments", comments);
				that.discussion = comments;
				that.drawDiscussion();
				// that.responses = responses;

				// that.foodle.setResponses(responses);
				// that.drawResponses();
				// var mr = that.foodle.getMyResponse();
				// that.myresponse.setMyResponse(mr);
				
			});
		},




		"getResponseCell": function(i) {
			// console.log("Value is ", i);
			if (parseInt(i, 10) === 1) {
				return '<td class="responseCellYes"><span class="glyphicon glyphicon-ok"></span></td>';
			} else if (parseInt(i, 10) === 0) {
				return '<td class="responseCellNo"><span class="glyphicon glyphicon-remove"></span></td>';
			} else if (parseInt(i, 10) === 2) {
				return '<td class="responseCellMaybe"><span class="glyphicon glyphicon glyphicon-question-sign"></span></td>';
			} else {
				return '<td class="responseCellFail"><span class="glyphicon glyphicon glyphicon-info-sign"></span></td>';
			}

		},

		"drawResponses": function() {
			if (!this.foodle) return;
			var responses = this.foodle.getResponses();

			// console.log("draw respones", responses, this.foodle);


			var c = $('#tablebody').empty();

			for(var i in responses) {

				var r = responses[i];
				var row = $('<tr></tr>');

				var ustr = '<span class="glyphicon glyphicon-user"></span> ' + r.username;
				if (r.notes) {
					ustr += '<span class="pull-right glyphicon glyphicon-file" style="color: #f3823e"></span>';
				}

				row.append('<td>' + ustr + '</td>');


				// console.log("Processing response ", r.response);
				for(var j = 0; j < r.response.data.length; j++) {
					row.append(this.getResponseCell(r.getResponse(j)));
				}

				var created = moment.unix(r.created);
				var ct = created.fromNow(true);

				
				if (r.updated !== r.created) {

					var udpated = moment.unix(r.updated);
					var ut = udpated.fromNow(true);
					row.append('<td style="color: #755"><span class="glyphicon glyphicon-time"></span> ' + ut + '</td>');
				} else {
					row.append('<td><span class="glyphicon glyphicon-time"></span> ' + ct + '</td>');
				}

				
				c.append(row);

				if (r.notes) {
					var cols = this.foodle.getColNo();
					var colspan = 2+cols;
					c.append('<td colspan="' + colspan + '" class="noterow">' + r.notes + '</td>');
				}


			}
			this.drawResponseSummary();

		},

		"drawDiscussion": function() {
			// console.log("drawDiscussion");

			if (!this.user) {
				$('#submitComment').addClass('disabled');
			}

			var c = $('<div class="list-group"></div>');

			$('#commentList').empty().append(c);

			if (!this.discussion) return;
			for(var i = 0; i < this.discussion.length; i++) {

				var item = this.discussion[i];
				var itemel = $('<div class="list-group-item"><span class="dateCreated badge">14</span>' + 
					'<h4 class="list-group-item-heading"><span class="glyphicon glyphicon-user"></span> <span class="name"></span></h4>' + 
					'<p class="textContent list-group-item-text"></p></div>');

				itemel.find('h4 .name').text(item.username);
				var cu = moment.unix(item.createdu);
				itemel.find('.dateCreated').text(cu.fromNow());
				itemel.find('.textContent').text(item.message);

				c.append(itemel);

				// $('#commentPane').append('<p>' + this.discussion[i].message + '</p>');

			}
			if (this.discussion.length > 0 ) {
				$('#discussionCount').empty().append(this.discussion.length).show();	
			}
			


		},

		"drawResponseSummary": function() {

			if (!this.foodle) return;
			var responses = this.foodle.getResponses();


			var c = [];
			var tablebody = $('#tablebody');

			var cn = this.foodle.getColNo();
			for(var i = 0; i < cn; i++) {
				c.push(0);
			}

			for(var i in responses) {
				var r = responses[i];
				for(var j = 0; j < r.response.data.length; j++) {

					if (parseInt(r.response.data[j], 10) === 1) {
						c[j]++;
					}
				}

			}

			var summaryRow = $('<tr class="warning"></tr>');

			summaryRow.append('<td>Summary</td>');
			for(var i = 0; i < cn; i++) {
				summaryRow.append('<td style="text-align: center">' + c[i] + '</td>');
			}
			summaryRow.append('<td>&nbsp;</td>');

			tablebody.append(summaryRow);


		},

		"isColumntypeDates": function() {
			if (this.foodle.columntype && this.foodle.columntype === 'dates') return true;
			return false;
		},

		"draw": function() {
			var that = this;

			this.el.empty().append(template(this.foodle));

			if (this.foodle.isOwner()) {
				
			} else {
				$('#ownerbar').hide();
				// console.log("NOT OWNER");
			}

			var tz = this.getDefaultTimezone();

			var mainspan = 3;

			if (this.foodle.datetime || this.foodle.expire || this.foodle.restrictions || this.isColumntypeDates()) {
				this.setTime(tz);
				this.setDeadline(tz);
				this.setRestrictions();
				
			} else {
				$("#colTime").hide();
				mainspan += 3;
			}


			if (this.foodle.location) {
				this.setLocation();	
			} else {
				$("#colLocation").hide();
				mainspan += 3;
			}

			if (mainspan !== 3) {
				$("#foodleDescription").removeClass('col-md-3').addClass('col-md-' + mainspan);	
			}
			

			

			this.setCreated();


			if (this.user && !this.user.anon) {
				this.upcomingcontroller = new UpcomingListController(this.api, $("#upcoming"), 6, 'slim', this.foodle);	
			}
			


			var defaultUserTimezone = this.getDefaultTimezone();

			this.th = new ResponseTableHeadController(this.el.find("#tablehead"), this.foodle, 1, defaultUserTimezone);
			this.mth = new ResponseTableHeadController(this.el.find("#mytablehead"), this.foodle, 2, defaultUserTimezone);
			// this.mth.highlight = this.foodle;

			


		},

		"getDefaultTimezone": function() {

			if (this.user && this.user.timezone && this.timezoneOK(this.user.timezone)) {
				return this.user.timezone;
			} else if (this.foodle && this.foodle.timezone && this.timezoneOK(this.foodle.timezone)) {
				return this.foodle.timezone;
			}
			return null;
		},


		"timezoneOK": function(tz) {
			for(var i = 0; i < window.moment_zones.length; i++) {
				if (tz === window.moment_zones[i]) return true;
			}
			return false;
		},

		"setTimezone": function() {

			var tz = $('#timezoneselect').val();
			if (this.timezoneOK(tz)) {
				// console.log("Set new timezone", tz);

				if (this.user) {
					this.api.setTimezone(tz);					
				}


				this.th.setTimezone(tz);
				this.mth.setTimezone(tz);
				this.setTime(tz);
				this.setDeadline(tz);
			}

		},

		"setupTimezoneController": function() {

			var that = this;
			var panel = $('#panelTimezone').show();

			var c = $('#sectTimezone');

			var s = $('<input id="timezoneselect" class="form-control" autocomplete="off" type="text" data-provide="typeahead" />').appendTo(c);

			var alternativeList = $('<ul class="uninett-ul"></ul>');



			// console.log("TIMEZONE");
			// console.log(this.user);

			s.typeahead({
				"source": window.moment_zones
			});



			var tz = '';
			if (this.foodle.hasOwnProperty('timezone')) {
				tz = this.foodle.timezone;
			}


			var userDefaultTimezone = this.getDefaultTimezone();

			if (userDefaultTimezone) { 
				s.val(userDefaultTimezone);
			}



			if (this.timezoneOK(tz) && this.user && this.timezoneOK(this.user.timezone) && this.user.timezone !== tz) {
				alternativeList.append('<li class="setTimezoneLink uninett-ul-li" data-timezone="' + tz + '"><a href="#">' + tz + '</a></li>');
				alternativeList.append('<li class="setTimezoneLink uninett-ul-li" data-timezone="' + this.user.timezone + '"><a href="#">' + this.user.timezone + '</a></li>');
				c.append(alternativeList);
			} else {
				// console.error("NOT READY TO LIST alternativeList", this.timezoneOK(tz), this.timezoneOK(this.user.timezone), this.user.timezone, tz);
			}

			alternativeList.on('click', '.setTimezoneLink', function(e) {
				e.preventDefault(); e.stopPropagation();
				var tz = $(e.currentTarget).closest('li').data('timezone');
				if (that.timezoneOK(tz)) {
					s.val(tz);
					that.setTimezone(tz);
				}
			})

			// for(var i = 0; i < window.moment_zones.length; i++) {
			// 	s.append('<option>' + moment_zones[i] + '</option>');
			// }


		},


		"setLocation": function() {
			var that = this;

			var c = $('#sectLocation');

			if (!this.foodle.location) {
				this.el.find('#panelLocation').hide();
				return;
			}

			if (this.foodle.location.address) {
				
				c.append('<div style="border: 1px solid #444; height: 200px" id="location-canvas"></div>');
			}


			if (this.foodle.location.local) {
				c.append('<p class="loc-local">' + this.foodle.location.local + '</p>');
			}


			if (this.foodle.location.address) {

				c.append('<p>' + this.foodle.location.address + '</p>');

				this.codeAddress(this.foodle.location.address, function(loc) {

					that.map = new google.maps.Map(document.getElementById("location-canvas"), {
						center: loc,
						zoom: 11
					});
					var marker = new google.maps.Marker({
						map: that.map,
						position: loc
					});

				});


			}

		},
		"codeAddress": function (address, callback) {
			var that = this;
			this.geocoder.geocode( { 'address': address}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {

					// console.log("Successfully obtained geo location for this address " + address, results[0].geometry.location);
					callback(results[0].geometry.location);

					// that.map.setCenter(results[0].geometry.location);
					// var marker = new google.maps.Marker({
					// 	map: that.map,
					// 	position: results[0].geometry.location
					// });
					// that.map.setZoom(11);
					// console.log("Successfully got geo location of address", address);
				} else {
					// console.error('Geocode was not successful for the following reason: ' + status);
				}
			});
		},


		"setTime": function(toTimezone) {
			if (!this.foodle.datetime) {
				this.el.find('#panelTime').hide();
				return;
			}

			var dt = this.foodle.datetime;
			var ct = $('#sectTime').empty();
			var mf, mt;


			var doTimezone = false;
			if (this.foodle.timezone && toTimezone) doTimezone = true;


			// console.log("Set time", dt);

			// Date range, full days
			if (dt.datefrom && dt.dateto && !dt.timefrom && !dt.timeto) {
				// console.log("Set time (1)", dt);
				mf = moment(dt.datefrom, 'YYYY-MM-DD');
				mt = moment(dt.dateto,   'YYYY-MM-DD');
				ct.append('<p>' + mf.format('ddd Do MMM') + ' to ' + mt.format('ddd Do MMM, YYYY')  + '</p>');
			} else if (dt.datefrom && dt.dateto && dt.timefrom && dt.timeto) {
				// console.log("Set time (2)", dt);
				
				if (doTimezone) {
					mf = moment.tz(dt.datefrom + ' ' + dt.timefrom, this.foodle.timezone).tz(toTimezone);
					mt = moment.tz(dt.dateto   + ' ' + dt.timeto,   this.foodle.timezone).tz(toTimezone);
				} else {
					mf = moment(dt.datefrom + ' ' + dt.timefrom, 'YYYY-MM-DD HH:mm');
					mt = moment(dt.dateto   + ' ' + dt.timeto,   'YYYY-MM-DD HH:mm');
				}


				ct.append('<p>' + mf.format('ddd Do MMM, YYYY, HH:mm') + '</p>');
				ct.append('<p>to</p>');
				ct.append('<p>' + mt.format('ddd Do MMM, YYYY, HH:mm') + '</p>');
			} else if (dt.datefrom && !dt.dateto && dt.timefrom && dt.timeto) {
				// console.log("Set time (3)", dt);

				if (doTimezone) {
					mf = moment.tz(dt.datefrom + ' ' + dt.timefrom, this.foodle.timezone).tz(toTimezone);
					mt = moment.tz(dt.datefrom + ' ' + dt.timeto,   this.foodle.timezone).tz(toTimezone);
				} else {
					mf = moment(dt.datefrom + ' ' + dt.timefrom, 'YYYY-MM-DD HH:mm');
					mt = moment(dt.datefrom + ' ' + dt.timeto,   'YYYY-MM-DD HH:mm');
				}


				ct.append('<p class="s-lg">' + mf.format('ddd Do MMM, YYYY') + '</p>');
				ct.append('<p class="s-lg">' + mf.format('HH:mm') + ' – ' + mt.format('HH:mm') + '</p>');
			} else if (dt.datefrom && !dt.dateto && !dt.timefrom && !dt.timeto) {
				// console.log("Set time (4)", dt);
				mf = moment(dt.datefrom, 'YYYY-MM-DD');
				ct.append('<p>' + mf.format('ddd Do MMM, YYYY') + '</p>');
			}

			if (mf) {
				ct.append('<p class="time-fromnow">Event starts in ' + mf.fromNow() + '</p>');
			}
			if (mf && mt) {
				ct.append('<p class="time-duration">Event last for ' + mt.from(mf, true) + '</p>');	
			}

		},

		"setRestrictions": function() {

			if (!this.foodle.restrictions) {
				this.el.find('#panelRestrictions').hide();
				return;
			}

			var r = this.foodle.restrictions;
			var container = $('#sectRestrictions');
			var x;

			// console.log("Set restrictions on ", this.foodle);

			if (r.rows) {
				x = this.foodle.getRowCount(r.rows);
				// console.log("Row count", x);
				container.append('<p>' + x.left + ' of ' + r.rows + ' responses left.</p>');

				if (x.locked) {
					container.append('<p style="font-size: 110%"><span class="label label-danger"><span class="glyphicon glyphicon-lock"></span> Locked for responses</span></p>');
				}
			}

			if (r.col) {
				x = this.foodle.getColCount(r.col.col, r.col.limit);
				// console.log("Col count", x);
				container.append('<p>' + x.left + ' of ' + r.col.limit + ' spaces left.</p>');

				if (x.locked) {
					container.append('<p style="font-size: 110%"><span class="label label-danger"><span class="glyphicon glyphicon-lock"></span> Locked for responses</span></p>');
				}
			}

			if (r.checklimit) {
				container.append('<p>You may check max ' + r.checklimit + ' columns in your response.</p>');
			}

		},

		"setDeadline": function(toTimezone) {
			if (!this.foodle.expire) {
				this.el.find('#panelDeadline').hide();
				return;
			}

			var container = $("#sectDeadline").empty();

			var doTimezone = false;
			if (this.foodle.timezone && toTimezone) doTimezone = true;

			var dlts;
			if (doTimezone) {
				dlts = moment(this.foodle.expire, 'X').tz(toTimezone);	
			} else {
				dlts = moment(this.foodle.expire, 'X');	
			}
			
			var str = dlts.format('ddd, Do MMM, YYYY, HH:mm');
			// console.log('deadline ' + str);

			if (this.foodle.locked()) {
				$("#sectDeadline").append('<p style="font-size: 110%"><span class="label label-danger"><span class="glyphicon glyphicon-lock"></span> Locked for responses</span></p>');
			}

			container
				.append('<p class="uninett-fontColor-red">' + str + '</p>')
				.append('<p class="time-fromnow">Respond within ' + dlts.fromNow(true) + '</p>');

		},

		"setCreated": function() {
			// console.log("Created", this.foodle.created);

			if (!this.foodle.created) return;

			var statusline = $("#statusline");
			var str = '';

			var o = 'anonymous';
			if (this.foodle.owner) {
				o = this.foodle.owner;
			}

			var c = moment.unix(this.foodle.created);
			
			str = 'Created ' + c.fromNow() + ' by <span class="glyphicon glyphicon-user"></span> ' + o + '.';

			if (this.foodle.updated) {
				var u = moment.unix(this.foodle.updated);
				str += ' Updated '+ u.fromNow();
			}


			statusline.append(str);

		}

	})

	return FoodleResponseController;

});