#!/usr/bin/env php
<?php

$SIMPLESAMLPHP_DIR = '/var/simplesamlphp-foodle';

/* This is the base directory of the simpleSAMLphp installation. */
$baseDir = dirname(dirname(__FILE__));
require_once($baseDir . '/www/_include.php');

/* Add library autoloader. */
require_once($SIMPLESAMLPHP_DIR . '/lib/_autoload.php');




// Needed in order to make session_start to be called before output is printed.
$session = SimpleSAML_Session::getInstance();
$sspconfig = SimpleSAML_Configuration::getConfig('config.php');
$config = SimpleSAML_Configuration::getInstance('foodle');


$db = new FoodleDBConnector($config);
echo 'Foodle group builder' . "\n";


$bconfig = $config->getValue('groupbuilder');

print_r($bconfig);


foreach($bconfig AS $groupid => $gdef) {
	echo "\n" . 'Processing group ' . $groupid . "\n";
	
	if (!is_numeric($groupid)) {
		$groupid = $db->addGroupIfNotExists($groupid);
	}
	
	$existingUsers = $db->getContactlist(null, $groupid);
	
	$users = array();
	
	foreach($existingUsers AS $euser) {
		$users[$euser['userid']] = 'old';
	}
	
	if (!empty($gdef['orgunit'])) {
		$correctUsers = $db->getUsersByOrgUnit($gdef['orgunit'], $gdef['realm']);
	
	} else if (!empty($gdef['org'])) {
		$correctUsers = $db->getUsersByOrg($gdef['org']);
		
	} else if (!empty($gdef['realm'])) {
		$correctUsers = $db->getUsersByRealm($gdef['realm']);
		if (!empty($gdef['realmemail'])) {
			foreach($correctUsers AS $key => $cu) {

				$realm = explode('@', $cu['email']);
				if (
					(count($realm) == 2) && ($realm[1] === $gdef['realm'])
					) {
				} else {
					unset($correctUsers[$key]);
				}
			}
		}
	}
	
	echo 'Found ' . count($correctUsers) . " existing group members\n";
	
	foreach($correctUsers AS $cuser) {
		if (!empty($users[$cuser['userid']])) {
			$users[$cuser['userid']] = 'ok';
		} else {
			$users[$cuser['userid']] = 'new';
		}
	}


	
	foreach($users AS $userid => $value) {
		switch($value) {
		
			case 'ok':
				echo 'X';
				break;
				
			case 'new':
				echo 'Add user [' . $userid. ']' . "\n"; 
				$db->addToContactlist($groupid, $userid);
				break;
			
			case 'old':
				echo 'Reomve user [' . $userid. ']' . "\n"; 
				$db->removeFromContactlist($groupid, $userid);
				break;
				
		}

	}
	

}

echo "\n";
