<?php



class Pages_PageProfile extends Pages_Page {
	
	protected $auth;
	protected $timezone;
	protected $user;
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		$this->template = new SimpleSAML_XHTML_Template($this->config, 'profile.php', 'foodle_foodle');
		$this->setLocale();
		

		$this->auth();
		$this->timezone = new TimeZone(null, $this->user);		
	}
	
	protected function setLocale() {
		$lang = $this->template->getLanguage();
		
		error_log('Language: ' . $lang);
		
		$localeMap = array(
			'no' => 'nb_NO.utf8',
			'nn' => 'nn_NO.utf8',
// 			'de' => 'de_DE',
// 			'fr' => 'fr_FR',
		);
		
		if (isset($localeMap[$lang])) {	
			setlocale(LC_ALL, $localeMap[$lang]);
			error_log('Setting locale to ' . $localeMap[$lang]);
		}
		
	}
	
	
	
	// Authenticate the user
	protected function auth() {
		$this->auth = new FoodleAuth($this->fdb);
		$this->auth->requireAuth();
		$this->user = $this->auth->getUser();
	}

	
	protected function updateProfile() {
		
		if (isset($_REQUEST['timezone'])) {
			$this->user->timezone = $_REQUEST['timezone'];
		}

		$notifications = array(
			'newresponse' => FALSE,
			'newfoodle'   => FALSE,
			'otherstatus' => FALSE,
			'news' => FALSE,
		);		
		foreach($_REQUEST AS $key => $value ) {			
			if (preg_match('/notify_(.*)$/', $key, $matches)) {
				$k = $matches[1];
				$notifications[$k] = TRUE;
			}			
		}
		foreach($notifications AS $k => $v) {
			$this->user->setNotification($k, $v);
		}

		
		#echo '<pre>'; print_r($this->user); print_r($_REQUEST); exit;
		$this->fdb->saveUser($this->user);
		
	}
	
	// Process the page.
	function show() {
	
		if (isset($_REQUEST['submit_profile'])) $this->updateProfile();
		
		if (!empty($_REQUEST['debug'])) {
			header('Content-Type: text/plain; charset=utf-8'); print_r($this->user); exit;
		}

		$this->template->data['user'] = $this->user;
		$this->template->data['authenticated'] = true;
		
		$this->template->data['showsupport'] = TRUE;
		$this->template->data['timezone'] = $this->timezone;
			
		$this->template->data['loginurl'] = $this->auth->getLoginURL();
		$this->template->data['logouturl'] = $this->auth->getLogoutURL();
		
		$this->template->data['bread'] = array(
			array('href' => '/' . $this->config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), 
			array('title' => $this->user->username), 
		);

		$this->template->show();


	}
	
}

