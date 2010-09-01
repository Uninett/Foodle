<?php



class Pages_PageFoodle extends Pages_Page {
	
	protected $foodle;
	protected $user;
	protected $foodleid;
	protected $foodlepath;
	
	protected $loginurl;
	protected $logouturl;
	
	protected $auth;
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		if (count($parameters) < 1) throw new Exception('Missing [foodleid] parameter in URL.');
		
		Data_Foodle::requireValidIdentifier($parameters[0]);
		$this->foodleid = $parameters[0];
		$this->foodlepath = '/foodle/' . $this->foodleid;
		
		$this->foodle = $this->fdb->readFoodle($this->foodleid);
		
		$this->auth();
	}
	

	
	
	// Authenticate the user
	protected function auth() {
		$this->auth = new FoodleAuth();
		$this->auth->requireAuth(TRUE);

		$this->user = new Data_User($this->fdb);
		
		if ($this->auth->isAuth()) {
			$this->user = new Data_User($this->fdb);
			$this->user->email = $this->auth->getMail();
			$this->user->userid = $this->auth->getUserID();
			$this->user->name = $this->auth->getDisplayName();
			$this->user->calendarURL = $this->auth->getCalendarURL();
		}

	}

	
	// Save the users response..
	protected function setResponse() {
		$myresponse = $this->foodle->getMyResponse($this->user);
		$myresponse->updateFromPost($this->user);
		
		#echo '<pre>Setting manual:'; print_r($myresponse); exit;
		$myresponse->save();
		
		SimpleSAML_Utilities::redirect(SimpleSAML_Utilities::selfURLNoQuery() . '?tab=1' );
	}
	
	// Save the users response..
	protected function setResponseCalendar() {
		$myresponse = $this->foodle->getMyCalendarResponse($this->user);

		$myresponse->updateFromical($this->user);

		#echo '<pre>Setting icalendar:'; print_r($myresponse); exit;
		$myresponse->save();
		SimpleSAML_Utilities::redirect(SimpleSAML_Utilities::selfURLNoQuery() . '?tab=1' );
	}


	protected function addDiscussionEntry() {
		$this->fdb->addDiscussionEntry($this->foodle, $this->user, $_REQUEST['message']);
		SimpleSAML_Utilities::redirect(SimpleSAML_Utilities::selfURLNoQuery() . '?tab=2' );
	}
	
	// Process the page.
	function show() {

		if (isset($_REQUEST['save'])) $this->setResponse();
		if (isset($_REQUEST['savecal'])) $this->setResponseCalendar();
		if (isset($_REQUEST['discussionentry'])) $this->addDiscussionEntry();

		$cols = array();
		$this->foodle->getColumnList(&$cols);

		// echo '<pre>'; 
		// print_r($this->foodle->getColumnDates());
		// print_r($cols); exit;

		$t = new SimpleSAML_XHTML_Template($this->config, 'foodleresponse.php', 'foodle_foodle');

		$t->data['title'] = 'Foodle :: ' . $this->foodle->name;
		$t->data['foodle'] = $this->foodle;
		$t->data['user'] = $this->user;
		$t->data['foodlepath'] = $this->foodlepath;
		

		// if ($this->user->hasCalendar()) echo 'User has calendar';
		// if ($this->foodle->calendarEnabled()) echo 'Foodle has calendar';
		
		$t->data['calenabled'] = ($this->foodle->calendarEnabled() && $this->user->hasCalendar());
		$t->data['myresponse'] = $this->foodle->getMyResponse($this->user);
		if ($t->data['calenabled']) {
			$t->data['myresponsecal'] = $this->foodle->getMyCalendarResponse($this->user);
			$t->data['defaulttype'] = $this->foodle->getDefaultResponse($this->user);
		}
		if (isset($_REQUEST['tab'])) {
			$t->data['tab'] = $_REQUEST['tab'];
		} elseif($t->data['myresponse']->loadedFromDB) {
			$t->data['tab'] = '1';
		}

		// Configuration
		$t->data['facebookshare'] = $this->config->getValue('enableFacebookAuth', TRUE);

		$t->data['expired'] = $this->foodle->isExpired();
		$t->data['expire'] = $this->foodle->expire;
		$t->data['expiretext'] = $this->foodle->getExpireText();
		
		$t->data['maxcol'] = $this->foodle->maxcolumn;
		$t->data['maxnum'] = $this->foodle->maxentries;
		$t->data['used'] = $this->foodle->countResponses();
		
				
		$t->data['authenticated'] = $this->auth->isAuth();
		$t->data['loginurl'] = $this->auth->getLoginURL();
		$t->data['logouturl'] = $this->auth->getLogoutURL('/');
		
		$isAdmin = ($this->user->userid == $this->foodle->owner) || ($this->user->userid == 'andreas@uninett.no') || ($this->user->userid == 'andreas@rnd.feide.no');
		
		$t->data['owner'] = $isAdmin;
		$t->data['ownerid'] = $this->foodle->owner;
		$t->data['showsharing'] = $isAdmin;
		
		$t->data['url'] = FoodleUtils::getUrl() . 'foodle/' . $this->foodle->identifier;

		// 
		// $et->data['header'] = $foodle->getName();
		// $et->data['identifier'] = $foodle->getIdentifier();
		// $et->data['descr'] = $foodle->getDescr();
		// $et->data['expire'] = $foodle->getExpire();
		// $et->data['expired'] = $foodle->expired();
		// $et->data['expiretext'] = $foodle->getExpireText();
		// $et->data['columns'] = $foodle->getColumns();
		// 
		// $et->data['url'] = FoodleUtils::getUrl() . 'foodle.php?id=' . $_REQUEST['id'];
		// 
		// 
		// $et->data['maxcol'] = $maxcol;
		// $et->data['maxnum'] = $maxnum;
		// $et->data['used'] = $used;
		// 
		// $et->data['registerEmail'] = (empty($email));
		// 

		// $et->data['userid'] = $userid;
		// $et->data['displayname'] = $displayname;
		// $et->data['email'] = $email;
		// 
		// $et->data['authenticated'] = $foodleauth->isAuth();
		// 
		// 
		// $et->data['loginurl'] = $loginurl;
		// $et->data['logouturl'] = $logouturl;
		// 		
		// $et->data['yourentry'] = $foodle->getYourEntry($displayname);
		// $et->data['otherentries'] = $foodle->getOtherEntries();
		// $et->data['discussion'] = $foodle->getDiscussion();
		// 
		// $et->data['identifier'] = $foodle->getIdentifier();
		// $et->data['thisisanewentry'] = $thisisanewentry;
		// 

		// $tab = 0;
		// if (isset($_REQUEST['tab'])) $tab = $_REQUEST['tab'];
		// 
		// $et->data['tab'] = $tab;
		// 
		$t->data['bread'] = array(
			array('href' => '/' . $this->config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), 
			array('href' => '/foodle/' . $this->foodle->identifier, 'title' => $this->foodle->name), 
		);


		# echo '<pre>'; print_r($t->data); exit;

		$t->show();


	}
	
}

