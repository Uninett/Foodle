/* 	
 *	Foodle Profile Calendar Setup
 */

var Foodle_ProfileCalendars_View = function() {

	var 
		// Dependencies
		api = Foodle_API,
		utils = FOODLE.utils,
		
		// Private variables
		currentList = null;
	
	$("input#addcalendarurl").click(addCalendarURL);
	
	getCalendars();

	/*
	 * End: Initalization phase 
	 * - - - - - - - - - - - - -
	 * A list of methods that are used on the group management page.
	 */

	function addCalendarURL(event) {
		event.preventDefault();
		var newURL = $("input#newcalendarurl").attr('value');
		$("input#newcalendarurl").attr('value', '');
		
		api.getData("/api/profile-calendars", {newcalendar: newURL}, null, showCalendars);
	}


	// List of recent foodles by owner
	function getCalendars () {			
		api.getData("/api/profile-calendars", null, null, showCalendars);
	}
	
	

	function removeCalendar(calendar) {
		api.getData("/api/profile-calendars", {removecalendar: calendar.src}, null, showCalendars );
	}

	function switchCalendar(calendar) {
		api.getData("/api/profile-calendars", {switchcalendar: calendar.src}, null, showCalendars );
	}


	function getRemoveCalendar(cal) {
		return function(event) {			
			event.preventDefault();
			event.stopPropagation();
			console.log('Calendar');
			console.log(cal);
			removeCalendar(cal);	
		}
	}
	
	function getSwitchCalendar(cal) {
		return function(event) {			
			event.preventDefault();
			event.stopPropagation();
			switchCalendar(cal);	
		}
	}


	function showCalendars(calendars) {
		
		var
			i,
			count,
			chtml,
			checked, checkedtext, current;

		count = calendars.length;
		
		$("div#usercalendars").empty();
		
		console.log('Show calendars');
		console.log(calendars);
		
		for(i = 0; i < count; i++) {
		
			current = calendars[i];
		
			console.log('show');
			console.log(current);
		
			chtml = '';
			checked = (calendars[i].include ? 'checked="checked"' : '');
			checkedtext = (calendars[i].include ? '<span style="color: #393">Active</span>' : '<span style="color: #933">Inactive</span>');
			
			chtml = '<input type="checkbox" id="enable-calendar-' + i + '" name="enable-calendar-' + i + '" ' + checked + ' /> ' + checkedtext;
			if (calendars[i].type === 'user') {
				chtml = chtml + '<input style="float: right" type="submit" id="remove-calendar-' + i + '" name="remove-calendar-' + i + '" value="Remove" />';
			}
			chtml = chtml + '<br /><tt>' + FOODLE.utils.escape(calendars[i].src) + '</tt>';
			
			chtml = '<div style="margin-top: 15px; border-bottom: 1px solid #eee">' + chtml + '</div>';
			
			chtml = $(chtml);
			$(chtml).find("input#remove-calendar-" + i).click(getRemoveCalendar(current) );
			$(chtml).find("input#enable-calendar-" + i).change(getSwitchCalendar(current) );
			
			$("div#usercalendars").append(chtml);
		}
		
	}


};
