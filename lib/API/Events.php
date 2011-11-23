<?php

class API_Events extends API_Authenticated {

	protected $contacts, $list;
	protected $parameters;

	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		
	}
	
	function prepare() {
		parent::prepare();

		$as = new Data_EventStream($this->fdb, $this->user);
		
		// error_log('Accessing API_Events');

		
		if (count($this->parameters) === 0) {
			$as->prepareUser();
			return $as->getData();
		}
		
		if (count($this->parameters) > 0) {
			
			if ($this->parameters[0] === 'group') {
				$groupid = $this->parameters[1];
				$as->prepareGroup($groupid);
				return $as->getData();				
			}
			
		}

		throw new Exception('Invalid parameters: ' . var_export($this->parameters, TRUE));
	}


	
}

