<?php



class Pages_FixDate extends Pages_PageFoodle {
	
	
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		$this->timezone = new TimeZone();
		
		$this->foodle->acl($this->user, 'write');
	}
	

	protected function saveChanges() {

		$this->foodle->updateFromPostFixDate($this->user);
#		echo '<pre>'; print_r($_REQUEST); print_r($this->foodle); exit;
		$this->foodle->acl($this->user, 'write');
		$this->foodle->save();
		
// 		if (isset($this->user->email)) {
// 			$this->sendMail();
// 		}

		if (!empty($_REQUEST['send_fixdate_mail'])) {

			$responses = $this->foodle->getResponses();
			
			foreach($responses AS $response) {
				
				$user = null;
				if (!empty($response->user)) $user = $response->user;
				
				if (empty($user)) {
					$user = new Data_User($this->fdb);
					$user->userid = $response->userid;
					$user->email = $response->email;
					$user->username = $response->username;
				}
				
				$this->sendFixDateMail($user, $this->foodle);
				
			}

		}
		
		$newurl = FoodleUtils::getUrl() . 'foodle/' . $this->foodle->identifier . '#distribute';
		SimpleSAML_Utilities::redirect($newurl);
		exit;
	}

	protected function presentInTimeZone() {
	}
	
	// Process the page.
	function show() {

		if (isset($_REQUEST['save'])) $this->saveChanges();
		
		if (isset($_REQUEST['col'])) {
			$this->foodle->fixDate($_REQUEST['col']);
		}

		$t = new SimpleSAML_XHTML_Template($this->config, 'fixdate.php', 'foodle_foodle');

		$t->data['authenticated'] = $this->auth->isAuth();
		$t->data['user'] = $this->user;
		
		$t->data['timezone'] = $this->timezone;
		$t->data['ftimezone'] = $this->foodle->timezone;

		$t->data['name'] = $this->foodle->name;
		$t->data['identifier'] = $this->foodle->identifier;
		$t->data['descr'] = $this->foodle->descr;
		

		$t->data['foodle'] = $this->foodle;

		$t->data['today'] = date('Y-m-d');
		$t->data['tomorrow'] = date('Y-m-d', time() + 60*60*24 );

		$t->data['bread'] = array(
			array('href' => '/', 'title' => 'bc_frontpage'), 
			array('href' => '/foodle/' . $this->foodle->identifier . '#responses', 'title' => $this->foodle->name), 
			array('title' => 'Fix timeslot')
		);
		$t->show();


	}
	
	
	
	protected function sendFixDateMail($user, $foodle) {
	
		if (!$this->user->notification('invite', TRUE)) {
			error_log('Foodle response was added, but mail notification was not sent because of users preferences');
			return;
		}
		error_log('Sending Foodle fixdate to ' . $user->email);
		
		
	
		$profileurl = FoodleUtils::getUrl() . 'profile/';
		$url = FoodleUtils::getUrl() . 'foodle/' . $foodle->identifier;
		$name = 'Date and time set for ' . $foodle->name;
		$to = $user->email;
//		$to = 'andreassolberg@gmail.com'; 
		
		$datetimetext = '';
		$extralinks = '';
		if (!empty($foodle->datetime)) {
			$tz = new TimeZone(NULL, $user);
			$url = FoodleUtils::getUrl() . 'foodle/' . $foodle->identifier . '?output=ical';
			$datetimetext = "\n\n### Date and time\n\n" . $foodle->datetimeText($tz->getTimeZone());
			$extralinks = "\n* Import to your calendar using the attached calendar file";
		}
		
		$mail = $foodle->descr . '

### Confirm your participation

* [Please confirm your participation on this event](' . $url . ')
* [View confirmation of other participants](' . $url . '#responses)' . $extralinks . '

' . $datetimetext . '

### Did you know

You may also create new Foodles on your own, and invite others to respond.

* [Go to Foodl.org to create a new Foodle.](http://foodl.org)

		';
		$mailer = new Foodle_EMail($to, htmlspecialchars($name), 'Foodl.org <no-reply@foodl.org>');
		$mailer->setBody($mail);
		
		if (!empty($foodle->datetime)) {
			$url = FoodleUtils::getUrl() . 'foodle/' . $foodle->identifier . '?output=ical';
			$ics = file_get_contents($url);
			$mailer->send(array(array('data' => $ics, 'file' => 'foodl-invitation.ics', 'type' => 'calendar/text')));
		} else {
			$mailer->send();
		}


	}

	
	
}

