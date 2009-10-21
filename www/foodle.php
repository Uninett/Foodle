<?php
require_once('_include.php');

$config = SimpleSAML_Configuration::getInstance('foodle');


try {


	if (!isset($_SESSION['foodle_cache'])) {
		$_SESSION['foodle_cache'] = array();
	}
	
	
	/*
	 * What wiki are we talking about?
	 */
	$thisfoodle = null;
	if (isset($_REQUEST['id'])) {
		$_SESSION['id'] = $_REQUEST['id'];
		$thisfoodle = $_REQUEST['id'];
	} elseif(isset($_SESSION['id'])) {
		$thisfoodle = $_SESSION['id'];
	}
	if (empty($thisfoodle)) throw new Exception('No foodle selected');
	
	
	
	$link = mysql_connect(
		$config->getValue('db.host', 'localhost'), 
		$config->getValue('db.user'),
		$config->getValue('db.pass'));
	if(!$link){
		throw new Exception('Could not connect to database: '.mysql_error());
	}
	mysql_select_db($config->getValue('db.name','feidefoodle'));
	
	

	
	
	$foodle = new Foodle($thisfoodle, NULL, $link);

	$foodleauth = new FoodleAuth();
	
#	echo '<pre>'; print_r($foodle); exit;
	
	$anon = ($foodle->getAnon() == '1' ? TRUE : FALSE);
	$foodleauth->requireAuth($anon);

	$email = $foodleauth->getMail();
	$userid = $foodleauth->getUserID();
	$displayname = $foodleauth->getDisplayName();
	
	#error_log('UserID: ' . $userid);
	
	// If anonymous, create a login link.
	$loginurl = $foodleauth->getLoginURL();
	$logouturl = $foodleauth->getLogoutURL('/');

	
	if (isset($_REQUEST['createnewsubmit'])) {
		if (!$foodle->isLoaded()) {
			$foodle->setOwner($userid);
		}
	}

	$foodle = new Foodle($thisfoodle, $userid, $link);


	if (!empty($_REQUEST['username'])) {

		$response = array_fill(0, $foodle->getNumCols(), '0');
		if (!empty($_REQUEST['myresponse'])) {
			foreach ($_REQUEST['myresponse'] AS $yes) {
				$response[(int)$yes] = '1';
			}
		}
	#	echo '<pre>'; print_r($response); echo '</pre>'; exit;		
		$newentry = array(
			'userid' => $userid, 'username' => $_REQUEST['username'], 'email' => $email,
			'response' => $response,
			'updated' => 'now', 'notes' => $_REQUEST['comment']
		);

		$foodle->setMyResponse($newentry);
		
	#	echo '<pre>'; print_r($foodle->getYourEntry($attributes['cn'][0])); echo '</pre>'; #exit;
	#	SimpleSAML_Logger::warning('Attribute debugging: ' . var_export($attributes, TRUE));


		$foodle = new Foodle($thisfoodle, $userid, $link);
	}
	
	$used = 0;
	$maxcol = 0;
	$maxnum = 0;
	
	
	$otherentries = $foodle->getOtherEntries();

	$thisisanewentry = 1;
	foreach($otherentries AS $oe) {
		if ($oe['userid'] == $userid) $thisisanewentry = 0;
	}
	

	$maxdef = $foodle->getMaxDef();
	if (!empty($maxdef)) {
		$maxdefc = split(':', $maxdef);
		$maxcol = $maxdefc[0];
		$maxnum = $maxdefc[1];
		if ($maxcol == 0) {
			$used = count($otherentries);
		} else {
			foreach($otherentries AS $oe) {
				if ($oe['response'][$maxcol-1] == '1') $used++;
			}

		}
	}
	
	// echo('<pre>');
	// print_r($foodle);
	// exit;
	
	#echo '<pre>'; print_r($foodle->getColumns()); echo '</pre>'; exit;
	
	$et = new SimpleSAML_XHTML_Template($config, 'foodleresponse.php', 'foodle_foodle');
	$et->data['title'] = 'Foodle :: ' . $foodle->getName();
	$et->data['header'] = $foodle->getName();
	$et->data['identifier'] = $foodle->getIdentifier();
	$et->data['descr'] = $foodle->getDescr();
	$et->data['expire'] = $foodle->getExpire();
	$et->data['expired'] = $foodle->expired();
	$et->data['expiretext'] = $foodle->getExpireText();
	$et->data['columns'] = $foodle->getColumns();
	
	$et->data['url'] = FoodleUtils::getUrl() . 'foodle.php?id=' . $_REQUEST['id'];
	$et->data['facebookshare'] = $config->getValue('enableFacebookAuth', TRUE);
	
	$et->data['maxcol'] = $maxcol;
	$et->data['maxnum'] = $maxnum;
	$et->data['used'] = $used;
	
	$et->data['registerEmail'] = (empty($email));
	
	$et->data['owner'] = ($userid == $foodle->getowner()) || ($userid == 'andreas@uninett.no') || ($userid == 'andreas@rnd.feide.no');
	$et->data['ownerid'] = $foodle->getowner();
	$et->data['userid'] = $userid;
	$et->data['displayname'] = $displayname;
	$et->data['email'] = $email;
	
	$et->data['authenticated'] = $foodleauth->isAuth();

	
	$et->data['loginurl'] = $loginurl;
	$et->data['logouturl'] = $logouturl;
			
	$et->data['yourentry'] = $foodle->getYourEntry($displayname);
	$et->data['otherentries'] = $foodle->getOtherEntries();
	
	$et->data['identifier'] = $foodle->getIdentifier();
	$et->data['thisisanewentry'] = $thisisanewentry;

	$et->data['bread'] = array(
		array('href' => '/' . $config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), 
		array('href' => 'foodle.php?id=' . $foodle->getIdentifier(), 'title' => $foodle->getName()), 
	);
	

	$et->show();
	
	
} catch(Exception $e) {

	$et = new SimpleSAML_XHTML_Template($config, 'foodleerror.php', 'foodle_foodle');
	$et->data['bread'] = array(array('href' => '/' . $config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), array('title' => 'bc_errorpage'));
	$et->data['message'] = $e->getMessage();	
	$et->show();

}
