<?php

class API_IdPList extends API_API {


	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
	}
	
	function prepare() {
		return $this->fdb->getIdPList();		
	}


	
}

