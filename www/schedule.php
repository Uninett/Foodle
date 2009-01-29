<?php
require_once('_include.php');



$config = SimpleSAML_Configuration::getInstance('foodle');

// Starting sessions.
session_start();



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
	
	
	
	
	// TODO: REMOVE true to enable caching..
// 	if (! array_key_exists($thiswiki,$_SESSION['foodle_cache'] ) || true) {
// 	
// 		#$foodle = new Foodle($thisfoodle, $attributes['eduPersonPrincipalName'][0], $link);
// 	
// 		if (isset($_REQUEST['createnewsubmit'])) {
// 			if (!$foodle->isLoaded()) {
// 				$foodle->setOwner($userid);
// 			}
// 		}
// 		
// 		$_SESSION['foodle_cache'][$thisfoodle] =& $foodle;
// 		
// 	} else {
// 	
// 		$foodle =& $_SESSION['foodle_cache'][$thiswiki];
// 	
// 	}
	
	#echo '<pre>'; print_r($foodle); echo '</pre>'; exit;
	
	
	
	if(!empty($_REQUEST['save'])) {
		#echo '<pre>'; print_r($_REQUEST['date']); echo '</pre>';
		#if (!is_array($_REQUEST['date'])) throw new Exception('Did not get a list of dates');
		if (empty($_REQUEST['name'])) throw new Exception('You did not type in a name for the foodle.');
		if (empty($_REQUEST['coldef'])) throw new Exception('Did not get column definition.');

		$name = $_REQUEST['name'];
		$descr = isset($_REQUEST['descr']) ? $_REQUEST['descr'] : '...';
		$expire = $_REQUEST['expire'];
		
		$maxdef = '';
		if(!empty($_REQUEST['maxentries']) && is_numeric($_REQUEST['maxentries'])) {
			$col =  0;
			if(is_int($_REQUEST['maxentriescol'])) {
				$col = $_REQUEST['maxentriescol'];
			}
			$maxdef = $col . ':' . $_REQUEST['maxentries'];
		}
		echo 'maxdef:' . $maxdef ;
		
		
		$anon = '0';
		if (array_key_exists('anon', $_REQUEST)) $anon = '1';
		
		
		$foodle = new Foodle(null, $userid);
		$foodle->setInfo($name, $descr, $expire, $maxdef, $anon );
		$foodle->setColumnsByDef($_REQUEST['coldef']);

		
		$foodle->setDBhandle($link);
		$foodle->savetoDB();
		
		$id = $foodle->getIdentifier();
		
		$et = new SimpleSAML_XHTML_Template($config, 'foodleready.php', 'foodle_foodle');
	
		$et->data['name'] = $foodle->getName();
		$et->data['identifier'] = $foodle->getIdentifier();
		$et->data['descr'] = $foodle->getDescr();
		$et->data['url'] = 'https://foodle.feide.no/foodle.php?id=' . $id;
		$et->data['bread'] = array(
			array('href' => '/', 'title' => 'bc_frontpage'), 
			array('href' => 'foodle.php?id=' . $id, 'title' => $foodle->getName()), 
			array('title' => 'bc_ready')
		);
		
		$et->show();
		exit;
	}
	
	
	

	
	
	#echo '<pre>'; print_r($foodle->getColumns()); echo '</pre>'; exit;
	
	$et = new SimpleSAML_XHTML_Template($config, 'foodlecreate.php', 'foodle_foodle');
	

	$et->data['bread'] = array(
		array('href' => '/', 'title' => 'bc_frontpage'), 
		array('title' => 'bc_createnew')
	);
	$et->show();

} catch(Exception $e) {

	$et = new SimpleSAML_XHTML_Template($config, 'foodleerror.php', 'foodle_foodle');
	$et->data['bread'] = array(array('href' => '/', 'title' => 'bc_frontpage'), array('title' => 'bc_errorpage'));
	$et->data['message'] = $e->getMessage();
	
	$et->show();


}


?>