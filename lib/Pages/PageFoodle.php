<?php



class Pages_PageFoodle extends Pages_Page {
	
	protected $foodle;
	protected $user;
	protected $foodleid;
	protected $foodlepath;
	
	protected $loginurl;
	protected $logouturl;
	
	protected $timezone;
	protected $timezoneEnable;
	protected $calendarEnabled;
	
	protected $auth;
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		if (count($parameters) < 1) throw new Exception('Missing [foodleid] parameter in URL.');
		
		Data_Foodle::requireValidIdentifier($parameters[0]);
		$this->foodleid = $parameters[0];
		$this->foodlepath = '/foodle/' . $this->foodleid;


		$this->timezone = new TimeZone();
				
		$this->foodle = $this->fdb->readFoodle($this->foodleid);
		$this->foodle->getColumnDates();
		$this->calendarEnabled = $this->foodle->calendarEnabled();
		$this->timezoneEnable = $this->foodle->timeZoneEnabled();

		$this->presentInTimeZone();
		
		$this->auth();
	}
	

	protected function presentInTimeZone() {
		if ($this->timezoneEnable) {
			$this->foodle->presentInTimeZone($this->timezone->getSelectedTimeZone());
		}
	}
	
	
	// Authenticate the user
	protected function auth() {
		$this->auth = new FoodleAuth();
		$this->auth->requireAuth($this->foodle->allowanonymous);

		$this->user = new Data_User($this->fdb);
		
		if ($this->auth->isAuth()) {
			$this->user = new Data_User($this->fdb);
			$this->user->email = $this->auth->getMail();
			$this->user->userid = $this->auth->getUserID();
			$this->user->name = $this->auth->getDisplayName();
			$this->user->calendarURL = $this->auth->getCalendarURL();
		} else {
			$this->user = new Data_User($this->fdb);
			$this->user->email = $this->auth->getMail();
			$this->user->userid = $this->auth->getUserID();
			$this->user->name = $this->auth->getDisplayName();			
	
		}


	}

	
	protected function sendMail() {
		$url = FoodleUtils::getUrl() . 'foodle/' . $this->foodle->identifier;
		$name = $this->foodle->name;
		$to = $this->user->email;
		$mail = '
		
		<p>Hi, your response to the Foodle named <i>' . htmlspecialchars($name) . '</i> was successfully stored.</p>
		
		<p>You may re-enter the Foodle link below to update your response, and view other responses:
		<ul>
			<li><a href="' . $url . '">Edit your response for this Foodle</a></li>
			<li><a href="' . $url . '#responses">View responses of other participants</a></li>
		</ul></p>

		<h2>Did you know?</h2>
		<p>You may also create new Foodles on your own, and invite others to respond.
		<ul>
			<li><a href="http://foodl.org">Go to Foodl.org to create a new Foodle.</a></li>
		</ul></p>
		
		';
		$mailer = new Foodle_EMail($to, 'Foodle: ' . htmlspecialchars($name), 'Foodl.org <no-reply@foodl.org>');
		$mailer->setBody($mail);
		$mailer->send();
		
		#echo '<pre>'; print_r($mail); exit;

	}
	
	// Save the users response..
	protected function setResponse() {
		$myresponse = $this->foodle->getMyResponse($this->user);
		$myresponse->updateFromPost($this->user);
		
		#echo '<pre>Setting manual:'; print_r($myresponse); exit;
		$myresponse->save();
		
		if (isset($this->user->email)) {
			/* 
				Disabled until we get a user profile where people can unset the preference for email notifications.
			*/
		//	$this->sendMail();
		}
		
		SimpleSAML_Utilities::redirect(SimpleSAML_Utilities::selfURLNoQuery() . '#responses' );
	}
	
	// Save the users response..
	protected function setResponseCalendar() {
		$myresponse = $this->foodle->getMyCalendarResponse($this->user);

		$myresponse->updateFromical($this->user);

		#echo '<pre>Setting icalendar:'; print_r($myresponse); exit;
		$myresponse->save();
		SimpleSAML_Utilities::redirect(SimpleSAML_Utilities::selfURLNoQuery() . '#responses' );
	}


	protected function addDiscussionEntry() {
		$this->fdb->addDiscussionEntry($this->foodle, $this->user, $_REQUEST['message']);
		SimpleSAML_Utilities::redirect(SimpleSAML_Utilities::selfURLNoQuery() . '#discussion' );
	}
	
	// Process the page.
	function show() {

		if (isset($_REQUEST['save'])) $this->setResponse();
		if (isset($_REQUEST['savecal'])) $this->setResponseCalendar();
		if (isset($_REQUEST['discussionentry'])) $this->addDiscussionEntry();

		$t = new SimpleSAML_XHTML_Template($this->config, 'foodleresponse.php', 'foodle_foodle');

		$t->data['title'] = 'Foodle :: ' . $this->foodle->name;
		$t->data['foodle'] = $this->foodle;
		$t->data['user'] = $this->user;
		$t->data['foodlepath'] = $this->foodlepath;
		

		// if ($this->user->hasCalendar()) echo 'User has calendar';
		// if ($this->foodle->calendarEnabled()) echo 'Foodle has calendar';
		
		$t->data['calenabled'] = ($this->calendarEnabled && $this->user->hasCalendar());
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

		if ($this->timezoneEnable) {
			if (isset($_REQUEST['timezone'])) {
				$t->data['stimezone'] = $_REQUEST['timezone'];
			}
			$t->data['timezone'] = $this->timezone;
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
				
		$t->data['showdebug'] = ($this->user->userid == 'andreas@uninett.no') || ($this->user->userid == 'andreas@rnd.feide.no');
		if (isset($_REQUEST['debug'])) {
			$t->data['showdebug'] = TRUE;
		}
		$t->data['showsupport'] = TRUE;
		$t->data['showdelete'] = $isAdmin;

		$t->data['customDistribute'] = array();
		$t->data['customDistribute'][] = new EmbedDistribute($this->foodle, $t);
		if (preg_match('/^.*?@uninett\.no$/', $this->user->userid)) {
			$t->data['customDistribute'][] = new UNINETTDistribute($this->foodle, $t);			
		}


		
		$t->data['debugUser'] = $this->user->debug();
		$t->data['debugFoodle'] = $this->foodle->debug();
		$t->data['debugCalendar'] = $this->user->debugCalendar();
		
		$t->data['url'] = FoodleUtils::getUrl() . 'foodle/' . $this->foodle->identifier;

		$t->data['bread'] = array(
			array('href' => '/' . $this->config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), 
			array('href' => '/foodle/' . $this->foodle->identifier, 'title' => $this->foodle->name), 
		);


		# echo '<pre>'; print_r($t->data); exit;

		$t->show();


	}
	
}

