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

		$s = ';';

		$url = FoodleUtils::getUrl() . 'foodle/' . $this->foodle->identifier;

		$responses = $this->foodle->getResponses();
		
		session_cache_limiter('public'); 
		
		$extra = $this->foodle->getExtraFields();


		header("Content-type: text/csv; charset=utf-8");
		header("Content-disposition:  attachment; filename=foodle-" . $this->foodle->identifier . "_" . date("Y-m-d") . ".csv");


		foreach ($responses AS $response) {

			$extrastr = '';
			$user = $this->fdb->readUser($response->userid);
			
			if (!empty($user) && !empty($extra)) {
				foreach($extra AS $e) {
					switch($e) {
						case 'org':
							$extrastr .= $user->org . $s;
							break;

						case 'location':
							$extrastr .= $user->location . $s;
							break;

						case 'timezone':
							$extrastr .= $user->timezone . $s;
							break;

						default:
					}

				}
			}

	
			echo $response->username . $s .  $response->userid . $s . $extrastr . join($s, $response->response['data']) . $s . date("Y-m-d H:i", $response->created) . "\r\n";
		}

	}
	
}

