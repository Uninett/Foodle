<?php

abstract class API_Authenticated extends API_API {

	protected $auth, $user;

	function __construct($config, $parameters) {
		$this->user = null;
		parent::__construct($config, $parameters);
	}
	
	// protected function requireOAuth() {

	// 	$store = new sspmod_oauth_OAuthStore();
	// 	$server = new sspmod_oauth_OAuthServer($store);
		
	// 	$hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
	// 	$plaintext_method = new OAuthSignatureMethod_PLAINTEXT();
		
	// 	$server->add_signature_method($hmac_method);
	// 	$server->add_signature_method($plaintext_method);
		
	// 	$req = OAuthRequest::from_request();
	// 	list($consumer, $token) = $server->verify_request($req);
		
	// 	$data = $store->getAuthorizedData($token->key);
		
	// 	$userid = FoodleAuth::getUserid($data);
	// 	if (empty($userid)) throw new Exception('User ID not found in stored authenticated session. Should not happen.');
	// 	$this->user = $this->fdb->readUser($userid);

	// }

	protected function optionalAuth() {
		$this->auth = new FoodleAuth($this->fdb);
		$this->auth->requireAuth(TRUE);
	
		if ($this->auth->isAuth()) {
			$this->user = $this->auth->getUser();
		}

		if ($this->auth->checkAnonymousSession()) {
			$this->user = $this->auth->getUser();	
		}

	}
	
	// Authenticate the user
	protected function auth() {
		$this->auth = new FoodleAuth($this->fdb);
		$this->auth->requireAuth(TRUE);

		// $this->user = 'andreas@uninett.no'; return;
		
		if ($this->auth->isAuth()) {
			$this->user = $this->auth->getUser();
			$this->requireUserToken();
			return;
		}
		
		// $this->requireOAuth();
		
	}

	protected function requireUserToken() {
		if (empty($_REQUEST['userToken'])) {
			throw new Exception('Authenticated API Calls require [userToken] to be provided.');
		}
		if (!$this->user->validateToken($_REQUEST['userToken'])) {
			throw new Exception('Invalid User Token provided [' . htmlspecialchars($_REQUEST['userToken']) . ']');
		}
	}
	
	protected function prepare() {
		$this->auth();
	}
	
}

