
function fillfields() {

	var col = 0;
	$("div.fcol > input.fcoli[@value = '']").each(function(i){
		col++;
	});
	if (col < 2) { 
		$("div.fcol").eq(0).clone().appendTo($("div.fcols")).find("input").attr('value', '').focus(function () {
			fillfields();
		});
	}
	
	$("div.fcol").each(function(i){
		var subcol = 0;
		$("div.fcol").eq(i).find("div.subcolcontainer input[@value = '']").each(function(ii){
			subcol++;
		});
		if (subcol < 2) {
			$("div.fcol").eq(i).find("div.subcolcontainer input").eq(0).clone().appendTo(
				$("div.fcol div.subcolcontainer").eq(i)
			).attr('value', '').focus(function () {
				fillfields();
			});
		}
	});
	
	$("div.fcol > input.fcoli[@value = '']").parent().slice(1).addClass('notinuse');
	$("div.fcol > input.fcoli[@value != '']").parent().removeClass('notinuse');
}


function getDefinitionString() {

	var defs = Array();
	
	$("div.fcol input.fcoli[@value != '']").each(function(i){
		var tdef = Array();
		$("div.fcol").eq(i).find("div.subcolcontainer input[@value != '']").each(function(ii){
			tdef.push( $("div.fcol").eq(i).find("div.subcolcontainer input[@value != '']").eq(ii).attr('value').replace(/,/, ";") );
		});
		if (tdef.length > 0) {
			defs.push( $("div.fcol").eq(i).find('input.fcoli').attr('value').replace(/,/, ";") + '(' + tdef.join(',') + ')' );
		} else {
			defs.push( $("div.fcol").eq(i).find('input.fcoli').attr('value').replace(/,/, ";") );
		}
	});
	var defstr = defs.join('|');
	return defstr;
}

function updatePreview() {
	var defstr = getDefinitionString();
	$("div[@id=previewpane]").load('preview.php', { 'def' : defstr }); 
	$("*[@id=previewheader]").text($("input[@name=name]").attr('value'));
	$("input[@id=coldef]").attr('value', defstr);
}



function addBefore(text) {
	$("div.fcol").eq(0).clone().prependTo($("div.fcols")).find("input").attr('value', '').focus(function () {
		fillfields();
	});	
	$("div.fcol").eq(0).find("input.fcoli").attr('value', text);
}

function addAfter(text) {
	fillfields();
	elem = $("div.fcol:last").prev();

	elem.before( 
		$("div.fcol").eq(0).clone()
	);
	inselem = elem.prev();
	inselem.find("input").attr('value', '').focus(function () {
		fillfields();
	});	
	inselem.find("input.fcoli").attr('value', text);
	fillfields();
	/* elem.find("input.fcoli").attr('value', text); */
}


function showFacebookShare() {
	
	$("#facebookshare").dialog("open");
}


$(document).ready(function() {
	$("a[@id=ac]").
		click(function(event){
			event.preventDefault();
			$("*[id=commentfield]").show();
			$(this).hide("fast");
			$("input[id=comment]").focus();
		}
	);
	
	$("#facebookshare").dialog({
		width: 450, height: 260,
		position: [100, 100],
		autoOpen: false
	});

	
	$("div.fcol input").focus(function () {
		fillfields();
	});


	$("a[@id=link_preview]").click(function(event){updatePreview();});
	$("a.buttonUpdatePreview").click(function(event){updatePreview();});
	
	/*
	$("#foodledescr").resizable({ 
	    handles: "all" 
	});
	*/
	$("#deadline").datepicker({  
		dateFormat: "yy-mm-dd 16:00",
		firstDay: 1,
		yearRange: '2009:2015'
/*		onSelect: function(date) { 
			alert("The chosen date is " + date); 
		} */
	});

	$("#inline").datepicker({  
		dateFormat: "d. M",
		altFormat: "yy-mm-dd 16:00",
		numberOfMonths: 1,
		firstDay: 1,
		yearRange: '2009:2015',
		onSelect: function(date) { 
			addAfter(date);
			/* alert("The chosen date is " + date);  */
		} 
	});
	
});


function toggle(x) {	
	$("*[@id=" + x + "]").toggle();
}