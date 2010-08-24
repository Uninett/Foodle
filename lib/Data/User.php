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
	
	
}
