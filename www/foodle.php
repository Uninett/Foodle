<?php

$path_extra = '/var/simplesamlphp-openwiki/lib';
$path = ini_get('include_path');
$path = $path_extra . PATH_SEPARATOR . $path;
ini_set('include_path', $path);


include('/var/simplesamlphp-openwiki/www/_include.php');


/**
 * Loading simpleSAMLphp libraries
 */
require_once('SimpleSAML/Configuration.php');
require_once('SimpleSAML/Utilities.php');
require_once('SimpleSAML/Session.php');
require_once('SimpleSAML/Metadata/MetaDataStorageHandler.php');
require_once('SimpleSAML/XHTML/Template.php');

/*
 * Loading Foodle libraries
 */
require_once('../lib/Foodle.class.php');
#require_once('../lib/OpenWikiDictionary.class.php');

/**
 * Initializating configuration
 */
SimpleSAML_Configuration::init(dirname(dirname(__FILE__)) . '/config', 'foodle');
SimpleSAML_Configuration::init('/var/simplesamlphp-openwiki/config');

$config = SimpleSAML_Configuration::getInstance('foodle');

// Starting sessions.
session_start();


#include('../config/groups.php');

try {

	/* Load simpleSAMLphp, configuration and metadata */
	$sspconfig = SimpleSAML_Configuration::getInstance();
	$session = SimpleSAML_Session::getInstance();
	
	/* Check if valid local session exists.. */
	if (!isset($session) || !$session->isValid('saml2') ) {
		SimpleSAML_Utilities::redirect(
			'/' . $sspconfig->getValue('baseurlpath') .
			'saml2/sp/initSSO.php',
			array('RelayState' => SimpleSAML_Utilities::selfURL())
			);
	}
	$attributes = $session->getAttributes();

	$email = '';	
	$userid = 'na';
	if (isset($attributes['mail'])) {
		$userid = $attributes['mail'][0];
	}
	if (isset($attributes['eduPersonPrincipalName'])) {
		$userid = $attributes['eduPersonPrincipalName'][0];
	}
	if (isset($attributes['mail'])) {
		$email = $attributes['mail'][0];
	}


	
	
	
	$displayname = 'NA';
	if (isset($attributes['smartname-fullname'])) 
		$displayname = $attributes['smartname-fullname'][0];
	
	
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
	
	
	
	
	// TODO: REMOVE true to enable caching..
	#if (! array_key_exists($thiswiki,$_SESSION['foodle_cache'] ) || true) {
	
		$foodle = new Foodle($thisfoodle, $userid, $link);
	
		if (isset($_REQUEST['createnewsubmit'])) {
			if (!$foodle->isLoaded()) {
				$foodle->setOwner($userid);
			}
		}
// 		
// 		$_SESSION['foodle_cache'][$thisfoodle] =& $foodle;
// 		
// 	} else {
// 	
// 		$foodle =& $_SESSION['foodle_cache'][$thiswiki];
// 	
// 	}
	
	#echo '<pre>'; print_r($foodle); echo '</pre>'; exit;
	

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
	
		SimpleSAML_Logger::warning('Attribute debugging: ' . var_export($attributes, TRUE));


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
	
	$et->data['maxcol'] = $maxcol;
	$et->data['maxnum'] = $maxnum;
	$et->data['used'] = $used;
	
	$et->data['owner'] = ($userid == $foodle->getowner()) || ($userid == 'andreas@uninett.no');
	
	$et->data['userid'] = $userid;
	$et->data['displayname'] = $displayname;
			
	$et->data['yourentry'] = $foodle->getYourEntry($displayname);
	$et->data['otherentries'] = $foodle->getOtherEntries();
	
	$et->data['identifier'] = $foodle->getIdentifier();
	$et->data['thisisanewentry'] = $thisisanewentry;

	$et->data['bread'] = array(
		array('href' => '/', 'title' => 'bc_frontpage'), 
		array('href' => 'foodle.php?id=' . $foodle->getIdentifier(), 'title' => $foodle->getName()), 
	);
	
	
	#$et->data['username'];
	
	$et->show();
	
	
} catch(Exception $e) {

	$et = new SimpleSAML_XHTML_Template($config, 'foodleerror.php', 'foodle_foodle');
	$et->data['bread'] = array(array('href' => '/', 'title' => 'bc_frontpage'), array('title' => 'bc_errorpage'));
	$et->data['message'] = $e->getMessage();
	
	$et->show();


}

?>