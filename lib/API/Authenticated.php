<?php

abstract class API_Authenticated extends API_API {

	protected $auth, $user;

	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		

	}
	
	// Authenticate the user
	protected function auth() {
		$this->auth = new FoodleAuth($this->fdb);
		$this->auth->requireAuth(FALSE);
		$this->user = $this->auth->getUser();
	}
		
	protected function prepare() {
		$this->auth();
	}
	
}

