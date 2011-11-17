var Foodle_Group = function() {
	
	var 
		// Dependencies
		api = Foodle_API,
		
		// Local variables
		groupid;

	function setGroupid(sgroupid) {
		groupid = sgroupid;
	}
	
	
	function getFiles(callback) {
		console.log('Get file list');
		api.getData('/api/files/' + groupid, null, FOODLE.data.File, callback);
	}
	
	function getActivity(callback) {
		api.getData('/api/activity/group/' + groupid, null, FOODLE.data.Activity, callback);
	}
	
	function getMembers(callback) {
		api.getData('/api/contacts/' + groupid, null, FOODLE.data.Person, callback);
	}
	
	function getEvents(callback) {
		api.getData('/api/events/group/' + groupid, null, FOODLE.data.Event, callback);
	}

	

	
	return {
		// Initialization methods
		setGroupid: setGroupid,
		
		// Get data methods
		getFiles: getFiles,
		getMembers: getMembers,
		getActivity: getActivity,
		getEvents: getEvents
	};
	
}();


var Foodle_Group_View = function(groupid) {

	Foodle_Group.setGroupid(groupid);
	
	Foodle_Group.getFiles(showFiles);
	Foodle_Group.getMembers(showMembers);
	Foodle_Group.getActivity(showActivity);
	Foodle_Group.getEvents(showEvents);
	
	$("div#dropbox").dndUploader({
		url : '/api/upload/' + groupid,
		progress: $("div#dropbox div.progress"),
		callback: function() {
			console.log(' callback function initzed after completed update to get updated file list');
			setTimeout(function() {
				Foodle_Group.getFiles(showFiles);
			}, 400)
		}
	});

	/*
	 * Show a list of files in the <div class="filelist"> container.
	 */
	function showFiles(files) {
		var i;
		
		$("div.filelist").empty();	
		for(i = 0; i < files.length; i++) {
			$("div.filelist").append(files[i].view() );
		}
	}
	
	function showMembers(persons) {
		var i;
	
		$("div.foodle_contacts").empty();
		for(i = 0; i < persons.length; i++) {
			$("div.foodle_contacts").append( persons[i].view(false) );
		}
		if (persons.length === 0) {
			$("div.foodle_contacts").append('<p class="foodle_contacts_meta">No contacts is currently added to this contact list. Select contacts below to add.</p>');
		} else {
			$("div.foodle_contacts").append('<p class="foodle_contacts_meta">' + persons.length + ' contacts</p>');
		}
	}
	
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
			if (i > 25) break;
		}
	}

};


