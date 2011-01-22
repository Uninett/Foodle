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
class Data_FoodleResponse {

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
	
	// this is set to TRUE if the number of columns does not match...
	public $invalid = FALSE;
	
	private $db;
	
	function __construct(FoodleDBConnector $db, Data_Foodle $foodle) {
		$this->db = $db;
		$this->foodle = $foodle;
	}
	
	public function updateFromical(Data_User $user, $cache = TRUE) {

		if (!$user->hasCalendar()) throw new Exception('User has no calendar information');
				
		$this->userid = $user->userid;
		$this->username = $user->name;
		$this->email = $user->email;
		$this->notes = $_REQUEST['comment'];
		#$this->updated = 'now';
		$this->response = array(
			'type' => 'ical',
			'data' => $responseData,
#			'crash' => $crashingEvents,
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
			$crash = $cal->available($slot[0], $slot[1]);
			if ($crash !== NULL) {
				if (is_a($crash, 'Event')) {
					$responseData[(int)$i] = '0';
					$crashingEvents[(int)$i] = $crash->showShort();					
				} elseif(is_string($crash)) {
					$responseData[(int)$i] = '0';
					$crashingEvents[(int)$i] = $crash;
				}
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
	
	public static function parsePostN($n) {
		if(preg_match('/([0-9]+)-([0-9]+)/', $n, $matches)) {
			return array('key' => $matches[1], 'value' => $matches[2]);
		} else {
			return array('key' => $n, 'value' => 1);
		}
	}
	
	public function updateFromPost(Data_User $user) {
		$responseData = array_fill(0, $this->foodle->getNofColumns(), '0');
		
		if (!empty($_REQUEST['myresponse'])) {
			foreach ($_REQUEST['myresponse'] AS $yes) {
				$pn = self::parsePostN($yes);
				$responseData[$pn['key']] = $pn['value'];
			}
		}		
		$this->userid = $user->userid;
		$this->username = $user->name;
		if (empty($this->username) && isset($_REQUEST['username']) ) {
			$this->username = FoodleUtils::cleanUsername($_REQUEST['username']);
		}
		
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