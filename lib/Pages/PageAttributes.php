<?php



class Pages_PageAttributes extends Pages_Page {
	
	
	protected $template;
	protected $auth;
	protected $timezone;
	protected $user;

	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		$this->template = new SimpleSAML_XHTML_Template($this->config, 'attributecheck.php', 'foodle_foodle');
		$this->setLocale();
		

		$this->auth();
		$this->timezone = new TimeZone($this->fdb, null, $this->user);		
	}
	
	protected function setLocale() {
		$lang = $this->template->getLanguage();
		
		// error_log('Language: ' . $lang);
		
		$localeMap = array(
			'no' => 'nb_NO.utf8',
			'nn' => 'nn_NO.utf8',
// 			'de' => 'de_DE',
// 			'fr' => 'fr_FR',
		);
		
		if (isset($localeMap[$lang])) {	
			setlocale(LC_ALL, $localeMap[$lang]);
			// error_log('Setting locale to ' . $localeMap[$lang]);
		}
		
	}
	
	
	
	// Authenticate the user
	protected function auth() {
		$this->auth = new FoodleAuth($this->fdb);
		$this->auth->requireAuth();
		$this->user = $this->auth->getUser();
	}
	
	// Process the page.
	function show() {
	
		if (isset($_REQUEST['submit_profile'])) $this->updateProfile();

		$this->template->data['user'] = $this->user;
		$this->template->data['auth'] = $this->auth;
		$this->template->data['authenticated'] = true;
		
		$this->template->data['attributes'] = $this->auth->getAttributes();
		$this->template->data['validate'] = $this->auth->validateAttributes();
		
		$this->template->data['showsupport'] = TRUE;
		$this->template->data['timezone'] = $this->timezone;
			
		$this->template->data['loginurl'] = $this->auth->getLoginURL();
		$this->template->data['logouturl'] = $this->auth->getLogoutURL();

		$this->template->data['bread'] = array(
			array('href' => '/' . $this->config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), 
			array('href' => '/profile', 'title' => $this->user->username), 
			array('title' => 'bc_attribute_check'), 
		);

		$this->template->show();


	}
	
}

