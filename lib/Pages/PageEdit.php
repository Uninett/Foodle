<?php

class Pages_PageEdit extends Pages_PageFoodle {
	
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		$this->timezone = new TimeZone($this->fdb);
		
		$this->foodle->acl($this->user, 'write');
	}
	
	
	protected function sendMail() {
	
		if (!$this->user->notification('newfoodle', FALSE)) {
			error_log('Foodle was updated, but mail notification was not sent because of users preferences');
			return;
		}
		error_log('Foodle was updated, sending notification!');
	
	
		$profileurl = FoodleUtils::getUrl() . 'profile/';
		$url = FoodleUtils::getUrl() . 'foodle/' . $this->foodle->identifier;
		$name = $this->foodle->name;
		$to = $this->user->email;
		
		$mail = '
		
Hi, your response to the Foodle named <i>' . htmlspecialchars($name) . '</i> was successfully updated.</p>

You may re-enter the Foodle link below to update your response, and view other responses:

* [Edit your Foodle response](' . $url . ')
* [View responses of other participants](' . $url . '#responses)

### Did you know

You may also create new Foodles on your own, and invite others to respond.

* [Go to Foodl.org to create a new Foodle.](http://foodl.org)

		';

		$mailer = new Foodle_EMail($to, 'Updated foodle: ' . htmlspecialchars($name), 'Foodl.org <no-reply@foodl.org>');
		$mailer->setBody($mail);
		$mailer->send();
		
		#echo '<pre>'; print_r($mail); exit;

	}
	

	protected function saveChanges() {

		$this->foodle->updateFromPost($this->user);
#		echo '<pre>'; print_r($_REQUEST); exit; print_r($this->foodle); exit;
		$this->foodle->acl($this->user, 'write');
		$this->foodle->save();
		
		if (isset($this->user->email)) {
			$this->sendMail();
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

		$t = new SimpleSAML_XHTML_Template($this->config, 'foodlecreate.php', 'foodle_foodle');

		$t->data['requirejs-main'] = 'main-create';
		
		$t->data['user'] = $this->user;	
		$t->data['userToken'] = $this->user->getToken();
		$t->data['loginurl'] = $this->auth->getLoginURL();
		$t->data['logouturl'] = $this->auth->getLogoutURL('/');
		$t->data['authenticated'] = $this->auth->isAuth();

		$t->data['foodleid'] = $this->foodle->identifier;

		$t->data['gmapsAPI'] = $this->config->getValue('gmapsAPI');

		$t->data['bread'] = array(
			array('href' => '/', 'title' => 'bc_frontpage'), 
			array('href' => '/foodle/' . $this->foodle->identifier, 'title' => $this->foodle->name), 
			array('title' => 'bc_edit')
		);
		$t->show();


	}
	
}

