<?php

class API_Foodlelist extends API_Authenticated {

	protected $contacts, $list;
	protected $parameters;

	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		
	}
	
	function prepare() {
		parent::prepare();
	
		$entries = $this->fdb->getOwnerEntries($this->user, 10);
	
		return $entries;
	}


	
}

