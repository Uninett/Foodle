<?php

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
		$this->loadCandidates();
		$this->loadDiscussion();
		$this->loadResponses();
		
		$this->prepareSort();
		$this->sortA();
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
			
			if ($a['type'] === 'response') {
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
			
			if (!empty($this->activity[$key]['foodle']['descr'])) {
				$this->activity[$key]['foodle']['summary'] = strip_tags(Data_Foodle::cleanMarkdownInput($this->activity[$key]['foodle']['descr']), '<p>');
				if (strlen($this->activity[$key]['foodle']['summary']) > 160) {
					$this->activity[$key]['foodle']['summary'] = substr($this->activity[$key]['foodle']['summary'], 0, 160) . ' …';
				}
			}
			
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
		
	}
	
	public function getData() {
		return $this->activity;
	}
	
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
#		echo '<pre>'; print_r($this->ids); exit;		

	}
	
	
	


}
