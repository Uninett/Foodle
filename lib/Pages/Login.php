<?php

class Pages_Login extends Pages_Page {
	
	private $auth;
	
	
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
				
		// error_log('Foodle /login ');
		$this->auth();
	
	}
	
	// Authenticate the user
	private function auth() {
		$this->auth = new FoodleAuth($this->fdb);
//		$this->auth->requireAuth(TRUE);
		
		if ($this->auth->isAuth()) {
			// error_log('Foodle /login User is already authenticated ');
			$this->complete();
		}
		
		$authtype = null;
		$idp = null;
		
		if (!empty($_REQUEST['auth'])) $authtype = $_REQUEST['auth'];
		if (!empty($_REQUEST['idp'])) $idp = $_REQUEST['idp'];
		
		switch($authtype) {
			
			case 'twitter':
			
				error_log('Foodle /login Proceed with twitter authentication ');
				$this->auth->twitterAuth();
				$this->complete();
			
				break;
			
			case 'saml':
			default:
				
				if (!empty($idp)) {
					error_log('Foodle /login Proceed with SAML authentication. Using IdP [' . $idp . ']');
					$this->auth->requireAuth(FALSE);
				} else {
					error_log('Foodle /login Send user to discovery service...');
					SimpleSAML_Utilities::redirect($this->auth->disco . '?entityID=' . 
						urlencode($this->auth->entityid)  . 
						'&returnIDParam=idp' .
						'&return=' . urlencode(FoodleUtils::getURL() . 'login?')
					);
					
				}
				$this->complete();
				break;
		}

	}
	
	

	function complete() {
		$return = FoodleUtils::getURL();
		if (!empty($_REQUEST['return'])) {
			$return = $_REQUEST['return'];
		}
		
		SimpleSAML_Utilities::redirect($return);

	}
	
	// Process the page.
	function show() {
		if (!empty($this->user))
		$this->complete();
	}
	
}

