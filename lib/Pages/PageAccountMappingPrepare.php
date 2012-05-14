<?php



class Pages_PageAccountMappingPrepare extends Pages_Page {
	
	protected $auth;
	protected $user;
	protected $allusers;
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);

		$this->auth();
		if (!$this->user->isAdmin()) throw new Exception('You do not have access to this page.');
				
		$this->template = new SimpleSAML_XHTML_Template($this->config, 'accountmapping-prepare.php', 'foodle_foodle');

		$this->prepare();
		
		$limit = TRUE;
		if (!empty($_REQUEST['showall'])) {
			$limit = FALSE;
		}
		$realmFrom = 'hio.no';
		if (!empty($_REQUEST['realmFrom'])) {
			$realmFrom = $_REQUEST['realmFrom'];
		}
		
		$realmTo = 'hioa.no';
		if (!empty($_REQUEST['realmTo'])) {
			$realmTo = $_REQUEST['realmTo'];
		}
		
		$this->template->data['realmFrom'] = $realmFrom;
		$this->template->data['realmTo'] = $realmTo;
		
		$this->allusers = $this->fdb->searchUsersRealm($realmFrom);
		
		
		foreach($this->allusers AS $k => $u) {
			$this->allusers[$k]['stats'] = $this->fdb->getUserStats($u['userid']);
		}

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
				SimpleSAML_Utilities::redirect('/accountmappingprepare');
		}
	
	}


	
	// Process the page.
	function show() {
	
		$this->template->data['user'] = $this->user;
		$this->template->data['authenticated'] = true;
		$this->template->data['showsupport'] = TRUE;
		$this->template->data['loginurl'] = $this->auth->getLoginURL();
		$this->template->data['logouturl'] = $this->auth->getLogoutURL();
		
		$this->template->data['allusers'] = $this->allusers;

		
		$this->template->data['bread'] = array(
			array('href' => '/' . $this->config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), 
			array('title' => 'Contacts'), 
		);

		$this->template->show();


	}
	
}

