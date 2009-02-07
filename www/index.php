<?php

require_once('_include.php');


$config = SimpleSAML_Configuration::getInstance('foodle');

// Starting sessions.
session_start();


try {


	$foodleauth = new FoodleAuth();
	
	$foodleauth->requireAuth(TRUE);	
	
// 	if (array_key_exists('foodleSession', $_COOKIE) || array_key_exists('sessionBootstrap', $_REQUEST)) {
// 		$foodleauth->requireAuth(TRUE);
// 	} else {
// 		$foodleauth->requireAuth(FALSE);	
// 	}

	$email = $foodleauth->getMail();
	$userid = $foodleauth->getUserID();
	$displayname = $foodleauth->getDisplayName();
	
	#error_log('UserID: ' . $userid);
	#echo 'email: ' . $email . ' userid:' . $userid . ' displayname:' . $displayname; exit;

	// If anonymous, create a login link.
	$loginurl = NULL;
	if (!$foodleauth->isAuth()) {
		$sspconfig = SimpleSAML_Configuration::getInstance();
		$loginurl = '/' . $sspconfig->getValue('baseurlpath') . 'saml2/sp/initSSO.php?RelayState=' . urlencode(SimpleSAML_Utilities::selfURL());
	}


	
	if (!isset($_SESSION['foodle_cache'])) {
		$_SESSION['foodle_cache'] = array();
	}
	
	
	$link = mysql_connect(
		$config->getValue('db.host', 'localhost'), 
		$config->getValue('db.user'),
		$config->getValue('db.pass'));
	if(!$link){
		throw new Exception('Could not connect to database: '.mysql_error());
	}
	mysql_select_db($config->getValue('db.name','feidefoodle'));
	
	

	
	$fl = new FoodleListings($userid, $link);
	$entries = $fl->getYourEntries();
	
	$adminusers = $config->getValue('adminUsers');
	
	$allentries = null;
	if (in_array($userid, $adminusers))
		$allentries = $fl->getAllEntries(25);
		
	$ownerentries = $fl->getOwnerEntries($userid, 10);
	
	$foodleids = array();
	if(!empty($entries)) foreach($entries AS $e) $foodleids[] = $e['foodleid'];
	if(!empty($allentries)) foreach($allentries AS $e) $foodleids[] = $e['foodleid'];
	if(!empty($ownerentries)) foreach($ownerentries AS $e) $foodleids[] = $e['foodleid'];

	#print_r($foodleids); exit;
	
	$statusupdate = $fl->getStatusUpdate($userid, $foodleids, 20);
	
	$stats = $fl->getStats($userid);
	
	#print_r($stats); exit; 
	
	#echo 'status: '; print_r($statusupdate); exit;	

	/*
	echo 'entries:<pre>';
	print_r($entries);
	exit;
	*/
	
	/*
	Array
(
    [0] => Array
        (
            [id] => tkgnpz3m
            [foodleid] => tkgnpz3m
            [userid] => andreas@rnd.feide.no
            [username] => Andreas Solberg
            [response] => 1,1,0
            [name] => test 2
            [descr] => sdfsdf
            [columns] => Thu 26. Jun|Fri 27. Jun|Sat 28. Jun
        )

    [1] => Array
        (
            [id] => hvcm1j8s
            [foodleid] => hvcm1j8s
            [userid] => andreas@rnd.feide.no
            [username] => Andreas Solberg
            [response] => 0,0
            [name] => Publishers meeting
            [descr] => Meeting with Dutch Publishers in Utrecht
            [columns] => Tue 24. Jun(I will attend lunch,I will attend drink)
        )

)
*/
	
	


	$et = new SimpleSAML_XHTML_Template($config, 'foodlefront.php', 'foodle_foodle');
	$et->data['yourentries'] = $entries;
	$et->data['allentries'] = $allentries;
	$et->data['ownerentries'] = $ownerentries;
	$et->data['userid'] = $userid;
	$et->data['displayname'] = $displayname;
	$et->data['bread'] = array(array('title' => 'bc_frontpage'));
	$et->data['authenticated'] = $foodleauth->isAuth();
	$et->data['loginurl'] = $loginurl;
	$et->data['enableFacebookAuth'] = $config->getValue('enableFacebookAuth', TRUE);
	$et->data['facebookshare'] = FALSE;
	$et->data['statusupdate'] = $statusupdate;
	$et->data['stats'] = $stats;
	$et->show();

} catch(Exception $e) {

	$et = new SimpleSAML_XHTML_Template($config, 'foodleerror.php', 'foodle_foodle');
	$et->data['bread'] = array(array('href' => '/' . $config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), array('title' => 'bc_errorpage'));
	$et->data['message'] = $e->getMessage();
	$et->show();

}


?>