<?php

class Pages_PageDelete extends Pages_PageFoodle {
	
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
	}
	

	protected function delete() {
		$this->foodle->acl($this->user, 'delete');
		$this->foodle->delete();
#		$this->foodle
	}


	// Process the page.
	function show() {

		#echo '<pre>'; print_r($_REQUEST); exit;
		
		

		if (
			isset($_REQUEST['deletefoodle']) && 
			isset($_REQUEST['confirmdelete']) && 
			$_REQUEST['confirmdelete'] === 'yes'
		) {
			$this->delete();
			SimpleSAML_Utilities::redirect(FoodleUtils::getUrl());
		}

		throw new Exception('You did not check the confirm checkbox that you would like to delete the foodle. Go back and confirm.');


	}
	
}

