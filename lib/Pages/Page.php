<?php

class Pages_Page {

	protected $config;	
	protected $parameters;
	
	protected $fdb;

	function __construct($config, $parameters) {
		$this->config = $config;
		$this->parameters = $parameters;
		
		$this->fdb = new FoodleDBConnector($this->config);
	}
	
	function show() {
		throw new Exception('Page not implemented');
	}


	
}

