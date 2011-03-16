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


#echo '<pre>'; print_r($parameters); exit;

try {
	
	$action = array_shift($parameters);
	
	switch($action) {

		case '':
			$page = new Pages_PageFront($config, $parameters);
			$page->show();
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
					
				case 'invite': 
					$api = new API_Invite($config, $parameters);
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

		case 'profile':
			$page = new Pages_PageProfile($config, $parameters);
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
			
		case 'contacts':
			$page = new Pages_PageContacts($config, $parameters);
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
	
	FastPass::$domain = "tjenester.ecampus.no";
	$t->data['getsatisfactionscript'] = FastPass::script(
		$config->getValue('getsatisfaction.key'), $config->getValue('getsatisfaction.secret'), 
		$email, $name, $userid);
	
	$t->show();

}