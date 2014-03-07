<?php



class Pages_Debug extends Pages_Page {
	
	private $foodle;
	private $user;
	private $foodleid;
	private $foodlepath;
	
	private $loginurl;
	private $logouturl;
	
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


		header('Content-Type: text/plain');
		

		echo '---- Foodle ----' . "\n";
		print_r($this->foodle);
		
		$responses = $this->foodle->getResponses();
		
		foreach($responses AS $response) {
			echo "\n\n\n" . '------- RESPONSE -------- ' . "\n";
			unset($response->foodle);
			print_r($response);
		}
		
		

	}
	
}

