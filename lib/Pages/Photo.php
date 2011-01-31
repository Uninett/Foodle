<?php

class Pages_Photo {

	protected $config;	
	protected $parameters;
	
	protected $fdb;

	function __construct($config, $parameters) {
		$this->config = $config;
		$this->parameters = $parameters;
		
		$this->fdb = new FoodleDBConnector($this->config);
	}
	
	function show() {
	
#		echo '<pre>'; print_r($this->parameters); exit;

		if (!in_array($this->parameters[1], array('s', 'm', 'l'))) throw new Exception('Invalid photo size [' . $parameters[1] . ']');
		
		if (!preg_match('/^[a-z0-9]+$/', $this->parameters[0])) {
			throw new Exception('Invalid format of image identifier');
		}
		
		$basepath = $this->config->getPathValue('photodir');
		$file = $basepath . $this->parameters[0] . '-' . $this->parameters[1] . '.jpeg';
		
		if (!file_exists($file)) throw new Exception('File not found');
		
		header('Content-type: image/jpeg');
		echo file_get_contents($file);
		exit;
	}


	
}

