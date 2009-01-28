<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="nb">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8">
	<meta http-equiv="Content-Language" content="en">
	
	<script type="text/javascript" src="js/jquery.js"></script>
	<script type="text/javascript" src="js/jquery-ui.js"></script>
	<link rel="stylesheet" media="screen" type="text/css" href="/js/uitheme/jquery-ui-themeroller.css" />

	<script type="text/javascript" src="js/wmd.js"></script>
	<style>
		div.fcol.notinuse{
			background: #eee;
/*			color: #777; */
		}
		div.fcol.notinuse input{
			background: #fff;
		}


	</style>
	<script type="text/javascript">

function showemail(col) {
	$("div#emailbox").hide("fast");
	<?php
	if (isset($_REQUEST['id'])) {
		echo '$("#inneremailbox").load("emailaddr.php", { \'id\': "' . addslashes($_REQUEST['id']) . '", \'col\': col } );'; 
	}
	?>
	$("div#emailbox").show("fast");
}

function addBefore(text) {
	$("div.fcol").eq(0).clone().prependTo($("div.fcols")).find("input").attr('value', '').focus(function () {
		fillfields();
	});	
	$("div.fcol").eq(0).find("input.fcoli").attr('value', text)
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
			});;
		}
	});
	
	$("div.fcol > input.fcoli[@value = '']").parent().slice(1).addClass('notinuse');
	$("div.fcol > input.fcoli[@value != '']").parent().removeClass('notinuse');
	
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
	
	$("div.fcol input").focus(function () {
		fillfields();
	});

	$("a[@id=link_preview]").
		click(function(event){
			var defstr = getDefinitionString();
 			$("div[@id=previewpane]").load('preview.php', { 'def' : defstr }); 
			$("*[@id=previewheader]").text($("input[@name=name]").attr('value'));
			$("input[@id=coldef]").attr('value', defstr);
		}
	);
	
	
	$("#foodletabs > ul").tabs();
	
	/*
	$("#foodledescr").resizable({ 
	    handles: "all" 
	});
	*/
	$("#deadline").datepicker({  
		dateFormat: "yy-mm-dd 16:00",
		firstDay: '1',
		yearRange: '2009:2015'
/*		onSelect: function(date) { 
			alert("The chosen date is " + date); 
		} */
	});

	$("#inline").datepicker({  
		dateFormat: "d. M",
		altFormat: "yy-mm-dd 16:00",
		numberOfMonths: 2,
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

	</script>



	<link rel="stylesheet" media="screen" type="text/css" href="/css/design.css" />
	<link rel="stylesheet" media="screen" type="text/css" href="/css/feide.css" />
	<link rel="stylesheet" media="screen" type="text/css" href="/css/feide-foodle.css" />
	
	
	
	<?php
		if (isset($_REQUEST['id'])) {
			echo '<link rel="alternate" type="application/rss+xml" title="Feide RnD RSS" href="rss.php?id=' . $_REQUEST['id'] . '" />';
		}
		
	?>

	<title><?php 
		if (isset($this->data['title'])) { 
			echo $this->data['title']; 
		} else {
			echo 'Foodle'; 
		}
	?></title> 
	
	
	<style>

		table#tlayout, table#tlayout td {
			border: none;
		}

	</style>
	
	
</head>
<body>

<div id="Hovedtopp">

</div>

<div id="logo">Foodle <span id="version"><?php echo $this->t('version'); ?> 2.0</span></div>
<a href="http://rnd.feide.no"><img id="ulogo" alt="notes" src="resources/uninettlogo.gif" /></a>


  <div class="stylehead">
	

		<div class="pagename">
		
		<?php 
		
	if (isset($this->data['bread'])) {
		$first = TRUE;
		foreach ($this->data['bread'] AS $item) {
			if (!$first) echo ' » ';
			
			if (isset($item['href'])) {
				
				if (strstr($item['title'],'bc_') == $item['title'] ) {
					echo '<a href="' . $item['href'] . '">' . $this->t($item['title']) . '</a>';
				} else {
					echo '<a href="' . $item['href'] . '">' . $item['title'] . '</a>';
				}
			
				
			} else {
				if (strstr($item['title'],'bc_') == $item['title'] ) {
					echo $this->t($item['title']);
				} else {
					echo $item['title'];
				}
				
			}
			
			
			$first = FALSE;
		}
	}
	
	
		?>
		

		</div>
		
		
				<!-- <a href="/simplesaml/saml2/sp/initSLO.php?RelayState=/simplesaml/logout.html">Feide logout</a>  -->

        <form class="button" method="get" action="/simplesaml/saml2/sp/initSLO.php"><input type="hidden" name="RelayState" value="http://rnd.feide.no" /><div class="no"><input type="submit" value="Single Log-Out" class="button" /></div></form>

        <form class="button" method="get" action="https://rnd.feide.no/content/foodle-users-guide"><div class="no"><input type="submit" value="<?php echo htmlentities($this->t('help')) . ' (' . htmlentities($this->t('usermanual')) . ')'; ?>" class="button" /></div></form>


		
		

  </div>

  



<div class="dokuwiki">
<?php
$languages = $this->getLanguageList();
$langnames = array(
	'no' => 'Bokmål',
	'nn' => 'Nynorsk',
	'se' => 'Sami',
	'da' => 'Dansk',
	'en' => 'English',
	'de' => 'Deutsch',
	'sv' => 'Svenska',
	'es' => 'Español',
	'fr' => 'Français',
	'nl' => 'Nederlands',
	'lb' => 'Luxembourgish', 
	'sl' => 'Slovenščina', // Slovensk
	'hr' => 'Hrvatski', // Croatian
	'hu' => 'Magyar', // Hungarian
);




if (empty($_POST) ) {
	$textarray = array();

/*
	foreach ($languages AS $lang => $current) {

		if ($current) {
			$textarray[] = '<form class="button" method="get" action="' . htmlspecialchars(SimpleSAML_Utilities::addURLparameter(SimpleSAML_Utilities::selfURL(), 'language=' . $lang)) . '"><div class="no"><input type="submit" value="[' . 
				$langnames[$lang] . ']" class="button" /></div></form>';
		} else {
			$textarray[] = '<form class="button" method="get" action="' . htmlspecialchars(SimpleSAML_Utilities::addURLparameter(SimpleSAML_Utilities::selfURL(), 'language=' . $lang)) . '"><div class="no"><input type="submit" value="' . 
				$langnames[$lang] . '" class="button" /></div></form>';
		}
	}
	*/

	foreach ($languages AS $lang => $current) {
		if ($current) {
			$textarray[] = $langnames[$lang];
		} else {
			$textarray[] = '<a href="' . htmlspecialchars(SimpleSAML_Utilities::addURLparameter(SimpleSAML_Utilities::selfURL(), 'language=' . $lang)) . '">' . 
				$langnames[$lang] . '</a>';
		}
	}
	echo '<div class="lang" style="float: right; width: 500px; text-align: right; padding-right: 4px">' .  join(' | ', $textarray) . '</div>';
	

	

}

?>
		


  
  <div class="page">
    <!-- wikipage start -->
    
