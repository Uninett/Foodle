<?php

/**
 * This class represents a response to a Foodle
 *
mysql> show columns from entries;
+----------+--------------+------+-----+-------------------+----------------+
| Field    | Type         | Null | Key | Default           | Extra          |
+----------+--------------+------+-----+-------------------+----------------+
| id       | int(11)      | NO   | PRI | NULL              | auto_increment | 
| foodleid | varchar(100) | NO   |     |                   |                | 
| userid   | tinytext     | YES  |     | NULL              |                | 
| username | tinytext     | YES  |     | NULL              |                | 
| response | tinytext     | YES  |     | NULL              |                | 
| created  | timestamp    | NO   |     | CURRENT_TIMESTAMP |                | 
| updated  | timestamp    | YES  |     | NULL              |                | 
| notes    | text         | YES  |     | NULL              |                | 
| email    | text         | YES  |     | NULL              |                | 
+----------+--------------+------+-----+-------------------+----------------+
9 rows in set (0.00 sec)
 *
 */
class FoodleResponse {

	// Foodle identifier
	public $foodle;
	
	public $userid;
	public $username;
	public $email;
	public $response;
	public $notes;
	
	public $created;
	public $updated;

	public $loadedFromDB = FALSE;
	
	private $db;
	
	function __construct(FoodleDBConnector $db, Foodle $foodle) {
		$this->db = $db;
		$this->foodle = $foodle;
	}
	
	public function updateFromical(User $user, $cache = TRUE) {

		if (!$user->hasCalendar()) throw new Exception('User has no calendar information');
				
		$this->userid = $user->userid;
		$this->username = $user->name;
		$this->email = $user->email;
		$this->notes = $_REQUEST['comment'];
		#$this->updated = 'now';
		$this->response = array(
			'type' => 'ical',
			'data' => $responseData,
			'crash' => $crashingEvents,
			'calendarURL' => $user->calendarURL,
		);
		
		$this->icalfill($cache);

	}
	
	
	/**
	 * Fill inn all columns from calendar URL (if present)
	 */
	public function icalfill($cache = TRUE) {
		
		#echo '<pre>icalfill on :'; print_r($this->response); echo('</pre>');# exit;
		
		if (!array_key_exists('calendarURL', $this->response)) return;
		
		$responseData = array_fill(0, $this->foodle->getNofColumns(), '1');
		$crashingEvents = array_fill(0, $this->foodle->getNofColumns(), NULL);
		
		$cal = new Calendar($this->response['calendarURL'], TRUE);
		$slots = $this->foodle->getColumnDates();
		foreach($slots AS $i => $slot) {
			$crash = $cal->available($slot, $slot + 3600 );
			if ($crash !== NULL) {
				$responseData[(int)$i] = '0';
				$crashingEvents[(int)$i] = $crash->showShort();
			}
		}
		$this->response['data'] = $responseData;
		$this->response['crash'] = $crashingEvents;
		
		#echo 'ical fill completed. '; 
	}
	
	public function asJSON() {
		$data = $this->response;
		#unset($data['data']);
		unset($data['crash']);
		return json_encode($data);
	}
	
	public function updateFromPost(User $user) {
		$responseData = array_fill(0, $this->foodle->getNofColumns(), '0');
		
		if (!empty($_REQUEST['myresponse'])) {
			foreach ($_REQUEST['myresponse'] AS $yes) {
				$responseData[(int)$yes] = '1';
			}
		}		
		$this->userid = $user->userid;
		$this->username = $user->name;
		$this->email = $user->email;
		$this->notes = $_REQUEST['comment'];
		#$this->updated = 'now';
		$this->response = array(
			'type' => 'manual',
			'data' => $responseData,
		);

	}
	
	public function save() {
		$this->db->saveFoodleResponse($this);
	}
	
	public function getAgo() {
		if (!empty($this->updated)) return FoodleUtils::date_diff(time() - $this->updated);
		if (!empty($this->created)) return FoodleUtils::date_diff(time() - $this->created);		
		return NULL;
	}
	
}

?>