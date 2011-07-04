<?php

class API_ProfileCalendars extends API_Authenticated {

	protected $contacts, $list;
	protected $parameters;

	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
	}
	
	public static function isURL($url) {
		$pattern = '/^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w][-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&amp;?([-+_~.\d\w]|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/';
		return preg_match($pattern, $url);
	}
	
	protected function addCalendar($url) {
		if (!self::isURL($url)) throw new Exception('Invalid URL provided.');
		$this->user->addCalendarURL($url);
		$this->fdb->saveUser($this->user);
	}
	
	protected function removeCalendar($url) {
//		if (!self::isURL($url)) throw new Exception('Invalid URL provided.');
		$this->user->removeCalendarURL($url);
		$this->fdb->saveUser($this->user);
	}

	protected function switchCalendar($url) {
//		if (!self::isURL($url)) throw new Exception('Invalid URL provided.');
		$this->user->switchCalendarURL($url);
		$this->fdb->saveUser($this->user);
	}
	
	function prepare() {
		parent::prepare();
		
		if (!empty($_REQUEST['newcalendar'])) {
			$this->addCalendar($_REQUEST['newcalendar']);
		}

		if (!empty($_REQUEST['removecalendar'])) {
			$this->removeCalendar($_REQUEST['removecalendar']);
		}
		if (!empty($_REQUEST['switchcalendar'])) {
			$this->switchCalendar($_REQUEST['switchcalendar']);
		}
		
		return array_values($this->user->getCalendar());
	}


	
}


