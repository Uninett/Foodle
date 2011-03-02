<?php

class API_Contacts extends API_Authenticated {



	protected $contacts;

	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
	}
	
	private function isEmail($str) {
		return (filter_var($str, FILTER_VALIDATE_EMAIL) !== FALSE);
	}
	
	
	private static function exclude(&$contacts, $responses) {
		
		if (empty($responses)) return;
		if (empty($contacts)) return;
		
		foreach($responses AS $response) {
			
			foreach($contacts AS $key => $contact) {

				//error_log(var_export($response, TRUE));
				
				if (!empty($response->email) && !empty($contact['email']) ) { 
				
					//error_log('Comparing [' . $response->email . '] with [' .$contact['email'] . ']');				
					if ($response->email == $contact['email'] ) {
						$contacts[$key]['disabled'] = TRUE;
						// error_log('Disabling email [' . $contact['email']. ']' );

					}
				}
				
				if (!empty($response->userid) && !empty($contact['userid']) ) { 
					//error_log('Comparing [' . $response->userid . '] with [' .$contact['userid'] . ']');				
					if ($response->userid == $contact['userid'] ) {
						$contacts[$key]['disabled'] = TRUE;
						// error_log('Disabling email [' . $contact['email']. ']' );

					}
				}
				
			}
			
		}
		// error_log(var_export($contacts, TRUE));
		
		
	}
	
	
	function prepare() {
		parent::prepare();
	
		$this->contacts = new Data_Contacts($this->fdb, $this->user);
		
		$excludes = NULL;
		
		if (!empty($_REQUEST['exclude'])) {
			$foodleid = $_REQUEST['exclude'];		
			$foodle = $this->fdb->readFoodle($foodleid);
			$excludes = $foodle->getResponses();
		}
		
		$contacts = NULL;

		if (!empty($_REQUEST['term'])) {
		
			error_log('Search term was [' . $_REQUEST['term']. ']');
			$contacts = $this->contacts->search($_REQUEST['term']);
		
		
			if ($this->isEmail($_REQUEST['term'])) {
				error_log('is email');
				$email = $_REQUEST['term'];
				if (empty($contacts)) {
					$contacts = array(
						array(
							'email' => $email, 
							'key' => sha1($email)
						)
					);
				}
			}  else {
				// error_log('is not email');			
			}
		
		


		} else {
			error_log('Returning all contacts');
			$contacts = $this->contacts->getContacts(10);
		}
		
		self::exclude(&$contacts, $excludes);
		
		// error_log(var_export($contacts, TRUE));
		
		return $contacts;
		
	}


	
}

