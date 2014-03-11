<?php

class Pages_PageWidget {

	protected $config;	
	protected $parameters;
	
	protected $fdb;

	function __construct($config, $parameters) {
		$this->config = $config;
		$this->parameters = $parameters;
		
		$this->fdb = new FoodleDBConnector($this->config);
	}
	
	function show() {
	
		$t = new SimpleSAML_XHTML_Template($this->config, 'widget.php', 'foodle_foodle');
		$t->data['bread'] = array(
			array('href' => '/' . $this->config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), 
			array('title' => 'Support'), 
		);
		$t->data['gmapsAPI'] = $this->config->getValue('gmapsAPI');
		$t->data['requirejs-main'] = 'main-widget';

		$t->show();



	}


	
}

