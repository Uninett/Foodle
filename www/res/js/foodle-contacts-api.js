var Foodle_Contacts = function() {
	
	var contacts, autolist;
	var lists;
	var foodlelist;
	var autolistHandler, constactsHandler, listsHandler, foodlelistHandler, errorHandler;
	
	// List of contacts is updated
	function updateContacts(newcontacts) {
		contacts = newcontacts;
		if (constactsHandler) {
			constactsHandler(contacts);
		}
	}
	
	// List of contacts is updated
	function updateAutolist(newautolist) {
		autolist = newautolist;
		if (autolistHandler) {
			autolistHandler(autolist);
		}
	}
	
	// List of lists is updated
	function updateLists(newlists) {
		lists = newlists;
		if (listsHandler) {
			listsHandler(lists);
		}
	}
	
	// Update foodle list
	function updateFoodlelist(newFoodlelist) {
		foodlelist = newFoodlelist;
		if (foodlelistHandler) {
			foodlelistHandler(foodlelist);
		}
	}
	
	
	// Error occured.
	function error(message) {
		console.log('Error with Foodle Contacts API: ' + message);
		if (errorHandler) {
			errorHandler(message);
		}
	}
	
	
	// Retrieve a list of contactlists owned by the current user
	function getContactlistsResponse(data) {
		if (data.status == 'ok' && data.data) {
			updateLists(data.data);
		} else {
			error('Error retrieving contactlists ' + data.message);
		}
	}
	
	// Retrieve a list of contacts in a specific list
	function getContactlistResponse(data) {
		if (data.status == 'ok' && data.data) {
			updateContacts(data.data);
		} else {
			error('Error retrieving contactlist ' + data.message);
		}
	}
	
	// List auto contacts (may include search terms)
	function getAutolistResponse(data) {
		if (data.status == 'ok' && data.data) {
			updateAutolist(data.data);
		} else {
			error('Error retrieving contacts [from autolist] ' + data.message);
		}
	}
	
	// List of recent foodles by owner
	function getFoodlelistResponse(data) {
		if (data.status == 'ok' && data.data) {
			updateFoodlelist(data.data);
		} else {
			error('Error retrieving contacts [from foodle] ' + data.message);
		}
	}
	
	
	
	return {
	
		addUser: function(userid, listid) {
			console.log('Adding user ' + userid + ' to list ' + listid);
			$.getJSON("/api/contacts/" + listid, {userToken: FoodleAPIuserToken, adduser: userid}, getContactlistResponse);
		},
		
		removeUser: function(userid, listid) {
			console.log('Removing user ' + userid + ' from list ' + listid);
			$.getJSON("/api/contacts/" + listid, {userToken: FoodleAPIuserToken, removeuser: userid}, getContactlistResponse);
		},
		
		setMembershipRole: function(userid, listid, role) {
			if (role !== 'member' && role !== 'admin') throw new Exception('Invalid membership role');
			$.getJSON("/api/contacts/" + listid, {userToken: FoodleAPIuserToken, setrole: role, user: userid}, getContactlistResponse);
		},
		
		addContactlist: function(name) {
			$.getJSON("/api/contacts", {userToken: FoodleAPIuserToken, newlist: name}, getContactlistsResponse);
		},
		
		removeContactlist: function(listid) {
			$.getJSON("/api/contacts", {userToken: FoodleAPIuserToken, removelist: listid}, getContactlistsResponse);
		},
	
		
		// Register a callback that will emit when the an error occur
		registerErrorHandler: function(newListener) {
			errorHandler = newListener;
		},
		
		// Register a callback that will emit when the list of contacts is updated...
		registerContactsHandler: function(newListener) {
			constactsHandler = newListener;
		},
		
		// Register a callback that will emit when the list of contacts is updated...
		registerAutolistHandler: function(newListener) {
			autolistHandler = newListener;
		},
		
		// Register a callback that will emit when the list of contacts is updated...
		registerFoodlelistHandler: function(newListener) {
			foodlelistHandler = newListener;
		},
		
		// Register a callback that will emit when the list of contactlists is updated....
		registerListsHandler: function(newListener) {
			listsHandler = newListener;
		},
		
		// Retrieve a list of contactlists owned by the current user
		getContactlists: function() {
			$.getJSON("/api/contacts", {userToken: FoodleAPIuserToken}, getContactlistsResponse);
		},
		
		// Get a specific contactlist.
		getContactlist: function(listid, foodleid) {
			console.log('Contacting ' + "/api/contacts/" + listid);
			var parameters = { userToken: FoodleAPIuserToken };
			if (foodleid) parameters.exclude = foodleid;
			$.getJSON("/api/contacts/" + listid, parameters, getContactlistResponse);
		},
		
		getFoodleResponders: function(foodleid, listid) {
			$.getJSON("/api/contacts/foodle:" + foodleid, {userToken: FoodleAPIuserToken, 'excludeList': listid}, getAutolistResponse);
		},
		
		// List auto contacts (may include search terms)
		autolist: function(term, listid, foodleid) {			
			$.getJSON("/api/contacts/auto", {userToken: FoodleAPIuserToken, 'term' : term, 'exclude': foodleid, 'excludeList': listid}, getAutolistResponse);
		},
		
		// List of recent foodles by owner
		getFoodlelist: function() {			
			$.getJSON("/api/foodlelist", {userToken: FoodleAPIuserToken}, getFoodlelistResponse);
		},
		
		addOneFoodle: function(foodleid, callback) {
			console.log('add one foodle ' + foodleid);
			$.getJSON("/api/foodle/" + foodleid, {userToken: FoodleAPIuserToken}, function(data) {
				console.log('addOneFoodle response');
			
				if (data.status == 'ok' && data.data) {
					console.log('addOneFoodle data ' + data.data.identifier + '   ' +  data.data.name);				
					console.log(data.data);
					callback(data.data.identifier, data.data.name);
				} else {
					error('Error retrieving oneFoodle ' + data.message);
				}
				
			});
			
		}
		
	};
	
}();

