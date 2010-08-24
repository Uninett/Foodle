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
		
		
		return;
		


		$cols = array();
		$this->foodle->getColumnList(&$cols);

		// echo '<pre>'; 
		// print_r($this->foodle->getColumnDates());
		// print_r($cols); exit;

		$t = new SimpleSAML_XHTML_Template($this->config, 'foodleresponse.php', 'foodle_foodle');

		$t->data['title'] = 'Foodle :: ' . $this->foodle->name;
		$t->data['foodle'] = $this->foodle;
		$t->data['user'] = $this->user;
		$t->data['foodlepath'] = $this->foodlepath;
		
		$t->data['calenabled'] = ($this->foodle->calendarEnabled() && $this->user->hasCalendar());
		$t->data['myresponse'] = $this->foodle->getMyResponse($this->user);
		if ($t->data['calenabled']) {
			$t->data['myresponsecal'] = $this->foodle->getMyCalendarResponse($this->user);
			$t->data['defaulttype'] = $this->foodle->getDefaultResponse($this->user);
		}
		if (isset($_REQUEST['tab'])) {
			$t->data['tab'] = $_REQUEST['tab'];
		}

		// Configuration
		$t->data['facebookshare'] = $this->config->getValue('enableFacebookAuth', TRUE);

		$t->data['expired'] = $this->foodle->isExpired();
		$t->data['expire'] = $this->foodle->expire;
		$t->data['expiretext'] = $this->foodle->getExpireText();

		$t->data['bread'] = array(
			array('href' => '/' . $this->config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), 
			array('href' => '/foodle/' . $this->foodle->identifier, 'title' => $this->foodle->name), 
		);


		# echo '<pre>'; print_r($t->data); exit;

		$t->show();


	}
	
}

