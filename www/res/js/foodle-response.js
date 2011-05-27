/* 	
	*******************
		INVITATION 
	*******************
*/

var currentList = null;




function autolist(contacts) {
//	console.log('Autolist called');

	var button =  [{
		'class': 'foodle_inviteuser',
		text: 'Invite user'
	}];

	console.log('currentlist');
	console.log(currentList);


	$("div.foodle_autolist").empty();
	for(var userid in contacts) {
		//console.log(contacts[userid]);
		$("div.foodle_autolist").append(Foodle_Contacts_Utils.contactHTML(contacts[userid], button));
	}
	if (button) {
		$(".foodle_inviteuser").click(function(event) {
			var user = {
				userid: $(event.target.parentNode).data('userid'),
				name: $(event.target.parentNode).data('name'),
				email: $(event.target.parentNode).data('email')
			};
			inviteUser(user);
//			updateContactlistPicklist();
		});
	}
	
	if (currentList !== null) {
		$("div.foodle_autolist").append(
			'<div class="foodle_invite_all">' + 
				'<input id="foodle_invite_all_btn" type="submit" value="Invite all the above users" />' +
			'</div>'
		);
		$("div.foodle_autolist").find("input#foodle_invite_all_btn").click(inviteAll);
	}
	
}



function lists(lists) {

	$("div.foodle_contactlists").empty();
	for(var i = 0 ; i < lists.length; i++) {
		//console.log(lists[i]);
		$("div.foodle_contactlists").append(Foodle_Contacts_Utils.listHTML(lists[i]));
	}
	
}

function openListFromEvent(event) {
	var listid = $(event.target).data('id');
	var listname = $(event.target).data('name');
	openList(listid, listname);
}

function openList(listid, listname) {
	console.log('open list [' + listid + '] [' + listname + ']');

	Foodle_Contacts.getContactlist(listid, foodle_id);
	currentList = {name: listname, id: listid};
	
	$("input#foodle_contact_search").attr('value', '');
	
	refreshContactListSelection();
//	updateContactlistPicklist();
}

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
	














function inviteAll() {
	console.log('inviteAll');
	$("div.foodle_autolist div.foodle_contact").each(function() {

		var user = {
			userid: $(this).data('userid'),
			name: $(this).data('name')
		};
		console.log(user);
		inviteUser(user);
	});
}


function inviteUser(user) {
	console.log('invite user');
	console.log(user);
	
	addInvitedPending(user);
	
	var inviteobj = {'foodle': foodle_id};
	if (user.userid) inviteobj.userid = user.userid;
	if (user.email) inviteobj.email = user.email;
	
	$.getJSON("/api/invite", inviteobj, function(data) {
		if (data.status == 'ok' && data.data) {
			invitationComplete(user);
		} else {
			console.log('Error when doing API / invite : ' + data.message);
		}
	});

}
var foodle_top_invite = null;


function isEmail(str) {
	var regex = /^[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/;
	return regex.test(str)
}


function invitationComplete(user) {

	console.log('Completed user');
	console.log(user);
	$("div#invited_list div.foodle_contact").each(function() {
		if($(this).data('userid') == user.userid) {
			$(this).find("img.waiting").hide();
			$(this).find("img.complete").show();
		}
		console.log(this);
	});

}

function addInvitedPending(user) {

	console.log('add pending user');
	console.log(user);

	var extra = '<img class="waiting" style="float: right" src="/res/spinning.gif" alt="waiting" /> ' +
				'<img class="complete" style="display: none; float: right" src="/res/maybe.png" alt="waiting" /> ';
	$("div#invited_list").prepend(Foodle_Contacts_Utils.contactHTML(user, null, extra));	

}

$(document).ready(function() {



	Foodle_Contacts.registerAutolistHandler(autolist);
 	Foodle_Contacts.registerListsHandler(lists);
// 	Foodle_Contacts.registerFoodlelistHandler(foodlelist);
 	Foodle_Contacts.registerContactsHandler(autolist);
	
	Foodle_Contacts.getContactlists();
	Foodle_Contacts.autolist();
	Foodle_Contacts.getFoodlelist();
	
	$("input#foodle_contact_search").autocomplete({
		minLength: 0,
		source: function( request, response ) {
			var term = request.term;

			if (term.length === 1) return;
			Foodle_Contacts.autolist(term, null, foodle_id);
			currentList = null;
			refreshContactListSelection();
		}
	}).focus();
// 	searchOptionEnable(false);
// 	$("select#foodle_contact_lookuptype").change(selectContactlisttype);
	

});
