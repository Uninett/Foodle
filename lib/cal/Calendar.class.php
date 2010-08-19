<?php

/**
 * Calendar representation with support for caching using SQLlite.
 */
class Calendar {

	
	/* Instance of sspmod_core_Storage_SQLPermanentStorage
	 * 
	 * key1		calendar URL
	 * key2		NULL
	 * type		'calendar'
	 *
	 */
	var $store;
	
	var $events;
	
	public function Calendar($url, $cache = FALSE) {
	
		#$cache = FALSE;
		
		if (empty($url))
			throw new Exception('Trying to access an Calendar with specifying an empty URL');
	
		error_log('Calendar init with URL [' . $url . ']');
	
		$this->events = array();
	
		$this->store = new sspmod_core_Storage_SQLPermanentStorage('calendarcache');
	
		$parser = new Parser();
		if ($cache && !$this->store->exists('calendar', $url, NULL)) {
			$parser->process_file($url);
			if (!empty($parser->event_list)) {
				foreach($parser->event_list AS $e) {
					$this->events[] = new Event($e);
				}
			}

			$this->store->set('calendar', $url, NULL, $this->events);
			error_log('Reading calendar Store to cache [' . $url . ']');
		} elseif ($cache === FALSE) {
			$parser->process_file($url);
			if (!empty($parser->event_list)) {
				foreach($parser->event_list AS $e) {
					$this->events[] = new Event($e);
				}
			}
			$this->store->set('calendar', $url, NULL, $this->events, 60*60*15); // Cache time: 15 minutes...
			error_log('Reading calendar force load, and store to cache [' . $url . ']');
		} else {
			$evnts = (array) $this->store->get('calendar', $url, NULL);
			$this->events = $evnts['value'];
			error_log('Reading calendar reading from cache [' . $url . ']');
		}
	}
	
	public function available($begin, $end) {
		$events = $this->getEvents();
		
#		echo '<pre>'; print_r($events); echo '</pre>';
		
		if (!empty($events)) {
			foreach($events AS $event) {
				#echo '<pre>'; print_r($event); exit;
				#echo('<p>Checking interval [' . date('j. M Y H:i', $begin) . '] [' . date('j. M Y H:i', $end) . ']   event start [' . date('j. M Y H:i', $event->getStart()) . ']');
				if ($event->overlap($begin, $end)) return $event;
			}
		}
		return NULL;
	}

	
	public function dump() {
		echo '<p>Calendar dump:</p><pre>';
		print_r($this->parser);
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



}
