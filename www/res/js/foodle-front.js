var Foodle_Front_View = function() {

	var 
		// Dependencies
		api = Foodle_API;

	function getActivity(callback) {
		api.getData('/api/activity', null, FOODLE.data.Activity, callback);
	}
	
	function getEvents(callback) {
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
	
	function showEvents(events) {
		var i;
		$("div#upcomming").empty();
		for(i = 0; i < events.length; i++) {
			$("div#upcomming").append( events[i].view() );
			if (i > 12) break;
		}
	}

};


