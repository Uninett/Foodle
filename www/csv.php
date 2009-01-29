<?php
require_once('_include.php');



$config = SimpleSAML_Configuration::getInstance('foodle');

// Starting sessions.
session_start();


#include('../config/groups.php');

try {

	/* Load simpleSAMLphp, configuration and metadata */
	$sspconfig = SimpleSAML_Configuration::getInstance();

	
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
	
		$foodle = new Foodle($thisfoodle, 'rss@example.org', $link);
		$_SESSION['foodle_cache'][$thisfoodle] =& $foodle;
		
	} else {
	
		$foodle =& $_SESSION['foodle_cache'][$thiswiki];
	
	}
	

	$name = $foodle->getName();
	$descr = $foodle->getDescr();
	$entries = $foodle->getOtherEntries();
	
	$identifier = $foodle->getIdentifier();
	
	$url = 'https://foodle.feide.no/foodle.php?id=' . $identifier;
	
	$et->data['expire'] = $foodle->getExpire();
	$et->data['expired'] = $foodle->expired();
	$et->data['expiretext'] = $foodle->getExpireText();
	$et->data['columns'] = $foodle->getColumns();
	
	
	function encodeSingleResponse($r) {
		if ($r == 1) {
			return '☒';
		}
		return '☐';
	}

	function encodeResponse($r) {
		$k = array();
		foreach ($r AS $nr) {
			$k[] = encodeSingleResponse($nr);
		}
		return join(' ', $k);
	}

// 	echo '<pre>';
// 	print_r($entries);
	
	header("Content-type: application/vnd.ms-excel; charset=utf-8");
	header("Content-disposition:  attachment; filename=foodle-" . $identifier . "_" . date("Y-m-d") . ".csv");

	$s = ';';

	foreach ($entries AS $entry) {
	
		echo $entry['username'] . $s .  $entry['userid'] . $s . join($s, $entry['response']) . $s . date("Y-m-d H:i", $entry['created']) . "\r\n";
	
// 		$newrssentry = array(
// 			'title' => $entry['username'] . ' (' . $entry['userid'] . ')',
// 			'description' => 'Response: ' . encodeResponse($entry['response']),
// 			'pubDate' => $entry['created'],
// #			'link' => $url, 
// 		);
// 		if (isset($entry['notes'])) {
// 			$newrssentry['description'] .= '<br /><strong>Comment from user: </strong><i>' . $entry['notes'] . '</i>';
// 		}
// 		$newrssentry['description'] .= '<br />[ <a href="' . $url . '">go to foodle</a> ]';
// 		
// 		$rssentries[] = $newrssentry;
	}

	
	
	
} catch(Exception $e) {

	

}

?>