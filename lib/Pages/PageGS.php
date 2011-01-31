<?php

class Pages_PageGS extends Pages_Page {
	
	private $user;
	private $auth;
	
	
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
				
		$this->auth();
	
	}
	
	// Authenticate the user
	private function auth() {
		$this->auth = new FoodleAuth();
		$this->auth->requireAuth(FALSE);

		$this->user = $this->auth->getUser();

	}
	


	
	// Process the page.
	function show() {

		$url = FastPass::url($this->config->getValue('getsatisfaction.key'), $this->config->getValue('getsatisfaction.secret'), 
			$this->user->email, $this->user->name, $this->user->userid);

#		setcookie("fastpass", $url, time()+60*60*24*30);

		$furl = 'http://tjenester.ecampus.no/fastpass/finish_signover?company=ecampus&fastpass=' . urlencode($url);
		
		SimpleSAML_Utilities::redirect($furl);

// 		echo '<html><body><img onload="" />
// 			<script src="' . $url . '" type="text/javascript"></script>
// 			<script type="text/javascript">window.close();</script>
// 			</body></html>';

// 		
// 	exit;
// 		echo '<pre>Request: '; 
// 		print_r($_REQUEST);
// 		echo '</pre>';
// 				
// 		echo 'url: ' . $url;
// 		
// 		exit;

	}
	
}

