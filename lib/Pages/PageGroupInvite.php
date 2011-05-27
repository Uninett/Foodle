<?php



class Pages_PageGroupInvite extends Pages_Page {
	
	protected $auth;
	protected $user;
	
	protected $groupid, $token;
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		$this->template = new SimpleSAML_XHTML_Template($this->config, 'groupinvite.php', 'foodle_foodle');
		$this->auth();
		
		if (count($parameters) !== 2) {
			throw new Exception('Missing required parameters');
		}
		
		$this->groupid = $parameters[0]; 
		$this->token = $parameters[1];
		
		if (!FoodleUtils::validateInvitationToken($this->groupid, $this->token)) 
			throw new Exception('Invitation Token for joining this group is invalid or has expired. Invitation tokens have a limited validity period');
		
		$this->process();
	}
	
	protected function process() {
		if (empty($_REQUEST['token'])) {
			return;
		}
		
		if (!$this->verifyToken($_REQUEST['token'])) {
			throw new Exception('The verification code to join the list was invalid. Probably someone gave you a broken URL.');
		}
		
		$this->fdb->addToContactlist($this->groupid, $this->user->userid);
		SimpleSAML_Utilities::redirect('/groups');
	}

	protected function verifyToken($token) {
		return ($this->getToken() === $token);
	}
	
	protected function getToken() {
		return $this->user->getUserToken('group:' . $this->groupid);
	}
	
	
	// Authenticate the user
	protected function auth() {
		$this->auth = new FoodleAuth($this->fdb);
		$this->auth->requireAuth(FALSE);
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
		
		$this->template->data['token'] = $this->getToken();
		
		$this->template->data['alreadymember']  = $this->fdb->isMemberOfContactlist($this->user, $this->groupid);
		
		$this->template->data['groupinfo'] = $this->fdb->getGroupInfo($this->groupid);
		
				
		$this->template->data['bread'] = array(
			array('href' => '/' . $this->config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), 
			array('title' => 'Contacts'), 
		);

		$this->template->show();


	}
	
}

