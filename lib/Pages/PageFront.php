<?php

class Pages_PageFront extends Pages_Page {
	
	private $user;
	private $auth;
	
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
				
		$this->auth();
	
	}
	
	// Authenticate the user
	private function auth() {
		$this->auth = new FoodleAuth($this->fdb);
		$this->auth->requireAuth(TRUE);

		$this->user = $this->auth->getUser();

	}
	


	// Process the page.
	function show() {

		$stats = $this->fdb->getStats($this->user->userid);

		$t = new SimpleSAML_XHTML_Template($this->config, 'foodlefront.php', 'foodle_foodle');

		$t->data['gmapsAPI'] = $this->config->getValue('gmapsAPI');
		$t->data['bread'] = array(
			array('title' => 'bc_frontpage'), 
		);

		$t->data['user'] = $this->user;

		$t->data['authenticated'] = $this->auth->isAuth();
		if ($this->auth->isAuth()) {
			$t->data['userToken'] = $this->user->getToken();			
		}
		
		$t->data['showprofile'] = $this->user->loadedFromDB;
		// $t->data['showcontacts'] = $this->auth->isAuth();


		$t->data['requirejs-main'] = 'main-front';

		
		// $t->data['mygroups'] = $this->fdb->getContactlists($this->user);
		
		$t->data['calendarurl'] = FoodleUtils::getUrl() . 'calendar/user/' . $this->user->userid . '/' . $this->user->getToken('calendar');
		
		$t->data['showsupport'] = TRUE;
	
		$t->data['loginurl'] = $this->auth->getLoginURL();
		$t->data['logouturl'] = $this->auth->getLogoutURL();
		
		$t->data['enableFacebookAuth'] = $this->config->getValue('enableFacebookAuth', TRUE);
		$t->data['facebookshare'] = FALSE;
		$t->data['stats'] = $stats;

		$t->show();




	}
	
}

