<?php

class Pages_PageCreate extends Pages_Page {
	
	private $user;
	private $auth;
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		$this->auth();
	}
	
	// Authenticate the user
	private function auth() {
		$this->auth = new FoodleAuth();
		$this->auth->requireAuth(FALSE);

		$this->user = new User($this->fdb);
		$this->user->email = $this->auth->getMail();
		$this->user->userid = $this->auth->getUserID();
		$this->user->name = $this->auth->getDisplayName();
	}
	
	
	function addEntry() {
	
		$foodle = new Foodle($this->fdb);
		$foodle->updateFromPost($this->user);
		#echo '<pre>'; print_r($foodle); exit;
		$foodle->save();
		
		$t = new SimpleSAML_XHTML_Template($this->config, 'foodleready.php', 'foodle_foodle');

		$t->data['url'] = FoodleUtils::getUrl() . 'foodle/' . $foodle->identifier;
		$t->data['bread'] = array(
			array('href' => '/', 'title' => 'bc_frontpage'), 
			array('href' => '/foodle/' . $foodle->identifier, 'title' => $foodle->name), 
			array('title' => 'bc_ready')
		);

		$t->show();
		exit;
	}
	
	
	
	
	// Process the page.
	function show() {

		if (isset($_REQUEST['save'])) $this->addEntry();

		$t = new SimpleSAML_XHTML_Template($this->config, 'foodlecreate.php', 'foodle_foodle');

		$t->data['authenticated'] = $this->auth->isAuth();
		$t->data['user'] = $this->user;		
		$t->data['loginurl'] = $this->auth->getLoginURL();
		$t->data['logouturl'] = $this->auth->getLogoutURL('/');

		$t->data['bread'] = array(
			array('href' => '/', 'title' => 'bc_frontpage'), 
			array('title' => 'bc_createnew')
		);
		$t->show();

	}
	
}

