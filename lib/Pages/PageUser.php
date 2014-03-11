<?php



class Pages_PageUser extends Pages_Page {
	
	protected $auth;
	protected $timezone;
	protected $showuser;
	protected $currentuser;
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		$this->template = new SimpleSAML_XHTML_Template($this->config, 'user.php', 'foodle_foodle');
		//$this->setLocale();
		
		$this->auth();

		
		if (count($parameters) !== 1) {
			throw new Exception('Wrong number of parameters to User Profile page. You should never be sent to this URL.');
		}
		
		$this->showuser = $this->fdb->readUser($parameters[0]);
		
		if (empty($this->showuser)) throw new Exception('Could not find user with ID ' . $parameters[0]);
		
		$this->timezone = new TimeZone($this->fdb, null, $this->showuser);
		
		$this->checkToken();
	}
	
	protected function checkToken() {
		
		if ($this->currentuser->isAdmin()) return;
	
		if (empty($_REQUEST['token']))
		
			throw new Exception('You do not have access to this profile.');
			error_log('User-s token is : ' . $this->showuser->getToken('profile'));
			
		if ($_REQUEST['token'] !== $this->showuser->getToken('profile')) {
			error_log('User-s token is : ' . $this->showuser->getToken('profile'));
			throw new Exception('You do not have access to this profile. The token you provided was invalid.');
		}
	}
	
	
	// Authenticate the user
	protected function auth() {
		$this->auth = new FoodleAuth($this->fdb);
		$this->auth->requireAuth();
		$this->currentuser = $this->auth->getUser();
	}

	
	// Process the page.
	function show() {
	
		if (isset($_REQUEST['submit_profile'])) $this->updateProfile();

		$this->template->data['user'] = $this->showuser;
		$this->template->data['authenticated'] = true;
		
		$this->template->data['showsupport'] = TRUE;
		$this->template->data['timezone'] = $this->timezone;
			
		$this->template->data['loginurl'] = $this->auth->getLoginURL();
		$this->template->data['logouturl'] = $this->auth->getLogoutURL();
		
		$this->template->data['sharedentries'] = $this->fdb->getSharedEntries($this->showuser, $this->currentuser);
		
//		echo '<pre>'; print_r($this->fdb->getSharedEntries($this->showuser, $this->currentuser)); exit;
		
		$this->template->data['bread'] = array(
			array('href' => '/' . $this->config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), 
			array('title' => $this->showuser->username), 
		);

		$this->template->show();


	}
	
}

