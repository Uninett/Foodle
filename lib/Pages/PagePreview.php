<?php

class Pages_PagePreview extends Pages_Page {
	protected $auth, $user;
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		$this->auth();
	}
	
	
	// Authenticate the user
	protected function auth() {
		$this->auth = new FoodleAuth($this->fdb);
		$this->auth->requireAuth(FALSE);
		$this->user = $this->auth->getUser();
	}
	
	// Process the page.
	function show() {
		$parameters = array('def');
		foreach($parameters AS $parameter) {
			$_REQUEST[$parameter] = strip_tags($_REQUEST[$parameter]);
		}
		$descr = Data_Foodle::cleanMarkdownInput($_REQUEST['descr']);
	
		
		echo '<h1>' . htmlspecialchars($_REQUEST['name']) . '</h1>';
		echo '<p>' . $descr . '</p>';

		$foodle = new Data_Foodle($this->fdb);
		$foodle->columns = FoodleUtils::parseOldColDef(strip_tags($_REQUEST['def']));

		echo '<table class="list" style="width: 100%">';

		$t = new SimpleSAML_XHTML_Template($this->config, 'foodleresponse.php', 'foodle_foodle');

		XHTMLCol::show($t, $foodle);
						
		echo '<tbody>';
			

		/*
		 * Include demo response
		 */
		$demoresponse = new Data_FoodleResponse($this->fdb, $foodle);
		$demoresponse->userid = 'you@acme.org';
		$demoresponse->username = 'John Doe';
		$demoresponse->email = 'you@acme.org';
		$demoresponse->response = array('type' => 'manual', 'data' => array_fill(0, $foodle->getNofColumns(), '0') );
		
		XHTMLResponseEntry::showEditable($this->user, $t, $demoresponse, FALSE);

		echo '
		</tbody>	
		</table>';


	}
	
}

