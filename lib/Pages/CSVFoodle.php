<?php



class Pages_CSVFoodle extends Pages_Page {
		
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		if (count($parameters) < 1) throw new Exception('Missing [foodleid] parameter in URL.');
		
		Data_Foodle::requireValidIdentifier($parameters[0]);
		$this->foodleid = $parameters[0];
		$this->foodlepath = '/foodle/' . $this->foodleid;
		
		$this->foodle = $this->fdb->readFoodle($this->foodleid);
		
	}
	
	
	// Process the page.
	function show() {


		$url = FoodleUtils::getUrl() . 'foodle/' . $this->foodle->identifier;

		$responses = $this->foodle->getResponses();
		
		session_cache_limiter('public'); 

		header("Content-type: text/csv; charset=utf-8");
		header("Content-disposition:  attachment; filename=foodle-" . $this->foodle->identifier . "_" . date("Y-m-d") . ".csv");

		$s = ';';
		foreach ($responses AS $response) {
			echo $response->username . $s .  $response->userid . $s . join($s, $response->response['data']) . $s . date("Y-m-d H:i", $entry->created) . "\r\n";
		}


	}
	
}

