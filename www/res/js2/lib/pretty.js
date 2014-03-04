define(function(require) {

	var jQuery = require('jquery');
	var prettydate = {};

	/*
	 * JavaScript Pretty Date
	 * Copyright (c) 2011 John Resig (ejohn.org)
	 * Licensed under the MIT and GPL licenses.
	 */

	// Takes an ISO time and returns a string representing how
	// long ago the date represents.
	prettydate.prettyDate = function(time){

		if (time < 4102488000) {
			time = time *1000;
		}

		var date = new Date(time),
			diff = (((new Date()).getTime() - date.getTime()) / 1000),
			day_diff = Math.floor(diff / 86400);
				
		if ( isNaN(day_diff) || day_diff < 0 ) return;
				
		return day_diff == 0 && (
				diff < 60 && "just now" ||
				diff < 120 && "1 minute ago" ||
				diff < 3600 && Math.floor( diff / 60 ) + " minutes ago" ||
				diff < 7200 && "1 hour ago" ||
				diff < 86400 && Math.floor( diff / 3600 ) + " hours ago") ||
			day_diff == 1 && "Yesterday" ||
			day_diff < 7 && day_diff + " days ago" ||
			Math.ceil( day_diff / 7 ) + " weeks ago" ;
	}

	prettydate.prettyUntil = function(time){
		var 
			diff = ((time.valueOf() - new Date().getTime()) / 1000),
			day_diff = Math.ceil(diff / 86400);
				
		if ( isNaN(day_diff) || day_diff < 0 ) return 'in the past';
		if (diff < 0) return 'in the past';
				
		return day_diff == 0 && (
				diff < 60 && "right now" ||
				diff < 120 && "in one minute" ||
				diff < 3600 && "in " + Math.floor( diff / 60 ) + " minutes" ||
				diff < 7200 && "in one hour" ||
				diff < 86400 && "in " + Math.floor( diff / 3600 ) + " hours") ||
			day_diff == 1 && "Tomorrow" ||
			day_diff < 7 && "in " + day_diff + " days" ||
			"in " + Math.ceil( day_diff / 7 ) + " weeks" ;
	}


	prettydate.prettyInterval = function(time){
		
		var diff = time /1000,
			day_diff = Math.floor(diff / 86400);
				
		if ( isNaN(day_diff) || day_diff < 0 ) return;
				
		return day_diff == 0 && (
				diff < 60 && Math.floor( diff ) + " seconds before" ||
				diff < 120 && "1 minute before" ||
				diff < 3600 && Math.floor( diff / 60 ) + " minutes before" ||
				diff < 7200 && "1 hour ago" ||
				diff < 86400 && Math.floor( diff / 3600 ) + " hours before") ||
			day_diff == 1 && "the day before" ||
			day_diff < 7 && day_diff + " days before" ||
			day_diff < 31 && Math.ceil( day_diff / 7 ) + " weeks before";
	}

	jQuery.fn.prettyDate = function(){
		return this.each(function(){
			var ts = parseInt($(this).data('ts'), 10);
			var date = prettydate.prettyDate(ts);
			// console.log("Processing", ts, this, date)
			if ( date )
				$(this).empty().append( date );
		});
	};

	return prettydate;

});