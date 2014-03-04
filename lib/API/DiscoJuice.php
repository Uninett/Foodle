<?php

class API_DiscoJuice extends API_API {



	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		
	}
	
	function prepare() {

		$config = SimpleSAML_Configuration::getInstance('foodle');
		$entityid = $this->config->getValue('entityid');
		$feeds = $this->config->getArrayize('feeds', array('edugain'));
		$responseurl = FoodleUtils::getUrl() . 'discoresponse';



		$data = array(
			'title' => 'Foodle',
			'entityid' => $entityid,
			'responseurl' => $responseurl,
			'feeds' => $feeds,
			'returnurl' => FoodleUtils::getUrl() . '?idp=',
			'extrafeed' => FoodleUtils::getUrl() . '/extradiscofeed',
			'subIDstores' => array(
				'https://idp.feide.no' => 'https://idp.feide.no/simplesaml/module.php/feide/getOrg.php',
				"https://wayf.wayf.dk" => "https://wayf.wayf.dk/module.php/wayfdiscopower/disco.php",
			),
			'subIDwritableStores' => array(
				'https://idp.feide.no' => 'https://idp.feide.no/simplesaml/module.php/feide/preselectOrg.php?ReturnTo=' .  urlencode(FoodleUtils::getUrl() . '/discoresponse') . '&HomeOrg=',
				'https://wayf.wayf.dk' => 'https://wayf.wayf.dk/module.php/wayfdiscopower/disco.php?entityID=https%3A%2F%2Fwayf.wayf.dk&return=https%3A%2F%2Fwayf.wayf.dk%2Fmodule.php%2Fsaml%2Fsp%2Fdiscoresp.php&returnIDParam=idpentityid&idpentityid=',
			),
			'baseURL' => FoodleUtils::getUrl(),
		);


		// $discojuiceconfig = '
		// 	"Foodle",
		// 	"' . $entityid . '",
		// 	"' . $responseurl . '", 
		// 	' . json_encode($feeds) . ',
		// 	"http://foodl.org/?idp="
		// ';


		return $data;



		throw new Exception('Invalid request parameters');
	}


	
}

