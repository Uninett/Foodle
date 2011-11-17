var Foodle_Front_View = function() {

	var 
		// Dependencies
		api = Foodle_API,
		
		showEvents;


	showEvents = function showEvents(events, showall) {
		var 
			i, 
			showmore = '';

		if (typeof showall === 'undefined') {showall = false;}

		console.log('showevents');

		$("div#upcomming").empty();
		for(i = 0; i < events.length; i++) {
			$("div#upcomming").append( events[i].view(true) );
			if (!showall && i > 8) {
				showmore = $('<p>[ <a href="">Show more</a> ]</p>');
				$(showmore).click(function(e) {
					e.preventDefault();
					showEvents(events, true)
				});
				$("div#upcomming").append(showmore);
				break;
			}	
		}
		if (events.length === 0) {
			$("div#upcomming").append('<p>No Foodle events ahead. May be you should add one?</p>');
		}
	}
	
	
	function getActivity(callback) {
		api.getData('/api/activity', null, FOODLE.data.Activity, callback);
	}
	
	function getEvents(callback) {
		console.log('getevents()');
		api.getData('/api/events', null, FOODLE.data.Event, callback);
	}

	
	getActivity(showActivity);
	getEvents(showEvents);
	
	
	function showActivity(activities) {
		var i;
		$("div#activity").empty();
		for(i = 0; i < activities.length; i++) {
			$("div#activity").append( activities[i].view() );
		}
	}
	


};


