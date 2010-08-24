<?php

/**
 * This class represents an activity stream
 */
class Data_ActivityStream {

	public $activity;
	
	public $loadedFromDB = FALSE;
	private $db;
	
	function __construct(FoodleDBConnector $db) {
		$this->db = $db;
	}
	
	private function compactEntry($entries) {
		$compact = array(
			'name' => $entries[0]['name'],
			'foodleid' => $entries[0]['foodleid'],
		);
		
		$compact['type'] = $entries[0]['type'];
		
		$users = array();
		$recentUpdate = 0;
		foreach($entries AS $entry) {
			if (!empty($entry['username'])) $users[] = $entry['username'];
			if ($entry['unix'] > $recentUpdate) $recentUpdate = $entry['unix'];
		}
		
		$compact['names'] = join(', ', $users);
		$compact['recent'] = $recentUpdate;
		return $compact;
	}
	
	public function compact() {
		$collapsed = array();
	#	echo '<pre>'; print_r($this->activity); echo '</pre>';
		
		
		foreach($this->activity AS $a) {
			if (isset($a['message'])) {
				$collapsed[$a['foodleid']]['messages'][] = $a;
			} elseif(isset($a['response'])) {
				$collapsed[$a['foodleid']]['responses'][] = $a;
			}
		}
		
		$compactlist = array();
		foreach($collapsed AS $cfoodle) {
			if (!empty($cfoodle['responses'])) $compactlist[] = $this->compactEntry($cfoodle['responses']);
			if (!empty($cfoodle['messages'])) $compactlist[] = $this->compactEntry($cfoodle['messages']);
		}
		
	#	echo '<pre>compact:'; print_r($compactlist); echo '</pre>';
		
		function cmp($a, $b){
		    if ($a['recent'] == $b['recent']) {
		        return 0;
		    }
		    return ($a['recent'] > $b['recent']) ? -1 : 1;
		}
		usort($compactlist, "cmp");
		
		return $compactlist;
	}


}
