var FOODLE = FOODLE || {};

FOODLE.utils = {};

FOODLE.utils.escape = function(str) {
	if (typeof(str) == "string") {
		str = str.replace(/&/g, "&amp;");
		str = str.replace(/"/g, "&quot;");
		str = str.replace(/'/g, "&#039;");
		str = str.replace(/</g, "&lt;");
		str = str.replace(/>/g, "&gt;");
	}
	return str;
}


FOODLE.data = {};

FOODLE.data.File = (function() {

	var
		// Dependencies
		utils = FOODLE.utils,
	
		// Private properties
		Constr;

	// Private method to get icon based upon mime type.
	function getIcon(mimetype) {
		
		switch(mimetype) {
			case 'image/png':
			case 'image/jpg':
			case 'image/jpeg':
				return 'page_white_picture.png';
		
			case 'application/pdf':
				return 'page_white_acrobat.png';
			
			case 'text/plain':
				return 'page_white_text.png';
			
			case 'application/vnd.oasis.opendocument.text':
				return 'page_word.png';
			
			case 'application/octet-stream':
			default:
				return 'page_white.png';
		}
		
	}

	Constr = function(obj) { 
		this.obj = obj;
	};
	
	Constr.prototype.view = function() {
		
		var html = '';
		
		html = '<img src="/res/files/' + getIcon(this.obj.mimetype) + '" alt="file" /> ' + utils.escape(this.obj.filename);
		
		html = '<a href="/api/download/' + utils.escape(this.obj.stored_filename) + '">' + html + '</a>';
		html = '<div class="fileItem">' + html + '</div>';
		
		return html;
	}
	
	return Constr;
	
})();





FOODLE.data.Group = (function() {

	var
		// Dependencies
		utils = FOODLE.utils,
		
		// Private properties
		Constr;
		
	
	function groupSpecificCallback(group, callback) {
		return function() {
			callback(group);
		}
	}

	Constr = function(obj) { 
		this.obj = obj;
	};
	
	Constr.prototype.view = function(callback) {
		
		var html = '';
		
		switch(this.obj.role) {

			case 'owner':
				html = '<img src="/res/group.png" alt="User profile" />' + utils.escape(this.obj.name);

				break;

			case 'admin':
				html = '<img src="/res/group_pale.png" alt="User profile" />' + utils.escape(this.obj.name);
				break;
		
			case 'member':
			default:
				html = '<img src="/res/group_grey.png" alt="User profile" />' + utils.escape(this.obj.name);
		} 
		
//		html = '<img src="/res/group.png" alt="Contact list" /> ' + list.name;
		html = $('<div rel="' + this.obj.id + '" class="foodle_contactlist">' + html + '</div>').
			data({id: this.obj.id, name: this.obj.name, role: this.obj.role, inviteToken: this.obj.inviteToken}); 
		
		if (callback && typeof callback === 'function') {
			$(html).click(groupSpecificCallback(this, callback));
		}
		
		// .click(openListFromEvent);
		
		return html;
	}
	
	return Constr;
	
})();







FOODLE.data.Person = (function() {

	var
		// Dependencies
		utils = FOODLE.utils,
		
		// Private properties
		Constr;
		
	
	function createUserSpecificCallback(user, callback) {
		return function() {
			callback(user);
		};
	}
		
	function viewButton(button, user) {
	
		var 
			html = '',
			extra = '',
			disabled = '',
			style = '';

		if (user.obj.disabled) {
			disabled = ' disabled="disabled" ';
		}
		
		if (button.float) {
			style = ' style="float: ' + button.float + '" ';
		}

		html = $('<input type="submit" ' + disabled + ' ' + style + 'name="' + button.name + '" value="' + button.value + '" />');
		
		if (button.callback) {
			html = $(html).click(createUserSpecificCallback(user, button.callback) );
		}
//		alert("button" + html);	

		return html;
	
	}

	Constr = function(obj) { 
		this.obj = obj;
	};
	
	Constr.prototype.view = function(includeID, buttons, extra) {
		
		// console.log(contact);

		includeID = !(!includeID || false);
	
		var 
			html = '',
			userpage = null;
		
		if (this.obj.token) {
			userpage = '/user/' + this.obj.userid + '?token=' + this.obj.token;
		}
		

		if(this.obj.name) {
			switch(this.obj.membership) {
	
				case 'owner':
					html = '<img style="position:relative; bottom: -2px" src="/res/user_red.png" alt="User profile" />' + utils.escape(this.obj.name);
	
					break;
	
				case 'admin':
					html = '<img style="position:relative; bottom: -2px" src="/res/user_suit.png" alt="User profile" />' + utils.escape(this.obj.name);
					break;
			
				case 'member':
				default:
					html = '<img style="position:relative; bottom: -2px" src="/res/user_grey.png" alt="User profile" />' + utils.escape(this.obj.name);				
			} 
		} else {
			this.obj.name = '';
//			html = '<img style="position:relative; bottom: -2px" src="/res/mail16.png" alt="User profile" />' + contact.name;				
		}
	

	
		
		if (userpage) {		
			html = '<a href="' + userpage + '">' + html  + '</a>';
		}
		
		if (includeID) {
		
			if (this.obj.twitter) {
				html = html + ' (<a href="http://twitter.com/' + utils.escape(this.obj.twitter) + '">@' + utils.escape(this.obj.twitter) + '</a>)';
			} else if (this.obj.email) {
				html = html + '<span style="font-size: 90%; color: #666"> (<img style="position:relative; bottom: -2px" src="/res/mail16.png" alt="User profile" /> ' + utils.escape(this.obj.email) + ')</span>';
			} else {
				html = html + ' (' + utils.escape(this.obj.userid) + ')'
			}

		
		}

		if (extra) {
			html = html + extra;
		}
		
		// return '';
		// html = $('<div rel="' + utils.escape(this.obj.userid) + '" class="foodle_contact" >' + html + '</div>').data({
		// 	'userid': this.obj.userid,
		// 	'name': this.obj.name,
		// 	'email': this.obj.email
		// }); //.click(addUser);

		
		html = $('<div rel="' + utils.escape(this.obj.userid) + '" class="foodle_contact" >' + html + '</div>');

		var dob = {
			"userid": this.obj.userid,
			'name': this.obj.name,
			'email': this.obj.email
		};
		$(html).data(dob);
		
//		$(html).data("person", dob);
		// $(html).data();


		if (buttons) {
			for(var i = 0; i < buttons.length; i++) {
				html = html.prepend(viewButton(buttons[i], this));
//				$(html).prepend(viewButton(buttons[i], this));
//				viewButton(buttons[i], this).prepentTo(html);
//				$(html).prepend("lkj");
			}
		}

		return html;
	}
	
	return Constr;
	
})();



FOODLE.data.Activity = (function() {

	var
		// Dependencies
		utils = FOODLE.utils,
		
		// Private properties
		Constr;
		

	
	

	Constr = function(obj) { 
		this.obj = obj;
	};
	
	Constr.prototype.view = function() {
			
		var html = '',
			message,
			i;
		
		
		if (this.obj.foodle.youcreated) {
			html = html + '<div style="" class="activitytag youcreated" >You created</div>';
		}
		if (this.obj.foodle.youresponded) {
			html = html + '<div class="activitytag youresponded" >You responded</div>';
		} else {
			if (this.obj.foodle.invited) {
				html = html + '<div class="activitytag invited" >Invited - not yet responded</div>';
			} else {
				html = html + '<div class="activitytag yourespondednot" >Not yet responded</div>';
			}
		}
	
		if (this.obj.foodle.groupname) {
			html = html + '<div class="activitytag groupref">Group [<a href="/group/' + utils.escape(this.obj.foodle.groupid) + '">' + 
				utils.escape(this.obj.foodle.groupname) + '</a>]</div>';
		}
	
		
		html = html + '<h2><a href="/foodle/' + utils.escape(this.obj.foodle.id) + '#responses">' + 
			utils.escape(this.obj.foodle.name) + '</a></h2>';
		
		if (this.obj.foodle.summary) {
			// The summary property is not escaped on purpose. This is already a controlled property that is generated from
			// the description property, only allowing <p> tags; generated from markdown.
			html = html + '<div>' + this.obj.foodle.summary + '</div>';
		}
		
		
		if (this.obj.foodle.discussion) {
			for(i = 0; i < this.obj.foodle.discussion.length; i++) {
				message = this.obj.foodle.discussion[i];
				html = html + '<div class="discussion">' + 
					utils.escape(message.message) + 
					'<p class="persons"><img style="position:relative: top: 3px" src="/res/user_grey.png" /> ' + utils.escape(message.username) + 
					'</p>' + 
					'</div>';
			}
		}
		
		html = html + '<p class="persons">';
		
		if (this.obj.responses) {
			html = html + 'Latest responses ';
			for (var i = 0; i < this.obj.responses.length; i++) {
				html = html + '<img style="position:relative: top: 3px" src="/res/user_grey.png" />' + 
					utils.escape(this.obj.responses[i].name) + ' ';
			}
			html = html + '';
		}
		
		if (this.obj.foodle.ownername) {
			html = html + ' &mdash; Created by <img style="position:relative: top: 3px" src="/res/user_grey.png" /> ' + 
				utils.escape(this.obj.foodle.ownername) + '';
		}
		html = html + '</p>';
	
		
		if (this.obj.ago) {
			html = '<div style="margin: 3px .4em; padding: 3px; float: right">' + 
				utils.escape(this.obj.ago) + ' ago</div>' + html;
		}
	
		html = '<div class="activity ' + utils.escape(this.obj.type) + '">' + html + '</div>';
		
		return html;
	}
	
	return Constr;
	
})();