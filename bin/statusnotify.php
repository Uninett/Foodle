#!/usr/bin/env php
<?php

$SIMPLESAMLPHP_DIR = '/var/simplesamlphp-foodle';

/* This is the base directory of the simpleSAMLphp installation. */
$baseDir = dirname(dirname(__FILE__));
require_once($baseDir . '/www/_include.php');

/* Add library autoloader. */
require_once($SIMPLESAMLPHP_DIR . '/lib/_autoload.php');



class SNM {	
	private $user, $updates;
	public function __construct(Data_User $user) {
		$this->user = $user;
	}
	
	public function addFoodle(Data_Foodle $foodle, $updates) {
		$this->updates[$foodle->identifier] = array('foodle' => $foodle, 'updates' => $updates);
	}
	
	public function execute() {
		
		$text = '
Below follows the latest updates on Foodles you have created.

		';
		
		foreach($this->updates AS $foodleid => $res) {
			$url = FoodleUtils::getUrl() . 'foodle/' . $res['foodle']->identifier;
			$text .= '
## ' . htmlspecialchars($res['foodle']->name) . '

';
			if (empty($res['updates']['responses'])) {
				$text .= "\n\nNo new responses was registered for this Foodle.\n";
			} else {
				$text .= "\n";
				foreach($res['updates']['responses'] AS $response) {
					$text .= '* ' . $response->statusline() . "\n";
				}
				$text .= "\n";
			}
			if (empty($res['updates']['discussion'])) {
				$text .= "\nNo new discussion entries was registered for this Foodle.\n";
			} else {
				$text .= "\n";
				foreach($res['updates']['discussion'] AS $discussion) {
					#print_r($discussion);
					$text .= "* " . date('l H:i', $discussion['createdu']) . ' ' . $discussion['username'] . " added a discussion entry.\n";
				}
				$text .= "\n";
			}
			
			$text .= "\n[Go to this foodle to view all responses](" . htmlspecialchars($url) . ")\n\n";
			
		}

		$profileurl = FoodleUtils::getUrl() . 'profile';
		$text .= '

## Setup your e-mail notification preferences

You can turn of this e-mail notification, and configure other notification messages <a href="' . 
			htmlspecialchars($profileurl) . '">from your Foodle preference page</a>:

	' . htmlspecialchars($profileurl);
	

		$to = $this->user->email;
		// $to = 'andreassolberg@gmail.com';
		$mailer = new Foodle_EMail($to, 'Daily Foodle status update', 'Foodl.org <no-reply@foodl.org>');
		$mailer->setBody($text);
		$mailer->send();

	
	}

}



// if (count($argv) < 1) {
// 	echo "Wrong number of parameters. Run:   " . $argv[0] . " [install,show] url [branch]\n"; exit;
// }
#$action = $argv[1];


// Needed in order to make session_start to be called before output is printed.
$session = SimpleSAML_Session::getInstance();
$sspconfig = SimpleSAML_Configuration::getConfig('config.php');
$config = SimpleSAML_Configuration::getInstance('foodle');


$db = new FoodleDBConnector($config);
echo 'Foodle status notifier' . "\n";

$users = $db->getChangesOwners();

$start = time();

$no = count($users);
$c = 0;
foreach($users AS $userid => $foodles) {
	$c++;
	echo "\nProcessing user " . $c . "/" . $no . "  "  . $userid . "\n";
	
	
	$user = $db->readUser($userid);
	if ($user === false) {
		echo 'Skipping user [' . $userid . '] beacuse user account is not yet created.'. "\n";
		continue;
	}
	if (!$user->notification('otherstatus', TRUE)) {
		echo 'Skipping user [' . $userid . '] beacuse has turned off this kind of notification..'. "\n";
		continue;		
	}
	if (!$user->notification('otherstatus', TRUE)) {
		echo 'Skipping user [' . $userid . '] beacuse user has no valid email address'. "\n";
		continue;		
	}

	$snm = new SNM($user);	

	foreach($foodles AS $foodle) {
		echo "Processing " . $foodle->identifier . "\n";
		$updates = $db->getChangesFoodle($foodle);
		$snm->addFoodle($foodle, $updates);
	}
	
	$snm->execute();
	
// 	$cal = new Calendar($url, FALSE);
}

echo "Completed calendar caching in " . (time() - $start) . " seconds.\n\n";



