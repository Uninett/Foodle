<?php



class Pages_FixDate extends Pages_PageFoodle {
	
	
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		$this->timezone = new TimeZone();
		
		$this->foodle->acl($this->user, 'write');
	}
	

	protected function saveChanges() {

		$this->foodle->updateFromPostFixDate($this->user);
#		echo '<pre>'; print_r($_REQUEST); print_r($this->foodle); exit;
		$this->foodle->acl($this->user, 'write');
		$this->foodle->save();
		
// 		if (isset($this->user->email)) {
// 			$this->sendMail();
// 		}
		
		$newurl = FoodleUtils::getUrl() . 'foodle/' . $this->foodle->identifier . '#distribute';
		SimpleSAML_Utilities::redirect($newurl);
		exit;
	}

	protected function presentInTimeZone() {
	}
	
	// Process the page.
	function show() {

		if (isset($_REQUEST['save'])) $this->saveChanges();
		
		if (isset($_REQUEST['col'])) {
			$this->foodle->fixDate($_REQUEST['col']);
		}

		$t = new SimpleSAML_XHTML_Template($this->config, 'fixdate.php', 'foodle_foodle');

		$t->data['authenticated'] = $this->auth->isAuth();
		$t->data['user'] = $this->user;
		
		$t->data['timezone'] = $this->timezone;
		$t->data['ftimezone'] = $this->foodle->timezone;

		$t->data['name'] = $this->foodle->name;
		$t->data['identifier'] = $this->foodle->identifier;
		$t->data['descr'] = $this->foodle->descr;
		

		$t->data['foodle'] = $this->foodle;

		$t->data['today'] = date('Y-m-d');
		$t->data['tomorrow'] = date('Y-m-d', time() + 60*60*24 );

		$t->data['bread'] = array(
			array('href' => '/', 'title' => 'bc_frontpage'), 
			array('href' => '/foodle/' . $this->foodle->identifier . '#responses', 'title' => $this->foodle->name), 
			array('title' => 'Fix timeslot')
		);
		$t->show();


	}
	
}

