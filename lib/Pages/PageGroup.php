<?php



class Pages_PageGroup extends Pages_Page {
	
	protected $auth;
	protected $user;
	protected $groupid;
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		if (count($parameters) < 1) {
			throw new Exception('Missing group parameter');
		}
		$this->groupid = $parameters[0];
		$this->auth();		
		$this->requireMembership();
		
		$this->template = new SimpleSAML_XHTML_Template($this->config, 'group.php', 'foodle_foodle');

	}

	protected function requireMembership() {
		if (!$this->fdb->isMemberOfContactlist($this->user, $this->groupid))
			throw new Exception('Access denied. You are not member of the group specified.');
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
		$this->template->data['authenticated'] = TRUE;
		$this->template->data['showsupport'] = TRUE;
		$this->template->data['loginurl'] = $this->auth->getLoginURL();
		$this->template->data['logouturl'] = $this->auth->getLogoutURL();
		
		$this->template->data['groupInfo'] = $this->fdb->getGroupInfo($this->groupid);
		
		$this->template->data['userToken'] = $this->user->getToken();
		
		$this->template->data['contacts'] = $contacts->getContacts();
				
		$this->template->data['bread'] = array(
			array('href' => '/' . $this->config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), 
			array('href' => '/' . $this->config->getValue('baseurlpath') . 'groups', 'title' => 'Groups'), 
			array('title' => $this->template->data['groupInfo']['name']), 
		);

		$this->template->show();
	}
	
}

