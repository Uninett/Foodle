<?php

class API_Activity extends API_Authenticated {

	protected $contacts, $list;
	protected $parameters;

	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		
	}
	
	function prepare() {
		parent::prepare();
	
		#$entries = $this->fdb->getOwnerEntries($this->user, 10);

		$as = new Data_ActivityStream($this->fdb, $this->user);

		
		if (count($this->parameters) === 0) {
			$as->prepareUser();
			return $as->getData();
		}
		
		if (count($this->parameters) > 0) {
			
			if ($this->parameters[0] === 'feed') {
				$feed = $this->parameters[1];
				$as->prepareFeed($feed);
				return $as->getData();				
			}
			
		}

		throw new Exception('Invalid parameters: ' . var_export($this->parameters, TRUE));
	}


	
}

