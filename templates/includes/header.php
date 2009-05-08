<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="nb">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<meta http-equiv="Content-Language" content="en" />
	
	<script type="text/javascript" src="/<?php echo($this->data['baseurlpath']); ?>js/jquery.js"></script>
	<script type="text/javascript" src="/<?php echo($this->data['baseurlpath']); ?>js/jquery-ui.js"></script>
	<link rel="stylesheet" media="screen" type="text/css" href="/<?php echo($this->data['baseurlpath']); ?>js/uitheme/jquery-ui-themeroller.css" />

	<script type="text/javascript" src="/<?php echo($this->data['baseurlpath']); ?>js/wmd.js"></script>
	<style type="text/css">
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

	function updatePreview() {
		var defstr = getDefinitionString();
		$("div[@id=previewpane]").load('preview.php', { 'def' : defstr }); 
		$("*[@id=previewheader]").text($("input[@name=name]").attr('value'));
		$("input[@id=coldef]").attr('value', defstr);
	}

	$("a[@id=link_preview]").click(function(event){updatePreview()});
	$("a.buttonUpdatePreview").click(function(event){updatePreview()});
	

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



	<link rel="stylesheet" media="screen" type="text/css" href="/<?php echo($this->data['baseurlpath']); ?>css/design.css" />
	<link rel="stylesheet" media="screen" type="text/css" href="/<?php echo($this->data['baseurlpath']); ?>css/feide.css" />
	<link rel="stylesheet" media="screen" type="text/css" href="/<?php echo($this->data['baseurlpath']); ?>css/feide-foodle.css" />
	
	
	
	<?php
		if (isset($_REQUEST['id'])) {
			echo '<link rel="alternate" type="application/rss+xml" title="' . $this->t('subscribe_rss') . '" href="rss.php?id=' . $_REQUEST['id'] . '" />';
		}
		
	?>

	<title><?php 
		$title = 'Foodle';
		if (isset($this->data['title']))
			$title = $this->data['title']; 
		echo $title;
	?></title> 
	
	
	<style type="text/css">

		table#tlayout, table#tlayout td {
			border: none;
		}

		.unicode {
			font-family: "Unicode Symbols", "Times New Roman", "Apple Symbols","Arial Unicode MS";
		}

	</style>
	
	

	
</head>
<body>

<div id="Hovedtopp">
	
	<div id="logo">Foodle <span id="version"><?php echo $this->t('version'); ?> 2.3</span> 
		<a id="news" style="font-size: small; color: white" target="_blank" href="http://rnd.feide.no/category/topics/foodle">
			∘ <?php echo $this->t('read_news'); ?></a>  
		<a id="mailinglist" style="font-size: small; color: white" target="_blank" href="http://rnd.feide.no/content/foodle-users">
			∘ <?php echo $this->t('join_mailinglist'); ?></a>
	</div>
	<a href="http://rnd.feide.no"><img id="ulogo" alt="notes" src="/<?php echo($this->data['baseurlpath']); ?>resources/uninettlogo.gif" /></a>

</div>




<div class="stylehead">


<?php 

echo '<p style="float: left; margin-left: 1em">';
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
echo '</p>';



	if (isset($this->data['loginurl'])) {
		echo '<a class="button" style="float: right" href="' . htmlentities($this->data['loginurl']) . '"><span>' . $this->t('login') . '</span></a>';
		
	} else {	
		$sspconfig = SimpleSAML_Configuration::getInstance();
		echo '<a class="button" style="float: right" href="/' . $sspconfig->getValue('baseurlpath') . 
			'/saml2/sp/initSLO.php?RelayState=/' . urlencode($this->data['baseurlpath']) . '"><span>Single Log-Out</span></a>';

	}
	
	if (array_key_exists('facebookshare', $this->data) && $this->data['facebookshare']) {
		echo '<a class="button" style="float: right" onclick="showFacebookShare()"><span>' . $this->t('facebookshare') . '</span></a>';
	}

	echo '<a class="button" style="float: right" title="Share this foodle on Twitter" href="' . 
		htmlspecialchars(
			SimpleSAML_Utilities::addURLparameter('http://twitter.com/home', array(
					'status' => 
						'#foodle ' . $title . ': ' . SimpleSAML_Utilities::addURLparameter(SimpleSAML_Utilities::selfURL(), array('auth' => 'twitter'))
				)
			)
		) . 
		'"><span>Tweet</span></a>';


	if ($this->data['owner']) {
		echo('<a class="button" href="edit.php?id=' .$this->data['identifier'] . '" style="float: right" <span>' . $this->t('editfoodle') . '</span></a>');
	}

	if (isset($this->data['headbar'])) {
		echo $this->data['headbar'];
	}




?>

<br class="clear" />
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



echo '<div class="lang" style="">';

	
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
	echo '<p style="float: right; margin-right: 1em">' .  join(' | ', $textarray) . '</p>';

	

}
echo '</div>';
?>
		


  
<div class="page">
<!-- wikipage start -->
    
