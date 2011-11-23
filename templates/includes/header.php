<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" lang="en">
<head xml:lang="en">

	<meta charset="utf-8" />

	<!-- Foodle: CSS -->	
	
<?php


if (!empty($this->data['theme'])) {
	$theme = $this->data['theme'];
	echo '<link rel="stylesheet" media="screen" type="text/css" href="/res/css/foodle-' . $theme . '.css" /> ';
} else {
	echo '<link rel="stylesheet" media="screen" type="text/css" href="/res/css/foodle-default.css" /> ';	
}
echo '<link rel="stylesheet" media="screen" type="text/css" href="/res/css/foodle-core.css" />';




$config = SimpleSAML_Configuration::getInstance('foodle');
$entityid = $config->getValue('entityid');
$feeds = $config->getArrayize('feeds', array('edugain'));
$responseurl = FoodleUtils::getUrl() . 'discoresponse';

$discojuiceconfig = '
	"Foodle",
    "' . $entityid . '",
    "' . $responseurl . '", 
	' . json_encode($feeds) . ',
	"http://foodl.org/?idp="
';


?>


<link rel="stylesheet" media="screen" type="text/css" href="/res/js/uitheme/jquery-ui-themeroller.css" />	
<link rel="stylesheet" type="text/css" href="https://static.discojuice.org/css/discojuice.css" />


	<!-- JQuery -->
	<script type="text/javascript" src="/res/js/jquery.js"></script>
	<script type="text/javascript" src="/res/js/jquery-ui.js"></script>
	<script type="text/javascript" src="/res/js/jquery-placeholder.js"></script>
	
	
	<script type="text/javascript" src="/res/js/foodle-api-generic.js"></script>	
	<!-- JQuery -->
	


	<!-- DiscoJuice hosted by UNINETT at discojuice.org -->
	<!-- <script type="text/javascript" language="javascript" src="http://dev.discojuice.org/discojuice/discojuice.misc.js"></script>
	<script type="text/javascript" language="javascript" src="http://dev.discojuice.org/discojuice/discojuice.ui.js"></script>
	<script type="text/javascript" language="javascript" src="http://dev.discojuice.org/discojuice/discojuice.control.js"></script>
	<script type="text/javascript" language="javascript" src="http://dev.discojuice.org/discojuice/discojuice.hosted.js"></script>
	<script type="text/javascript" language="javascript" src="http://dev.discojuice.org/discojuice/discojuice.dict.nb.js"></script> -->
	<script type="text/javascript" src="https://engine.discojuice.org/discojuice-stable.min.js"></script>

	

	<script type="text/javascript">
		var djc = DiscoJuice.Hosted.getConfig(<?php echo $discojuiceconfig; ?>);
		djc.overlay = true;
		// djc.always = true;
		djc.disco.subIDstores = {
			'https://idp.feide.no': 'https://idp.feide.no/simplesaml/module.php/feide/getOrg.php',
			"https://wayf.wayf.dk": "https://wayf.wayf.dk/module.php/wayfdiscopower/disco.php"
		};
		
<?php
	echo "
		djc.metadata.push('" . FoodleUtils::getUrl(). "/extradiscofeed');
		djc.disco.subIDwritableStores = {};
		djc.disco.subIDwritableStores['https://idp.feide.no'] = 'https://idp.feide.no/simplesaml/module.php/feide/preselectOrg.php?ReturnTo=" .  urlencode(FoodleUtils::getUrl() . '/discoresponse') . "&HomeOrg=';
		djc.disco.subIDwritableStores['https://wayf.wayf.dk'] = 'https://wayf.wayf.dk/module.php/wayfdiscopower/disco.php?entityID=https%3A%2F%2Fwayf.wayf.dk&return=https%3A%2F%2Fwayf.wayf.dk%2Fmodule.php%2Fsaml%2Fsp%2Fdiscoresp.php&returnIDParam=idpentityid&idpentityid=';

		
	";
		
?>


		djc.callback = function(e) {
			console.log(e);

			var auth = e.auth || null;
			var returnto = window.location.href || 'https://foodl.org';
			switch(auth) {

				case 'twitter':
					window.location = '<?php echo FoodleUtils::getUrl(); ?>simplesaml/module.php/core/as_login.php?AuthId=twitter&ReturnTo=' + escape(returnto);
				break;


				case 'saml':
				default:
					window.location = '<?php echo FoodleUtils::getUrl(); ?>simplesaml/module.php/core/as_login.php?AuthId=saml&ReturnTo=' + escape(returnto) + '&saml:idp=' + escape(e.entityID);
				break;							

			}
		}
		$(document).ready(function() {
			$("a.signin").DiscoJuice(djc);
		});

	</script>

<?php


// sspmod_discojuice_EmbedHelper::head(false);


?>


	<script type="text/javascript">
<?php

if (!empty($this->data['foodle'])) {
	echo 'var foodle_id = "' . htmlspecialchars($this->data['foodle']->identifier) . '"; ' . "\n\n";
}


?>
	</script>
	
	
	
	<!-- Foodle: JS -->	
	<script type="text/javascript" src="/res/js/foodle.js"></script>	

	<script type="text/javascript">

<?php

if (!empty($this->data['userToken'])) {
	echo 'var FoodleAPIuserToken = "' . $this->data['userToken'] . '";' . "\n\n";

}

?>
	
		$(document).ready(function() {


<?php

if (!empty($this->data['userToken'])) {
	echo 'Foodle_API.init("' . $this->data['userToken'] . '");';
}

?>


			$("#foodletabs").tabs();
			$("#foodletabs").bind('tabsshow',function(event, ui) {
	            window.location = ui.tab;
	        });
			if (selectTab) {
				window.onhashchange = selectTab;				
			}

			
			// $("div#responsetyperadio").buttonset({ icons: {primary:'ui-icon-gear',secondary:'ui-icon-triangle-1-s'} });
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
				$('.maybe_help').show();
			});
			$('#radio2').click(function() {
				$('#responserowmanual').hide();
				$('#responserowcal').show();
				$('.maybe_help').hide();
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
	<div id="logo">Foodle <span id="version"><?php echo $this->t('version'); ?> 3.4</span> 
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

	if (isset($this->data['showcontacts'])) {
		echo '<a class="button" style="float: right" href="/groups"><span>' . $this->t('groups') . '</span></a>';
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
			'se' => 'Sámegiella',
			'sam' => 'Åarjelh-saemien giele',
			'da' => 'Dansk',
			'en' => 'English',
			'de' => 'Deutsch',
			'sv' => 'Svenska',
			'fi' => 'Suomeksi',
			'es' => 'Español',
			'fr' => 'Français',
			'it' => 'Italiano',
			'nl' => 'Nederlands',
			'lb' => 'Luxembourgish', 
			'cs' => 'Czech',
			'sl' => 'Slovenščina', // Slovensk
			'lt' => 'Lietuvių kalba', // Lithuanian
			'hr' => 'Hrvatski', // Croatian
			'hu' => 'Magyar', // Hungarian
			'pl' => 'Język polski', // Polish
			'pt' => 'Português', // Portuguese
			'pt-BR' => 'Português brasileiro', // Portuguese
			'ru' => 'русский язык', // Russian
			'et' => 'Eesti keel',
			'tr' => 'Türkçe',
			'el' => 'ελληνικά',
			'ja' => '日本語',
			'zh-tw' => '中文',
			'ar' => 'العربية', // Arabic
			'fa' => 'پارسی', // Persian
			'ur' => 'اردو', // Urdu
			'he' => 'עִבְרִית', // Hebrew
);



echo '<div id="langbar" style="clear: both"><span>';
if (empty($_POST) ) {
	$textarray = array();

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
