<?php

$THISPATH = dirname(dirname(__FILE__)) . '/';
require_once($THISPATH . 'lib/Timer.php');


Timer::start();
Timer::tick('started');

require_once('_include.php');

#Timer::tick('Include complete');

$config = SimpleSAML_Configuration::getInstance('foodle');

$fullURI = $_SERVER['PATH_INFO'];
$script = $_SERVER['SCRIPT_NAME'];

$path = substr($fullURI, strlen($script) + 1, strlen($fullURI) - strlen($script) - 1);
$parameters = explode('/', $path);


// echo '<pre>'; print_r($parameters); echo '</pre>'; exit;

try {
	
	$action = array_shift($parameters);
	
	switch($action) {

		case '':
			$page = new Pages_PageFront($config, $parameters);
			$page->show();
			break;
			
		
		case 'calendar':
			if (count($parameters) !== 3) throw new Exception('Missing parameter');
			
			$type = array_shift($parameters);
			$user = array_shift($parameters);
			$token = array_shift($parameters);
			
			$calendar = new Calendar_CalendarUser($config, $user, $token);
			$calendar->show();
			
			break;
		
		/*
		 * API used by JS, and possibly others...
		 */
		case 'api': 
			if (count($parameters) < 1) throw new Exception('Missing parameter');			
			$action2 = array_shift($parameters);
			
			switch($action2) {
				case 'contacts': 
					$api = new API_Contacts($config, $parameters);
					$api->show();
					break;
					
				case 'foodlelist': 
					$api = new API_Foodlelist($config, $parameters);
					$api->show();
					break;

				case 'activity': 
					$api = new API_Activity($config, $parameters);
					$api->show();
					break;					
					
				case 'events': 
					$api = new API_Events($config, $parameters);
					$api->show();
					break;
						
				case 'foodle': 
					$api = new API_Foodle($config, $parameters);
					$api->show();
					break;
					
				case 'invite': 
					$api = new API_Invite($config, $parameters);
					$api->show();
					break;

				case 'idplist': 
					$api = new API_IdPList($config, $parameters);
					$api->show();
					break;
					
				case 'files':
					$api = new API_Files($config, $parameters);
					$api->show();
					break;

				case 'profile-calendars':
					$api = new API_ProfileCalendars($config, $parameters);
					$api->show();
					break;

					
				case 'upload':
					$api = new API_Upload($config, $parameters);
					$api->show();
					break;

				case 'download':
					$api = new API_Download($config, $parameters);
					$api->show();
					break;

				case 'groups':
					$api = new API_Groups($config, $parameters);
					$api->show();
					break;


			}
			break;
			
			
			
			
	
		case 'embed':
			$embed = new Pages_EmbedFoodle($config, $parameters);
			$embed->getContent($_REQUEST['output']);
			break;
			
		case 'timezone':
			require('timezone.php');
			break;
			
		case 'mail':
			require('mail.php');
			break;

		case 'accountmapping':
			$page = new Pages_PageAccountMapping($config, $parameters);
			$page->show();
			break;

		case 'accountmappingprepare':
			$page = new Pages_PageAccountMappingPrepare($config, $parameters);
			$page->show();
			break;

		case 'profile':
			$page = new Pages_PageProfile($config, $parameters);
			$page->show();
			break;
			
		case 'profile-calendars':
			$page = new Pages_PageProfileCalendars($config, $parameters);
			$page->show();
			break;

		case 'attributes':
			$page = new Pages_PageAttributes($config, $parameters);
			$page->show();
			break;

			
		case 'login':
			$page = new Pages_Login($config, $parameters);
			$page->show();
			break;

		case 'user':
			$page = new Pages_PageUser($config, $parameters);
			$page->show();
			break;
			
			
		case 'group':
			$page = new Pages_PageGroup($config, $parameters);
			$page->show();
			break;
			

			
			
		case 'groups':
			$page = new Pages_PageContacts($config, $parameters);
			$page->show();
			break;
			
		case 'group-invite':
			$page = new Pages_PageGroupInvite($config, $parameters);
			$page->show();
			break;

		case 'photo':
			$page = new Pages_Photo($config, $parameters);
			$page->show();
			break;
			
		case 'stats':
			$page = new Pages_PageStats($config, $parameters);
			$page->show();
			break;

		case 'fixdate':
			$page = new Pages_FixDate($config, $parameters);
			#Timer::tick('before foodle show');
			$page->show();
			break;
			
	
		case 'foodle':
		
			if (isset($_REQUEST['output']) && $_REQUEST['output'] == 'rss') {
				$rss = new Pages_RSSFoodle($config, $parameters);
				$rss->show();
				break;				
			} elseif(isset($_REQUEST['output']) && $_REQUEST['output'] == 'csv') {
				$csv = new Pages_CSVFoodle($config, $parameters);
				$csv->show();
				break;								
			} elseif(isset($_REQUEST['output']) && $_REQUEST['output'] == 'ical') {
				$csv = new Pages_CalFoodle($config, $parameters);
				$csv->show();
				break;								
			}
		
			#Timer::tick('before new foodle page');
			$page = new Pages_PageFoodle($config, $parameters);
			#Timer::tick('before foodle show');
			$page->show();
			break;

			
		case 'delete':
			$page = new Pages_PageDelete($config, $parameters);
			$page->show();
			break;
			
		case 'debug':
			$page = new Pages_Debug($config, $parameters);
			$page->show();
			break;

		case 'debug2':
			$page = new Pages_FDebug($config, $parameters);
			$page->show();
			break;
			
			
		case 'support':
			$page = new Pages_PageSupport($config, $parameters);
			$page->show();
			break;

		case 'getsatisfaction':
			$page = new Pages_PageGS($config, $parameters);
			$page->show();

			break;

		case 'create':
			$page = new Pages_PageCreate($config, $parameters);
			$page->show();
			break;

		case 'edit':
			$page = new Pages_PageEdit($config, $parameters);
			$page->show();
			break;

		case 'preview':
			$page = new Pages_PagePreview($config, $parameters);
			$page->show();
			break;

		case 'disco':
			$page = new Pages_PageDisco($config, $parameters);
			$page->show();
			break;
			
		case 'discoresponse':
			require_once('discoresponse.html');
			break;
			
		case 'extradiscofeed':
			header('Content-Type: application/javascript; charset: utf-8');
			$data = file_get_contents('extradiscofeed.js');
			if ($_REQUEST['callback']) {
				if(!preg_match('/^[a-z0-9A-Z\-_]+$/', $_REQUEST['callback'])) throw new Exception('Invalid characters in callback.');

				header('Content-Type: application/javascript; charset=utf-8');
				echo $_REQUEST['callback'] . '(' . $data . ')';
			} else {
				header('Content-Type: application/json; charset=utf-8');
				echo $data;
			}
			
			break;

		case 'test':
			require_once('test-calendar.php');
			break;


		// Redirecting user if using old 
		case 'foodle.php':
			header('Location: /foodle/' . $_REQUEST['id']);
			break;

		// Redirecting user if using old 
		case 'favicon.ico':
			header('Content-Type: image/x-icon');
			include('res/favicon.ico');
			break;

		
		// No page found.
		default:
			throw new Exception('404: Page not found [' . $action . '].');
	
	}


} catch(Exception $e) {


	$isAuth = FALSE;

	try {
	
		$db = new FoodleDBConnector($config);
		$auth = new FoodleAuth($db);
		$auth->requireAuth(TRUE);

		$user = $auth->getUser();

		$email = $user->email;
		$userid = $user->userid;
		$name = $user->username;
		
		$isAuth = $auth->isAuth();
		
	} catch (Exception $e) {
		
	}

	$t = new SimpleSAML_XHTML_Template($config, 'foodleerror.php', 'foodle_foodle');
	$t->data['bread'] = array(array('href' => '/' . $config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), array('title' => 'bc_errorpage'));
	$t->data['message'] = $e->getMessage() . '<pre>' . $e->getTraceAsString() . '</pre>';
	$t->data['authenticated'] = $isAuth;
	$t->data['showsupport'] = TRUE;
	
	
	$t->show();

}
