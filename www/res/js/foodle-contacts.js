var currentList = null;
var doOpenLastList = false;




function refreshContactListSelection() {
	var matchList = undefined;
	if(currentList) matchList = currentList.id;
	$("div.foodle_contactlist").each(function() {
		if ($(this).data('id') == matchList) {
			$(this).addClass("foodle_contactlist_selected");
		} else {
			$(this).removeClass("foodle_contactlist_selected");
		}
		console.log(this);
	});
}
	

function openListFromEvent(event) {
	var listid = $(event.target).data('id');
	var listname = $(event.target).data('name');
	var listrole = $(event.target).data('role');
	var inviteToken = $(event.target).data('inviteToken');
	openList(listid, listname, listrole, inviteToken);
}


function openList(listid, listname, listrole, inviteToken) {
	console.log('open list [' + listid + '] [' + listname + ']');

	Foodle_Contacts.getContactlist(listid);
	currentList = {name: listname, id: listid, role: listrole};
	if (inviteToken) currentList.inviteToken = inviteToken;
	
	refreshContactListSelection();
 	updateContactlistPicklist();
}


function openLastList() {
	var lastElement = $("div.foodle_contactlist").last();
	if (!lastElement) return;
	
	var listid = $(lastElement).data('id');
	var listname = $(lastElement).data('name');
	openList(listid, listname);
}



function addUser(a) {
	console.log($(a.target).data('userid'));
}

function autolist(contacts) {
//	console.log('Autolist called');

	var buttons = [];
	if (currentList) {
		if (currentList.role === 'owner' || currentList.role === 'admin') {
			buttons.push({
				'class': 'foodle_adduser',
				text: 'Add user'
			});
		}
	}

	$("div.foodle_autolist").empty();
	for(var userid in contacts) {
		$("div.foodle_autolist").append(Foodle_Contacts_Utils.contactHTML(contacts[userid], buttons));
	}

	$(".foodle_adduser").click(function(event) {
		var userid = $(event.target.parentNode).data('userid');
		Foodle_Contacts.addUser(userid, currentList.id);
		updateContactlistPicklist();
	});

}

function contacts(contacts) {

	var buttons;
	var count = 0;

	$("div.foodle_contacts").empty();
	$("div.foodle_contacts").append('<h2>' + currentList.name + '</h2>');
	for(var userid in contacts) {
		count++;
		
		buttons = [];
		
		if (currentList.role === 'owner' || currentList.role === 'admin') {
			buttons.push({
				'class': 'foodle_removeuser',
				text: 'Remove'
			});
		}
		
		if (contacts[userid].membership) {
			if (currentList.role === 'owner' || currentList.role === 'admin') {
				if (contacts[userid].membership === 'member') {
					buttons.push({
						'class': 'foodle_promoteuser',
						text: 'Promote'
					});
				} else if(contacts[userid].membership === 'admin') {
					buttons.push({
						'class': 'foodle_demoteuser',
						text: 'Demote'
					});
				
				} else if(contacts[userid].membership === 'owner') {
					buttons = [];
				}
			}
		}
		
		
		
		$("div.foodle_contacts").append(Foodle_Contacts_Utils.contactHTML(contacts[userid], buttons));
	}
	
	if (count === 0) {
		$("div.foodle_contacts").append('<p class="foodle_contacts_meta">No contacts is currently added to this contact list. Select contacts below to add.</p>');
	} else {
		$("div.foodle_contacts").append('<p class="foodle_contacts_meta">' + count + ' contacts</p>');
	}
	
	$(".foodle_removeuser").click(function(event) {
		var userid = $(event.target.parentNode).data('userid');	
		Foodle_Contacts.removeUser(userid, currentList.id);
		updateContactlistPicklist();
	});
	
	$(".foodle_promoteuser").click(function(event) {
		var userid = $(event.target.parentNode).data('userid');	
		Foodle_Contacts.setMembershipRole(userid, currentList.id, 'admin');
		updateContactlistPicklist();
	});
	$(".foodle_demoteuser").click(function(event) {
		var userid = $(event.target.parentNode).data('userid');	
		Foodle_Contacts.setMembershipRole(userid, currentList.id, 'member');
		updateContactlistPicklist();
	});

	if (currentList.role === 'owner') {
		$("div.foodle_contacts").append(
			'<div class="foodle_removecontactlist">' +
				'<input id="foodle_button_removecontactlist" type="submit" value="Remove contactlist" /> ' +
				'If you remove the whole contactlist there is no way to restore that list.' +
			'</div>'
		);
	}
	
	
	if (currentList.role === 'owner' || currentList.role === 'admin') {
		if (currentList.inviteToken) {
			$("div.foodle_contacts").append(
				'<div class="foodle_invitetoken">' +
					'<h3>User invitation</h3>' +
					'<p>Users that are directed to this magic URL are automatically assigned to this group as members:</p>' +
					'<pre><code>https://foodl.org/group-invite/' + currentList.id  + '/' + currentList.inviteToken + '</code></pre>' +
				'</div>'
			);
		}
	}
	
	$("input#foodle_button_removecontactlist").click(function() {
// 		doOpenLastList = false;
		$("div.foodle_contacts").empty();
		Foodle_Contacts.removeContactlist(currentList.id);
	});
	
}


function foodlelist(foodlelist) {
	
	console.log('Foodle list response');
	console.log(foodlelist);
	
	$("select#foodle_contact_lookuptype option.foodlelist").remove();
	
	for(var i = 0; i < foodlelist.length; i++) {
		$("select#foodle_contact_lookuptype").append(
			'<option class="foodlelist" value="' + escape(foodlelist[i].id) + '">from Foodle [' + foodlelist[i].name + ']</option>'
		);
	}
	
	
}

function lists(lists) {

	$("div.foodle_contactlists").empty();
	for(var i = 0 ; i < lists.length; i++) {
		console.log(lists[i]);
		$("div.foodle_contactlists").append(Foodle_Contacts_Utils.listHTML(lists[i]));
	}
	
// 	if (doOpenLastList) {
// 		openLastList();
// 	}
// 	doOpenLastList = true;
}

function addContactlist(event) {

	event.preventDefault();
	
	var newName = $("input#foodle_add_contactlist_name").attr('value');
	$("input#foodle_add_contactlist_name").attr('value', '')
	Foodle_Contacts.addContactlist(newName);
}

function selectContactlisttype(event) {
	event.preventDefault();
	updateContactlistPicklist();
}

function updateContactlistPicklist() {
	var type = $("select#foodle_contact_lookuptype").val();
	
	console.log('update picklist ' + type);
	
	var excludeList;
	if (currentList) excludeList = currentList.id;
	
	if (type === '__auto__') {
		Foodle_Contacts.autolist(undefined, excludeList);
		searchOptionEnable(false);
		$("input#foodle_contact_search").attr('value', '');
	} else if (type === '__search__') {		
		Foodle_Contacts.autolist($("input#foodle_contact_search").val(), excludeList );
		// Should not be possible to select search item unless you actually do a search
	} else {
		searchOptionEnable(false);
		$("input#foodle_contact_search").attr('value', '');
		Foodle_Contacts.getFoodleResponders(type, excludeList);
	}
}

function searchOptionEnable(what) {
	if (what) {
		$("option#foodle__search__").removeAttr('disabled');
	} else {
		$("option#foodle__search__").attr('disabled', 'disabled');
	}
}

function addOneFoodle(id, name) {
	$("select#foodle_contact_lookuptype").append(
		'<option selected="selected" class="onefoodlelist" value="' + escape(id) + '">from Foodle [' + name + ']</option>'
	);
	$("select#foodle_contact_lookuptype").change();
}

$(document).ready(function() {


	Foodle_Contacts.registerAutolistHandler(autolist);
	Foodle_Contacts.registerListsHandler(lists);
	Foodle_Contacts.registerFoodlelistHandler(foodlelist);
	Foodle_Contacts.registerContactsHandler(contacts);
	
	Foodle_Contacts.getContactlists();
	Foodle_Contacts.autolist();
	Foodle_Contacts.getFoodlelist();
	
	$("input#foodle_contact_search").autocomplete({
		minLength: 0,
		source: function( request, response ) {
			var term = request.term;
			
			var excludeList;
			if (currentList) excludeList = currentList.id;
			
			if (term.length < 2) {
				$("select#foodle_contact_lookuptype").val('__auto__');
				searchOptionEnable(false);
			} else {
				searchOptionEnable(true);
				$("select#foodle_contact_lookuptype").val('__search__');
			}
			
			if (term.length === 1) return;
			Foodle_Contacts.autolist(term, excludeList);
		}
	}).focus();
	searchOptionEnable(false);
	$("form#foodle_form_add_contactlist").submit(addContactlist);
	$("select#foodle_contact_lookuptype").change(selectContactlisttype);
	
});