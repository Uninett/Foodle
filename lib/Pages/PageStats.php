<?php

class Pages_PageStats extends Pages_Page {
	
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

	}
	


	// Process the page.
	function show() {

 		$entries = $this->fdb->getYourEntries($this->user);

		$statstotal = $this->fdb->getStatsRealm();
		$statsweek = $this->fdb->getStatsRealm(60*60*24*7);
		$statsday = $this->fdb->getStatsRealm(60*60*24);
		
		$totals = array('total' => 0, 'week' => 0, 'day' => 0 );
		
		$stats = array();
		foreach($statstotal AS $s) {
			$stats[$s['realm']] = array('total' => $s);
			$totals['total'] += $s['c'];
		}
		foreach($statsweek AS $s) {
			$stats[$s['realm']]['week'] = $s;
			$totals['week'] += $s['c'];
		}
		foreach($statsday AS $s) {
			$stats[$s['realm']]['day'] = $s;
			$totals['day'] += $s['c'];
		}
		
		
		$realm = NULL;
		if (!empty($_REQUEST['realm']) && array_key_exists($_REQUEST['realm'], $stats)) {
			$realm = $_REQUEST['realm'];
		}
		$users = $this->fdb->getRecentUsers($realm);

		// ---- o ----- o ---- o ----- o ---- o ----- o



		$t = new SimpleSAML_XHTML_Template($this->config, 'stats.php', 'foodle_foodle');

		$t->data['bread'] = array(
			array('href' => '/' . $this->config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), 
			array('href' => '/stats', 'title' => 'Statistics'), 
		);
		$t->data['user'] = $this->user;
		$t->data['users'] = $users;
		$t->data['statsrealm'] = $stats;
		$t->data['totalsrealm'] = $totals;
		$t->show();




	}
	
}

