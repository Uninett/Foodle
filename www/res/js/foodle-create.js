$(document).ready(function() {

	$("#foodledescr").markItUp(mySettings);

	

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

		$("select[name='timezone']").change(updatePreview);


		// Section for the box associating a foodle with a specific date or time...
		$("input#eventtimeopt").change(onoffEventDateTimeSelector);
		$("input#eventallday").change(prepareEventDateTimeSelector);
		$("input#eventmultipledays").change(prepareEventDateTimeSelector);


		prepareEventDateTimeSelector();
		onoffEventDateTimeSelector();

	// 	$("div.columnsetupgeneric input.fscoli").placeholder({'className': 'placeholdertemp'});
	// 	$("div.columnsetupgeneric input.fcoli").placeholder({'className': 'placeholdertemp'});




		// Datepicker for expiration date
		$("#deadline").datepicker({  
			dateFormat: "yy-mm-dd 16:00",
			firstDay: 1,
			yearRange: '2009:2015'
		});

		// Datepicker for expiration date
		$("#eventdatefrom").datepicker({  
			dateFormat: "yy-mm-dd",
			firstDay: 1,
			yearRange: '2009:2015'
		});
		$("#eventdateto").datepicker({  
			dateFormat: "yy-mm-dd",
			firstDay: 1,
			yearRange: '2009:2015'
		});	








	
	

});



function selectColumnTypes() {

	if ($("input#includedatefield").val() == 'enabled') {
		$("div#eventdatetime").show();
		$("div.timezoneselector").hide();
	} else {
		switch($('input:radio[name="columntypes"]:checked').val()) {
			case 'text':
				$("div#eventdatetime").show();
				break;
				
			case 'dates':
				$("div#eventdatetime").hide();
				break;
				
			case 'timezone':
				$("div#eventdatetime").hide();
				break;
		}	
	}

	switch($('input:radio[name="columntypes"]:checked').val()) {
	
		case 'text':
			$("div.columnsetupdates").hide();
			$("div.columnsetupgeneric").show();
			$("div.columnsetuptimezone").hide();
			break;
	
		case 'dates':
			$("div.columnsetupdates").show();
			$("div.columnsetupgeneric").hide();
			$("div.columnsetuptimezone").hide();
			break;
			
		case 'timezone':
			$("div.columnsetupdates").hide();
			$("div.columnsetupgeneric").hide();
			$("div.columnsetuptimezone").show();
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
	$("div.columnsetuptimezone input.fcoli").datepicker({
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
	
//	$("div.columnsetupdates input.fscoli").placeholder({'className': 'placeholdertemp'});
//	$("div.columnsetupdates input.fcoli").placeholder({'className': 'placeholdertemp'});

}


// Section for the box associating a foodle with a specific date or time...
function onoffEventDateTimeSelector() {
	if ($("input#eventtimeopt").attr('checked')) {
		$("div#eventdatetimecontent").show('slow');
	} else {
		$("div#eventdatetimecontent").hide();
	}
}

function prepareEventDateTimeSelector() {
	if ($("input#eventallday").attr('checked') && !$("input#eventmultipledays").attr('checked')) {
		$("span#todelimiter").hide();
	} else {
		$("span#todelimiter").show();
	}

	if ($("input#eventallday").attr('checked')) {
		$("input#eventtimefrom").hide();
		$("input#eventtimeto").hide();
	} else {
		$("input#eventtimefrom").show();
		$("input#eventtimeto").show();
	}
	if ($("input#eventmultipledays").attr('checked')) {
		$("input#eventdateto").show();
	} else {
		$("input#eventdateto").hide();
	}
}


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
			container.eq(0).find("div.subcolcontainer").clone()
		);
		container.eq(i).find("a.duplicate").detach();		
		container.eq(i).find("a.onemoreoption").click(addOneMoreOption);
	}
	prepareDateColumns();
	updatePreview();
}

function fillfields() {

	var col = 0;

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
	
	updatePreview();
}

function getColumntype() {
	return $('input:radio[name="columntypes"]:checked').val();
}
function getResponsetype() {
	return $('input:radio[name="responsetype"]:checked').val();
}

function getColumntypeClass() {
	var defs = Array();
	var columntype = $('input:radio[name="columntypes"]:checked').val();
	var columntypeclass = 'columnsetupdates';
	if (columntype == 'text') {
		columntypeclass = 'columnsetupgeneric';
	} else if (columntype == 'timezone') {
		columntypeclass = 'columnsetuptimezone';
	}
	return columntypeclass;
}

/* Generate a string out of the column definition... */
function getDefinitionString() {

	var defs = Array();
	columntypeclass = getColumntypeClass();
	// alert('column type was ' + columntype + ' and class was ' + columntypeclass);
	
	/* Foreach column header of the selected type */
	// $("div." + columntypeclass).fadeOut().fadeIn();
	$("div." + columntypeclass + " div.fcol input.fcoli[value != '']").not("input[class~='placeholdertemp']").each(function(i){
		
		/* Define an array used for all the sub-item texts.. */
		var tdef = Array();
		/* Find all the sub-items below this column header, and push the content to the array */
		$("div." + columntypeclass + " div.fcol").eq(i).find("div.subcolcontainer input[value != '']").not("input[class~='placeholdertemp']").each(function(ii){
			tdef.push( $("div." + columntypeclass + " div.fcol").eq(i).find("div.subcolcontainer input[value != '']").not("input[class~='placeholdertemp']").eq(ii).attr('value').replace(/,/, ";") );
		});
		if (tdef.length > 0) {
			defs.push( $("div." + columntypeclass + " div.fcol").not("input[class~='placeholdertemp']").eq(i).find('input.fcoli').attr('value').replace(/,/, ";") + '(' + tdef.join(',') + ')' );
		} else {
			defs.push( $("div." + columntypeclass + " div.fcol").not("input[class~='placeholdertemp']").eq(i).find('input.fcoli').attr('value').replace(/,/, ";") );
		}
	});
	var defstr = defs.join('|');
	return defstr;
}

function updatePreview() {
	var columntype = getColumntype();
	var responsetype = getResponsetype();
	var defstr = getDefinitionString();
	$("div[id='previewpane']").load('/preview', 
		{ 
			'def' : defstr, 
			"name" : $("input#foodlename").val(), 
			"descr" : $("textarea#foodledescr").val(),
			"columntype": columntype,
			"responsetype": responsetype
		}
	); 
	// $("*[id='previewheader']").text($("input[name='name']").attr('value'));
	$("input[id='coldef']").attr('value', defstr);
	$("input[id='columntype']").attr('value', columntype);
	
	
	var timezone = $("div.eventdatetime select[name='timezone'] option:selected").val();
	if (columntype == 'dates' && $("input#includedatefield").val() !== 'enabled') {
		timezone = $("div.columnsetupdates select[name='timezone'] option:selected").val();
	} else if (columntype == 'timezone') {
		timezone = $("div.columnsetuptimezone select[name='timezone'] option:selected").val();
	}
	// console.log('Timezone detected: ' + timezone);
	if (timezone) $("input[id='settimezone']").attr('value', timezone);
	
	
	var enabled = true;
	if ($("input#foodlename").val() == '') {
		$("p#readytextname").show();
		enabled = false;
	} else {
		$("p#readytextname").hide();
	}
	if ( defstr == '') {
		$("p#readytextcol").show();	
		enabled = false;
	} else {						
		$("p#readytextcol").hide();	
	}
	
	if (columntype == 'dates') {
		var datesok = verifyDatefields();
		if (!datesok) {
			$("p#readytextdates").show();	
			enabled = false;
		} else {
			$("p#readytextdates").hide();	
		}
	}
	if (enabled) {
		$("input#save").removeAttr("disabled", "false");
	} else {
		$("input#save").attr("disabled", "disabled");
	}
}

function verifyDate(str) {
	var regex = /^[0-9]{4}-[0-9]{2}-[0-9]{2}$/;
	return regex.test(str)
}
function verifyTime(str) {
	var regex = /^[0-9]{1,2}([:.][0-9]{2})?(-[0-9]{1,2}([:.][0-9]{2})?)?$/;
	return regex.test(str)
}

function verifyDatefields() {
	var enable = true;
	$("div.columnsetupdates div.fcol input.fcoli[value != '']").each(function(i){

		if (!verifyDate($(this).attr('value'))) {
			$(this).addClass('invalid');
			enable = false;
		} else {
			$(this).removeClass('invalid');
		}
	});
	$("div.columnsetupdates div.fcol input.fscoli[value != '']").each(function(i) {
		if (!verifyTime($(this).attr('value'))) {
			$(this).addClass('invalid');
			enable = false;
		} else {
			$(this).removeClass('invalid');
		}		
	});
	return enable;
	
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


