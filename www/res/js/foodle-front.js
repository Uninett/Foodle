var Foodle_Front_View = function() {

	var 
		// Dependencies
		api = Foodle_API;

	function getActivity(callback) {
		api.getData('/api/activity', null, FOODLE.data.Activity, callback);
	}
	
	getActivity(showActivity);
	
	
	function showActivity(activities) {
		var i;
		$("div#activity").empty();
		for(i = 0; i < activities.length; i++) {
			$("div#activity").append( activities[i].view() );
		}
	}

};


