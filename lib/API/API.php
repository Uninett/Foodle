<?php

abstract class API_API {

	protected $config;	
	protected $parameters;
	
	protected $fdb;

	function __construct($config, $parameters) {
		$this->config = $config;
		$this->parameters = $parameters;
		
		$this->fdb = new FoodleDBConnector($this->config);
	}
	
	protected function prepare() {
		throw new Exception('API not implemented');
	}
	
	public function show() {
	
		$returnobj = array('status' => 'ok');

	
		try {			
			$returnobj['data'] = $this->prepare();			

		} catch(Exception $e) {
			
			$returnobj['status'] = 'error';
			$returnobj['message'] = $e->getMessage();
			
			error_log('API returning error: ' . $e->getMessage());
			
		}
		
		header('Content-type: application/json; charset=utf-8');
		echo json_encode($returnobj);
		exit;
	}

	
}

