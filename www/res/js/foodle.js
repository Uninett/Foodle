

function addOneNewColumn() {
	var columntypeclass = getColumntypeClass();
	
	var container = $("div." + columntypeclass);
	
	// var nofields = container.eq(0).find("div.subcolcontainer").children().length;
	
	var template = $("div." + columntypeclass + " div.fcol").eq(0).clone();
	
	template.find("input").val("");
	template.find("a.duplicate").detach();
	template.find("input.hasDatepicker").removeAttr("id");
	template.find("input.hasDatepicker").removeClass("hasDatepicker");

	template.find("a.onemoreoption").click(addOneMoreOption);

	template.insertBefore(container.find("a.onemorecolumn"));

	
	// $("div." + columntypeclass + " div.fcol").last().find("input").focus();
	
	if (columntypeclass == 'columnsetupgeneric') {
		$("div." + columntypeclass + " div.fcol").last().find("input").first().focus();
	}
	prepareDateColumns();	
}

function addOneMoreOption(event) {

	var container = $(event.target).parentsUntil("div.fcol");
	
	var template = container.find("input.fscoli").eq(0).clone();
	template.val("");
	template.insertBefore(container.find("a.onemoreoption"));
	

	
	container.find("input").last().focus();
	
	prepareDateColumns();
}

function duplicateTimeSlots() {

	var columntypeclass = getColumntypeClass();
	
	var container = $("div." + columntypeclass + " div.fcol");
	var nofields = container.eq(0).find("div.subcolcontainer").children().length;
	
	for(var i = 1; i <= nofields; i++) {
		container.eq(i).find("div.subcolcontainer").empty().append(
			container.eq(0).find("div.subcolcontainer").clone().unwrap()
		);
		container.eq(i).find("a.duplicate").detach();		
	}
	prepareDateColumns();
	updatePreview();
}

function fillfields() {

	var col = 0;
	// $("div.fcol > input.fcoli").css("background", "#f88");
	// $("div.fcol > input.fcoli[value=]").css("background", "#977");
	// 
	$("div.fcol > input.fcoli[value=]").each(function(i){
		col++;
	});
	//alert('#Col: ' + col);
	if (col < 1) { 
		$("div.fcol").eq(0).clone().appendTo($("div.fcols")).find("input").attr('value', '').focus(function () {
			fillfields();
		});
	}
	
	$("div.fcol").each(function(i){
		var subcol = 0;
		$("div.fcol").eq(i).find("div.subcolcontainer input[value='']").each(function(ii){
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
	
	// $("div.fcol > input.fcoli[value = '']").parent().slice(1).addClass('notinuse');
	// $("div.fcol > input.fcoli[value != '']").parent().removeClass('notinuse');
	
	updatePreview();
}

function getColumntypeClass() {
	var defs = Array();
	var columntype = $('input:radio[name="columntypes"]:checked').val();
	var columntypeclass = 'columnsetupdates';
	if (columntype == 'text') {
		columntypeclass = 'columnsetupgeneric';
	}
	return columntypeclass;	
}

/* Generate a string out of */
function getDefinitionString() {

	var defs = Array();
	columntypeclass = getColumntypeClass();
	// alert('column type was ' + columntype + ' and class was ' + columntypeclass);
	
	/* Foreach column header of the selected type */
	// $("div." + columntypeclass).fadeOut().fadeIn();
	$("div." + columntypeclass + " div.fcol input.fcoli[value != '']").each(function(i){
		
		/* Define an array used for all the sub-item texts.. */
		var tdef = Array();
		/* Find all the sub-items below this column header, and push the content to the array */
		$("div." + columntypeclass + " div.fcol").eq(i).find("div.subcolcontainer input[value != '']").each(function(ii){
			tdef.push( $("div." + columntypeclass + " div.fcol").eq(i).find("div.subcolcontainer input[value != '']").eq(ii).attr('value').replace(/,/, ";") );
		});
		if (tdef.length > 0) {
			defs.push( $("div." + columntypeclass + " div.fcol").eq(i).find('input.fcoli').attr('value').replace(/,/, ";") + '(' + tdef.join(',') + ')' );
		} else {
			defs.push( $("div." + columntypeclass + " div.fcol").eq(i).find('input.fcoli').attr('value').replace(/,/, ";") );
		}
	});
	var defstr = defs.join('|');
	return defstr;
}

function updatePreview() {
	var defstr = getDefinitionString();
	$("div[id='previewpane']").load('/preview', { 'def' : defstr, "name" : $("input#foodlename").val(), "descr" : $("textarea#foodledescr").val() }); 
	// $("*[id='previewheader']").text($("input[name='name']").attr('value'));
	$("input[id='coldef']").attr('value', defstr);
	if ($("input#foodlename").val() == '' || defstr == '') {
		$("input#save").attr("disabled", "disabled");
	} else {
		$("input#save").removeAttr("disabled", "false");
	}
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

function selectColumnTypes() {
	switch($('input:radio[name="columntypes"]:checked').val()) {
		case 'dates':
			$("div.columnsetupdates").show();
			$("div.columnsetupgeneric").hide();

			break;
		case 'text':
			$("div.columnsetupdates").hide();
			$("div.columnsetupgeneric").show();

			break;
		default: 
	}
	updatePreview();
}

function prepareDateColumns() {
	$("div.columnsetupdates input.fcoli").datepicker({
		dateFormat: "yy-mm-dd",
		numberOfMonths: 1,
		firstDay: 1,
		yearRange: '2009:2015',
		onSelect: updatePreview
	});
	var availableTags = ["08:00", "08:30", "09:00", "09:30", "10:00", "10:30", "11:00", "11:30", "12:00", "12:30", "13:00", "13:30", 
	"14:00", "14:30", "15:00", "15:30", "16:00", "16:30", "17:00"];
	$("div.columnsetupdates input.fscoli").autocomplete({
		minLength: 0,
		deplay: 0,
		source: availableTags
	});
	// Event handler on editing the column fields
	$("div.fcol input").blur(function () {
		updatePreview();
	});

}


$(document).ready(function() {
	
	selectColumnTypes();
	prepareDateColumns();
	
	/* --- Register button clicks --- */
	$('input:radio[name="columntypes"]').change(selectColumnTypes);
	
	$('a.duplicate').click(duplicateTimeSlots);
	$("a[id='link_preview']").click(updatePreview);
	$("a[id='btnToColSetup']").click(updatePreview);
	$("a.buttonUpdatePreview").click(updatePreview);
	$("a.onemorecolumn").click(addOneNewColumn);
	$("a.onemoreoption").click(addOneMoreOption);
	
	$("a.ac").
		click(function(event){
			event.preventDefault();
			$("*[id='commentfield']").show();
			$("a.ac").hide();
			$("input[id='comment']").focus();
		}
	);

	
	// Facebook dialog box.
	$("#facebookshare").dialog({
		width: 450, height: 260,
		position: [100, 100],
		autoOpen: false
	});


	// Datepicker for expiration date
	$("#deadline").datepicker({  
		dateFormat: "yy-mm-dd 16:00",
		firstDay: 1,
		yearRange: '2009:2015'
	});
	
});


function toggle(x) {	
	$("*[id='" + x + "']").toggle();
}