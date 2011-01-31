<?php

/**
 * This class represents a Foodle
 *
mysql> show columns from def;
+----------+--------------+------+-----+-------------------+-------+
| Field    | Type         | Null | Key | Default           | Extra |
+----------+--------------+------+-----+-------------------+-------+
| id       | varchar(100) | NO   | PRI | NULL              |       |
| name     | tinytext     | YES  |     | NULL              |       |
| descr    | text         | YES  |     | NULL              |       |
| columns  | text         | YES  |     | NULL              |       |
| owner    | text         | YES  |     | NULL              |       |
| created  | timestamp    | NO   |     | CURRENT_TIMESTAMP |       |
| updated  | timestamp    | YES  |     | NULL              |       |
| expire   | datetime     | YES  |     | NULL              |       |
| maxdef   | text         | YES  |     | NULL              |       |
| anon     | tinytext     | YES  |     | NULL              |       |
| timezone | text         | YES  |     | NULL              |       |
+----------+--------------+------+-----+-------------------+-------+
11 rows in set (0.00 sec)

mysql> show columns from discussion;
+----------+--------------+------+-----+-------------------+----------------+
| Field    | Type         | Null | Key | Default           | Extra          |
+----------+--------------+------+-----+-------------------+----------------+
| id       | int(11)      | NO   | PRI | NULL              | auto_increment | 
| foodleid | varchar(100) | NO   |     |                   |                | 
| username | tinytext     | YES  |     | NULL              |                | 
| message  | text         | YES  |     | NULL              |                | 
| created  | timestamp    | NO   |     | CURRENT_TIMESTAMP |                | 
+----------+--------------+------+-----+-------------------+----------------+
5 rows in set (0.00 sec)
 *
 */
class Data_Foodle {

	public $identifier;
	public $name;
	public $descr;
	
	public $columntype;
	public $responsetype = 'default';
	
	public $columns;
	
	public $maxentries;
	public $maxcolumn;
	
	public $expire;
	public $owner;
	public $allowanonymous = FALSE;
	
	public $extrafields;

	public $timezone = NULL;
	
	public $loadedFromDB = FALSE;
	
	protected $datecache = NULL;
	
	private $db;
	private $responses = NULL;
	private $discussion = NULL;
	
	function __construct(FoodleDBConnector $db) {
		$this->db = $db;
	}
	
	public static function debugfield($text, $value) {
		$text = '<dt>' . $text . '</dt><dd><tt>' . var_export($value, TRUE) . '</tt></dd>';
		return $text;
	}
	
	public function getDescription() {
		return self::cleanMarkdownInput($this->descr);
	}
	
	public static function cleanMarkdownInput($input) {
		$input = Markdown($input);
		$input = strip_tags($input, '<h1><h2><h3><h4><h5><h6><p><a><strong><em><ul><ol><li><dd><dt><dl><hr><img><pre><code>');
		return $input;
	}
	
	public function getExtraFields() {
		if (empty($this->extrafields)) return array();
		return $this->extrafields;
	}
	
	public function debug() {
		$text = '<dl>' .
#			self::debugfield('Identifier', $this->userid) . 
			self::debugfield('Name', $this->name) . 
			self::debugfield('Description', $this->descr) . 
			self::debugfield('Columns', $this->columns) . 
			self::debugfield('Max entries', $this->maxentries) . 
			self::debugfield('Max column', $this->maxcolumn) . 
			self::debugfield('Expire', $this->expire) . 
			self::debugfield('Owner', $this->owner) . 			
			self::debugfield('Allow anonymous', $this->allowanonymous) . 
			self::debugfield('Timezone', $this->timezone) . 
			'</dl>'
			;
		if ($this->calendarEnabled()) {
			$dates = $this->getColumnDates();
			$text .= '<p>This Foodle is calendar-enabled:</p><ul>';
			foreach($dates AS $d) {
				$text .= '<li>From ' . date('r', $d[0]). ' to ' . date('r', $d[1]). '.</li>';
			}
			$text .= '</ul>';

		} else {
			$text .= '<p>This Foodle is not calendar-enabled. That means not all column text is reckognized as text.</p>';
		}
		
		return $text;
	}
	
	public function updateResponses(Data_FoodleResponse $response) {
		$this->responses[$response->userid] = $response;
	}
	
	// Return all responses to this foodle. This function caches.
	public function getResponses() {
		if ($this->responses === NULL) $this->responses = $this->db->readResponses($this);

		if (isset($_REQUEST['debug'])) {
			echo '<pre>'; print_r($this->responses); exit;
		}

		foreach($this->responses AS $resp) {
			#$resp2 = $resp; unset($resp2->foodle); echo '<pre>NEW RESPONSE'; print_r($resp2); echo '</pre>'; 
			$resp->icalfill();
			#exit;
		}
		
		return $this->responses;
	}
	
	public function timezoneEnabled() {
		if (empty($this->timezone)) return FALSE;
		if ($this->getColumnDepth() < 2) return FALSE;
		if (!$this->onlyDateColumns()) return FALSE;
		return TRUE;
	}
	
	public function toTimezone($time, $timezone) {
		$d =  new DateTime('@' . $time, new DateTimeZone($this->timezone));
		$d->setTimeZone(new DateTimeZone($timezone));
		return $d;
	}
	
	private function presentTimeZonePlanner($timezone) {
		
		$slots = $this->getColumnDates();

		$this->columns = array();
		
		foreach($slots AS $slot) {
			$newDate = array('title' => $this->toTimezone($slot[0], $timezone)->format('H:i') );			
			$this->columns[] = $newDate;
		}
		
	}
	
	public function presentDatesOnly() {
		
		$slots = $this->getColumnDates();

		$this->columns = array();
		
		foreach($slots AS $slot) {
		
#			echo '<pre>FOOOO'; print_r($slot);
			$newDate = array('title' => strftime('%a %e. %b', $slot[0]) );
			$this->columns[] = $newDate;
		}
		
	}
	
	public function presentInTimeZone($timezone) {
	
		if (isset($this->columntype) && $this->columntype === 'timezone') {
			$this->presentTimeZonePlanner($timezone); 
			return;
		}
	
		$dates = $this->getColumnDates();
		
		$sortedByDate = array();
		foreach($dates AS $date) {
			$sortedByDate[$this->toTimezone($date[0], $timezone)->format('Y-m-d')][] = $date;
		}
		
		$this->columns = array();
		foreach($sortedByDate AS $dates) {
#			print_r($dates[0][0]); exit;
#			echo 'convert: ' . $this->toTimezone($dates[0][0], $timezone)->format('D j. M');
			
			$newDate = array('title' => $this->toTimezone($dates[0][0], $timezone)->format('D j. M') );
			
			#print_r($newDate);
			$children = array();
			foreach($dates AS $date) {
				$children[] = array(
					'title' => $this->toTimezone($date[0], $timezone)->format('H:i') . '-' . 
						$this->toTimezone($date[1], $timezone)->format('H:i')
				);
			}
			$newDate['children'] = $children;
			$this->columns[] = $newDate;
		}
		
// echo '<pre>Present in [' . $timezone.  ']: '; print_r($sortedByDate); 
// echo 'Present in [' . $timezone.  ']: '; print_r($this->columns); 
// 
// exit;
	}
	
	public function datesOnly() {
		foreach($this->columns AS $col) {
			if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $col['title'])) return FALSE;
			if (isset($col['children'])) return FALSE;
		}
		return TRUE;
	}
	
	
	public function onlyDateColumns() {
		foreach($this->columns AS $col) {
			if (!preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/', $col['title'])) return FALSE;
			if (isset($col['children'])) {
				foreach($col['children'] AS $option) {
					if (!preg_match('/^[0-9]{1,2}([:.][0-9]{2})?(-[0-9]{1,2}([:.][0-9]{2})?)?$/', $option['title'])) return FALSE;
				}
			}
		}
		return TRUE;
	}
	
	// Return all responses to this foodle. This function caches.
	public function getDiscussion() {
		if ($this->discussion === NULL) $this->discussion = $this->db->readDiscussion($this);
		return $this->discussion;
	}
	
	public function getDefaultResponse(Data_User $user) {
		$type = 'ical';
		$responses = $this->getResponses();
		if (array_key_exists($user->userid, $responses)) {
			$type = $responses[$user->userid]->response['type'];
			// echo '<pre>';
			// print_r($responses[$user->userid]);
			// echo '</pre>'; 
		}
		return $type;
	}
	
	// Return all responses to this foodle. This function caches.
	public function getMyResponse(Data_User $user) {
		$responses = $this->getResponses();

		$newresponse = new Data_FoodleResponse($this->db, $this);
		
		if (array_key_exists($user->userid, $responses)) {
			if ($responses[$user->userid]->response['type'] === 'manual') {
				return $responses[$user->userid];
			} else {
				$newresponse = clone $responses[$user->userid];
			}
		} else {

		}
		$newresponse->user = $user;
		$newresponse->userid = $user->userid;
		$newresponse->username = $user->username;
		$newresponse->email = $user->email;
		$nofc = $this->getNofColumns(); 
		
		$newresponse->response = array('type' => 'manual', 'data' => array_fill(0, $nofc, 0));
		
		#echo '<pre>Returning my response:  '; print_r( $newresponse ) ; echo '</pre>'; exit;
		
		return $newresponse;
	}
	
	
	// Return all responses to this foodle. This function caches.
	public function getMyCalendarResponse(Data_User $user) {
		$responses = $this->getResponses();


		$newresponse = new Data_FoodleResponse($this->db, $this);
		
		if (array_key_exists($user->userid, $responses)) {
			if ($responses[$user->userid]->response['type'] === 'ical') {
				return $responses[$user->userid];
			} else {
				$newresponse = clone $responses[$user->userid];
			}
		} 
		$newresponse->user = $user;
		$newresponse->userid = $user->userid;
		$newresponse->username = $user->name;
		$newresponse->email = $user->email;

		$nofc = $this->getNofColumns(); 

		$newresponse->updateFromical($user);
		#echo '<pre>Returning calendar response:  '; print_r( $newresponse->response ) ; echo '</pre>';

		return $newresponse;
	}
	
	public function calendarEnabled() {
	
		if ($this->getColumnDepth() < 2) return FALSE;
		if (isset($this->columntype) && $this->columntype === 'timezone') return FALSE;
	
		$coldates = $this->getColumnDates();
		
#		echo '<pre>'; print_r($coldates); echo '</pre>';
		foreach($coldates AS $cd) {
			if (is_array($cd)) {
				if (count($cd) != 2) return FALSE;
				if (empty($cd[0])) return FALSE;
				if (empty($cd[1])) return FALSE;
			}
			if (empty($cd)) return FALSE;
		}

		return TRUE;
	}
	
	public function isLocked() {
		return (boolean)($this->isExpired() || $this->maxReached());
	}
	
	// Is this Foodle expired
	public function isExpired() {
		#echo 'isExpired? expires [' . var_export((int)$this->expire, TRUE). '] now [' . time(). '] ';
		if (!empty($this->expire))
			return (boolean) (((int)$this->expire) < time());
		return FALSE;
	}
	
	// Has this foodle reached maximum
	public function maxReached() {
		return FALSE;
	}



	public static function requireValidIdentifier($id) {
		if (!preg_match("/^[a-zA-Z0-9\-]+$/", $id)) {
		    throw new Exception('Invalid characters in Foodle ID provided [' . $id . ']. Only [A-Z], [a-z], [0-9] and "-" are legal.');
		}
	}
	
	/*
	 * Returns an array prepared for presentation using XHTML
	 * Contains information about row and colspan.
	 * Each element in the array (first level) represents one row of 
	 * column headers.
	 */
	public function getColumnHeaders($headers, $col = NULL, $level = 0) {
		$depth = $this->getColumnDepth();
		if ($col === NULL) $col = $this->columns;
		foreach($col AS $c) {
			if (isset($c['children'])) {
				$headers[$level][] = array(
					'title' => $c['title'],
					'colspan' => count($c['children']),
				);
				$this->getColumnHeaders(&$headers, $c['children'], $level+1);
			} else {
				$newheader = array('title' => $c['title']);
				if ($level + 1 < $depth) $newheader['rowspan'] = ($depth - $level);
				$headers[$level][] = $newheader;
			}
		}
	}
	
	
	
	/*
	 * Returns an array prepared for presentation using XHTML (90 degrees rotated)
	 * Contains information about row and colspan.
	 * Each element in the array (first level) represents one row of 
	 * column headers.
	 */
	public function getColumnHeadersVertical() {
		$depth = $this->getColumnDepth();
		$col = $this->columns;
		$headers = array();
		
#		echo '<pre>'; print_r($col); exit;
		
		foreach($col AS $c) {
			
			$newRow = array();
			if (isset($c['children'])) {
				$newRow[] = array(
					'title' => $c['title'],
					'rowspan' => count($c['children']),
				);
				$i = 0;
				foreach($c['children'] AS $child) {
					if ($i++ == 0) {
						$newRow[] = array('title' => $child['title']);
						$headers[] = $newRow;
					} else {
						$headers[] = array(array('title' => $child['title']));
					}

				}
			} else {
				$headers[] =  array(array('title' => $c['title'], 'colspan' => 2));
			}
		}
		return $headers;
	}

	
	
	/*
	 * Get each raw column as a concatenated string of the headers above the column.
	 * Such that:
	 *   Nov 23rd 15:00,
	 *   Oct 13th 16:00
	 */
	public function getColumnList($columns, $col = NULL, $strings = array()) {
		if ($col === NULL) $col = $this->columns;
		foreach($col AS $c) {
			if (isset($c['children'])) {
				$lstrings = $strings;
				$lstrings[] = $c['title'];
				$this->getColumnList(&$columns, $c['children'], $lstrings);
			} else {
				$lstrings = $strings;
#				$lstrings[] = $c['title'];

				if (preg_match('|^[^-]+-[^-]+$|', $c['title'])) {

					$splitted = explode('-', $c['title']);
					
					foreach($splitted AS $k => $v) {
						if (preg_match('/^[0-9]{2}$/', $v)) {
							$splitted[$k] = $v . ':00';
						}
					}

					$columns[] = array(
						join(' ', $lstrings) . ' ' . $splitted[0],
						join(' ', $lstrings) . ' ' . $splitted[1]
					);
				} else {
					if (preg_match('/^[0-9]{2}$/', $c['title'])) {
						$c['title'] = $c['title'] . ':00';
					}

					$columns[] = join(' ', $lstrings) . ' ' . $c['title'];
				}
			}
		}
	}
	
	
	public function getColumnDates() {
		
		if (!is_null($this->datecache)) return $this->datecache;
		
		$cols = array();
		$this->getColumnList(&$cols);
		$dates = array();
		$anyDate = FALSE;
		
		foreach($cols AS $col) {
			
			if (is_array($col)) {
				$from = strtotime($col[0]);
				$to = strtotime($col[1]);
				$dates[] = array($from, $to );
			} else {
				$from = strtotime($col);
				if (!empty($from)) {
					$to = strtotime($col)+3600;
					$dates[] = array($from, $to );
				} else {
					$dates[] = NULL;
				}

			}
		}
		// echo '<pre>collected Dates'; print_r($dates); echo '</pre>';
		// echo '<pre>columns: '; print_r($cols); echo '</pre>';
		
		$this->datecache = $dates;
		return $dates;
	}
	
	
	// Return the number of columns...
	public function getNofColumns($col = NULL) {
		if ($col === NULL) $col = $this->columns;
		
		$no = 0;
		foreach($col AS $c) {
			if (isset($c['children'])) {
				$no += $this->getNofColumns($c['children']);
			} else {
				$no += 1;
			}
		}
		return $no;
	}
	
	// Return the depth of columns...
	public function getColumnDepth($col = NULL) {
		if ($col === NULL) $col = $this->columns;
		
		$max = 0;
		foreach($col AS $c) {
			if (isset($c['children'])) {
				$ndepth = $this->getColumnDepth($c['children']);
				if ($ndepth > $max) $max = $ndepth;
			} 
		}
		return $max + 1;
	}
	
	
	/* 
	 * Returns a list of email addresses for each column
	 * The first index in the array contains e-mail addresses for
	 * all responders that respond to the foodle, even if no columns
	 * were checked.
	 */
	public function getEmail() {

		$nofc = $this->getNofColumns();
		$responses = $this->getResponses();
		
		$emailaddrs = array_fill(0, $nofc+1, array());
		
		foreach($responses AS $response) {
			if (!empty($response->email)) $emailaddrs[0][] = self::emailformat($response->username, $response->email);
			foreach($response->response['data'] AS $key => $value) {
				if ($value == '1') {
					if (!empty($response->email)) $emailaddrs[$key+1][] = self::emailformat($response->username, $response->email);
				}				
			}
		}
		
		return $emailaddrs;	
	}
	
	public function countResponses($col = NULL) {
		if(is_null($col)) {
			if (isset($this->maxcolumn)) {
				$col = $this->maxcolumn;
			}
		}
	#	print_r($this->maxcolumn);
	#	echo 'counting col [' . var_export($col, TRUE) . ']';
		
		$responses = $this->getResponses();
		$no = 0;
		foreach($responses AS $response) {
			if (($col == 0) || ($col == NULL)) {
				$no++;
			} else {
				if ($response->response['data'][$col-1] == '1') {
					$no++;
				}
			}
		}
		return $no;
	}
	
	/*
	 * will return a specific responsetype to use; if the responsetype is set to default, it will resolve
	 * the default response type for the columntype.
	 */
	public function responseType() {
		
		switch($this->responsetype) {
			case 'yesno':
				return 'yesno';
			
			case 'yesnomaybe':
				return 'yesnomaybe';
			
			case 'default':
			default:
				if ($this->columntype === 'timezone') return 'yesnomaybe';
				if ($this->columntype === 'dates') return 'yesnomaybe';
				if ($this->columntype === 'text') return 'yesno';

		}
		return 'yesno';		
	}
	
	
	private static function emailformat($username, $email) {
		if (isset($username)) 
			return '"' . $username. '" <' . $email . '>';
		return $email;
	}
	
	
	/*
	 * Calculate how many that replied to each column...
	 */
	public function calculateColumns() {
		$nofc = $this->getNofColumns();
		$calc = array_fill(0, $nofc, array('count' => 0));
		
		$responses = $this->getResponses();
		
		foreach($responses AS $response) {# echo '<pre>'; print_r($calc);
			foreach($response->response['data'] AS $key => $value) {
				if ($value == '1') {
					$calc[$key]['count']++;
				}
				
			}
		}
		
		$max = 0;
		foreach($calc AS $c) {
			if (isset($c['count'])) {
				if ($c['count'] > $max) $max = $c['count'];
			}
		}
		foreach($calc AS $key => $c) {
			if ($c['count'] == $max) {
				$calc[$key]['style'] = 'highest';
			}
		}		
		
		return $calc;		
	}
	
	public static function decode($s) {
		if (empty($s)) return null;
		return json_decode($s, TRUE);
	}
	
	public static function encode($s) {
		if (empty($s)) return '';
		return json_encode($s);
	}
	
	public function updateFromPost(Data_User $user) {

		if (empty($_REQUEST['name'])) throw new Exception('You did not type in a name for the foodle.');
		if (empty($_REQUEST['coldef'])) throw new Exception('Did not get column definition.');

		$this->name = strip_tags($_REQUEST['name']);
		$this->descr = isset($_REQUEST['descr']) ? 
			strip_tags($_REQUEST['descr'], '<h1><h2><h3><h4><h5><h6><p><a><strong><em><ul><ol><li><dd><dt><dl><hr><img><pre><code>') : 
			'...';

		if(!empty($_REQUEST['maxentries']) && is_numeric($_REQUEST['maxentries'])) {
			$this->maxentries = strip_tags($_REQUEST['maxentries']);
			$this->maxcolumn = strip_tags($_REQUEST['maxentriescol']);
		}
		if (array_key_exists('anon', $_REQUEST) && !empty($_REQUEST['anon']))
			$this->allowanonymous = TRUE;
			
		if (!empty($_REQUEST['timezone'])) {
			$this->timezone = $_REQUEST['timezone'];			
		}

		if (!empty($_REQUEST['columntype'])) {
			$this->columntype = $_REQUEST['columntype'];
		}
		if (!empty($_REQUEST['responsetype'])) {
			$this->responsetype = $_REQUEST['responsetype'];
		}
		
		$this->extrafields = array();
		if (!empty($_REQUEST['extrafields_photo'])) $this->extrafields[] = 'photo';
		if (!empty($_REQUEST['extrafields_org'])) $this->extrafields[] = 'org';
		if (!empty($_REQUEST['extrafields_location'])) $this->extrafields[] = 'location';
		
#				echo '<pre>'; print_r($_REQUEST);  print_r($this); exit;
		
		$this->expire = strip_tags($_REQUEST['expire']);
		
		$this->owner = $user->userid;
		
		$this->columns = FoodleUtils::parseOldColDef($_REQUEST['coldef']);
		
		if (empty($this->identifier)) $this->setIdentifier(TRUE);
		
		#echo '<pre>'; print_r($this); echo '</pre>'; exit;

	}
	
	public function setIdentifier($privacy = FALSE) {
		
		$table = array(
			'Š'=>'S', 'š'=>'s', 'Đ'=>'Dj', 'đ'=>'dj', 'Ž'=>'Z', 'ž'=>'z', 'Č'=>'C', 'č'=>'c', 'Ć'=>'C', 'ć'=>'c',
			'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
			'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
			'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
			'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
			'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
			'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
			'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r',
		);
		
		$basename = $this->name;
		$basename = strtr($basename, $table);
		
		$basename = preg_replace('/([^a-zA-Z0-9]+)/', '-', $basename);
		$basename = preg_replace('/^(-*)/', '', $basename);
		$basename = preg_replace('/(-*)$/', '', $basename);
		
		$privacytag = '';
		if ($privacy) {
			$privacytag = '-' . substr(uniqid(), 0, 5);
		}
		
		$checkname = $basename . $privacytag; $counter = 1;
		while(!$this->db->checkIdentifier($checkname)) {
			$counter++; $checkname = $basename . $privacytag . '-' . $counter;
		}
		
		$this->identifier = $checkname;
	}
	
	public function acl(Data_User $user, $operation = 'write') {
		if ($user->isAdmin()) return TRUE;
		if ($user->userid === $this->owner) {
			return TRUE;
		}
		throw new Exception('Current user [' . $user->userid . '] do not have permissions to perform operation [' . $operation . '] on this Foodle.');
	}
	
	
	public function save() {
		$this->db->saveFoodle($this);
	}

	public function delete() {
		$this->db->deleteFoodle($this);
	}
	
	
	public function getTimeZone() {
		if (!empty($this->timezone)) return $this->timezone;
		return '';
	}
	
	
	public function getMaxDef() {
		if (empty($this->maxentries)) return '';
		if ($this->maxentries == 0) return '';
		
#		echo '<pre>maxentries [' . $this->maxentries. '] maxcolumns [' . $this->maxcolumn. ']';
		
		if (is_numeric($this->maxentries) && is_numeric($this->maxcolumn)) {
			return $this->maxentries . ':' . $this->maxcolumn;
		}
		return '';
	}
	
	
	
	public function getExpireText() {

		if (empty($this->expire)) return 'This foodle will not expire';
		if ($this->isExpired()) return 'This foodle is expired';
		
		return date("Y-m-d H:i", (int)$this->expire) . ' (expires in ' . FoodleUtils::date_diff((int)$this->expire - time()) . ')';
	}	
	
	
	
	public function getExpireTextField() {
		if (empty($this->expire)) return '';
		return date("Y-m-d H:i", $this->expire);
	}
	
	
	

}

