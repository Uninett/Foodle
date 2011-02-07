<?php



class Pages_CalFoodle extends Pages_Page {
		
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		if (count($parameters) < 1) throw new Exception('Missing [foodleid] parameter in URL.');
		
		Data_Foodle::requireValidIdentifier($parameters[0]);
		$this->foodleid = $parameters[0];
		$this->foodlepath = '/foodle/' . $this->foodleid;
		
		$this->foodle = $this->fdb->readFoodle($this->foodleid);
		
	}
	

	function dtstamp() {
		return gmdate('Ymd\THis\Z');
	}
	
	function dtstart(Data_Foodle $foodle) {
		
		// From stamp is date and time
		if (!empty($foodle->datetime['timefrom'])) {
			
			$from = strtotime($foodle->datetime['datefrom'] . ' ' . $foodle->datetime['timefrom']);
			
			if (!empty($foodle->timezone)) {
				// DTSTART;TZID=Europe/London:20110205T080000
				return 'DTSTART;TZID=' . $foodle->timezone . ':' . date('Ymd\THis', $from);
			} 
			return 'DTSTART:' . date('Ymd\THis', $from);
		}
		
		// From stamp is only a date		
		$from = strtotime($foodle->datetime['datefrom']);
		return 'DTSTART;VALUE=DATE:' . date('Ymd', $from);
	}
	
	function dtend(Data_Foodle $foodle) {
		
		$dateto = $foodle->datetime['datefrom'];
		if (!empty($foodle->datetime['dateto'])) $dateto = $foodle->datetime['dateto'];
		
		// to stamp is date and time
		if (!empty($foodle->datetime['timeto'])) {
			
			$to = strtotime($dateto . ' ' . $foodle->datetime['timeto']);
			
			if (!empty($foodle->timezone)) {
				// DTSTART;TZID=Europe/London:20110205T080000
				return 'DTEND;TZID=' . $foodle->timezone . ':' . date('Ymd\THis', $to);
			} 
			return 'DTEND:' . date('Ymd\THis', $to);
			
		}
		
		// to stamp is only a date		
		$to = strtotime($dateto);
		return 'DTEND;VALUE=DATE:' . date('Ymd', $to + 86400);
	}
	
	
	function createical(Data_Foodle $foodle) {
	
		
	
		$url = FoodleUtils::getUrl() . 'foodle/' . $foodle->identifier;
		$str = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
X-WR-CALNAME:' . $foodle->name . '
METHOD:PUBLISH
PRODID:-//UNINETT//Foodle//EN
BEGIN:VEVENT
CREATED:' . $foodle->getCreatedStamp() . '
UID:' . strtoupper(sha1($foodle->identifier)) . '@foodl.org
' . $this->dtend($foodle) . '
TRANSP:OPAQUE
SUMMARY:' . $foodle->name . '
' . $this->dtstart($foodle) . '
DTSTAMP:' . $this->dtstamp() . '
DESCRIPTION:' . trim(chunk_split(preg_replace('/[\n\r]+/', '\n\n', strip_tags($foodle->descr)), 76, "\n ")) . '
URL;VALUE=URI:' . $url . '
SEQUENCE:' . $foodle->getCreatedStampEpoch() . '
END:VEVENT
END:VCALENDAR';
		
		
		
		return $str;
	}
	
	
	
	// Process the page.
	function show() {



		
		//set correct content-type-header
		header('Content-type: text/calendar; charset=utf-8');
		header('Content-Disposition: inline; filename=calendar.ics');

		echo $this->createical($this->foodle);		

	}
	
}

