<?php

/**
 * This class represents a user in Foodle
 */
class Data_User {

	public $userid;
	public $name;
	public $email;
#	public $calendarURL = 'http://www.google.com/calendar/ical/ibt0e3subvqqili53gel89igu8%40group.calendar.google.com/private-6637d5c39c9eeb711243b6d7fb46d025/basic.ics';
	public $calendarURL = NULL; 
	# = 'https://evolution.uninett.no/ical.php?id=fc5f2065pli1iq5hmmjpghxop3rv7kj7o3n4vh6gatsn6gpawcuve44egrco4967etqxkrz9ew8sox3q1zayjisnm84etcgcvkj1&year=1';
	public $anonymous = TRUE;

	public $loadedFromDB = FALSE;
	
	private $db;
	
	function __construct(FoodleDBConnector $db) {
		$this->db = $db;
	}
	
	public function hasCalendar() {
		return ($this->calendarURL !== NULL);
	}
	
	public static function debugfield($text, $value) {
		$text = '<dt>' . $text . '</dt><dd><tt>' . var_export($value, TRUE) . '</tt></dd>';
		return $text;
	}
	
	public function debug() {
		$text = '<dl>' .
			self::debugfield('User ID', $this->userid) . 
			self::debugfield('Name', $this->name) . 
			self::debugfield('E-mail', $this->email) . 
			self::debugfield('Calendar URL', $this->calendarURL) . '</dl>'
			;
		return $text;
	}
	
	public function isAdmin() {
		if ($this->userid == 'andreas@uninett.no') return TRUE;
		if ($this->userid == 'andreas@rnd.feide.no') return TRUE;
		return FALSE;
	}
	
	public function debugCalendar() {
		$text = '';
		if ($this->hasCalendar() ) {
			
			$cal = new Calendar($this->calendarURL, TRUE);
			$freebusy = $cal->getFreeBusy();
			
			$text .= '<p>List of free busy times:</p><ul>';
			foreach($freebusy AS $fb) {
				$text .= '<li>Busy from <i>' . date('r', $fb[0]). '</i> to <i>' . date('r', $fb[1]). '</i>.</li>';
			}
			$text .= '</ul>';
#			$text .= '<p>Calenar output: ' . var_export($freebusy, TRUE) . '</p>';
			
		} else {
			$text .= '<p>User has not enabled calendar</p>';
		}
		return $text;
	}
	
}
