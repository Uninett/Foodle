<?php



class Pages_EmbedFoodle extends Pages_PageFoodle {
	
	// 
	// function __construct($config, $parameters) {
	// 	parent::__construct($config, $parameters);
	// 	
	// 	if (count($parameters) < 1) throw new Exception('Missing [foodleid] parameter in URL.');
	// 	
	// 	Data_Foodle::requireValidIdentifier($parameters[0]);
	// 	$this->foodleid = $parameters[0];
	// 	$this->foodlepath = '/foodle/' . $this->foodleid;
	// 	
	// 	$this->foodle = $this->fdb->readFoodle($this->foodleid);
	// 	
	// 	$this->auth();
	// }
	// 
	
	
	
	// Authenticate the user
	protected function auth() {
		$this->auth = new FoodleAuth();
		$this->auth->requireAuth(TRUE);

		$this->user = new Data_User($this->fdb);
		
		if ($this->auth->isAuth()) {
			$this->user = new Data_User($this->fdb);
			$this->user->email = $this->auth->getMail();
			$this->user->userid = $this->auth->getUserID();
			$this->user->name = $this->auth->getDisplayName();
			$this->user->calendarURL = $this->auth->getCalendarURL();
		} else {
			$this->user = new Data_User($this->fdb);
			$this->user->email = $this->auth->getMail();
			$this->user->userid = $this->auth->getUserID();
			$this->user->name = $this->auth->getDisplayName();			
	
		}
	}

	
	
	// Process the page.
	function getContent($type) {


		$t = new SimpleSAML_XHTML_Template($this->config, 'foodleresponse.php', 'foodle_foodle');

		$text = '<h2 class="foodleHeader">' . $this->foodle->name . '</h2>';
		$text .= '<div class="foodleDescription">' . $this->foodle->descr . '</div>';

		$table = XHTMLEmbed::getTable($t, $this->foodle);
		$text .= $table;
		
		$url = FoodleUtils::getUrl() . 'foodle/' . $this->foodle->identifier;
		$additionalData = '<div class="foodleAdditionalDetails"><form action="' . htmlspecialchars($url)  . '"><input type="submit" name="subm" value="Go to this Foodle" /></form></div>';
		$text .= $additionalData;
		
		
		$content = array(
			'name' => $this->foodle->name,
			'descr' => $this->foodle->descr,
			'result' => $table,
			'extra' => $additionalData,
		);
		
		
		$iframe = '<!DOCTYPE html>
		<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

			<title>Foodle</title>

		</head>

		<body>
			
			' . $text . '
		
		</body>
		</html>
		';
		
		
		switch($type) {
			

			case 'htmliframe':
				header('Content-Type: text/html; charset=utf8');
				echo($iframe);
				break;
			
			case 'htmlembed':
				header('Content-Type: text/html; charset=utf8');
				echo($text);
				break;

			
			case 'json':
			default:
				header('Content-Type: application/json; charset=utf-8');
				echo json_encode($content);
				
			
		}
		
		



#		$t->show();


	}
	
}
