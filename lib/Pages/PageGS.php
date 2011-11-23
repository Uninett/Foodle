<?php

class Pages_PageGS extends Pages_Page {
	
	private $user;
	private $auth;
	
	
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
				
		$this->auth();
	
	}
	
	// Authenticate the user
	private function auth() {
	
#		echo '<pre>'; print_r($this); exit;
	
		$this->auth = new FoodleAuth($this->fdb);
		$this->auth->requireAuth(FALSE);

		$this->user = $this->auth->getUser();

	}
	


	
	// Process the page.
	function show() {
		
		// error_log('Fast Pass URL');
		
		$url = FastPass::url($this->config->getValue('getsatisfaction.key'), $this->config->getValue('getsatisfaction.secret'), 
			$this->user->email, $this->user->name, $this->user->userid);

		$furl = 'http://tjenester.ecampus.no/fastpass/finish_signover?company=ecampus&fastpass=' . urlencode($url);
		// error_log('Fast Pass URL generated was: ' . $furl);
		
		SimpleSAML_Utilities::redirect($furl);


	}
	
}

