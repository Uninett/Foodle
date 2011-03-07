<?php

class Pages_PageSupport extends Pages_Page {
	
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

		$t = new SimpleSAML_XHTML_Template($this->config, 'support.php', 'foodle_foodle');
		$t->data['bread'] = array(
			array('href' => '/' . $this->config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), 
			array('title' => 'Support'), 
		);

		$t->data['user'] = $this->user;
		$t->data['userid'] = $this->user->userid;
		$t->data['displayname'] = $this->user->username;

 		$t->data['authenticated'] = $this->auth->isAuth();
		
		FastPass::$domain = "tjenester.ecampus.no";
		$t->data['getsatisfactionscript'] = FastPass::script(
			$this->config->getValue('getsatisfaction.key'), $this->config->getValue('getsatisfaction.secret'), 	
			$this->user->email, $this->user->username, $this->user->userid);
		
// 		$t->data['loginurl'] = $this->auth->getLoginURL();
// 		$t->data['url'] = $this->auth->getURL();

		
		$t->data['loginurl'] = $this->auth->getLoginURL();
		$t->data['logouturl'] = $this->auth->getLogoutURL();
		
		$t->show();

	}
	
}

