<?php

$path_extra = '/var/simplesamlphp-openwiki/lib';
$path = ini_get('include_path');
$path = $path_extra . PATH_SEPARATOR . $path;
ini_set('include_path', $path);

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

/* Load simpleSAMLphp, configuration and metadata */
$sspconfig = SimpleSAML_Configuration::getInstance();
$metadata = SimpleSAML_Metadata_MetaDataStorageHandler::getMetadataHandler();
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

$userid = 'na';
if (isset($attributes['mail'])) {
	$userid = $attributes['mail'][0];
}
if (isset($attributes['eduPersonPrincipalName'])) {
	$userid = $attributes['eduPersonPrincipalName'][0];
}



$displayname = 'NA';
if (isset($attributes['cn'])) 
	$displayname = $attributes['cn'][0];

if (isset($attributes['displayName'])) 
	$displayname = $attributes['displayName'][0];


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
if (! array_key_exists($thiswiki,$_SESSION['foodle_cache'] ) || true) {

	$foodle = new Foodle($thisfoodle, $userid, $link);

	if (isset($_REQUEST['createnewsubmit'])) {
		if (!$foodle->isLoaded()) {
			$foodle->setOwner($userid);
		}
	}
	
	$_SESSION['foodle_cache'][$thisfoodle] =& $foodle;
	
} else {

	$foodle =& $_SESSION['foodle_cache'][$thiswiki];

}

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
		'userid' => $userid, 'username' => $_REQUEST['username'], 'response' => $response
	);
	
	$foodle->setMyResponse($newentry);
#	echo '<pre>'; print_r($foodle->getYourEntry($attributes['cn'][0])); echo '</pre>'; #exit;
}




#echo '<pre>'; print_r($foodle->getColumns()); echo '</pre>'; exit;

$et = new SimpleSAML_XHTML_Template($config, 'foodleresponse.php');
$et->data['header'] = $foodle->getName();
$et->data['identifier'] = $foodle->getIdentifier();
$et->data['descr'] = $foodle->getDescr();
$et->data['columns'] = $foodle->getColumns();
		
$et->data['yourentry'] = $foodle->getYourEntry($displayname);
$et->data['otherentries'] = $foodle->getOtherEntries();

$et->data['identifier'] = $foodle->getIdentifier();



$et->data['username'];

$et->show();


?>