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
	
	
	$link = mysql_connect(
		$config->getValue('db.host', 'localhost'), 
		$config->getValue('db.user'),
		$config->getValue('db.pass'));
	if(!$link){
		throw new Exception('Could not connect to database: '.mysql_error());
	}
	mysql_select_db($config->getValue('db.name','feidefoodle'));
	
	
	$thisfoodle = null;
	if (isset($_REQUEST['id'])) {
		$_SESSION['id'] = $_REQUEST['id'];
		$thisfoodle = $_REQUEST['id'];
	} elseif(isset($_SESSION['id'])) {
		$thisfoodle = $_SESSION['id'];
	}
	if (empty($thisfoodle)) throw new Exception('No foodle selected');
	
	
	
	
	
	if(!empty($_REQUEST['name'])) {
	
		if (empty($_REQUEST['name'])) throw new Exception('You did not type in a name for the foodle.');
		
		$name = $_REQUEST['name'];
		$descr = isset($_REQUEST['descr']) ? $_REQUEST['descr'] : 'No description available.';
		
		$expire = isset($_REQUEST['expire']) ? $_REQUEST['expire'] : NULL;
	
		
		$foodle = new Foodle($thisfoodle, $userid, $link);
		
		$foodle->setInfo($name, $descr, $expire);
		
		$foodle->requireOwner();
		
		$foodle->setDBhandle($link);
		$foodle->savetoDB();

	}
	
	$foodle = new Foodle($thisfoodle, $userid, $link);
		
	
	
	#echo '<pre>'; print_r($foodle->getColumns()); echo '</pre>'; exit;
	
	$et = new SimpleSAML_XHTML_Template($config, 'foodleedit.php', 'foodle_foodle');
	$et->data['header'] = $foodle->getName();
	$et->data['identifier'] = $foodle->getIdentifier();
	$et->data['descr'] = $foodle->getDescr();
	$et->data['expire'] = $foodle->getExpire();
	$et->data['expired'] = $foodle->expired();
	$et->data['expiretext'] = $foodle->getExpireText();
	$et->data['expiretextfield'] = $foodle->getExpireTextField();
	$et->data['bread'] = array(
		array('href' => '/', 'title' => 'bc_frontpage'), 
		array('href' => 'foodle.php?id=' . $foodle->getIdentifier(), 'title' => $foodle->getName()), 
		array('title' => 'bc_edit')
	);
	$et->show();

} catch(Exception $e) {

	$et = new SimpleSAML_XHTML_Template($config, 'foodleerror.php', 'foodle_foodle');
	$et->data['bread'] = array(array('href' => '/', 'title' => 'bc_frontpage'), array('title' => 'bc_errorpage'));
	$et->data['message'] = $e->getMessage();
	
	$et->show();


}

?>