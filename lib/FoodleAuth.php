<?php

/**
 *
 */
class FoodleAuth {

	
	private $isAuth = FALSE;
	private $attributes = array();
	
	private $sspconfig;
	private $config;

	function __construct() {
		/* Load simpleSAMLphp, configuration and metadata */
		$this->sspconfig = SimpleSAML_Configuration::getInstance();
		$this->config = SimpleSAML_Configuration::getInstance('foodle');
		$session = SimpleSAML_Session::getInstance();
		
		$this->secret = $secret;
		$this->foodleid = $foodleid;
		
		/* Check if valid local session exists.. */
		if ($session->isValid() ) {
		
			$this->isAuth = TRUE;
			$this->attributes = $session->getAttributes();
		
			unset($_COOKIE['foodleSession']); 
			unset($_COOKIE['foodleDisplayName']);
			unset($_COOKIE['foodleEmail']);
		
		}
		
	}
	
	private function setUserID($userid) {
		$this->attributes['eduPersonTargetedID'] = array($userid);
	}
	private function setDisplayName($displayName) {
		$this->attributes['displayName'] = array($displayName);	
	}
	private function setEmail($mail) {
		$this->attributes['mail'] = array($mail);

	}
	
	private function facebookAuth() {
		
		$session = SimpleSAML_Session::getInstance();
		if (!$session->isValid($as)) {
			SimpleSAML_Auth_Default::initLogin('facebook', SimpleSAML_Utilities::selfURL());
		}
		$attributes = $session->getAttributes();
	}
	
	
	private function checkAnonymousSession() {
	
	
		if (array_key_exists('foodleSession', $_COOKIE)) $this->setUserID(substr(sha1('sf65d4d5' . $_COOKIE['foodleSession']), 0, 10));
		if (array_key_exists('foodleDisplayName', $_COOKIE)) $this->setDisplayName($_COOKIE['foodleDisplayName']);
		if (array_key_exists('foodleEmail', $_COOKIE)) $this->setEmail($_COOKIE['foodleEmail']);
	
		if (array_key_exists('sessionBootstrap', $_REQUEST)) {
			
			unset($_COOKIE['foodleSession']); 
			unset($_COOKIE['foodleDisplayName']);
			unset($_COOKIE['foodleEmail']);

			$decode = base64_decode($_REQUEST['sessionBootstrap']);
			$decodes = explode('|', $decode);
			
			setcookie('foodleSession', $decodes[0], time() + 60*60*24*90);
			setcookie('foodleDisplayName', $decodes[1], time() + 60*60*24*90);
			$this->setUserID(substr(sha1('sf65d4d5' . $decodes[0]), 0, 10));
			$this->setDisplayName($decodes[1]);
			if (count($decodes) > 2) {
				setcookie('foodleEmail', $decodes[2], time() + 60*60*24*90);
				$this->setEmail($decodes[2]);
				
			}
			#print_r($decodes); exit;
		} elseif(!array_key_exists('foodleSession', $_COOKIE)) {
			$sessid = SimpleSAML_Utilities::generateID();
			setcookie('foodleSession', $sessid, time() + 60*60*24*90);
			$this->setUserID(substr(sha1('sf65d4d5' . $sessid), 0, 10));
		}
		
		if (array_key_exists('setEmail', $_REQUEST)) {
			setcookie('foodleEmail', $_REQUEST['setEmail'], time() + 60*60*24*90);
			$this->setEmail($_REQUEST['setEmail']);
		}
		if (array_key_exists('setDisplayName', $_REQUEST)) {
			setcookie('foodleDisplayName', $_REQUEST['setDisplayName'], time() + 60*60*24*90);
			$this->setDisplayName($_REQUEST['setDisplayName']);
		}
		

		
		if (array_key_exists('setEmail', $_REQUEST)) {
			$this->sendEmail();
		}
		
		return TRUE;
		
	}
	
	private function sendEmail() {
		
		$fromAddress = $this->config->getValue('fromAddress', 'no-reply@foodle.feide.no');
		
		$url =  FoodleUtils::getUrl() . '?sessionBootstrap=' . $this->getBootstrap();
		
		$message = '<h2>Foodle</h2><p>Seems like you have been using Foodle for the first time. Welcome!
			<p>You have been using Foodle as an anonymous user, and we send you this e-mail so that you can
			edit your Foodle respones by going to the special URL below.
			<p>We strongly reccomend that instead of using Foodle as a anonymous user, you use the login button
			to login to your home institusions user ID. If you do not have an user accout, you may create one for free, by
			using the Feide Guest IdP.
			
			<p>The URL to edit your Foodle response is:
			
			<p><a href="' . $url . '">Go here to edit your Foodle</a></p>
		';
		
		$email = new SimpleSAML_XHTML_EMail($this->getMail(), 'Welcome to Foodle', $fromAddress);
		$email->setBody($message);
		$email->send();
		
	}
	
	private function getBootstrap() {
		
		if (array_key_exists('foodleSession', $_COOKIE)) {
			$str = $_COOKIE['foodleSession'];
			
			$displayName = $this->getDisplayName();			
			$str .= '|' . ($displayName ? $displayName : 'Unknown');

			$email = $this->getMail();
			if ($email) $str .= '|' . $email;

			return base64_encode($str);
		}
		
		return NULL;
	}

	
	public function isAuth() {
		return $this->isAuth;
	}
	
	public function getUserID() {
		if (array_key_exists('eduPersonPrincipalName', $this->attributes)) return $this->attributes['eduPersonPrincipalName'][0];
		if (array_key_exists('eduPersonTargetedID', $this->attributes)) return $this->attributes['eduPersonTargetedID'][0];
		if (array_key_exists('mail', $this->attributes)) return $this->attributes['mail'][0];
		
		throw new Exception('Could not retrieve User ID. None of the required attributes was found [eduPersonPrincipalName] [eduPersonTargetedID] [mail]');
	}
	
	public function getMail() {
		if (array_key_exists('mail', $this->attributes)) return $this->attributes['mail'][0];
		
		return NULL;
	}

	public function getDisplayName() {
		if (array_key_exists('smartname-fullname', $this->attributes)) return $this->attributes['smartname-fullname'][0];
		if (array_key_exists('displayName', $this->attributes)) return $this->attributes['displayName'][0];
		if (array_key_exists('cn', $this->attributes)) return $this->attributes['cn'][0];
		
		return NULL;
	}

	public function requireAuth($allowAnonymous = FALSE) {
		
		if ($this->isAuth) return TRUE;
		
		if (array_key_exists('auth', $_GET) && $_GET['auth'] === 'facebook') {
			$this->facebookAuth();
		}
		
		if (!$allowAnonymous) {
			SimpleSAML_Utilities::redirect(
				'/' . $this->sspconfig->getValue('baseurlpath') . 'saml2/sp/initSSO.php',
				array('RelayState' => SimpleSAML_Utilities::selfURL())
			);
			exit;
		}
		
		$this->checkAnonymousSession();
		
	}
	
	
}
