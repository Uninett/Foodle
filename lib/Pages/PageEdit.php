<?php

class Pages_PageEdit extends Pages_Page {
	
	private $foodle;
	private $user;
	private $foodleid;
	private $foodlepath;
	
	private $loginurl;
	private $logouturl;
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		if (count($parameters) < 1) throw new Exception('Missing [foodleid] parameter in URL.');
		
		Foodle::requireValidIdentifier($parameters[0]);
		$this->foodleid = $parameters[0];
		$this->foodlepath = '/foodle/' . $this->foodleid;
		
		$this->auth();
		
		$this->foodle = $this->fdb->readFoodle($this->foodleid);
	}
	
	// Authenticate the user
	private function auth() {
		$foodleauth = new FoodleAuth();
		$foodleauth->requireAuth($this->foodle->allowanonymous);

		$this->user = new User($this->fdb);
		$this->user->email = $foodleauth->getMail();
		$this->user->userid = $foodleauth->getUserID();
		$this->user->name = $foodleauth->getDisplayName();
		
		// If anonymous, create a login link.
		$this->loginurl = $foodleauth->getLoginURL();
		$this->logouturl = $foodleauth->getLogoutURL('/');
	}
	
	// Save the users response..
	private function setResponse() {
		$myresponse = $this->foodle->getMyResponse($this->user);
		$myresponse->updateFromPost($this->user);
		
		#echo '<pre>'; print_r($myresponse); exit;
		$myresponse->save();
	}
	
	private function addDiscussionEntry() {
		$this->fdb->addDiscussionEntry($this->foodle, $this->user, $_REQUEST['message']);
	}
	
	// Process the page.
	function show() {

		if (isset($_REQUEST['setresponse'])) $this->setResponse();
		if (isset($_REQUEST['discussionentry'])) $this->addDiscussionEntry();


		// We got an response!!!
		if (!empty($_REQUEST['username'])) {

		// 	$response = array_fill(0, $foodle->getNumCols(), '0');
		// 	if (!empty($_REQUEST['myresponse'])) {
		// 		foreach ($_REQUEST['myresponse'] AS $yes) {
		// 			$response[(int)$yes] = '1';
		// 		}
		// 	}
		// #	echo '<pre>'; print_r($response); echo '</pre>'; exit;		
		// 	$newentry = array(
		// 		'userid' => $userid, 'username' => $_REQUEST['username'], 'email' => $email,
		// 		'response' => $response,
		// 		'updated' => 'now', 'notes' => $_REQUEST['comment']
		// 	);
		// 
		// 	$foodle->setMyResponse($newentry);
		// 	
		// #	echo '<pre>'; print_r($foodle->getYourEntry($attributes['cn'][0])); echo '</pre>'; #exit;
		// #	SimpleSAML_Logger::warning('Attribute debugging: ' . var_export($attributes, TRUE));
		// 
		// 	SimpleSAML_Utilities::redirect('foodle.php', array('id' => $thisfoodle));
		}

		if (!empty($_REQUEST['message'])) {
			// $foodle->addDiscussion($displayname, utf8_decode($_REQUEST['message']));
			// SimpleSAML_Utilities::redirect('foodle.php', array('id' => $thisfoodle, 'tab' => '1'));
		}


		// 
		// $maxdef = $foodle->getMaxDef();
		// if (!empty($maxdef)) {
		// 	$maxdefc = split(':', $maxdef);
		// 	$maxcol = $maxdefc[0];
		// 	$maxnum = $maxdefc[1];
		// 	if ($maxcol == 0) {
		// 		$used = count($otherentries);
		// 	} else {
		// 		foreach($otherentries AS $oe) {
		// 			if ($oe['response'][$maxcol-1] == '1') $used++;
		// 		}
		// 
		// 	}
		// }


		$t = new SimpleSAML_XHTML_Template($this->config, 'foodleresponse.php', 'foodle_foodle');

		$t->data['title'] = 'Foodle :: ' . $this->foodle->name;
		$t->data['foodle'] = $this->foodle;
		$t->data['user'] = $this->user;
		$t->data['foodlepath'] = $this->foodlepath;


		// Configuration
		$t->data['facebookshare'] = $this->config->getValue('enableFacebookAuth', TRUE);



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
		// $et->data['owner'] = ($userid == $foodle->getowner()) || ($userid == 'andreas@uninett.no') || ($userid == 'andreas@rnd.feide.no');
		// $et->data['ownerid'] = $foodle->getowner();
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
			array('href' => '/foodle/' . $this->foodle->getIdentifier(), 'title' => $this->foodle->getName()), 
		);


		$t->show();


	}
	
}

