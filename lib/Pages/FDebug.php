<?php



class Pages_FDebug extends Pages_Page {
	
	private $foodle;
	private $user;
	private $foodleid;
	private $foodlepath;
	
	private $loginurl;
	private $logouturl;
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		if (count($parameters) < 1) throw new Exception('Missing [foodleid] parameter in URL.');
		
		Data_Foodle::requireValidIdentifier($parameters[0]);
		$this->foodleid = $parameters[0];
		$this->foodlepath = '/foodle/' . $this->foodleid;
		
		$this->foodle = $this->fdb->readFoodle($this->foodleid);
	}
	
	private function dump($header, $text) {
	

		echo('<h3 style="color: #060; font-weight: normal"><tt>' . $header . '</tt></h3>');

			
		echo('<pre style="width: 90%; border: 1px solid #ccc; max-height: 300px; margin: .5em 5em 1em 5em; overflow: auto">');
		
		if (!isset($text)) {
			echo('<span style="background: red; color: #eee; padding: 3px; border: 1px solid #600; margin: 2px">undefined</span>');
		} else if($text === null) {
			echo('<span style="background: red; color: #eee; padding: 3px; border: 1px solid #600; margin: 2px">null</span>');
		} else if($text === '') {
			echo('<span style="background: red; color: #eee; padding: 3px; border: 1px solid #600; margin: 2px">empty string</span>');
		} else if(is_bool($text)) {
			echo('<span style="background: yellow; color: #555; padding: 3px; border: 1px solid #882; margin: 2px">' . ($text ? 'true' : 'false' ). '</span>');
		} else if(empty($text)) {
			echo('<span style="background: red; color: #eee; padding: 3px; border: 1px solid #600; margin: 2px">empty</span>');
		} else {
			print_r($text);
		}
		

		echo('</pre>');
	}

	// Process the page.
	function show() {


		echo('<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>Foodle Object Debugger</title>
</head>
<body>

<h1>Documentation of the Foodle object</h1>

');



		echo('<h2>General column handling</h2>');
		
		$this->dump('getColumnHeaders()', $this->foodle->getColumnHeaders());
		$this->dump('getColumnHeadersVertical()', $this->foodle->getColumnHeadersVertical());
		
		$col = array();
		$this->foodle->getColumnList($col);
		$this->dump('getColumnList()', $col);

		$this->dump('calculateColumns()', $this->foodle->calculateColumns());		
		


		echo('<h2>Date and time handling</h2>');

		$this->dump('responsetype', $this->foodle->responsetype);
		$this->dump('responseType()', $this->foodle->responseType());
		$this->dump('timezoneEnabled()', $this->foodle->timezoneEnabled());
		$this->dump('onlyDateColumns()', $this->foodle->onlyDateColumns());
		$this->dump('calendarEnabled()', $this->foodle->calendarEnabled());
		$this->dump('getColumnDates()', $this->foodle->getColumnDates());
		$this->dump('getNofColumns()', $this->foodle->getNofColumns());
		$this->dump('getColumnDepth()', $this->foodle->getColumnDepth());
		
		
		$this->dump('foodle->columns', $this->foodle->columns );
		
		$this->foodle->presentInTimeZone("Europe/Dublin");
		$this->dump('presentInTimeZone("Europe/Dublin")', $this->foodle->columns );
		
		
		


		echo('<h2>Misc properties</h2>');

		$this->dump('isLocked()', $this->foodle->isLocked());
		$this->dump('isExpired()', $this->foodle->isExpired());
		$this->dump('maxReached()', $this->foodle->maxReached());
		
		$this->dump('getMaxDef()', $this->foodle->getMaxDef());
		$this->dump('getExpireText()', $this->foodle->getExpireText());
		$this->dump('getExpireTextField()', $this->foodle->getExpireTextField());
		
		
			


		echo('</body>
</html>
');

	}
	
}

