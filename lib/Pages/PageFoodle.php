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
	
	protected $template;
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		if (count($parameters) < 1) throw new Exception('Missing [foodleid] parameter in URL.');
		
		$this->template = new SimpleSAML_XHTML_Template($this->config, 'foodleresponse.php', 'foodle_foodle');
		$this->setLocale();
		
		Data_Foodle::requireValidIdentifier($parameters[0]);
		$this->foodleid = $parameters[0];
		$this->foodlepath = '/foodle/' . $this->foodleid;

		#Timer::tick('Preparation started');
				
		$this->foodle = $this->fdb->readFoodle($this->foodleid);
		
		#Timer::tick('Foodle read');
		
		// $this->foodle->getColumnDates();
		// $this->calendarEnabled = $this->foodle->calendarEnabled();
		// $this->timezoneEnable = $this->foodle->timeZoneEnabled();
		// $this->datesonly = $this->foodle->datesOnly();
		

		#Timer::tick('Timezone preparations');
		
		$this->auth();
		// $this->timezone = new TimeZone(NULL, $this->user);
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
	

	protected function presentCustom() {
		
		try {
			if ($this->timezoneEnable) {
				$this->foodle->presentInTimeZone($this->timezone->getSelectedTimeZone());
			} elseif($this->datesonly) {
				$this->foodle->presentDatesOnly();
			}
		} catch(Exception $e) {
			error_log('Could not presentCustom() Could be error in parsing dates etc: ' . $e->getMessage());
			throw new Exception('Error parsing time and date fields in Foodle columns: ' . $e->getMessage() );
		}
	}
	
	
	// Authenticate the user
	protected function auth() {
		$this->auth = new FoodleAuth($this->fdb);
		$this->auth->requireAuth($this->foodle->allowanonymous);

		$this->user = $this->auth->getUser();
		
// 		if ($this->auth->isAuth()) {
// 			$this->user = new Data_User($this->fdb);
// 			$this->user->email = $this->auth->getMail();
// 			$this->user->userid = $this->auth->getUserID();
// 			$this->user->name = $this->auth->getDisplayName();
// 			$this->user->calendarURL = $this->auth->getCalendarURL();
// 		} else {
// 			$this->user = new Data_User($this->fdb);
// 			$this->user->email = $this->auth->getMail();
// 			$this->user->userid = $this->auth->getUserID();
// 			$this->user->name = $this->auth->getDisplayName();			
// 	
// 		}


	}

	
	protected function sendMail() {
	
		if (!$this->user->notification('newresponse', FALSE)) {
			error_log('Foodle response was added, but mail notification was not sent because of users preferences');
			return;
		}
		error_log('Foodle response was added, sending notification!');
		
		
	
		$profileurl = FoodleUtils::getUrl() . 'profile/';
		$url = FoodleUtils::getUrl() . 'foodle/' . $this->foodle->identifier;
		$name = $this->foodle->name;
		$to = $this->user->email;
		$mail = '
		
Hi, your response to the Foodle named <i>' . htmlspecialchars($name) . '</i> was successfully stored.</p>

You may re-enter the Foodle link below to update your response, and view other responses:

* [Edit your Foodle response](' . $url . ')
* [View responses of other participants](' . $url . '#responses)

### Did you know

You may also create new Foodles on your own, and invite others to respond.

* [Go to Foodl.org to create a new Foodle.](http://foodl.org)

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
			$this->sendMail();
		}
		
		$newurl = SimpleSAML_Utilities::selfURLNoQuery() ;
		if (isset($_REQUEST['timezone'])) {
			$newurl .= '?timezone=' . urlencode($_REQUEST['timezone']);
		}
		
		SimpleSAML_Utilities::redirect($newurl  . '#responses' );
	}
	
	protected function removeResponse () {
		

		$myresponse = $this->foodle->getMyResponse($this->user);
		$myresponse->remove();

		$newurl = SimpleSAML_Utilities::selfURLNoQuery() ;
		if (isset($_REQUEST['timezone'])) {
			$newurl .= '?timezone=' . urlencode($_REQUEST['timezone']);
		}
		SimpleSAML_Utilities::redirect($newurl );
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


		$this->template->data['requirejs-main'] = 'main-response';
		$this->template->data['foodleid'] = $this->foodle->identifier;
		$this->template->data['userid'] = $this->user->userid;
		$this->template->data['title'] = 'Foodle :: ' . $this->foodle->name;
		$this->template->data['foodle'] = $this->foodle;
		$this->template->data['user'] = $this->user;
		$this->template->data['userToken'] = $this->user->getToken();

		$this->template->data['gmapsAPI'] = $this->config->getValue('gmapsAPI');
				
		$this->template->data['authenticated'] = $this->auth->isAuth();
		$this->template->data['loginurl'] = $this->auth->getLoginURL();
		$this->template->data['logouturl'] = $this->auth->getLogoutURL('/');
		

		$this->template->data['url'] = FoodleUtils::getUrl() . 'foodle/' . $this->foodle->identifier;

		$this->template->data['bread'] = array(
			array('href' => '/' . $this->config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), 
			array('href' => '/foodle/' . $this->foodle->identifier, 'title' => $this->foodle->name), 
		);

		Timer::tick('Presenting page');
		$this->template->data['timer'] = Timer::getList();

		//echo '<pre>'; print_r($this->template->data); exit;

		$this->template->show();



	}
	
}

