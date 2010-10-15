<?php

require_once('_include.php');

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
	
		case 'embed':


			$embed = new Pages_EmbedFoodle($config, $parameters);
			$embed->getContent($_REQUEST['output']);
			break;
			
		case 'timezone':
			require('timezone.php');
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
			}
		
			$page = new Pages_PageFoodle($config, $parameters);
			$page->show();
			break;
			
		case 'debug':
			$page = new Pages_Debug($config, $parameters);
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

	$t = new SimpleSAML_XHTML_Template($config, 'foodleerror.php', 'foodle_foodle');
	$t->data['bread'] = array(array('href' => '/' . $config->getValue('baseurlpath'), 'title' => 'bc_frontpage'), array('title' => 'bc_errorpage'));
	$t->data['message'] = $e->getMessage() . '<pre>' . $e->getTraceAsString() . '</pre>';	
	$t->show();

}