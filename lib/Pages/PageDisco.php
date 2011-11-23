<?php

class Pages_PageDisco extends Pages_Page {
	
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
	
	}


	
	// Process the page.
	function show() {

		$t = new SimpleSAML_XHTML_Template($this->config, 'disco.php', 'foodle_foodle');


		
		$t->show();

	}
	
}

