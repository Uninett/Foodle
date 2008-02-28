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

try {

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
	
		#$foodle = new Foodle($thisfoodle, $attributes['eduPersonPrincipalName'][0], $link);
	
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
	
	
	
	if(!empty($_REQUEST['c1'])) {
	
	
		#echo '<pre>'; print_r($_REQUEST['date']); echo '</pre>';
		#if (!is_array($_REQUEST['date'])) throw new Exception('Did not get a list of dates');
		if (empty($_REQUEST['name'])) throw new Exception('You did not type in a name for the foodle.');
		
		$name = $_REQUEST['name'];
		$descr = isset($_REQUEST['descr']) ? $_REQUEST['descr'] : 'No description available.';
		
	
	
		$cols = array();
	
		for ($head = 0; $head < 5; $head++) {
		
			if (array_key_exists('c' . $head, $_REQUEST) && !empty($_REQUEST['c' . $head]) ) {
			
				$nextlevel = array();						
				for ($second = 0; $second < 5; $second++) {
					if (array_key_exists('c' . $head . $second, $_REQUEST) && !empty($_REQUEST['c' . $head . $second]))
						$nextlevel[] = $_REQUEST['c' . $head . $second];
				}
				
				$cols[ $_REQUEST['c' . $head]] = empty($nextlevel) ? null : $nextlevel;
				
			}
		
		}
	
		#print_r($cols);
		
		$foodle = new Foodle(null, $userid);
		$foodle->setInfo($name, $descr);
		$foodle->setColumns($cols);
		
		#echo 'Columns: '. print_r($foodle->encodeColumn($cols));
		#exit;
	
	
		
	
		
		$foodle->setDBhandle($link);
		$foodle->savetoDB();
		
		$id = $foodle->getIdentifier();
		
		$et = new SimpleSAML_XHTML_Template($config, 'foodleready.php');
	
		$et->data['name'] = $foodle->getName();
		$et->data['identifier'] = $foodle->getIdentifier();
		$et->data['descr'] = $foodle->getDescr();
		$et->data['url'] = 'https://foodle.feide.no/foodle.php?id=' . $id;
		$et->show();
		exit;
	}
	
	
	
	
	
	#echo '<pre>'; print_r($foodle->getColumns()); echo '</pre>'; exit;
	
	$et = new SimpleSAML_XHTML_Template($config, 'foodlecreatemc.php');
	
	
	
	$et->show();

} catch(Exception $e) {

	$et = new SimpleSAML_XHTML_Template($config, 'foodleerror.php');
	
	$et->data['message'] = $e->getMessage();
	
	$et->show();


}

?>