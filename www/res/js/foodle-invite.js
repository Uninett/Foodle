/* 	
	*******************
		INVITATION 
	*******************
*/

var Foodle_Invitation_View = function(foodleid) {

	var 
		// Dependencies
		api = Foodle_API,
		
		// Private variables
		currentList = null,
		allUsers = null;
	
	
	function getGroup(newgroup) {
	
		console.log('getGroup');
		console.log(newgroup);
	
		currentList = newgroup;
		allUsers = null;
		api.getData("/api/contacts/" + currentList.obj.id, {'exclude': foodleid}, FOODLE.data.Person, showMembers);
	}

	function getSearchlist(term) {
		allUsers = null;
		api.getData("/api/contacts/auto",  {'term' : term, 'exclude': foodleid}, FOODLE.data.Person, showMembers);
	}

	function getMemberlist() {
		allUsers = null;
		resetTerm();
		api.getData("/api/contacts/auto", null, FOODLE.data.Person, showMembers);
	}
	
	function getGrouplist() {
		api.getData("/api/contacts", null, FOODLE.data.Group, showGroups);
	}
	
	function getTerm() {
		return $("input#foodle_contact_search").attr('value');
	}
	
	function resetTerm() {
		$("input#foodle_contact_search").attr('value', '');
	}
	
	function refresh() {
		var term = getTerm();
		if (currentList !== null) {
			getGroup(currentList);
		} else if (term !== '') {
			getSearchlist(term);
		} else {
			getMemberlist();
		}
	}
	
	function addInvitedPending(user) {

		console.log('add pending user');
		console.log(user);
	
		var extra = '<img class="waiting" style="float: right" src="/res/spinning.gif" alt="waiting" /> ' +
					'<img class="complete" style="display: none; float: right" src="/res/maybe.png" alt="waiting" /> ';
		$("div#invited_list").prepend(user.view(true, null, extra));	
	
	}

	
	
	function invitationComplete(user) {
	
		console.log('Completed user');
		console.log(user);
		$("div#invited_list div.foodle_contact").each(function() {
			if(
				($(this).data('userid') == user.obj.userid) ||
				($(this).data('email') == user.obj.email)
			) {
				$(this).find("img.waiting").hide();
				$(this).find("img.complete").show();
			}
			console.log(this);
			console.log($(this).data('userid'));
		});
		refresh();
	}
	
	function inviteAllUsers() {
		var i;
		if (!allUsers) return;
		for (i = 0; i < allUsers.length; i++) {
			inviteUser(allUsers[i]);
		}
	}
	
	function inviteUser(user) {
		console.log('invite user');
		console.log(user);
				
		addInvitedPending(user);
		
		var inviteobj = {'foodle': foodleid};
		if (user.obj.userid) inviteobj.userid = user.obj.userid;
		if (user.obj.email) inviteobj.email = user.obj.email;
		
		console.log('Invite object is:' );
		console.log(inviteobj);
		
		api.getData("/api/invite", inviteobj, null, function() {
			invitationComplete(user);
		});

	}

	
	console.log('Loading invite API');
	
	getMemberlist();
	getGrouplist();

	$("input#foodle_contact_search").autocomplete({
		minLength: 0,
		source: function( request, response ) {
			var term = request.term;

			if (term.length === 1) return;
			getSearchlist(term);
			
			currentList = null;
			// refreshContactListSelection();
		}
	}).focus();

	
	function showMembers(persons) {
		var 
			i, 
			buttons;
		
		buttons = [
			{
				name: 'invite_btn',
				value: 'Invite',
				callback: inviteUser
			}
		];
		
		console.log('showMembers()');
		console.log(persons);
	
		$("div.foodle_autolist").empty();
		for(i = 0; i < persons.length; i++) {
			$("div.foodle_autolist").append( persons[i].view(true, buttons) );
			//console.log(persons[i]);
		}
		
		if (currentList !== null) {
			allUsers = persons;		
			$("div.foodle_autolist").append(
				$('<div class="foodle_invite_all">' + 
					'<input id="foodle_invite_all_btn" type="submit" value="Invite all the above users" />' +
				'</div>').click(inviteAllUsers)
			);
//			$("div.foodle_autolist").find("input#foodle_invite_all_btn").click(inviteAll);
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
	
	

};


