<?php

class API_EventsAnon extends API_API {

	protected $contacts, $list;
	protected $parameters;

	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		
	}
	
	function prepare() {
		// parent::prepare();

		$as = new Data_EventStream($this->fdb);
		
		// error_log('Accessing API_Events');

		
		if (count($this->parameters) === 0) {
			$as->prepareUser();

			$limit = null;
			if (isset($_REQUEST['limit'])) {
				$limit = $_REQUEST['limit'];
			}

			return $as->getData($limit);
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

