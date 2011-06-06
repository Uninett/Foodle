<?php

class API_Files extends API_Authenticated {


	protected $parameters;
	protected $groupid;
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		if (count($parameters) < 1) {
			throw new Exception('Missing group parameter');
		}
		$this->auth();
		$this->groupid = $parameters[0];
		$this->requireMembership();
		
	}
	
	protected function requireMembership() {
		if (!$this->fdb->isMemberOfContactlist($this->user, $this->groupid))
			throw new Exception('Access denied. You are not member of the group specified.');
	}

		
	function prepare() {
		parent::prepare();
		
		return $this->fdb->getFiles($this->groupid);
  
    }
	
}

