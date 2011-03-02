<?php

class API_Invite extends API_Authenticated {

	protected $contacts;

	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
	}
		
	
	function prepare() {
		parent::prepare();	
		$this->contacts = new Data_Contacts($this->fdb, $this->user);
		
		error_log('API: api/invite');

		if (empty($_REQUEST['foodle'])) throw new Exception('Missing parameter [foodle] on api/invite');
		
		$foodleid = $_REQUEST['foodle'];
		$userid = (!empty($_REQUEST['userid']) ? $_REQUEST['userid'] : NULL);
		$email = (!empty($_REQUEST['email']) ? $_REQUEST['email'] : NULL);
		
		if (empty($userid) && empty($email)) throw new Exception('Missing parameter [userid] or [email] on api/invite');
		
		if (!empty($userid)) {
			$this->inviteByUserID($userid, $foodleid);
			error_log('Inviting user [' . $userid . ']');
		} elseif(!empty($email)) {
			$this->inviteByEmail($email, $foodleid);
			error_log('Inviting user by email [' . $email . ']');
			
			
		} else {
			
		}
		
		return TRUE;

	}


	protected function inviteByEmail($email, $foodleid) {
	
		$foodle = $this->fdb->readFoodle($foodleid);
		

		
		$searchUser = $this->fdb->lookupEmail($email);
		
		
		if ($searchUser) {
			$user = $this->fdb->readUser($searchUser);
		} else {
			$user = new Data_User($this->fdb);
			$user->userid = SimpleSAML_Utilities::generateID();
			$user->email = $email;
			$user->username = $email;
		}
		

		
		
		
		$response = new Data_FoodleResponse($this->fdb, $foodle);
		$response->userid = $user->userid;
		$response->username = $user->username;
		$response->email = $email;
		$response->invitation = TRUE;
	
		$this->fdb->saveFoodleResponse($response);
		$this->sendMail($user, $foodle);		
	
	}


	protected function inviteByUserID($userid, $foodleid) {
		$foodle = $this->fdb->readFoodle($foodleid);
		$user = $this->fdb->readUser($userid);
		
		if ($foodle->responseExists($userid)) throw new Exception('User [' . $userid . '] cannot be invited. Has already responded to Foodle.');
		
		$response = new Data_FoodleResponse($this->fdb, $foodle);
		$response->userid = $userid;
		if (!empty($user->email)) {
			$response->email = $user->email;
		}
		if (!empty($user->username)) {
			$response->username = $user->username;
		}
		$response->invitation = TRUE;
		
		$this->fdb->saveFoodleResponse($response);
		$this->sendMail($user, $foodle);
		
	}

	
	protected function sendMail($user, $foodle) {
	
		if (!$this->user->notification('invite', TRUE)) {
			error_log('Foodle response was added, but mail notification was not sent because of users preferences');
			return;
		}
		error_log('Sending Foodle invitation... !');
		
		
	
		$profileurl = FoodleUtils::getUrl() . 'profile/';
		$url = FoodleUtils::getUrl() . 'foodle/' . $foodle->identifier;
		$name = $foodle->name;
		$to = $user->email;
//		$to = 'andreassolberg@gmail.com'; 
		
		$datetimetext = '';
		$extralinks = '';
		if (!empty($foodle->datetime)) {
			$tz = new TimeZone(NULL, $user);
			$url = FoodleUtils::getUrl() . 'foodle/' . $foodle->identifier . '?output=ical';
			$datetimetext = "\n\n### Date and time\n\n" . $foodle->datetimeText($tz->getTimeZone());
			$extralinks = "\n* [Import to your calendar with iCalendar](" . $url  . ")";
		}
		
		$mail = $foodle->descr . '

---

* [Resond to this Foodle](' . $url . ')
* [View responses of other participants](' . $url . '#responses)' . $extralinks . '

' . $datetimetext . '

### Did you know

You may also create new Foodles on your own, and invite others to respond.

* [Go to Foodl.org to create a new Foodle.](http://foodl.org)

		';
		$mailer = new Foodle_EMail($to, htmlspecialchars($name), 'Foodl.org <no-reply@foodl.org>');
		$mailer->setBody($mail);
		
// 		if (!empty($foodle->datetime)) {
// 			$url = FoodleUtils::getUrl() . 'foodle/' . $foodle->identifier . '?output=ical';
// 			$ics = file_get_contents($url);
// 			$mailer->sendWithAttachment($ics);
// 		} else {
			$mailer->send();
// 		}


	}


	
}

