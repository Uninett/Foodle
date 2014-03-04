<?php


function timer(&$i, $str = null) {
	$now = microtime(true);
	$diff = $now - $i;

	$diffs = floor($diff * 1000);
	if ($str !== null) {
		error_log(' [TIMER] ' . str_pad($str, 16) . '    ' . str_pad($diffs, 10, ' ', STR_PAD_LEFT) . 'ms');	
	}

	$i = $now;
}


/*
[Thu Feb 13 11:24:23 2014] [error] [client 94.246.37.42]  [TIMER] ------------, referer: https://beta.foodl.org/
[Thu Feb 13 11:24:23 2014] [error] [client 94.246.37.42]  [TIMER] loadCandidates              19ms, referer: https://beta.foodl.org/
[Thu Feb 13 11:24:23 2014] [error] [client 94.246.37.42]  [TIMER] loadDiscussion               3ms, referer: https://beta.foodl.org/
[Thu Feb 13 11:24:23 2014] [error] [client 94.246.37.42]  [TIMER] loadResponses              206ms, referer: https://beta.foodl.org/
[Thu Feb 13 11:24:23 2014] [error] [client 94.246.37.42]  [TIMER] prepareSort                330ms, referer: https://beta.foodl.org/
[Thu Feb 13 11:24:23 2014] [error] [client 94.246.37.42]  [TIMER] sortA                       16ms, referer: https://beta.foodl.org/

*/

/**
 * This class represents an activity stream
 */
class Data_ActivityStream {

	public $activity;
	
	public $loadedFromDB = FALSE;
	
	protected $db, $user;
	
	protected $ids;
	protected $foodleData;
	
	function __construct(FoodleDBConnector $db, Data_User $user) {
		$this->ids = array();
		$this->activity = array();
		$this->db = $db;
		$this->user = $user;
	}
	
	public function prepareUser() {

		$i = microtime(true);

		error_log(' [TIMER] ------------');	
	
		$this->loadCandidates();
		timer($i, 'loadCandidates');

		$this->loadDiscussion();
		timer($i, 'loadDiscussion');

		$this->loadResponses2();
		timer($i, 'loadResponses');

		$this->prepareActivity();
		timer($i, 'prepareActivity');
		
		$this->prepareSort();
		timer($i, 'prepareSort');

		$this->sortA();
		timer($i, 'sortA');
	}
	
	
	public function prepareGroup($groupid) {
		$this->loadCandidatesGroup($groupid);
		$this->loadDiscussion();
		$this->loadResponses();
		
		$this->prepareSort();
		$this->sortA();
	}
	
	
	protected function prepareSort() {
		
		foreach($this->activity AS $key => $a) {
			
			if (isset($a['type']) && $a['type'] === 'response') {
				if (!empty($a['responses'])) {
					foreach($a['responses'] AS $resp) {
						if (empty($this->activity[$key]['unix']) || $this->activity[$key]['unix'] < $resp['modified']) {
							$this->activity[$key]['unix'] = $resp['modified'];
						}
					}
				}

				if (!empty($a['discussion'])) {
					foreach($a['discussion'] AS $resp) {
						if (empty($this->activity[$key]['unix']) || $this->activity[$key]['unix'] < $resp['unix']) {
							$this->activity[$key]['unix'] = $resp['unix'];
						}
					}
				}
				
			}
			
			if (!empty($this->activity[$key]['foodle']['unix'])) {
				if (empty($this->activity[$key]['unix']) || $this->activity[$key]['unix'] < $this->activity[$key]['foodle']['unix']) {
					$this->activity[$key]['unix'] = $this->activity[$key]['foodle']['unix'];
				}
			}
			
			// if (!empty($this->activity[$key]['foodle']['descr'])) {
			// 	$this->activity[$key]['foodle']['summary'] = strip_tags(Data_Foodle::cleanMarkdownInput($this->activity[$key]['foodle']['descr']), '<p>');
			// 	if (strlen($this->activity[$key]['foodle']['summary']) > 160) {
			// 		$this->activity[$key]['foodle']['summary'] = substr($this->activity[$key]['foodle']['summary'], 0, 160) . ' …';
			// 	}
			// }
			
			if (!empty($this->activity[$key]['unix'])) {
				$this->activity[$key]['ago'] = FoodleUtils::date_diff(time() - $this->activity[$key]['unix']);
			}

			
		}
		
	}
	
	protected function sortA() {
		
		function cmp($a, $b) {
			if (empty($a['unix'])) return 1;
			if (empty($b['unix'])) return -1;
			return ($a['unix'] > $b['unix']) ? -1 : 1;
		}
		
		usort($this->activity, 'cmp');
		
		
		$uniqueids = array();

		/*
		 * Limiting the shown entries to the last 20 entries, for performance reasons.
		 */
		$na = array(); $i = 0;
		foreach($this->activity AS $a) {

			if ($i++ > 20) break;
			$na[] = $a;
		}
		
		$this->activity = $na;
	}
	
	public function getData($limit = null) {

		$stream = $this->activity;

		error_log("ACTIVITY STREAM API LIMIT " . $limit);

		if ($limit !== null) {
			return array_slice($stream, 0, $limit);
		}

		return $stream;
	}
	
	/*
	 * Get all discussion entries for the set of candidate Identifiers
	 */
	protected function loadDiscussion() {
		if(empty($this->ids)) return;
		$data = $this->db->getDiscussionEntries($this->ids);
		foreach($data AS $e) {
		
			if (empty($this->foodleData[$e['foodleid']]['discussion'])) {
				$this->foodleData[$e['foodleid']]['discussion'] = array();
			}
			$this->foodleData[$e['foodleid']]['discussion'][] = $e;
		}
	}


	// protected function prepareActivity() {
	// 	if(empty($this->ids)) return;
	// 	$data = $this->db->getResponseEntries($this->ids);
	// 	foreach($data AS $e) {
	// 		if (empty($this->foodleData[$e['foodleid']]['discussion'])) {
	// 			$this->foodleData[$e['foodleid']]['discussion'] = array();
	// 		}
	// 		$this->foodleData[$e['foodleid']]['discussion'][] = $e;
	// 	}
	// }

	protected function loadResponses2() {
		if(empty($this->ids)) return;
		$data = $this->db->getResponseEntries($this->ids);

		// echo '<pre>'; print_r($data); exit;

		foreach($data AS $e) {
		
			if (empty($this->foodleData[$e['foodleid']]['responses'])) {
				$this->foodleData[$e['foodleid']]['responses'] = array();
			}
			$this->foodleData[$e['foodleid']]['responses'][] = $e;
		}
		// echo '<pre>'; print_r($this->foodleData); exit;

	}

	protected function prepareActivity() {
		if(empty($this->ids)) return;
		foreach($this->ids AS $id) {
			$newactivity = array(
				'foodle' => $this->foodleData[$id]
			);
			$this->activity[] = $newactivity;
		}
		// echo '<pre>'; print_r($this->foodleData); exit;
	}



	protected function loadResponses() {
		if(empty($this->ids)) return;
		foreach($this->ids AS $id) {
			
			$resp = $this->db->getRecentResponse($id);
			
			$newactivity = array(
				'type' => 'response',
				'foodle' => $this->foodleData[$id],
				'responses' => $resp,
			);
			
			$this->activity[] = $newactivity;
			
		}
		
	}
	
	
	
	protected function loadCandidatesGroup($groupid) {
	
		$candidates = array();
		
		$nc = $this->db->getGroupEntriesSpecific($groupid);
		foreach($nc AS $c) {
			$candidates[$c['id']] = 1;
			$this->foodleData[$c['id']] = $c;
		}
		
#		echo '<pre>Data: '; print_r($this->foodleData); exit;	
		
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
	



	/*
	 * The result of the loadCandidates, is to fill the $this->ids array with 
	 * all possible foodle { id: name} pairs that is relevant to that user.
	 * Also the $this->foodleData is populated with 
	 * Including :
	 * 	group entries
	 * 	all entries if user is admin
	 * 	foodles that is created by the current user
	 * 	foodles that you have responded to.
	 */
	protected function loadCandidates() {
		$candidates = array();
		
		$nc = $this->db->getGroupEntries($this->user);
		foreach($nc AS $c) {
			$candidates[$c['id']] = 1;
			$this->foodleData[$c['id']] = $c;
		}
		

		
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


		
		$this->ids = array_keys($candidates);
		// echo '<pre>'; print_r($this->ids); exit;

	}
	
	
	


}
