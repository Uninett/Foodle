var FOODLE = FOODLE || {};

function selectTab() {
	var opts = {};
	$("#foodletabs ul li a").each(function(index) {
		opts[$(this).attr('href').substring(1)] = index;
	});
	if (window.location.hash && opts[window.location.hash.substring(1)]) {
		 $("#foodletabs").tabs('select', opts[window.location.hash.substring(1)]);
	}
}


function showFacebookShare() {
	
	$("#facebookshare").dialog("open");
	

	
}



$(document).ready(function() {

	$("#share_accordion").accordion({
		autoHeight: false
	});

	$("#foodletabs").bind('tabsshow',function(event, ui) {
		//console.log(ui);
		if (ui.tab.hash == '#invite') {
			if ($("input#invite_search").attr('value') == '') {
				inviteSearch();
			}
			$("input#invite_search").focus();
		}
	});

	$("a.ac").
		click(function(event){
			event.preventDefault();
			$("*[id='commentfield']").show();
			$("a.ac").hide();
			$("input[id='comment']").focus();
		}
	);


	$("input#start_invite").
		click(function(event) {
			event.preventDefault();
			$('#foodletabs').tabs('select', '#invite');
		}
	);

	// Facebook dialog box.
	$("#facebookshare").dialog({
		width: 450, height: 260,
		position: [100, 100],
		autoOpen: false
	});

});





function toggle(x) {	
	$("*[id='" + x + "']").toggle();
}










