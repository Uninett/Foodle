<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head xml:lang="en">

	<meta charset="utf-8" />

	<!-- Foodle: CSS -->	
	<link rel="stylesheet" media="screen" type="text/css" href="/res/css/foodle.css" /> 
	<link rel="stylesheet" media="screen" type="text/css" href="/res/css/foodle-layout.css" /> 


	<!-- JQuery -->
	<script type="text/javascript" src="/res/js/jquery.js"></script>
	<script type="text/javascript" src="/res/js/jquery-ui.js"></script>
	<script type="text/javascript" src="/res/js/jquery-placeholder.js"></script>
	<link rel="stylesheet" media="screen" type="text/css" href="/res/js/uitheme/jquery-ui-themeroller.css" />
	
	
	<!-- DiscoJuice -->
	<!-- DiscoJuice -->
	<script type="text/javascript" language="javascript" src="/res/discojuice/discojuice.misc.js"></script>
	<script type="text/javascript" language="javascript" src="/res/discojuice/discojuice.ui.js"></script>
	<script type="text/javascript" language="javascript" src="/res/discojuice/discojuice.control.js"></script>
	<link rel="stylesheet" type="text/css" href="/res/discojuice/css/discojuice.css" />
	


	<script type="text/javascript">
<?php

if (!empty($this->data['foodle'])) {
	echo 'var foodle_id = "' . htmlspecialchars($this->data['foodle']->identifier) . '"; ' . "\n\n";
}


?>
	
		$(document).ready(function() {
	
			$("a.signin").DiscoJuice({
				"title": 'Sign in to <strong>Foodle</strong>',
				"subtitle": "Select your Provider",
				"always": false,
				"overlay": true,
				"cookie": true,
				"type": false,
				"country": true,
				"countryAPI": "/simplesaml/module.php/ulxmeta/country.php",
				"metadata": "/simplesaml/module.php/ulxmeta/index.php",
				"discoPath": "/res/discojuice/",
				"location": false,
				"disco": {
					"spentityid": "https://foodl.org/simplesaml/module.php/saml/sp/metadata.php/saml",
					"url": "https://foodl.org/res/discojuice/discojuiceDiscoveryResponse.html?",
					"stores": [
						'https://disco.uninett.no/',
						'https://foodle.feide.no/simplesaml/module.php/discopower/disco.php',
						'https://kalmar2.org/simplesaml/module.php/discopower/disco.php'
					],
					'writableStore': 'https://disco.uninett.no/'
				},
				"callback": function(e) {
					window.location = 'https://foodl.org/simplesaml/module.php/core/as_login.php?AuthId=saml&ReturnTo=https%3A%2F%2Ffoodle.feide.no%2F&saml:idp=' + escape(e);
				}
			});
		});
	</script>
	
	
	
	<!-- WMD -->
	<!-- <script type="text/javascript" src="/res/js/wmd.js"></script> -->


	<!-- Foodle: JS -->	
	<script type="text/javascript" src="/res/js/foodle.js"></script>	

	<script type="text/javascript">
	
		$(document).ready(function() {

			$("#foodletabs").tabs();
			$("#foodletabs").bind('tabsshow',function(event, ui) {
	            window.location = ui.tab;
	        });
			if (selectTab) {
				window.onhashchange = selectTab;				
			}

			
			$("div#responsetyperadio").buttonset({ icons: {primary:'ui-icon-gear',secondary:'ui-icon-triangle-1-s'} });
			<?php
			if (!empty($this->data['calenabled'])) {
				if ($this->data['defaulttype'] === 'ical') {
					echo '$(\'#responserowmanual\').hide();';
				} else {
					echo '$(\'#responserowcal\').hide();';
				}
			}
			?>
			
			$('#radio1').click(function() {
				$('#responserowmanual').show();
				$('#responserowcal').hide();
			});
			$('#radio2').click(function() {
				$('#responserowmanual').hide();
				$('#responserowcal').show();
			});

		});
	
		function showemailX(col) {
			$("div.inneremailbox").hide("fast");
			<?php
			if (isset($this->data['foodle']->identifier)) {
				echo '$("div#inneremailbox" + col).show("fast");  ';
			}
			?>
			$("div#emailbox").show("fast");
		}

		function showemail(col) {
			$("div.inneremailbox").hide("fast");
			$("div#inneremailbox" + col).show("fast");
		}
	</script>


	<?php
		if (isset($this->data['foodle'])) {
			echo '<link rel="alternate" type="application/rss+xml" title="' . $this->t('subscribe_rss') . '" href="/foodle/' . $this->data['foodle']->identifier . '?output=rss" />';
		}
		
	?>

	<title><?php 
		$title = 'Foodle';
		if (isset($this->data['title']))
			$title = $this->data['title']; 
		echo $title;
	?></title> 

<?php

if (isset($this->data['head']))
	echo $this->data['head']; 


?>

	
</head>
<body>

<!-- Red logo header -->
<div id="header">	
	<div id="logo">Foodle <span id="version"><?php echo $this->t('version'); ?> 3.2</span> 
		<a id="news" style="font-size: small; color: white" target="_blank" href="http://rnd.feide.no/category/foodle/">
			∘ <?php echo $this->t('read_news'); ?></a>  
		<a id="mailinglist" style="font-size: small; color: white" target="_blank" href="http://rnd.feide.no/software/foodle/">
			∘ <?php echo $this->t('join_mailinglist'); ?></a>
	</div><!-- end #logo -->
	<a href="http://rnd.feide.no"><img id="ulogo" alt="notes" src="/res/uninettlogo.gif" /></a>
</div><!-- end #header -->








<!-- Grey header bar below -->
<div id="headerbar" style="clear: both">
<?php 

echo '<p id="breadcrumb">';
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
		echo '<a class="button signin" style="float: right" href="' . htmlentities($this->data['loginurl']) . '"><span>' . $this->t('login') . '</span></a>';
	} elseif(isset($this->data['logouturl'])) {
		echo '<a class="button" style="float: right" href="' . htmlentities($this->data['logouturl']) . '"><span>' . $this->t('logout') . '</span></a>';
	}

	if (isset($this->data['showprofile'])) {
		echo '<a class="button" style="float: right" href="' . htmlentities('/profile') . '"><span>' . $this->t('myprofile') . '</span></a>';
	}

	
	if (isset($this->data['showsupport'])) {
		echo '<a class="button" style="float: right" href="' . htmlentities('/support') . '"><span>' . $this->t('support') . '</span></a>';
	}

	
	if (array_key_exists('facebookshare', $this->data) && $this->data['facebookshare']) {
		echo '<a class="button" style="float: right" onclick="showFacebookShare()"><span>' . $this->t('facebookshare') . '</span></a>';
	}

	if (array_key_exists('twittershare', $this->data) && $this->data['twittershare']) {
		echo '<a class="button" style="float: right" title="Share this foodle on Twitter" href="' . 
			htmlspecialchars(
				SimpleSAML_Utilities::addURLparameter('http://twitter.com/home', array(
						'status' => 
							'#foodle ' . $title . ': ' . SimpleSAML_Utilities::addURLparameter(SimpleSAML_Utilities::selfURL(), array('auth' => 'twitter'))
					)
				)
			) . 
			'"><span>Tweet</span></a>';
	}



	if (array_key_exists('showedit', $this->data)) {
		echo('<a class="button" href="/edit/' .$this->data['foodle']->identifier . '" style="float: right"><span>' . $this->t('editfoodle') . '</span></a>');
	}

	if (isset($this->data['headbar'])) {
		echo $this->data['headbar'];
	}
?>

<p style="height: 0px; clear: both"></p>
</div><!-- /#headerbar -->

  




<?php
$languages = $this->getLanguageList();
$langnames = array(
	'no' => 'Bokmål',
	'nn' => 'Nynorsk',
	'se' => 'Sami',
	'da' => 'Dansk',
	'fi' => 'Suomeksi',
	'en' => 'English',
	'de' => 'Deutsch',
	'sv' => 'Svenska',
	'es' => 'Español',
	'fr' => 'Français',
	'nl' => 'Nederlands',
	'lb' => 'Luxembourgish', 
	'cs' => 'Čeština',
	'sl' => 'Slovenščina', // Slovensk
	'hr' => 'Hrvatski', // Croatian
	'hu' => 'Magyar', // Hungarian
);



echo '<div id="langbar" style="clear: both"><span>';
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
			$textarray[] = '<a href="' . htmlspecialchars(
				SimpleSAML_Utilities::addURLparameter(
						SimpleSAML_Utilities::selfURL(), array(
							'language' => $lang,
						))) . '">' . 
				$langnames[$lang] . '</a>';
		}
	}
	echo '' .  join(' | ', $textarray) . '';

	

}
echo '</span></div><!-- end #langbar -->';
?>






<div id="content">
