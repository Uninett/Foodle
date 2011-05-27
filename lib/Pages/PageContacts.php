<?php



class Pages_PageContacts extends Pages_Page {
	
	protected $auth;
	protected $user;
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		$this->template = new SimpleSAML_XHTML_Template($this->config, 'contacts.php', 'foodle_foodle');
		$this->auth();
#		$this->timezone = new TimeZone(null, $this->user);		
	}
	

	
	
	// Authenticate the user
	protected function auth() {
		$this->auth = new FoodleAuth($this->fdb);
		$this->auth->requireAuth();
		$this->user = $this->auth->getUser();
	}

	
	
	// Process the page.
	function show() {
	
		$contacts = new Data_Contacts($this->fdb, $this->user);
	
		$this->template->data['user'] = $this->user;
		$this->template->data['authenticated'] = true;
		$this->template->data['showsupport'] = TRUE;
		$this->template->data['loginurl'] = $this->auth->getLoginURL();
		$this->template->data['logouturl'] = $this->auth->getLogoutURL();
		
		$this->template->data['contacts'] = $contacts->getContacts();
				
		$this->template->data['bread'] = array(
			array('href' => '/' . $this->config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), 
			array('title' => 'Contacts'), 
		);

		$this->template->show();


	}
	
}

