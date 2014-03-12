<?php

/**
 *
 */
class FoodleAuth {

	private $isAuth = FALSE;

	
	private $sspconfig;
	private $config;
	
	private $as;
	
	protected $user = null;
	
	private $db;
	
	public $disco, $entityid;
	

	protected $bootstrap = null;

	function __construct($db) {
		$this->db = $db;
	
		/* Load simpleSAMLphp, configuration and metadata */
		$this->sspconfig = SimpleSAML_Configuration::getInstance();
		$this->config = SimpleSAML_Configuration::getInstance('foodle');
		$session = SimpleSAML_Session::getInstance();
		
		$this->disco = $this->config->getString('disco', 'https://disco.uninett.no');
		$this->entityid = $this->config->getString('entityid');
		
		$authsource = $this->config->getString('auth', 'default-sp');
		if ($session->isValid('twitter')) $authsource = 'twitter';

		if ($session->isValid('facebook')) $authsource = 'facebook';
		
		$this->as = new SimpleSAML_Auth_Simple($authsource);

		/* Check if valid local session exists.. */
		if ($this->as->isAuthenticated() ) {
		
			$this->isAuth = TRUE;
			$attributes = $this->as->getAttributes();
			$attributes['idp'] = array($this->as->getAuthData('saml:sp:IdP'));
			
			$this->user = new Data_User($this->db);
			$this->user->anonymous = FALSE;
			$this->user->userid = self::getUserid($attributes);

			$this->user->username = self::getUsername($attributes);
			$this->user->email = self::getEmail($attributes);
			$this->user->org = self::getOrg($attributes);
			$this->user->orgunit = self::getOrgunit($attributes);
			$this->user->location = self::getLocation($attributes);
			$this->user->realm = self::getRealm($attributes);
			$this->user->language = self::getLanguage($attributes);

			// $this->user->calendar = self::getCalendar($attributes);			
			// $this->user->setCalendarsExternal(self::getCalendar($attributes));


			if ($authsource === 'twitter') $this->user->auth = 'twitter';
			if ($authsource === 'facebook') $this->user->auth = 'facebook';

			if (array_key_exists('jpegPhoto', $attributes))
				$this->user->setPhoto($attributes['jpegPhoto'][0]);

			if (array_key_exists('twitter.profile_image_url', $attributes)) {
				try {
					$photo = base64_encode(file_get_contents($attributes['twitter.profile_image_url'][0]));
					
					$this->user->setPhoto($photo);
				} catch(Exception $e) {
				}

			}

				
			
			// TODO twitter photo.
				
			$idp = $this->as->getAuthData('saml:sp:IdP');
			$this->user->idp = $idp;
			

// 			$this->user->photol = self::getPhoto($attributes, 'l');
// 			$this->user->photom = self::getPhoto($attributes, 'm');
// 			$this->user->photos = self::getPhoto($attributes, 's');

//			error_log('User realm is [' .$this->user->realm . ']');
			
// 			echo '<pre>'; print_r($this->user); 
// 			print_r($attributes);
// 			exit;
			
			if ($this->db->userExists($this->user->userid)) {
				$dbUser = $this->db->readUser($this->user->userid);
				$dbUser->anonymous = FALSE;
				
				// echo '<pre>dbUser'; print_r($dbUser); exit;
				
				if (!$dbUser->shaddowed) {
					$modified = $dbUser->updateData($this->user);
					$this->user = $dbUser;
					if ($modified) {
						$this->db->saveUser($this->user);
						error_log('Saving user: ' . var_export($attributes, TRUE));
					}
					
					// echo '<pre>dbUser'; print_r($dbUser); exit;

				} else {
					// error_log('Logging in with a shaddowed user. User data is then not updated...');
					$this->user = $dbUser;
					
				}
				
				
			} else {
				$this->db->saveUser($this->user);
				error_log('Saving user: ' . var_export($attributes, TRUE));
			}
			
			/*
				$username, $name, $email, $org, $orgunit, $photol, $photom, $photos, 
				$notifications, $features, $calendar, $timezone, $location, $realm, $language
			 */
			
			unset($_COOKIE['foodleSession']); 
			unset($_COOKIE['foodleDisplayName']);
			unset($_COOKIE['foodleEmail']);	
		}
	}
	
	
	
	public function getAttributes() {
		
		/* Load simpleSAMLphp, configuration and metadata */
		$this->sspconfig = SimpleSAML_Configuration::getInstance();
		$this->config = SimpleSAML_Configuration::getInstance('foodle');
		$session = SimpleSAML_Session::getInstance();

		
		$authsource = $this->config->getString('auth', 'default-sp');
		if ($session->isValid('twitter')) $authsource = 'twitter';
		if ($session->isValid('facebook')) $authsource = 'facebook';
		
		$this->as = new SimpleSAML_Auth_Simple($authsource);

		/* Check if valid local session exists.. */
		if ($this->as->isAuthenticated() ) {

			$attrs = $this->as->getAttributes();
			$attrs['idp'] = array($this->as->getAuthData('saml:sp:IdP'));

			return $attrs;
		}
		return FALSE;
	}
	
	
// 	private function setUserID($userid) {
// 		$this->attributes['eduPersonTargetedID'] = array($userid);
// 	}
// 	private function setDisplayName($displayName) {
// 		$this->attributes['displayName'] = array($displayName);	
// 	}
// 	private function setEmail($mail) {
// 		$this->attributes['mail'] = array($mail);
// 
// 	}
	
	public function getUser() {
		return $this->user;
	}
	
	private function facebookAuth() {

		$this->as = new SimpleSAML_Auth_Simple('facebook');
		$this->as->requireAuth();			
	}
	
	public function twitterAuth() {

		$this->as = new SimpleSAML_Auth_Simple('twitter');
		$this->as->requireAuth();

	}

	public function registerUser($name, $email) {

		$sessid = SimpleSAML_Utilities::generateID();

		$this->user = new Data_User($this->db);


		setcookie('foodleSession', $sessid, time() + 60*60*24*90, '/');
		$this->user->userid = (substr(sha1('sf65d4d5' . $sessid), 0, 10)); 
		// this salt does NOT need to be secret.

		setcookie('foodleEmail', $email, time() + 60*60*24*90, '/');
		$this->user->email = $email;

	
		setcookie('foodleDisplayName', $name, time() + 60*60*24*90, '/');
		$this->user->username = $name;


		$this->bootstrap = $this->getBootstrap($sessid);
		$this->sendEmail();

		return $this->user;
	
	}
	

	/**
	 * This function checks for parameters that initiates a new anonymous session and adds username, 
	 * and other user data to it.
	 *
	 * The anonymous session is kept in cookies only. Represented by the foodleSession (persistent) cookie.
	 * 
	 * @return [type] [description]
	 */
	public function checkAnonymousSession() {

		$this->user = new Data_User($this->db);

		if (array_key_exists('foodleSession', $_COOKIE)) 
			$this->user->userid = (substr(sha1('sf65d4d5' . $_COOKIE['foodleSession']), 0, 10));
			
		if (array_key_exists('foodleDisplayName', $_COOKIE))
			$this->user->username = ($_COOKIE['foodleDisplayName']);
			
		if (array_key_exists('foodleEmail', $_COOKIE)) 
			$this->user->email = ($_COOKIE['foodleEmail']);
	
		if (array_key_exists('sessionBootstrap', $_REQUEST)) {
			
			unset($_COOKIE['foodleSession']); 
			unset($_COOKIE['foodleDisplayName']);
			unset($_COOKIE['foodleEmail']);

			$decode = base64_decode($_REQUEST['sessionBootstrap']);
			$decodes = explode('|', $decode);
			
			setcookie('foodleSession', $decodes[0], time() + 60*60*24*90, '/');
			setcookie('foodleDisplayName', $decodes[1], time() + 60*60*24*90, '/');
			
			$this->user->userid = (substr(sha1('sf65d4d5' . $decodes[0]), 0, 10));
			$this->user->username = ($decodes[1]);
			if (count($decodes) > 2) {
				setcookie('foodleEmail', $decodes[2], time() + 60*60*24*90, '/');
				$this->user->email = ($decodes[2]);
			}

			
		} 

		// echo "poot"; Exit;

		if (!array_key_exists('foodleSession', $_COOKIE)) {
			$this->user = null;
			return false;
		}



		return TRUE;
		
	}
	
	// If not authenticated return a link to initiate login (with SAML)
	// If authenticated return NULL.
	public function getLoginURL() {
		if (!$this->isAuth()) {
			return FoodleUtils::getUrl() . 'login';
		}
		return NULL;
	}
	
	// If not authenticated return a link to initiate login (with SAML)
	// If authenticated return NULL.
	public function getLogoutURL($path = NULL) {
		if ($this->isAuth()) return $this->as->getLogoutURL($path);
		return NULL;
	}
	
	private function sendEmail() {
		
		$fromAddress = $this->config->getValue('fromAddress', 'no-reply@foodle.org');
		
		$url =  FoodleUtils::getUrl() . '?sessionBootstrap=' . $this->bootstrap;
		
		$message = '<h2>Foodle</h2><p>It seems like you have been using Foodle for the first time. Welcome!
			<p>You have been using Foodle as an anonymous user, and we send you this e-mail so that you can
			edit your Foodle response by going to the special URL below.</p>
			<p>We strongly reccomend that instead of using Foodle as a anonymous user, you use the login button
			to login to your home institusions user ID. If you do not have an user accout, you may create one for free, by
			using the Feide Guest IdP.</p>
			
			<p>The URL to edit your Foodle response is:</p>
			
			<p><tt>' . htmlspecialchars($url) . '</tt></p>
			
			<p><a href="' . htmlspecialchars($url) . '">Go here to edit your Foodle</a></p>
		';
		
		$email = new SimpleSAML_XHTML_EMail($this->user->email, 'Welcome to Foodle', $fromAddress);
		$email->setBody($message);
		$email->send();
		
	}
	
	private function getBootstrap($sessid = null) {
		
		if ($sessid === null) return null;

		$str = $sessid;
		
		$displayName = $this->user->username;			
		$str .= '|' . ($displayName ? $displayName : 'Unknown');

		$email = $this->user->email;
		if ($email) $str .= '|' . $email;

		return base64_encode($str);

	}

	
	public function isAuth() {
		return $this->isAuth;
	}
	
	
	public function validateAttributes() {
		
		$attributes = $this->getAttributes();
		$result = array();
		
		if (empty($attributes)) {
			$result[] = array('fatal', 'No attributes');
			return $result;
		}
	

		self::checkAttribute($result, 'User ID', $this->getUserid($attributes), 'fatal', array(
			array('pattern' => '^.*@(.+?)$')
		));

		self::checkAttribute($result, 'Email address', $this->getEmail($attributes), 'error', array(
			array('pattern' => '^.*@(.+?)$', 'pri' => 'error')
		));

		self::checkAttribute($result, 'Name of user', $this->getUsername($attributes), 'error', array(
			array('pattern' => '\s', 'pri' => 'warning', 'text' => 'Name shuold not be a single word. Both firstname and lastname should be included.')
		));
		
		self::checkAttribute($result, 'User realm', $this->getRealm($attributes), 'error', array(
		));

		// self::checkAttribute($result, 'Freebusy Calendar', $this->getCalendar($attributes), 'warning', array(
		// ));
		

		self::checkAttribute($result, 'Name of Organisation', $this->getOrg($attributes), 'warning', array(
		));		

		self::checkAttribute($result, 'Name of Organisation Unit (Department)', $this->getOrgunit($attributes), 'warning', array(
		));				
		
		self::checkAttribute($result, 'Location of user', $this->getLocation($attributes), 'warning', array(
		));	
		

		self::checkAttribute($result, 'Preferred Language of user', $this->getLanguage($attributes), 'warning', array(
		));			

		return $result;
	}
	
	private static function checkAttribute(&$results, $name, $value, $required = 'fatal', $patterns = array()) {
	
		$ok = TRUE;
		if (empty($value)) {
			$results[] = array($required, 'Attribute [' . $name . '] was empty');
			$ok = FALSE;
			return;
		}
		
		foreach($patterns AS $pattern) {
			if(!preg_match('/' . $pattern['pattern'] . '/', $value)) {
				$pri = 'warning';
				if (!empty($pattern['pri'])) $pri = $pattern['pri'];
				
				$text = 'Attribute [' . $name . '] did not match pattern [' . $pattern . ']';
				if (!empty($pattern['text'])) $text = 'Attribute [' . $name . ']' . $pattern['text'];
				
				$results[] = array($pri, $text);

				$ok = FALSE;
			}			
		}
		
		if ($ok) {
			$results[] = array('ok', 'Attribute [' . $name . '] succeeded all tests');
		}
	}
	
	
	
	public static function getUserid($attributes) {
		if (array_key_exists('eduPersonPrincipalName', $attributes)) 
			return strtolower($attributes['eduPersonPrincipalName'][0]);
		if (array_key_exists('urn:oid:2.16.756.1.2.5.1.1.1', $attributes)) 
			return strtolower($attributes['urn:oid:2.16.756.1.2.5.1.1.1'][0]);			
			
		if (array_key_exists('eduPersonTargetedID', $attributes)) {
			return md5($attributes['eduPersonTargetedID'][0]) . '@' . $attributes['idp'][0];

		}
			
		if (array_key_exists('twitter_at_screen_name', $attributes))
			return strtolower($attributes['twitter_at_screen_name'][0]);
		if (array_key_exists('mail', $attributes)) 
			return strtolower($attributes['mail'][0]);
		
		throw new Exception('Could not retrieve User ID. None of the required attributes was found [eduPersonPrincipalName] [eduPersonTargetedID] [mail]');
	}
	
	protected static function getEmail($attributes) {
		if (array_key_exists('mail', $attributes)) return $attributes['mail'][0];
		return NULL;
	}

	protected static function getUsername($attributes) {
		if (array_key_exists('smartname-fullname', $attributes)) return $attributes['smartname-fullname'][0];
		if (array_key_exists('displayName', $attributes)) return $attributes['displayName'][0];
		if (array_key_exists('cn', $attributes)) return $attributes['cn'][0];
		if (array_key_exists('twitter.name', $attributes)) return $attributes['twitter.name'][0];
		return NULL;
	}

	protected static function getCalendar($attributes) {
		if (array_key_exists('freebusyurl', $attributes)) 
			return $attributes['freebusyurl'];
		return NULL;
	}
	
	protected static function getRealm($attributes) {
		$userid = self::getUserid($attributes);
		if (preg_match('/^@(.*?)$/', $userid, $matches)) {
			return 'twitter.com';
		}
		if (preg_match('/^.*@(.*?)$/', $userid, $matches)) {
			return $matches[1];
		}
		return null;
	}
	
	protected static function getOrg($attributes) {
		if (!empty($attributes['eduPersonOrgDN:o'][0])) return $attributes['eduPersonOrgDN:o'][0];
		if (!empty($attributes['o'][0])) return $attributes['o'][0];
		if (!empty($attributes['eduPersonOrgDN:eduOrgLegalName'][0])) return $attributes['eduPersonOrgDN:eduOrgLegalName'][0];
		if (!empty($attributes['eduPersonOrgDN:cn'][0])) return $attributes['eduPersonOrgDN:cn'][0];

		return null;
	}
	
	
	protected static function getOrgunit($attributes) {
		// find the index of the primary orgunit.
		$index = 0;
		if (!empty($attributes['eduPersonPrimaryOrgUnitDN'][0]) && !empty($attributes['eduPersonOrgUnitDN'])) {
			$index = array_search($attributes['eduPersonPrimaryOrgUnitDN'][0], $attributes['eduPersonOrgUnitDN']);
			if ($index === FALSE) $index = 0;
		}
		
		// Try to get ou and cn with the correct index...
		if (!empty($attributes['eduPersonOrgUnitDN:cn'][$index])) return $attributes['eduPersonOrgUnitDN:cn'][$index];
		if (!empty($attributes['eduPersonOrgUnitDN:ou'][$index])) return $attributes['eduPersonOrgUnitDN:ou'][$index];
		
		// If this fails, try the first entry.
		$index = 0;
		if (!empty($attributes['eduPersonOrgUnitDN:cn'][$index])) return $attributes['eduPersonOrgUnitDN:cn'][$index];
		if (!empty($attributes['eduPersonOrgUnitDN:ou'][$index])) return $attributes['eduPersonOrgUnitDN:ou'][$index];
		
		if (!empty($attributes['ou'][0])) return $attributes['ou'][0];
		
		return null;
	}
	
	protected static function getLocation($attributes) {
		if (array_key_exists('l', $attributes)) return $attributes['l'][0];
		if (array_key_exists('eduPersonOrgDN:l', $attributes)) return $attributes['eduPersonOrgDN:l'][0];
		if (array_key_exists('twitter.location', $attributes)) return $attributes['twitter.location'][0];
		return null;
	}
	protected static function getLanguage($attributes) {
		if (array_key_exists('preferredLanguage', $attributes)) return $attributes['preferredLanguage'][0];
		if (array_key_exists('twitter.lang', $attributes)) return $attributes['twitter.lang'][0];
		return null;
	}

	
	public function requireAuth($allowAnonymous = FALSE) {
		
		#echo '<pre>allowanon:' . var_export($allowAnonymous, TRUE) . '</pre>';
		
		if ($this->isAuth) return TRUE;
		
		if (array_key_exists('auth', $_GET) && $_GET['auth'] === 'facebook') {
			$this->facebookAuth();
		}
		
		if (array_key_exists('auth', $_GET) && $_GET['auth'] === 'twitter') {
			$this->twitterAuth();
		}
		
		if (!empty($_REQUEST['idp']) && !$this->as->isAuthenticated()) {
			$this->as->login(array(
				'saml:idp' => $_REQUEST['idp'],
			));
		}
		
		if (!$allowAnonymous) {
		
			SimpleSAML_Utilities::redirect($this->disco . '?entityID=' . 
				urlencode($this->auth->entityid)  . 
				'&returnIDParam=idp' .
				'&return=' . urlencode(SimpleSAML_Utilities::selfURL() . '?')
			);
			exit;
		
// 			$this->as->requireAuth();
// 			exit;
		}
		
		$this->checkAnonymousSession();
		
	}
	
	
}
