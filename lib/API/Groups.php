<?php

class API_Groups extends API_API {

	protected $contacts, $list;
	protected $parameters;

	protected $user;

	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		if (count($parameters) < 1) {
			throw new Exception('Missing group parameter');
		}
		// $this->auth();
		$userid = $parameters[0];

		// echo $this->config->getValue('uwap-api-user') . ':';
		// 	echo $this->config->getValue('uwap-api-pass');
		// 	exit;

		if (!isset($_SERVER['PHP_AUTH_USER'])) {
			header('WWW-Authenticate: Basic realm="Foodle"');
			header('HTTP/1.0 401 Unauthorized');
			exit;
		} else {
			
			if ($this->config->getValue('uwap-api-user') !== $_SERVER['PHP_AUTH_USER']) {
				throw new Exception('Invalid API user');
			}
			if ($this->config->getValue('uwap-api-pass') !== $_SERVER['PHP_AUTH_PW']) {
				throw new Exception('Invalid API user password');
			}
		}


		$this->user = new Data_User($this->fdb);
		$this->user->userid = $userid;


		$this->config->getValue('uwap-api-pass');

	}
	

	protected function prepare() {
		// parent::prepare();

		$groups = array();
		$rawgroups = $this->fdb->getContactlists($this->user);

		$rolemap  = array(
			'owner' => 'admin',
			'admin' => 'admin',
			'member' => 'member'
		);

		foreach($rawgroups AS $g) {
			$gid = $g['id'];

			$groups[$gid] = array(
				'title' => $g['name'] . ' [Foodle]',
				'role' => $g['role']
			);
		}


		return $groups;
	}




}