<?php

/**
 * Calendar representation with support for caching using SQLlite.
 */
class Calendar {

#	const CACHETIME = 60*15; // 15 minutes
	const CACHETIME = 15; // 15 seconds
	
	/* Instance of sspmod_core_Storage_SQLPermanentStorage
	 * 
	 * key1		calendar URL
	 * key2		NULL
	 * type		'calendar'
	 *
	 */
	public $store;
	public $events;
	public $freebusy;

	protected $parser;
	
	public function Calendar($url, $cache = FALSE) {
	
		#$cache = FALSE;
		
		if (empty($url))
			throw new Exception('Trying to access an Calendar with specifying an empty URL');
	
		error_log('Calendar init with URL [' . $url . '] ' . ($cache ? 'CACHE' : 'NOCACHE'));
	
		$this->events = array();
		$this->freebusy = array();
		$this->store = new sspmod_core_Storage_SQLPermanentStorage('calendarcache');
	
		$this->parser = new Parser();
		
		if (
			(!$cache)
				||
			($cache && !$this->store->exists('calendar', $url, NULL))
		) {
			/*
			 * Retrieving calendar from HTTP, parse it and store the result to cache.
			 */

			if (!$this->store->exists('calendar', $url, NULL)) {
				error_log('Calendar was NOT found in cache. Refreshing....');
			}

			$this->parser->process_file($url);
			
			if (!empty($this->parser->event_list)) {
				foreach($this->parser->event_list AS $e) {
					$this->events[] = new Event($e);
				}
			}
			if (!empty($this->parser->freebusy_list)) {
				$this->freebusy = $this->parser->freebusy_list[0]->freebusy;
			} 
			$cached = array('events' => $this->events, 'freebusy' => $this->freebusy);
			
			$this->store->set('calendar', $url, NULL, $cached, self::CACHETIME);
			error_log('Storing retrieved calendar to cache [' . $url . ']');

			// echo '<pre>cached:'; print_r($cached); 
			// echo 'freebusy'; print_r($this->freebusy);
			// echo 'events:'; print_r($this->events);
			// echo '</pre>'; 
			// exit;

		} else {
			
			/*
			 * Retrieving calendar from cache.
			 */
			
			$cached = (array) $this->store->get('calendar', $url, NULL);
			$this->events = $cached['value']['events'];
			$this->freebusy = $cached['value']['freebusy'];
			
			error_log('Reading calendar reading from cache [' . $url . ']');
			error_log('From cache: ' . var_export(array_keys($cached['value']), TRUE));
			error_log('Cache expires: ' . date('j. F Y  H:i:s', $cached['expire']));
			
			// echo '<pre>cached:'; print_r($cached); 
			// echo "\n" . 'freebusy:'; print_r($this->freebusy);
			// echo "\n" . 'events:'; print_r($this->events);
			// echo '</pre>'; 
			// exit;
			
		}
		

	}

	public static function parseFreeBusyLine($line) {
		$splp = explode('/', $line);
		$busybegin = self::parseTime($splp[0]);
		$busyend   = self::parseTime($splp[1]);
		return array($busybegin, $busyend);
	}

	
	public function checkFreeBusy2($begin, $end) {
#		error_log('Checking freebusy');
		if (!empty($this->freebusy)) {
			$i = 0;
			foreach($this->freebusy AS $fb) {

				$splp = explode('/', $fb);
				$busybegin = self::parseTime($splp[0]);
				$busyend   = self::parseTime($splp[1]);

#				error_log('Checking BUSY slot [' . date('r', $busybegin) . '] to [' . date('r', $busyend) . ']');

				if (((int)$end > (int)$busybegin) && ((int)$end < (int)$busyend)) return $i;
				if (((int)$begin > (int)$busybegin) && ((int)$begin < (int)$busyend)) return $i;
				if (((int)$begin < (int)$busybegin) && ((int)$end > (int)$busyend)) return $i;
								$i++;
			}
		}
		return NULL;
	}
	
	/*
	 * Check if the user is available in the period (begin, end).
	 * That means that no freebusy overlaps with this interval.
	 * 
	 * Returns TRUE if no overlap was found.
	 */
	public function checkFreeBusy($begin, $end) {
#		error_log('Checking freebusy');
		if (!empty($this->freebusy)) {
			foreach($this->freebusy AS $fb) {
				$splp = explode('/', $fb);
				$busybegin = self::parseTime($splp[0]);
				$busyend   = self::parseTime($splp[1]);
				
#				error_log('Checking BUSY slot [' . date('r', $busybegin) . '] to [' . date('r', $busyend) . ']');

				if (((int)$end > (int)$busybegin) && ((int)$end <= (int)$busyend)) return FALSE;
				if (((int)$begin >= (int)$busybegin) && ((int)$begin < (int)$busyend)) return FALSE;
				if (((int)$begin <= (int)$busybegin) && ((int)$end >= (int)$busyend)) return FALSE;
			}
		}
		return TRUE;
	}
	
	public function available($begin, $end) {
		$events = $this->getEvents();
		
#		echo '<pre>'; print_r($events); echo '</pre>';
		
		$freebusyAvailable = $this->checkFreeBusy($begin, $end);

		// if ($freebusyAvailable !== NULL) {
		// 	error_log('Checking if user is avalable in period [' . date('r', $begin) . '] to [' . date('r', $end) .']   BUSY [' . $freebusyAvailable. ']');			
		// } else {
		// 	error_log('Checking if user is avalable in period [' . date('r', $begin) . '] to [' . date('r', $end) .']   AVAIL');
		// }
		// 	
		// if ($freebusyAvailable) {
		// 	error_log('Checking if user is avalable in period [' . date('r', $begin) . '] to [' . date('r', $end) .']   AVAILABLE');			
		// } else {
		// 	error_log('Checking if user is avalable in period [' . date('r', $begin) . '] to [' . date('r', $end) .']   BUSY');
		// }
		
		if (!$freebusyAvailable) return 'Busy';
		

		
		if (!empty($events)) {
			foreach($events AS $event) {
				#echo '<pre>'; print_r($event); exit;
				#echo('<p>Checking interval [' . date('j. M Y H:i', $begin) . '] [' . date('j. M Y H:i', $end) . ']   event start [' . date('j. M Y H:i', $event->getStart()) . ']');
				if ($event->overlap($begin, $end)) return $event;
			}
		}
		return NULL;
	}

	public static function parseTime($text) {
		// Handle zulu time
		if (substr($text,-1) == 'Z') {
			return strtotime($text);
		}
		
		$splitted = explode(':', $text);
		$key = $splitted[0];
		$value = $splitted[1];

		return strtotime($value);
		
	}
	
	public function dump() {
		echo '<p>Calendar dump:</p><pre>';
		print_r($this->parser);
		echo '</pre>';

		echo '<p>Events dump:</p><pre>';
		print_r($this->events);
		echo '</pre>';

		echo '<p>Freebusy dump:</p><pre>';
		print_r($this->freebusy);
		echo '</pre>';


	}
	
	public function getSlots($begin, $end, $resolution = 900 ) {
		$startslot = floor($begin / $resolution);
		$endslot   = floor($end / $resolution);
		
		$eventslots = array();
		foreach ($this->getEvents() AS $event)
			$eventslots[] = array('event' => $event, 'slots' => $event->slotRange());
		
		$slots = array_fill(0, $endslot - $startslot, null);

		foreach ($this->getEvents() AS $event) {
			$es = $event->slotRange();			
			if ( (min($es[0] + $es[1], $endslot) - max($es[0], $startslot)) > 0) {
				for ($i = max($es[0], $startslot); $i <= min($es[0] + $es[1], $endslot); $i++) {
					$slots[($i-$startslot)][] = $event;
#					echo '<p>adding event to ' . ($i - $startslot) . ' : ' . $event->dump();
				}
			}
		}
		return $slots;	
	}
	
	
	public function getEvents() {
		return $this->events;
	}

	public function getFreeBusy() {
		$res = array();
		foreach($this->freebusy AS $fb) {
			$res[] = self::parseFreeBusyLine($fb);
		}
		return $res;
	}


}
