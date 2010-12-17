<?php

class Pages_PageEdit extends Pages_PageFoodle {
	
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		$this->timezone = new TimeZone();
	}
	
	
	protected function sendMail() {
		$url = FoodleUtils::getUrl() . 'foodle/' . $this->foodle->identifier;
		$name = $this->foodle->name;
		$to = $this->user->email;
		$mail = '
		
		<p>Hi, your new Foodle named <i>' . htmlspecialchars($name) . '</i> was successfully updated.</p>
		
		<p>You may visit the Foodle link below to respond to the foodle or to view other responses:
		<ul>
			<li><a href="' . $url . '">Response to this Foodle</a></li>
			<li><a href="' . $url . '#responses">View responses of other participants</a></li>
		</ul></p>
		
		<p>If you want so invite others to respond to this Foodle, you should share the link below:</p>
		
		<pre><code>' . htmlspecialchars($url) . '</code></pre>
		
		';
		$mailer = new Foodle_EMail($to, 'Updated foodle: ' . htmlspecialchars($name), 'Foodl.org <no-reply@foodl.org>');
		$mailer->setBody($mail);
		$mailer->send();
		
		#echo '<pre>'; print_r($mail); exit;

	}
	

	protected function saveChanges() {

		$this->foodle->updateFromPost($this->user);
		#echo '<pre>'; print_r($foodle); exit;
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

		$t->data['authenticated'] = $this->auth->isAuth();
		
		$t->data['user'] = $this->user;
		
		$t->data['edit'] = TRUE;
		
		$t->data['timezone'] = $this->timezone;
		$t->data['ftimezone'] = $this->foodle->timezone;

		$t->data['name'] = $this->foodle->name;
		$t->data['identifier'] = $this->foodle->identifier;
		$t->data['descr'] = $this->foodle->descr;
		$t->data['expire'] = $this->foodle->expire;
		$t->data['anon'] = $this->foodle->allowanonymous;
		
		$t->data['maxcol'] = $this->foodle->maxcolumn;
		$t->data['maxnum'] = $this->foodle->maxentries;
		

		
		$t->data['columns'] = $this->foodle->columns;
		
		$t->data['isDates'] = $this->foodle->onlyDateColumns();

		$t->data['expire'] = $this->foodle->getExpireTextField();

		$t->data['bread'] = array(
			array('href' => '/', 'title' => 'bc_frontpage'), 
			array('href' => '/foodle/' . $this->foodle->identifier, 'title' => $this->foodle->name), 
			array('title' => 'bc_edit')
		);
		$t->show();


	}
	
}

