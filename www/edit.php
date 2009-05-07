<?php
require_once('_include.php');



$config = SimpleSAML_Configuration::getInstance('foodle');

// Starting sessions.
session_start();



try {

	
	
	$foodleauth = new FoodleAuth();
	$foodleauth->requireAuth(FALSE);

	$email = $foodleauth->getMail();
	$userid = $foodleauth->getUserID();
	$displayname = $foodleauth->getDisplayName();
	
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
		if (empty($_REQUEST['coldef'])) throw new Exception('Did not get column definition.');

		$name = $_REQUEST['name'];
		$descr = isset($_REQUEST['descr']) ? $_REQUEST['descr'] : '...';
		$expire = $_REQUEST['expire'];
		
		$maxdef = '';
		if(!empty($_REQUEST['maxentries']) && is_numeric($_REQUEST['maxentries'])) {
			$col =  0;
			if(isset($_REQUEST['maxentriescol'])) {
				$col = $_REQUEST['maxentriescol'];
			}
			$maxdef = $col . ':' . $_REQUEST['maxentries'];
		}

		$anon = '0';
		if (array_key_exists('anon', $_REQUEST)) $anon = '1';
		
		$foodle = new Foodle($thisfoodle, $userid, $link);
		
		$foodle->setInfo($name, $descr, $expire, $maxdef, $anon );
		$foodle->setColumnsByDef($_REQUEST['coldef']);
		
		$foodle->requireOwner();
		
		$foodle->setDBhandle($link);
		$foodle->savetoDB();
		
		
		
		/*
		 * Show screen with edit completed.
		 */
		$t = new SimpleSAML_XHTML_Template($config, 'foodleready.php', 'foodle_foodle');
	
		$t->data['name'] = $name;
		$t->data['identifier'] = $thisfoodle;
		$t->data['descr'] = $descr;
		$t->data['authenticated'] = $foodleauth->isAuth();
		$t->data['url'] = FoodleUtils::getUrl() . 'foodle.php?id=' . $thisfoodle;
		$t->data['bread'] = array(
			array('href' => '/' . $config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), 
			array('href' => 'foodle.php?id=' . $thisfoodle, 'title' => $foodle->getName()), 
			array('title' => 'bc_ready')
		);
		
		$t->show();
		exit;

	}
	
	$foodle = new Foodle($thisfoodle, $userid, $link);
		
	$dateepoch = $foodle->getExpire();
	$date = NULL;
	if (!empty($dateepoch)) {
		$date = date('Y-m-d H:s', $dateepoch);
		
	}
	
	#echo '<pre>'; print_r($foodle->getColumns()); echo '</pre>'; exit;
	

	$t = new SimpleSAML_XHTML_Template($config, 'foodlecreate.php', 'foodle_foodle');
	
	$t->data['authenticated'] = $foodleauth->isAuth();
	$t->data['userid'] = $userid;
	$t->data['displayname'] = $displayname;
	
	$t->data['edit'] = TRUE;

	$t->data['name'] = $foodle->getName();
	$t->data['identifier'] = $foodle->getIdentifier();
	$t->data['descr'] = $foodle->getDescr();
	$t->data['expire'] = $date;
	$t->data['maxdef'] = $foodle->getMaxDef();
	$t->data['anon'] = $foodle->getAnon();
	$t->data['expiretext'] = $foodle->getExpireText();
	$t->data['expiretextfield'] = $foodle->getExpireTextField();
	$t->data['columns'] = $foodle->getColumns();
	
	// echo('<pre>');
	// print_r($t->data['columns']); exit;
	
	$t->data['bread'] = array(
		array('href' => '/', 'title' => 'bc_frontpage'), 
		array('href' => 'foodle.php?id=' . $foodle->getIdentifier(), 'title' => $foodle->getName()), 
		array('title' => 'bc_edit')
	);
	$t->show();
	
} catch(Exception $e) {


	$t = new SimpleSAML_XHTML_Template($config, 'foodleerror.php', 'foodle_foodle');
	$t->data['bread'] = array(array('href' => '/' . $config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), array('title' => 'bc_errorpage'));
	$t->data['message'] = $e->getMessage();
	$t->data['authenticated'] = $foodleauth->isAuth();
	$t->data['userid'] = $userid;
	$t->data['displayname'] = $displayname;

	
	$t->show();


}
