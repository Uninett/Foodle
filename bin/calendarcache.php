#!/usr/bin/env php
<?php


$SIMPLESAMLPHP_DIR = '/var/simplesamlphp-foodle/';

/* This is the base directory of the simpleSAMLphp installation. */
$baseDir = dirname(dirname(__FILE__));
require_once($baseDir . '/www/_include.php');

/* Add library autoloader. */
require_once($SIMPLESAMLPHP_DIR . '/lib/_autoload.php');

// if (count($argv) < 1) {
// 	echo "Wrong number of parameters. Run:   " . $argv[0] . " [install,show] url [branch]\n"; exit;
// }
#$action = $argv[1];


// Needed in order to make session_start to be called before output is printed.
$session = SimpleSAML_Session::getInstance();
$sspconfig = SimpleSAML_Configuration::getConfig('config.php');
$config = SimpleSAML_Configuration::getInstance('foodle');

$db = new FoodleDBConnector($config);
echo 'Foodle calendar cache fetcher' . "\n";

$userids = $db->getUserIDs();

if (count($argv) > 1) {
	$userids = array(array('userid' => $argv[1]));
	echo "Running caledarcache updates only for this user:   " . $argv[1] . "\n";
}

foreach($userids AS $userid) {
	
	echo 'Processing user ID ' . $userid['userid'] . "\n";
	$user = $db->readUser($userid['userid']);
	
	$urls = $user->getCalendarURLs();
	
	if (empty($urls)) continue;
	
	foreach($urls AS $url) {
		
		echo ' Processing URL : ' . $url . "\n";
		
		$cal = new Calendar($url, FALSE);
		$cal->updateCache();
		
	}
	
	
}


if (count($argv) > 1) {
	$userids = array(array('userid' => $argv[1]));
	echo "Running caledarcache updates only for this user:   " . $argv[1] . "\n";
	
	$user = $db->readUser($userids[0]['userid']);
	$aggregator = $user->getCalendarAggregator();
	$aggregator->testSomeDates();
	
}



// $cal = new Calendar('https://www.google.com/calendar/ical/andreassolberg%40gmail.com/public/basic.ics', FALSE);
// $cal->updateCache();
// 
// $cal->testSomeDates();

exit;


$start = time();

$no = count($urls);
$c = 0;
foreach($urls AS $url) {
	$c++;
	echo "Processing " . $c . "/" . $no . "  "  . $url . "\n";
	$cal = new Calendar($url, FALSE);
}

echo "Completed calendar caching in " . (time() - $start) . " seconds.\n\n";



