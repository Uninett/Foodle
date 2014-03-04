<?php

class API_Foodle extends API_API {

	protected $contacts, $list;
	protected $parameters;
	protected $foodleid, $foodle, $responses;

	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		
	}
	
	function prepare() {
	
		if (empty($this->parameters[0])) {
			throw new Exception('Foodle identifier missing');
		}

		Data_Foodle::requireValidIdentifier($this->parameters[0]);
		$this->foodleid = $this->parameters[0];

		$this->foodle = $this->fdb->readFoodle($this->foodleid);
		
		if (count($this->parameters) === 1) {
			return $this->foodle;
		}
		
		$subrequest = $this->parameters[1];


		
		if ($subrequest === 'responders') {
			
			$this->responses = $this->fdb->readResponses($this->foodle, NULL, FALSE);

			$respobj = array();
			foreach($this->responses AS $key => $r) {
				$respobj[$key] = $r->getView();
			}

			return $respobj;
			
		}




		throw new Exception('Invalid request parameters');
	}


	
}

