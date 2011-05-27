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
			$.getJSON("/api/contacts/" + listid, {adduser: userid}, getContactlistResponse);
		},
		
		removeUser: function(userid, listid) {
			console.log('Removing user ' + userid + ' from list ' + listid);
			$.getJSON("/api/contacts/" + listid, {removeuser: userid}, getContactlistResponse);
		},
		
		setMembershipRole: function(userid, listid, role) {
			if (role !== 'member' && role !== 'admin') throw new Exception('Invalid membership role');
			$.getJSON("/api/contacts/" + listid, {setrole: role, user: userid}, getContactlistResponse);
		},
		
		addContactlist: function(name) {
			$.getJSON("/api/contacts", {newlist: name}, getContactlistsResponse);
		},
		
		removeContactlist: function(listid) {
			$.getJSON("/api/contacts", {removelist: listid}, getContactlistsResponse);
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
			$.getJSON("/api/contacts", null, getContactlistsResponse);
		},
		
		// Get a specific contactlist.
		getContactlist: function(listid, foodleid) {
			console.log('Contacting ' + "/api/contacts/" + listid);
			var excludes = null;
			if (foodleid) excludes = {exclude: foodleid};
			$.getJSON("/api/contacts/" + listid, excludes, getContactlistResponse);
		},
		
		getFoodleResponders: function(foodleid, listid) {
			$.getJSON("/api/contacts/foodle:" + foodleid, {'excludeList': listid}, getAutolistResponse);
		},
		
		// List auto contacts (may include search terms)
		autolist: function(term, listid, foodleid) {			
			$.getJSON("/api/contacts/auto", {'term' : term, 'exclude': foodleid, 'excludeList': listid}, getAutolistResponse);
		},
		
		// List of recent foodles by owner
		getFoodlelist: function() {			
			$.getJSON("/api/foodlelist", null, getFoodlelistResponse);
		},
		
		addOneFoodle: function(foodleid, callback) {
			console.log('add one foodle ' + foodleid);
			$.getJSON("/api/foodle/" + foodleid, null, function(data) {
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


var Foodle_Contacts_Utils = {
	
	contactHTML: function(contact, buttons, extra) {
	
		// console.log(contact);
	
		var html = '';	
		var userpage = null;
		
		if (contact.token) {
			userpage = '/user/' + $userid + '?token=' + contact.token;
		}
		

		if(contact.name) {
			switch(contact.membership) {
	
				case 'owner':
					html = '<img style="position:relative; bottom: -2px" src="/res/user_red.png" alt="User profile" />' + contact.name;
	
					break;
	
				case 'admin':
					html = '<img style="position:relative; bottom: -2px" src="/res/user_suit.png" alt="User profile" />' + contact.name;
					break;
			
				case 'member':
				default:
					html = '<img style="position:relative; bottom: -2px" src="/res/user_grey.png" alt="User profile" />' + contact.name;				
			} 
		} else {
			contact.name = '';
//			html = '<img style="position:relative; bottom: -2px" src="/res/mail16.png" alt="User profile" />' + contact.name;				
		}
	

	
		
		if (userpage) {		
			html = '<a href="' + userpage + '">' + html  + '</a>';
		}
		
		if (contact.twitter) {
			html = html + ' (<a href="http://twitter.com/' + contact.twitter + '">@' + contact.twitter + '</a>)';
		} else if (contact.email) {
			html = html + '<span style="font-size: 90%; color: #666"> (<img style="position:relative; bottom: -2px" src="/res/mail16.png" alt="User profile" /> ' + contact.email + ')</a>'
		} else {
			html = html + ' (' + contact.userid + ')'
		}
		
		if (buttons) {
		
			for(var i = 0; i < buttons.length; i++) {
				var button = buttons[i];
				if (contact.disabled) {
					html = '<input type="submit" disabled="disabled" class="contactButton ' + button['class'] + '" value="' + button.text + '" />' + html;
				} else {
					html = '<input type="submit" class="contactButton ' + button['class'] + '" value="' + button.text + '" />' + html;
				}
			}
		
		}
		
		if (extra) {
			html = html + extra;
		}
		

	
		
		html = $('<div rel="' + contact.userid + '" class="foodle_contact" >' + html + '</div>').data({
			'userid': contact.userid,
			'name': contact.name,
			'email': contact.email
		}); //.click(addUser);
		return html;
	},
		
	listHTML: function (list) {
		
		var html = '';
		
		switch(list.role) {

			case 'owner':
				html = '<img src="/res/group.png" alt="User profile" />' + list.name;

				break;

			case 'admin':
				html = '<img src="/res/group_pale.png" alt="User profile" />' + list.name;
				break;
		
			case 'member':
			default:
				html = '<img src="/res/group_grey.png" alt="User profile" />' + list.name;				
		} 
		
//		html = '<img src="/res/group.png" alt="Contact list" /> ' + list.name;
		html = $('<div rel="' + list.id + '" class="foodle_contactlist">' + html + '</div>').
			data({id: list.id, name: list.name, role: list.role, inviteToken: list.inviteToken}).click(openListFromEvent);
		return html;
	}
	
	
};
