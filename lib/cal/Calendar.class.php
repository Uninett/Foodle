<?php

/**
 * Calendar representation with support for caching using SQLlite.
 */
class Calendar {

	const CACHETIME = 900; // 15 minutes
	
	protected $parser, $url;
	
	protected $calID;

	protected $freebusy;


	protected static function cacheGET($calID) {
		return json_decode(SimpleSAML_Memcache::get('calendar-' . $calID), TRUE);
	}
	
	protected static function cacheSET($calID, $freebusy) {
		return SimpleSAML_Memcache::set('calendar-' . $calID, json_encode($freebusy) );
	}
		
	
	public function Calendar($url) {

		if (empty($url))
			throw new Exception('Trying to access an Calendar with specifying an empty URL');

		$this->url = $url;	
		$this->calID = sha1($url);
	
		//error_log('Calendar init with URL [' . $url . '] ');
	
		$this->freebusy = array();
		
//		$this->store = new sspmod_core_Storage_SQLPermanentStorage('calendarcache');
		

		$this->parser = new Parser();
		
		/*
		 * Retrieving calendar from cache.
		 */
		
		$cached = self::cacheGET($this->calID);
		
		
		if(empty($cached)) {
//			error_log('Reading calendar from cache. NOT FOUND.');
		} else {
//			error_log('Reading calendar from cache. FOUND ');
			$this->freebusy = $cached;
			
			// error_log('Reading calendar from cache. Found ' . count($this->freebusy) . ' entries');
			
//			print_r($this->freebusy);	
		}
		

		
// 		error_log('Reading calendar reading from cache [' . $url . ']');
// 		error_log('From cache: ' . var_export(array_keys($cached['value']), TRUE));
// 		error_log('Cache expires: ' . date('j. F Y  H:i:s', $cached['expire']));
		
		// echo '<pre>cached:'; print_r($cached); 
		// echo "\n" . 'freebusy:'; print_r($this->freebusy);
		// echo "\n" . 'events:'; print_r($this->events);
		// echo '</pre>'; 
		// exit;
		
	}
	
	

	
	
	public function updateCache() {
	
		$this->parser->process_file($this->url);
		

		$this->processFreeBusyObjects();

//		print_r($this->freebusy);	
		
		$this->cacheSET($this->calID, $this->freebusy);
		
		//error_log('Storing retrieved calendar to cache');
		
		
	}
	
	
	
	private function processFreeBusyObjects() {
		

		$this->freebusy = array();


		/*
		 * If the iCalendar file contains some VFreeBusy elements, then process them.
		 */
		if (!empty($this->parser->freebusy_list) && is_array($this->parser->freebusy_list)) {

			//error_log('Processing free busy list');
			
			foreach($this->parser->freebusy_list AS $fbobj) {
				if (!$fbobj instanceof Vfreebusy) continue;
				
				if (isset($fbobj->freebusy)) {
					foreach($fbobj->freebusy AS $fb) {
						$this->freebusy[] = self::parseFreeBusyLine($fb);
						#echo 'POOOT';
					}
				} else {
					
					$start = self::parseTime($fbobj->dtstart);
					$end   = self::parseTime($fbobj->dtend);
					
					$this->freebusy[] = array($start, $end, 'BUSY');
				}
			}
		}
		
		
		

		/*
		 * If the iCalendar file contains some regular events, then process them.
		 */
		if (!empty($this->parser->event_list) && is_array($this->parser->event_list) ) {
		
			//error_log('Processing event list');
			
			foreach($this->parser->event_list AS $e) {
				$event = new Event($e);
				
				$tempfreebusyentry = array(
					$event->getStart(),
					$event->getEnd(),
					'BUSY'
				);
				//error_log('New entry: ');
				//print_r($tempfreebusyentry);
				$this->freebusy[] = $tempfreebusyentry;
				
			}
		}
		
		

	}
	

	public static function parseFreeBusyLine($line) {
		#echo 'parseFreeBusyLine='; echo '<pre>'; print_r( $line); echo '</pre>';
		$splp = explode('/', $line['data']);
		$busybegin = self::parseTime($splp[0]);
		$busyend   = self::parseTime($splp[1]);
		return array($busybegin, $busyend, (isset($line['FBTYPE']) ? $line['FBTYPE'] : 'BUSY'));
	}

	
	
	
	public static function updateAvailability($pre, $type) {
		//echo "\n" . '[updateAvailability] ' . $pre . ' ' . var_export($type, TRUE);
		if ($pre === 'BUSY') return $pre;
		if ($type === 'BUSY') return $type;
		if ($type === 'BUSY-TENTATIVE') return $type;
		return 'FREE';
	}
	
	/*
	 * Check if the user is available in the period (begin, end).
	 * That means that no freebusy overlaps with this interval.
	 * 
	 * Returns TRUE if no overlap was found.
	 */
	public function checkFreeBusy($begin, $end) {
#		error_log('Checking freebusy');
		
		$result = 'FREE';



		if (!empty($this->freebusy)) {
			foreach($this->freebusy AS $fb) {
//				echo '#';
				#$splp = explode('/', $fb);
				$busybegin = $fb[0];
#				self::parseTime($splp[0]);
				$busyend   = $fb[1];
#				self::parseTime($splp[1]);
				
//				error_log('Checking BUSY slot [' . date('r', $busybegin) . '] to [' . date('r', $busyend) . ']');

				#echo '<pre>ENTRY:'; print_r($fb); echo '</pre>';
				
				//echo $fb[2];
				
				$match = false;

				if (((int)$end > (int)$busybegin) && ((int)$end <= (int)$busyend)) $match = true;
				if (((int)$begin >= (int)$busybegin) && ((int)$begin < (int)$busyend)) $match = true;
				if (((int)$begin <= (int)$busybegin) && ((int)$end >= (int)$busyend)) $match = true;
				
				if($match) {
					$result = self::updateAvailability($result, $fb[2]);
					
					// error_log('Found ' . $fb[2] . ' match [' . date('r', $busybegin) . '] to [' . date('r', $busyend) . ']');

				}
			}
		}
		return $result;
	}
	
	public function available($begin, $end) {

		return $this->checkFreeBusy($begin, $end);
	}

	public static function parseTime($text) {
		// Handle zulu time
		// if (substr($text,-1) == 'Z') {
		// 	return strtotime($text);
		// }
		
		if (preg_match('/^(.*?:)(.*)$/', $text, $matches)) {
			$text = $matches[2];
		}
		
		// $splitted = explode(':', $text);
		// $key = $splitted[0];
		// $value = $splitted[1];

		return strtotime($text);
		
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
		
// 		$eventslots = array();
// 		foreach ($this->getEvents() AS $event)
// 			$eventslots[] = array('event' => $event, 'slots' => $event->slotRange());
		
		$slots = array_fill(0, $endslot - $startslot, null);

// 		foreach ($this->getEvents() AS $event) {
// 			$es = $event->slotRange();			
// 			if ( (min($es[0] + $es[1], $endslot) - max($es[0], $startslot)) > 0) {
// 				for ($i = max($es[0], $startslot); $i <= min($es[0] + $es[1], $endslot); $i++) {
// 					$slots[($i-$startslot)][] = $event;
// #					echo '<p>adding event to ' . ($i - $startslot) . ' : ' . $event->dump();
// 				}
// 			}
// 		}
		return $slots;	
	}
	
// 	
// 	public function getEvents() {
// 		return $this->events;
// 	}

	public function getFreeBusy() {
		return $this->freebusy;
	}


}
