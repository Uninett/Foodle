<?php
require_once('_include.php');

$config = SimpleSAML_Configuration::getInstance('foodle');


try {


	
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
	
	$otherentries = $foodle->getOtherEntries();
	$col = 0;
	if (!empty($_REQUEST['col']) && is_numeric($_REQUEST['col'])) {
		$col = $_REQUEST['col'];
	} 

	$emaillist = array();
	foreach($otherentries AS $entry) {
		if (empty($entry['email'])) continue;
		if ($col == 0) { 
			$emaillist[] = '"' . htmlspecialchars($entry['username'], ENT_QUOTES, 'UTF-8') . '" &lt;' . htmlspecialchars($entry['email'], ENT_QUOTES, 'UTF-8') . '&gt;';
		} elseif ($entry['response'][$col-1] == 1) {
			$emaillist[] = '"' . htmlspecialchars($entry['username'], ENT_QUOTES, 'UTF-8') . '" &lt;' . htmlspecialchars($entry['email'], ENT_QUOTES, 'UTF-8') . '&gt;';

		}
	}
	echo join(', ', $emaillist); 
	
	
} catch(Exception $e) {

	$et = new SimpleSAML_XHTML_Template($config, 'foodleerror.php', 'foodle_foodle');
	$et->data['bread'] = array(array('href' => '/' . $config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), array('title' => 'bc_errorpage'));
	$et->data['message'] = $e->getMessage();
	
	$et->show();


}

?>