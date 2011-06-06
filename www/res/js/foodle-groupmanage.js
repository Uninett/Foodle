/* 	
		Foodle Group Management 
*/

var Foodle_GroupManage_View = function(foodleid) {

	var 
		// Dependencies
		api = Foodle_API,
		utils = FOODLE.utils,
		
		// Private variables
		currentList = null;
	
	
	/*
	 * Initalization phase 
	 */
	
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
			getSearchlist(term);
		}
	}).focus();
	searchOptionEnable(false);
	$("form#foodle_form_add_contactlist").submit(addContactlist);
	$("select#foodle_contact_lookuptype").change(selectContactlisttype);

	
	
	getGrouplist();
	getFoodlelist();
	
	if (foodleid) {
		addOneFoodle(foodleid);
	} else {
		getAutolist();
	}

	$("form#foodle_form_add_contactlist").submit(addContactlist);
	
	/*
	 * End: Initalization phase 
	 * - - - - - - - - - - - - -
	 * A list of methods that are used on the group management page.
	 */

	function addContactlist(event) {
		event.preventDefault();
		var newName = $("input#foodle_add_contactlist_name").attr('value');
		$("input#foodle_add_contactlist_name").attr('value', '');
		
		api.getData("/api/contacts", {newlist: newName}, getGrouplist);
	}
	
	function resetCurrentGroup() {
		currentList = null;
		refreshContactListSelection();
		updateContactlistPicklist();
		$("div.foodle_contacts").empty();
	}
	
		
	// List of recent foodles by owner
	function getFoodlelist () {			
		api.getData("/api/foodlelist", null, null, showFoodleList);
	}
	
	function addOneFoodle (foodleid) {
		//console.log('add one foodle ' + foodleid);
		api.getData("/api/foodle/" + foodleid, null, null, function(data) {
			//console.log('addOneFoodle response');
			//console.log('addOneFoodle data ' + data.identifier + '   ' +  data.name);				
			//console.log(data);
			addOneFoodleEntry(data.identifier, data.name);
			
		});
		
	}

	
	function addOneFoodleEntry(id, name) {
		$("select#foodle_contact_lookuptype").append(
			'<option selected="selected" class="onefoodlelist" value="' + escape(id) + '">from Foodle [' + name + ']</option>'
		);
		$("select#foodle_contact_lookuptype").change();
	}
	
	function removeGroup(group) {
		resetCurrentGroup();
		api.getData("/api/contacts", {removelist: group.obj.id}, null, getGrouplist );
	}
	
	function addUser(user) {
		api.getData("/api/contacts/" + currentList.obj.id, {adduser: user.obj.userid}, null, function() { getGroup(currentList) } );
	}

	function removeUser(user) {
		api.getData("/api/contacts/" + currentList.obj.id, {removeuser: user.obj.userid}, null, function() { getGroup(currentList) } );
	}

	function setMembershipRole(user, role) {
		if (role !== 'member' && role !== 'admin') throw new Exception('Invalid membership role');
		api.getData("/api/contacts/" + currentList.obj.id, {setrole: role, user: user.obj.userid}, null, function() { getGroup(currentList) } );
	}

	function promoteUser(user) {
		setMembershipRole(user, 'admin');
	}
	
	function demoteUser(user) {
		setMembershipRole(user, 'member');
	}
	
	
	function getGroup(newgroup) {
		currentList = newgroup;
		refreshContactListSelection();
		allUsers = null;
		//console.log('get group');
		//console.log(currentList);
		api.getData("/api/contacts/" + currentList.obj.id, null, FOODLE.data.Person, showMembers);
	}
		
	function refreshContactListSelection() {
		var matchList = undefined;
		if(currentList) matchList = currentList.obj.id;
		$("div.foodle_contactlist").each(function() {
			if ($(this).data('id') == matchList) {
				$(this).addClass("foodle_contactlist_selected");
			} else {
				$(this).removeClass("foodle_contactlist_selected");
			}
			//console.log(this);
		});
	}

	function getSearchlist(term) {
		var params = {term: term};
		if (currentList) {
			params.excludeList = currentList.obj.id;
		}
		api.getData("/api/contacts/auto",  params, FOODLE.data.Person, showAutolist);
	}

	function getAutolist() {
		var params = {};
		if (currentList) {
			params.excludeList = currentList.obj.id;
		}
		api.getData("/api/contacts/auto", params, FOODLE.data.Person, showAutolist);
	}
	
	function getGrouplist() {
		api.getData("/api/contacts", null, FOODLE.data.Group, showGroups);
	}
	
	function getFoodleResponders (foodleid) {
		var params = {};
		if (currentList) {
			params.excludeList = currentList.obj.id;
		}
		api.getData("/api/contacts/foodle:" + foodleid, params, FOODLE.data.Person, showAutolist);
	}
	
	function getTerm() {
		return $("input#foodle_contact_search").attr('value');
	}
	
	function resetTerm() {
		$("input#foodle_contact_search").attr('value', '');
	}


	function searchOptionEnable(what) {
		if (what) {
			$("option#foodle__search__").removeAttr('disabled');
		} else {
			$("option#foodle__search__").attr('disabled', 'disabled');
		}
	}
		
	function updateContactlistPicklist() {
		var type = $("select#foodle_contact_lookuptype").val();
		
		//console.log('update picklist ' + type);

		if (type === '__auto__') {
			getAutolist();
			searchOptionEnable(false);
			$("input#foodle_contact_search").attr('value', '');
			
		} else if (type === '__search__') {
			getSearchlist($("input#foodle_contact_search").val());
			
			// Should not be possible to select search item unless you actually do a search
		} else {
			searchOptionEnable(false);
			$("input#foodle_contact_search").attr('value', '');
			getFoodleResponders(type);
		}
	}

	function selectContactlisttype(event) {
		event.preventDefault();
		updateContactlistPicklist();
	}



	function prepareButtons(person) {
		var buttons = [];
		
		
		if (person.obj.membership !== 'owner' && 
				(currentList.obj.role == 'owner' || currentList.obj.role == 'admin' ) ) {
			buttons.push(
				{
					name: 'remove_btn',
					value: 'Remove',
					float: 'right',
					callback: getUserSpecificCallback(person, removeUser)
				}
			);
		};
		
		if (person.obj.membership === 'member' && currentList.obj.role == 'owner') {
			buttons.push(
				{
					name: 'promote_btn',
					value: 'Promote',
					float: 'right',
					callback: getUserSpecificCallback(person, promoteUser)
				}
			);
		}

		if (person.obj.membership === 'admin' && currentList.obj.role == 'owner') {
			buttons.push(
				{
					name: 'demote_btn',
					value: 'Demote',
					float: 'right',
					callback: getUserSpecificCallback(person, demoteUser)
				}
			);
		}
		
		return buttons;
	}
	
	function showMembers(persons) {
		var 
			i, 
			count,
			buttons;
		
		// Also refresh autolist, to get the disabled buttons right.
		updateContactlistPicklist();		
		
		//console.log('showMembers()');
		//console.log(persons);
	
		$("div.foodle_contacts").empty();
		
		$("div.foodle_contacts").append('<h2>' + utils.escape(currentList.obj.name) + '</h2>');
		$("div.foodle_contacts").append('<p><a href="/group/' + utils.escape(currentList.obj.id) + '">Go to group page</p>');
		
		for(i = 0; i < persons.length; i++) {
		
			$("div.foodle_contacts").append( persons[i].view(true, prepareButtons(persons[i]) ) );

		}
		
		count = persons.length;
		
		if (count === 0) {
			$("div.foodle_contacts").append('<p class="foodle_contacts_meta">No contacts is currently added to this contact list. Select contacts below to add.</p>');
		} else {
			$("div.foodle_contacts").append('<p class="foodle_contacts_meta">' + count + ' contacts</p>');
		}
		
		
		var removeCallback = getGroupSpecificCallback(currentList, removeGroup);
	
		if (currentList.obj.role === 'owner') {
			$("div.foodle_contacts").append(
				$('<div class="foodle_removecontactlist"></div>').
					prepend(
						$('<input id="foodle_button_removecontactlist" type="submit" value="Remove contactlist" /> ').click(removeCallback)
					).append('If you remove the whole contactlist there is no way to restore that list.')
			);
		}
		
		
		if (currentList.obj.role === 'owner' || currentList.obj.role === 'admin') {
			if (currentList.obj.inviteToken) {
				$("div.foodle_contacts").append(
					'<div class="foodle_invitetoken">' +
						'<h3>User invitation</h3>' +
						'<p>Users that are directed to this magic URL are automatically assigned to this group as members:</p>' +
						'<pre><code>https://foodl.org/group-invite/' + escape(currentList.obj.id)  + '/' + utils.escape(currentList.obj.inviteToken) + '</code></pre>' +
					'</div>'
				);
			}
		}
		

	}
	
	function getUserSpecificCallback(user, callback) {
		return function() {
			callback(user);
		};
	}
	
	function getGroupSpecificCallback(group, callback) {
		return function() {
			callback(group);
		};
	}
	
	
	function showAutolist(persons) {
		var 
			i, 
			buttons;
		
	
		$("div.foodle_autolist").empty();
		for(i = 0; i < persons.length; i++) {
			
			buttons = [];
			if (currentList && currentList.obj.role !== 'member') {
				buttons = [
					{
						name: 'add_btn',
						value: 'Add to group',
						float: 'right',
						callback: getUserSpecificCallback(persons[i], addUser)
					}
				];
			};
		
			$("div.foodle_autolist").append( persons[i].view(true, buttons ) );
			//console.log(persons[i]);
		}

	}
	
	
	function showGroups(groups) {
		
		var 
			i;
		
		$("div.foodle_contactlists").empty();
		for(i = 0; i < groups.length; i++) {
			$("div.foodle_contactlists").append( groups[i].view(getGroup) );
		}
		
	}
	
	
	function showFoodleList(foodlelist) {
		//console.log('Foodle list response');
		//console.log(foodlelist);
	
		$("select#foodle_contact_lookuptype option.foodlelist").remove();
		
		for(var i = 0; i < foodlelist.length; i++) {
			$("select#foodle_contact_lookuptype").append(
				'<option class="foodlelist" value="' + escape(foodlelist[i].id) + '">from Foodle [' + foodlelist[i].name + ']</option>'
			);
		}

	}
	
};
