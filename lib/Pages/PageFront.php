<?php

class Pages_PageFront extends Pages_Page {
	
	private $user;
	private $auth;
	
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
				
		$this->auth();
	
	}
	
	// Authenticate the user
	private function auth() {
		$this->auth = new FoodleAuth($this->fdb);
		$this->auth->requireAuth(TRUE);

		$this->user = $this->auth->getUser();
		
		if ($_REQUEST['debuguser']) {
			header('Content-type: text/plain; char-set: utf8');
			print_r($this->user); exit;
		}

	}
	


	// Process the page.
	function show() {

		if (isset($_REQUEST['setresponse'])) $this->setResponse();
		if (isset($_REQUEST['discussionentry'])) $this->addDiscussionEntry();


		
		// ---- o ----- o ---- o ----- o ---- o ----- o
		// This part needs to be updated.

 		$entries = $this->fdb->getYourEntries($this->user);

		$allentries = null;
		$adminusers = $this->config->getValue('adminUsers');
		if (in_array($this->user->userid, $adminusers))
			$allentries = $this->fdb->getAllEntries(25);

 		$ownerentries = $this->fdb->getOwnerEntries($this->user, 10);

		$foodleids = array();
 		if(!empty($entries)) foreach($entries AS $e) $foodleids[] = $e['foodleid'];
		if(!empty($allentries)) foreach($allentries AS $e) $foodleids[] = $e['id'];
 		if(!empty($ownerentries)) foreach($ownerentries AS $e) $foodleids[] = $e['id'];

		$statusupdate = $this->fdb->getActivityStream($this->user, $foodleids, 100);
		
		$stats = $this->fdb->getStats($this->user->userid);

		// ---- o ----- o ---- o ----- o ---- o ----- o



		$t = new SimpleSAML_XHTML_Template($this->config, 'foodlefront.php', 'foodle_foodle');

		$t->data['bread'] = array(
			array('title' => 'bc_frontpage'), 
		);

		$t->data['user'] = $this->user;
// 		$t->data['userid'] = $this->user->userid;
// 		$t->data['displayname'] = $this->user->username;

		$t->data['showprofile'] = $this->user->loadedFromDB;

		$t->data['authenticated'] = $this->auth->isAuth();
		
		$t->data['showsupport'] = TRUE;
		
		$t->data['loginurl'] = $this->auth->getLoginURL();
		$t->data['logouturl'] = $this->auth->getLogoutURL();
		
		// ---- o ----- o ---- o ----- o ---- o ----- o
		$t->data['yourentries'] = $entries;
		$t->data['allentries'] = $allentries;
		$t->data['ownerentries'] = $ownerentries;

		$t->data['enableFacebookAuth'] = $this->config->getValue('enableFacebookAuth', TRUE);
		$t->data['facebookshare'] = FALSE;
		$t->data['statusupdate'] = $statusupdate;
		$t->data['stats'] = $stats;
		// ---- o ----- o ---- o ----- o ---- o ----- o

		$t->show();




	}
	
}

