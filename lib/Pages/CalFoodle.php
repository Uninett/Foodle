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
			
			if (!empty($foodle->timezone)) {
				$from = $foodle->toEpoch($foodle->datetime['datefrom'] . ' ' . $foodle->datetime['timefrom']);
				
				// Alternative return with local timezone reference...
				// 		DTSTART;TZID=Europe/London:20110205T080000
				// return 'DTSTART;TZID=' . $foodle->timezone . ':' . date('Ymd\THis', $from)  . '; from: ' . $from;
				
				return 'DTSTART:' . gmdate('Ymd\THis\Z', $from);
			} 
			$from = strtotime($foodle->datetime['datefrom'] . ' ' . $foodle->datetime['timefrom']);
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
			

			
			if (!empty($foodle->timezone)) {
			
				$to = $foodle->toEpoch($dateto . ' ' . $foodle->datetime['timeto']);
				
				// DTSTART;TZID=Europe/London:20110205T080000
				// return 'DTEND;TZID=' . $foodle->timezone . ':' . date('Ymd\THis', $to);
				
				return 'DTEND:' . gmdate('Ymd\THis\Z', $to);
			} 

			$to = strtotime($dateto . ' ' . $foodle->datetime['timeto']);
			return 'DTEND:' . date('Ymd\THis', $to);
			
		}
		
		// to stamp is only a date		
		$to = strtotime($dateto);
		return 'DTEND;VALUE=DATE:' . date('Ymd', $to + 86400);
	}
	
	function rsvpSection() {
		

		
		$str = 'ORGANIZER:MAILTO:' . $this->foodle->owner. "\n";
		
		
// 'ATTENDEE;ROLE=REQ-PARTICIPANT;PARTSTAT=TENTATIVE;CN=Andreas Åkre Solberg:MAILTO:andreassolberg@gmail.com
// ATTENDEE;ROLE=REQ-PARTICIPANT;PARTSTAT=TENTATIVE;CN=Test Person:MAILTO:test@example.org
// ATTENDEE;ROLE=REQ-PARTICIPANT;DELEGATED-FROM="MAILTO:bob@host.com";PARTSTAT=ACCEPTED;CN=Jane Doe:MAILTO:jdoe@host1.com';

		$responses = $this->foodle->getResponses();
		foreach($responses AS $response) {
			$str .= "ATTENDEE;ROLE=REQ-PARTICIPANT;PARTSTAT=TENTATIVE;CN=" . $response->username . ":MAILTO:" . $response->email . "\n";
		}

		return $str;
	}
	
	
	function createical() {
	
		
	
		$url = FoodleUtils::getUrl() . 'foodle/' . $this->foodle->identifier;
		$str = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
METHOD:PUBLISH
PRODID:-//UNINETT//Foodle//EN
BEGIN:VEVENT
CREATED:' . $this->foodle->getCreatedStamp() . '
UID:' . strtoupper(sha1($this->foodle->identifier)) . '@foodl.org
' . $this->dtstart($this->foodle) . '
' . $this->dtend($this->foodle) . '
TRANSP:OPAQUE
SUMMARY:' . $this->foodle->name . '
DTSTAMP:' . $this->dtstamp() . '
DESCRIPTION:' . trim(chunk_split(preg_replace('/[\n\r]+/', '\n\n', strip_tags($this->foodle->descr)), 76, "\n ")) . '
URL;VALUE=URI:' . $url . '
' . $this->rsvpSection() . 'SEQUENCE:' . $this->foodle->getCreatedStampEpoch() . '
END:VEVENT
END:VCALENDAR';
		
		
		
		return $str;
	}
	
	
	
	// Process the page.
	function show() {



		
		//set correct content-type-header
		header('Content-type: text/calendar; charset=utf-8');
		header('Content-Disposition: inline; filename=calendar.ics');

		echo $this->createical();		

	}
	
}

