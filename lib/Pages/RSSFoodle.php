<?php



class Pages_RSSFoodle extends Pages_Page {
		
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		if (count($parameters) < 1) throw new Exception('Missing [foodleid] parameter in URL.');
		
		Data_Foodle::requireValidIdentifier($parameters[0]);
		$this->foodleid = $parameters[0];
		$this->foodlepath = '/foodle/' . $this->foodleid;
		
		$this->foodle = $this->fdb->readFoodle($this->foodleid);
		
	}
	
	
	private static function encodeSingleResponse($r) {
		if ($r == 1) {
			return '☒';
		}
		return '☐';
	}

	private static function encodeResponse($r) {
		$k = array();
		foreach ($r AS $nr) {
			$k[] = self::encodeSingleResponse($nr);
		}
		return join(' ', $k);
	}
	
	// Process the page.
	function show() {


		$url = FoodleUtils::getUrl() . 'foodle/' . $this->foodle->identifier;

		$responses = $this->foodle->getResponses();
		$rssentries = array();
		foreach ($responses AS $response) {
			#echo '<pre>'; print_r($response); echo '</pre>';
			
			$newrssentry = array(
				'title' => $response->username,
				'description' => 'Response: ' . self::encodeResponse($response->response['data']),
				'pubDate' => $response->created,
	#			'link' => $url, 
			);
			if (isset($entry['notes'])) {
				$newrssentry['description'] .= '<br /><strong>Comment from user: </strong><i>' . $response->notes . '</i>';
			}
			$newrssentry['description'] .= '<br />[ <a href="' . $url . '">go to foodle</a> ]';

			$rssentries[] = $newrssentry;
		}

		$rss = new RSS($this->foodle->name);
		#$rss->description = $this->foodle->description;
		$rsstext = $rss->get($rssentries);


		header('Content-Type: text/xml');
		echo $rsstext;


	}
	
}

