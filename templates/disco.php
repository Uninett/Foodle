<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Select Your Login Provider</title>

    <link rel="shortcut icon" href="http://discojuice.bridge.uninett.no/simplesaml/module.php/discojuice/favicon.png" />

    <!-- JQuery hosted by Google -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js" type="text/javascript"></script>

    <!-- DiscoJuice hosted by UNINETT at discojuice.org -->
    <script type="text/javascript" src="https://engine.discojuice.org/discojuice-stable.min.js"></script>
    <script type="text/javascript" src="https://engine.discojuice.org/idpdiscovery.js"></script>
    <link rel="stylesheet" type="text/css" href="https://static.discojuice.org/css/discojuice.css" />

    <style type="text/css">
        body {
            text-align: center;
        }
        div.discojuice {
            text-align: left;
            position: relative;
            width: 600px;
            margin-right: auto;
            margin-left: auto;
        }
    </style>


<?php

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





		<script type="text/javascript">
            var acl = ['foodl.org', 'beta.foodl.org', 'beta2.foodl.org', 'deploy.foodl.org'];
			var djc = DiscoJuice.Hosted.getConfig(<?php echo $discojuiceconfig; ?>);

			djc.overlay = true;
            djc.always = true;
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

			djc.callback = IdPDiscovery.setup(djc, acl);
			
			$(document).ready(function() {
				$("a.signin").DiscoJuice(djc);
			});

		</script>








</head>
<body style="background: #ccc">
    <p style="display: none" style="text-align: right"><a class="signin" href="/">signin</a></p>
</body>
</html>