<?php



class Pages_PageAccountMapping extends Pages_Page {
	
	protected $auth;
	protected $user;
	protected $allusers;
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		$this->template = new SimpleSAML_XHTML_Template($this->config, 'accountmapping.php', 'foodle_foodle');
		$this->auth();
#		$this->timezone = new TimeZone(null, $this->user);		

		if (!$this->user->isAdmin()) throw new Exception('You do not have access to this page.');

		$this->prepare();
		
		$limit = TRUE;
		if (!empty($_REQUEST['showall'])) {
			$limit = FALSE;
		}
		$this->allusers = $this->fdb->getAllUsers($limit);

	}
	

	
	
	// Authenticate the user
	protected function auth() {
		$this->auth = new FoodleAuth($this->fdb);
		$this->auth->requireAuth();
		$this->user = $this->auth->getUser();
	}
	
	function prepare() {
	
		if (!empty($_REQUEST['useridFrom']) && !empty($_REQUEST['useridTo'])) {
		
			$this->fdb->migrateAccount($_REQUEST['useridFrom'], $_REQUEST['useridTo']);
		}
	
	//	$this->emailMatch()
	}

	function nameMatch() {
		$matches = array();

		foreach($this->allusers AS $u) {
			if (empty($u['username'])) continue;
			if (!isset($matches[$u['username']])) {
				$matches[$u['username']] = array();
			}
			$matches[$u['username']][] = $u;
		}
		

		
		$hits = array();
		foreach($matches AS $m) {
			if (count($m) > 1) {
				$hits[] = $m;
			}
		}
// 		echo '<pre>'; print_r($hits); exit;
 		return $hits;
	}

	function emailMatch() {
		$matches = array();
		

		
		foreach($this->allusers AS $u) {
			if (empty($u['email'])) continue;
			if (!isset($matches[$u['email']])) {
				$matches[$u['email']] = array();
			}
			$matches[$u['email']][] = $u;
		}
		

		
		$hits = array();
		foreach($matches AS $m) {
			if (count($m) > 1) {
				$hits[] = $m;
			}
		}
// 		echo '<pre>'; print_r($hits); exit;
 		return $hits;
	}
	
	
	// Process the page.
	function show() {
	
		$this->template->data['user'] = $this->user;
		$this->template->data['authenticated'] = true;
		$this->template->data['showsupport'] = TRUE;
		$this->template->data['loginurl'] = $this->auth->getLoginURL();
		$this->template->data['logouturl'] = $this->auth->getLogoutURL();
		
#		$this->template->data['contacts'] = $contacts->getContacts();

		$this->template->data['hits'] = $this->emailMatch();
		$this->template->data['hitsname'] = $this->nameMatch();
		
		$this->template->data['bread'] = array(
			array('href' => '/' . $this->config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), 
			array('title' => 'Contacts'), 
		);

		$this->template->show();


	}
	
}

