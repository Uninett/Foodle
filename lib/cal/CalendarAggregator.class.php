<?php

/**
 * Calendar representation with support for caching using SQLlite.
 */
class CalendarAggregator {

	protected $calendars;

	public function __construct() {

		$this->calendars = array();
	}
	
	public function addCalendar($url) {
		// error_log('adding url: ' . $url);
		$this->calendars[] = new Calendar($url);
	}

	public static function updateAvailability($pre, $type) {
		#echo '[updateAvailability] ' . $pre . ' ' . var_export($type, TRUE);
		if ($pre === 'BUSY') return $pre;
		if ($type === 'BUSY') return $type;
		if ($type === 'BUSY-TENTATIVE') return $type;
		return 'FREE';
	}
	

	public function available($begin, $end) {
	
		$resultCode = 'FREE';
		
		$result = array(
			'crash' => null,
			'available' => 'FREE'
		);
		
		if (empty($this->calendars)) return $result;
		
// 		echo 'about to rock';
// 		print_r(array_keys($this->calendars));
		
		foreach($this->calendars AS $c) {
			$resultCode = self::updateAvailability($resultCode, $c->available($begin, $end) );
//			error_log('Result code ' . $resultCode);
		}
		
		if ($result['available'] !== 'FREE') $result['crash'] = 'Busy';
		$result['available'] = $resultCode;
		return $result;
		
	}
	
	public function getFreeBusy() {
		$fb = array();
		foreach($this->calendars AS $c) {
			$fb[] = $c->getFreeBusy();
		}
		return $fb;
	}
	
	public function testSomeDates() {
		
		$check = array(
			array('2011-06-13 10:00', '2011-06-13 12:00'),
			array('2011-07-10 10:00', '2011-07-10 12:00'),
			array('2011-07-11 10:00', '2011-07-11 12:00'),
			array('2011-07-12 10:00', '2011-07-12 12:00'),

		);
		foreach($check AS $c) {
			
			echo 'Check from [' . $c[0] . '] to [' . $c[1] . ']: ';
			echo var_export($this->available(strtotime($c[0]), strtotime($c[1])), TRUE) . "\n";
		}
		
		
	}

}
