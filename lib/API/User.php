<?php

class API_User extends API_Authenticated {


	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		
	}
	
	function prepare() {


		$parameters = null;
		$object = null;

		self::optionalAuth();

		if (self::route('post', '^/api/user/register', $parameters, $object)) {

			$name = filter_var($object['name'], FILTER_SANITIZE_SPECIAL_CHARS);
			$email = filter_var($object['email'], FILTER_SANITIZE_EMAIL);

			
			$this->user = $this->auth->registerUser($name, $email);

			$res = array('authenticated' => true);
			$res['user'] = $this->user->getView();
			$res['token'] = $this->user->getToken();

			return $res;

		}


		if ($this->user === null) {
			return array('authenticated' => false);
		}


		// All requests point at a specific Foodle
		if (self::route('post', '^/api/user/timezone', $parameters, $object)) {

			$tz = filter_var($object, FILTER_SANITIZE_EMAIL);

			$this->user->timezone = strip_tags($object);
			$this->fdb->saveUser($this->user);
			return true;

		}



		$res = array('authenticated' => true);
		$res['user'] = $this->user->getView();
		$res['token'] = $this->user->getToken();

		// header('Content-type: text/plain; charset=utf-8');
		// print_r($this->user);
		return $res;

		throw new Exception('Invalid request parameters');
	}


	
}

