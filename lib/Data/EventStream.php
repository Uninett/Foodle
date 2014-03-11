<?php

/**
 * This class represents an activity stream
 */
class Data_EventStream {

	public $activity;
	
	public $loadedFromDB = FALSE;
	
	protected $db, $user;
	
	protected $ids;
	protected $foodleData;
	
	protected $includeExpires;
	
	private $timezone;
	
	function __construct(FoodleDBConnector $db, Data_User $user, $includeExpires = true) {

		$this->ids = array();
		$this->activity = array();
		$this->db = $db;
		$this->user = $user;
		
		$this->includeExpires = $includeExpires;
		
		$this->timezone = new TimeZone(NULL, $this->user);
	}
	
	public function prepareUser() {
		$this->loadCandidates();
		$this->loadFoodles();
		$this->sortA();
	}
	
	
	public function prepareGroup($groupid) {
		$this->loadCandidatesGroup($groupid);
		$this->loadFoodles();
		$this->sortA();
	}
	
	public function loadFoodles() {
		if(empty($this->ids)) return;
		
		$now = time();
		$past = 3600 * 5;
		

		
		foreach($this->ids AS $id) {
			
		//	echo '<p>' . $id;
			
			$foodle = $this->db->readFoodle($id);
			$timezone = $this->timezone->getTimezone();

			// header('Content-type: text/plain; charset=utf-8');
			// echo 'Timezone is '; print_r($timezone); echo "\n";
			
			
			if ($this->includeExpires && !empty($foodle->expire) && $foodle->expire > $now) {
				$newevent = array(
					'type' => 'expire',
					'foodle' => $this->foodleData[$id],
					'unix' => $foodle->expire,
					'expiretext' => $foodle->getExpireTextShort(),
				);
				$this->activity[] = $newevent;
			}
			
			
			
			if (!empty($foodle->datetime)) {
				$unix = $foodle->datetimeEpoch();
				// $texttime = $foodle->toTimeZone($unix, $timezone)->format('D j. M H:i');
				
				if ($unix > ($now - $past)) {
					$newevent = array(
						'type' => 'event',
						'foodle' => $this->foodleData[$id],
						'unix' => $unix,
						// 'unixt' => $texttime,
						'created' => $foodle->getCreatedStamp(),
						'dtstart' => $foodle->dtstart(),
						'dtend' => $foodle->dtend(),
					);
					$this->activity[] = $newevent;
					
					//echo '<pre>'; print_r($newevent); echo '</pre>'; 
					
				} else {

				}
				
			} else if ($foodle->calendarEnabled()) {

				$dcolumns = $foodle->getColumnDates();
				
				$i = 0;
				foreach($dcolumns AS $dcolumn) {

					if (empty($dcolumn[0])) continue;

					$unix = $dcolumn[0];
					$unixTo = $dcolumn[1];
					// $texttime = $foodle->toTimeZone($unix, $timezone)->format('D j. M H:i');
					
					if ($unix > ($now - $past)) {
						
						$newevent = array(
							'subid' => $i++,
							'type' => 'tentative',
							'foodle' => $this->foodleData[$id],
							'unix' => $unix,
							// 'unixt' => $texttime,
							'dtstart' => 'DTSTART:' . gmdate('Ymd\THis\Z', $unix),
							'dtend' => 'DTEND:' . gmdate('Ymd\THis\Z', $unixTo),
							'created' => $foodle->getCreatedStamp(),
						);
						$this->activity[] = $newevent;
						
					}
					
					//echo '<p>Date: ' . $texttime->format('D j. M H:i') . ' @' . $dcolumn[0];
					
					
				}
				

			}

		}
		
	}
	
	
	// protected function prepareSort() {
	// 	
	// 	foreach($this->activity AS $key => $a) {
	// 		
	// 		if ($a['type'] === 'response') {
	// 			if (!empty($a['responses'])) {
	// 				foreach($a['responses'] AS $resp) {
	// 					if (empty($this->activity[$key]['unix']) || $this->activity[$key]['unix'] < $resp['modified']) {
	// 						$this->activity[$key]['unix'] = $resp['modified'];
	// 					}
	// 				}
	// 			}
	// 
	// 			if (!empty($a['discussion'])) {
	// 				foreach($a['discussion'] AS $resp) {
	// 					if (empty($this->activity[$key]['unix']) || $this->activity[$key]['unix'] < $resp['unix']) {
	// 						$this->activity[$key]['unix'] = $resp['unix'];
	// 					}
	// 				}
	// 			}
	// 			
	// 		}
	// 		
	// 		if (!empty($this->activity[$key]['foodle']['unix'])) {
	// 			if (empty($this->activity[$key]['unix']) || $this->activity[$key]['unix'] < $this->activity[$key]['foodle']['unix']) {
	// 				$this->activity[$key]['unix'] = $this->activity[$key]['foodle']['unix'];
	// 			}
	// 		}
	// 		
	// 		if (!empty($this->activity[$key]['foodle']['descr'])) {
	// 			$this->activity[$key]['foodle']['summary'] = strip_tags(Data_Foodle::cleanMarkdownInput($this->activity[$key]['foodle']['descr']), '<p>');
	// 			if (strlen($this->activity[$key]['foodle']['summary']) > 160) {
	// 				$this->activity[$key]['foodle']['summary'] = substr($this->activity[$key]['foodle']['summary'], 0, 160) . ' …';
	// 			}
	// 		}
	// 		
	// 		if (!empty($this->activity[$key]['unix'])) {
	// 			$this->activity[$key]['ago'] = FoodleUtils::date_diff(time() - $this->activity[$key]['unix']);
	// 		}
	// 
	// 		
	// 	}
	// 	
	// }
	
	protected function sortA() {
		
		function cmp($a, $b) {
			if (empty($a['unix'])) return -1;
			if (empty($b['unix'])) return 1;
			return ($a['unix'] > $b['unix']) ? 1 : -1;
		}
		
		usort($this->activity, 'cmp');
		
		$uniqueids = array();
		
		/*
		 * Limiting the shown entries to the last 20 entries, for performance reasons.
		 */
		$na = array(); $i = 0;
		foreach($this->activity AS $a) {
			$id = $a['foodle']['id'];
			// print_r(); exit;
			if (isset($uniqueids[$id])) {
				continue;
			}
			$uniqueids[$id] = 1;
			if ($i++ > 20) break;
			$na[] = $a;
		}
		
		$this->activity = $na;
	}
	
	public function getData($limit = null) {

		$stream = $this->activity;

		error_log("ACTIVITY STREAM API LIMIT " . $limit);

		if ($limit !== null && $limit > 0) {
			return array_slice($stream, 0, $limit);
		}

		return $stream;
	}
	
	
	
	
	protected function loadCandidatesGroup($groupid) {
	
		$candidates = array();
		
		$nc = $this->db->getGroupEntriesSpecific($groupid);
		foreach($nc AS $c) {
			$candidates[$c['id']] = 1;
			$this->foodleData[$c['id']] = $c;
		}




		
		
//		echo '<pre>Data: '; print_r($this->foodleData); exit;	
		
		$nc = $this->db->getOwnerEntries($this->user);
		foreach($nc AS $c) {
			$c['youcreated'] = true;
			if (!empty($this->foodleData[$c['id']])) {
				$this->foodleData[$c['id']] = array_merge($this->foodleData[$c['id']], $c);
			} 
		}


		$nc = $this->db->getYourEntries($this->user);
		foreach($nc AS $c) {

			$c['youresponded'] = true;
			if (!empty($this->foodleData[$c['id']])) {
				$this->foodleData[$c['id']] = array_merge($this->foodleData[$c['id']], $c);
			} 
		}
		
#		echo '<pre>Data: '; print_r($this->foodleData); exit;	
		
		$this->ids = array_keys($candidates);

	}
	
	
	protected function loadCandidates() {
		$candidates = array();
		

		// echo "about to load candidates"; print_r($this->user); exit;

		header("Content-type: text/plain; charset=utf-8");


		// $nc = $this->db->getGroupEntries($this->user);
		// foreach($nc AS $c) {
		// 	$candidates[$c['id']] = 1;
		// 	$this->foodleData[$c['id']] = $c;
		// }
		
		// echo "Group entries";
		// print_r($nc);


		
		if ($this->user->isAdmin()) {

			$nc = $this->db->getAllEntries();
			foreach($nc AS $c) {
				$candidates[$c['id']] = 1;
				if (!empty($this->foodleData[$c['id']])) {
					$this->foodleData[$c['id']] = array_merge($this->foodleData[$c['id']], $c);
				} else {
					$this->foodleData[$c['id']] = $c;
				}
			}
		}


		foreach($this->user->getFeeds() AS $feed) {
			// $this->loadFeed($feed['id']);
			$nc = $this->db->getFeedEntries($feed['id']);
			foreach($nc AS $c) {
				$candidates[$c['id']] = 1;
				$c['feed'] = $feed['id'];

				if (!empty($this->foodleData[$c['id']])) {
					$this->foodleData[$c['id']] = array_merge($this->foodleData[$c['id']], $c);
				} else {
					$this->foodleData[$c['id']] = $c;
				}
			}
		}

		
		
		$nc = $this->db->getOwnerEntries($this->user);
		foreach($nc AS $c) {
			$candidates[$c['id']] = 1;
			$c['youcreated'] = true;
			if (!empty($this->foodleData[$c['id']])) {
				$this->foodleData[$c['id']] = array_merge($this->foodleData[$c['id']], $c);
			} else {
				$this->foodleData[$c['id']] = $c;
			}
		}


		$nc = $this->db->getYourEntries($this->user);
		foreach($nc AS $c) {
			$candidates[$c['id']] = 1;
			
#			print_r($c); exit;

			if ($c['invitation'] == 1) {
				$c['invited'] = true;		
			} else {
				$c['youresponded'] = true;		
			}
			
			if (!empty($this->foodleData[$c['id']])) {
				$this->foodleData[$c['id']] = array_merge($this->foodleData[$c['id']], $c);
			} else {
				$this->foodleData[$c['id']] = $c;
			}
		}

		// echo "Group entries";
		// print_r($this->foodleData); exit;


		
		$this->ids = array_keys($candidates);
#		echo '<pre>'; print_r($this->ids); exit;		

	}
	
	
	


}
