<?php

/**
 * This class represents a Foodle
 *
mysql> show columns from def;
+---------+--------------+------+-----+-------------------+-------+
| Field   | Type         | Null | Key | Default           | Extra |
+---------+--------------+------+-----+-------------------+-------+
| id      | varchar(100) | NO   | PRI |                   |       | 
| name    | tinytext     | YES  |     | NULL              |       | 
| descr   | text         | YES  |     | NULL              |       | 
| columns | text         | YES  |     | NULL              |       | 
| owner   | text         | YES  |     | NULL              |       | 
| created | timestamp    | NO   |     | CURRENT_TIMESTAMP |       | 
| updated | timestamp    | YES  |     | NULL              |       | 
| expire  | datetime     | YES  |     | NULL              |       | 
| maxdef  | text         | YES  |     | NULL              |       | 
| anon    | tinytext     | YES  |     | NULL              |       | 
+---------+--------------+------+-----+-------------------+-------+
10 rows in set (0.00 sec)

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
class Foodle {

	public $identifier;
	public $name;
	public $descr;
	public $columns;
	
	public $maxentries;
	public $maxcolumn;
	
	public $expire;
	public $owner;
	public $allowanonymous = FALSE;
	
	public $loadedFromDB = FALSE;
	
	private $db;
	private $responses = NULL;
	private $discussion = NULL;
	
	function __construct(FoodleDBConnector $db) {
		$this->db = $db;
	}
	
	public function updateResponses(FoodleResponse $response) {
		$this->responses[$response->userid] = $response;
	}
	
	// Return all responses to this foodle. This function caches.
	public function getResponses() {
		if ($this->responses === NULL) $this->responses = $this->db->readResponses($this);
		
		
		
		foreach($this->responses AS $resp) {
			#$resp2 = $resp; unset($resp2->foodle); echo '<pre>NEW RESPONSE'; print_r($resp2); echo '</pre>'; 
			$resp->icalfill();
			#exit;
		}
		
		return $this->responses;
	}
	
	// Return all responses to this foodle. This function caches.
	public function getDiscussion() {
		if ($this->discussion === NULL) $this->discussion = $this->db->readDiscussion($this);
		return $this->discussion;
	}
	
	public function getDefaultResponse(User $user) {
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
	public function getMyResponse(User $user) {
		$responses = $this->getResponses();

		$newresponse = new FoodleResponse($this->db, $this);
		
		if (array_key_exists($user->userid, $responses)) {
			if ($responses[$user->userid]->response['type'] === 'manual') {
				return $responses[$user->userid];
			} else {
				$newresponse = clone $responses[$user->userid];
			}
		} else {

		}

		$newresponse->userid = $user->userid;
		$newresponse->username = $user->name;
		$newresponse->email = $user->email;
		$nofc = $this->getNofColumns(); 
		
		$newresponse->response = array('type' => 'manual', 'data' => array_fill(0, $nofc, 0));
		
		#echo '<pre>Returning my response:  '; print_r( $newresponse ) ; echo '</pre>';
		
		return $newresponse;
	}
	
	// Return all responses to this foodle. This function caches.
	public function getMyCalendarResponse(User $user) {
		$responses = $this->getResponses();


		$newresponse = new FoodleResponse($this->db, $this);
		
		if (array_key_exists($user->userid, $responses)) {
			if ($responses[$user->userid]->response['type'] === 'ical') {
				return $responses[$user->userid];
			} else {
				$newresponse = clone $responses[$user->userid];
			}
		} 
		
		$newresponse->userid = $user->userid;
		$newresponse->username = $user->name;
		$newresponse->email = $user->email;

		$nofc = $this->getNofColumns(); 

		$newresponse->updateFromical($user);
		#echo '<pre>Returning calendar response:  '; print_r( $newresponse->response ) ; echo '</pre>';

		return $newresponse;
	}
	
	public function calendarEnabled() {
		$coldates = $this->getColumnDates();
		foreach($coldates AS $cd) {
			if (empty($cd)) return FALSE;
		}
		return TRUE;
	}
	
	public function isLocked() {
		return (boolean)($this->isExpired() || $this->maxReached());
	}
	
	// Is this Foodle expired
	public function isExpired() {
		if (!empty($this->expire))
			return (boolean) $this->expire < time();
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
				$lstrings[] = $c['title'];
				$columns[] = join(' ', $lstrings);				
			}
		}
	}
	
	
	public function getColumnDates() {
		$cols = array();
		$this->getColumnList(&$cols);
		$dates = array();
		$anyDate = FALSE;
		
		foreach($cols AS $col) {
			$dates[] = strtotime($col);
		}
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
	
	public function updateFromPost(User $user) {

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
		
		$this->expire = strip_tags($_REQUEST['expire']);
		
		$this->owner = $user->userid;
		
		$this->columns = FoodleUtils::parseOldColDef($_REQUEST['coldef']);
		
		if (empty($this->identifier)) $this->setIdentifier();
		
		#echo '<pre>'; print_r($this); echo '</pre>'; exit;

	}
	
	public function setIdentifier() {
		
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
		
		$checkname = $basename; $counter = 1;
		while(!$this->db->checkIdentifier($checkname)) {
			$counter++; $checkname = $basename . '-' . $counter;
		}
		
		$this->identifier = $checkname;
	}
	
	
	
	
	public function save() {
		$this->db->saveFoodle($this);
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
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	// ------------ o ------------ o --------------
	

	
	public function xloadFromDB() {
	
		$sql ="SELECT *,
			IF(expire=0,null,UNIX_TIMESTAMP(expire)) AS expire_unix 
			FROM def WHERE id = '" . $this->getIdentifier() . "'";
		$result = mysql_query($sql, $this->db);
		
		#echo 'SQL: ' . $sql; exit;
		
		if(!$result){
			throw new Exception ("Could not successfully run query ($sql) fromDB:" . mysql_error());
		}

		if(mysql_num_rows($result) > 0){		
			$row = mysql_fetch_assoc($result);
			
			$this->setInfo($row['name'], $row['descr'], $row['expire_unix'], $row['maxdef'], $row['anon']);
			$this->setOwner($row['owner']);
			$this->setColumns($this->parseColumn($row['columns']));
			$this->loadEntriesFromDB();
			$this->loadDiscussion();
			$this->loadedFromDB = true;
		} else throw new Exception('Could not find foodle in database with id ' . $this->getIdentifier());
		mysql_free_result($result);
	}
	
	public function getExpireText() {

		if (empty($this->expire)) return 'This foodle will not expire';
		
		if ($this->isExpired()) return 'This foodle is expired';
		
		return date("Y-m-d H:i", $this->expire) . ' (expires in ' . FoodleUtils::date_diff($this->expire - time()) . ')';
	}
	
	public function xgetExpireTextField() {
		$expire = $this->getExpire();
		if (empty($expire)) return '';
		return date("Y-m-d H:i", $expire);
	}
	
	
	public function xgetExpire() {
		return $this->expire;
	}
	

	
	
	/**
	 * Here is the database schema:
+----------+--------------+------+-----+---------+----------------+
| Field    | Type         | Null | Key | Default | Extra          |
+----------+--------------+------+-----+---------+----------------+
| id       | int(11)      | NO   | PRI | NULL    | auto_increment | 
| foodleid | varchar(100) | NO   |     |         |                | 
| userid   | tinytext     | YES  |     | NULL    |                | 
| username | tinytext     | YES  |     | NULL    |                | 
| response | tinytext     | YES  |     | NULL    |                | 
+----------+--------------+------+-----+---------+----------------+
	*/
	private function xloadEntriesFromDB() {
				
		$link = $this->getDBhandle();
		
		$sql ="SELECT *, UNIX_TIMESTAMP(created) AS createdu, 
				IF(created=0,null,UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(created)) AS ago
			FROM entries
			WHERE foodleid='" . $this->getIdentifier() . "' order by id desc";

		$result = mysql_query($sql, $this->db);
		
		if(!$result){
			throw new Exception ("Could not successfully run query ($sql) from DB:" . mysql_error());
		}
		
		if(mysql_num_rows($result) > 0){		
			while($row = mysql_fetch_assoc($result)){
				$this->addEntry(
					$row['userid'], 
					$row['username'],
					$row['email'],
					$this->decodeResponse($row['response']),
					$this->date_diff($row['ago']),
					$row['notes'],
					$row['createdu']
				);
			}
		}		
		mysql_free_result($result);
		
	}
	
	
	
	/**
	 * Here is the database schema:
+----------+--------------+------+-----+-------------------+----------------+
| Field    | Type         | Null | Key | Default           | Extra          |
+----------+--------------+------+-----+-------------------+----------------+
| id       | int(11)      | NO   | PRI | NULL              | auto_increment | 
| foodleid | varchar(100) | NO   |     |                   |                | 
| username | tinytext     | YES  |     | NULL              |                | 
| message  | text         | YES  |     | NULL              |                | 
| created  | timestamp    | NO   |     | CURRENT_TIMESTAMP |                | 
+----------+--------------+------+-----+-------------------+----------------+
	*/
	private function xloadDiscussion() {

		$this->discussion = array();
		
		$link = $this->getDBhandle();

		$sql ="SELECT *, UNIX_TIMESTAMP(created) AS createdu, 
				IF(created=0,null,UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(created)) AS ago
			FROM discussion
			WHERE foodleid='" . $this->getIdentifier() . "' order by id desc";

		$result = mysql_query($sql, $this->db);

		if(!$result){
			throw new Exception ("Could not successfully run query ($sql) from DB:" . mysql_error());
		}

		if(mysql_num_rows($result) > 0){		
			while($row = mysql_fetch_assoc($result)){
				$row['agotext'] = $this->date_diff($row['ago']);
				$this->discussion[] = $row;
			}
		}		
		mysql_free_result($result);

	}

	// The parameters of this function are the dates to be compared.
	// The first should be prior to the second. The dates are in
	// the form of: 1978-04-26 02:00:00.
	// They also can come from a web form using the global $_POST['start']
	// and $_POST['end'] variables.
	function xdate_diff($secondsago)
	{
#		echo 'comparing ' . $secondsago;
		
#		return $secondsago . ' seconds';
		if (is_null($secondsago)) return 'NA';
	
		$nseconds = $secondsago; // Number of seconds between the two dates
		$ndays = round($nseconds / 86400); // One day has 86400 seconds
		$nseconds = $nseconds % 86400; // The remainder from the operation
		$nhours = round($nseconds / 3600); // One hour has 3600 seconds
		$nseconds = $nseconds % 3600;
		$nminutes = round($nseconds / 60); // One minute has 60 seconds, duh!
		$nseconds = $nseconds % 60;

		if ($ndays > 0) 
			return $ndays . " days";
		elseif ($nhours > 0) 
			return $nhours . "h " . $nminutes . "m";
		elseif ($nminutes > 0) 
			return $nminutes . " min";
		else 
			return $nseconds . " sec";
	} 
	
	public function xencodeResponse(array $response) {
		return join(',', $response);	
	}
	
	public function dxecodeResponse($response) {
		return explode(',', $response);
	}
	
	
	public function xaddEntry($userid, $username, $email, $response, $updated, $notes, $created = NULL) {
		$newentry = array(
			'userid' => $userid, 'username' => $username, 'email' => $email,
			'response' => $response, 
			'updated' => $updated, 'notes' => $notes
		);
		if ($created) {
			$newentry['created'] = $created;
		}
		
		#print_r($notes); exit;

		$this->otherentries[] = $newentry;		
		if ($userid == $this->currentuser)
			$this->yourentry = $newentry;

	}

	// public function getDiscussion() {
	// 	return $this->discussion;
	// }
	
	public function xgetOtherEntries() {
		return $this->otherentries;
	}
	
	public function xgetYourEntry($name = null) {
		if (!empty($this->yourentry)) 
			return $this->yourentry;
		
		return array(
			'userid' => $this->currentuser, 
			'username' => (isset($name) ? $name : ''), 
			'response' => array_fill(0, $this->numcols, '0'),
			'updated' => 'never',
			'notes' => '',
			);
	}
	
	public static function xparseColumnUtil($string) {
	
		$counter = 0;
		$result = array();
		$level1 = explode('|', $string);
		foreach($level1 AS $head) {
			if (preg_match('/(.*)\((.*)\)/', $head, $matches)) {
				$result[$matches[1]] = explode(',', $matches[2]);
				$counter += count($result[$matches[1]]);
			} else {
				$result[$head] = null;
				$counter++;
			}
		}
		return $result;
	
	}
	
	
	public function xparseColumn($string) {
	
		$counter = 0;
		$result = array();
		$level1 = explode('|', $string);
		foreach($level1 AS $head) {
			if (preg_match('/(.*)\((.*)\)/', $head, $matches)) {
				$result[$matches[1]] = explode(',', $matches[2]);
				$counter += count($result[$matches[1]]);
			} else {
				$result[$head] = null;
				$counter++;
			}
		}
		$this->numcols = $counter;
		return $result;
	
	}
	
	public function xencodeColumn(array $column) {
	
		$colstrings = array();
		foreach ($column AS $key => $col) {
			$colstrings[] = empty($col) ? $key : $key . '(' . join(',', $col) . ')';
		}
		return join('|', $colstrings);
	
	}
	
	
	// TODO: addslashes
	public function xsavetoDB() {

		$expire = 'null';
		if (!empty($this->expire))
			$expire = "'" . addslashes($this->expire) . "'";
		

		
		$link = $this->getDBhandle();

		if ($this->isLoaded() ) {
			$sql = "UPDATE def SET 
				name ='" . addslashes($this->getName()) . "', 
				descr ='" . addslashes($this->getDescr()) . "', 
				columns = '" . addslashes($this->encodeColumn($this->getColumns())) . "',
				expire = " . $expire . ",
				maxdef = '" . addslashes($this->getMaxDef()) . "',
				anon = '" . addslashes($this->allowanonymous) . "',
				updated = now(),
				owner = '" . addslashes($this->getOwner()) . "' WHERE id = '" . addslashes($this->getIdentifier()) . "'";

			// echo 'query: ' . $sql; exit;

			$res = mysql_query($sql, $this->db);
			if(mysql_error()){
				throw new Exception('Invalid query: ' . mysql_error());
			}

			
		} else {
		
			$res = mysql_query("INSERT INTO def (id, name, descr, maxdef, columns, expire, owner, anon) values ('" . 
				addslashes($this->getIdentifier()) . "','" . addslashes($this->getName()) . "', '" . 
				addslashes($this->getDescr()) . "', '" . 
				addslashes($this->getMaxDef()) . "', '" .
				addslashes($this->encodeColumn($this->getColumns())) . "', " . $expire . ", '" . 
				addslashes($this->currentuser) . "', '" . 
				addslashes($this->allowanonymous) . "')", $this->db);
			if(mysql_error()){
				throw new Exception('Invalid query: ' . mysql_error());
			}
		}
	}
	
	public function xaddDiscussion($name, $message) {
		
		$link = $this->getDBhandle();
		
		$res = mysql_query("INSERT INTO discussion (foodleid,username,message) values ('" . 
			mysql_real_escape_string($this->getIdentifier()) . "','" . mysql_real_escape_string($name) . "', '" . 
			mysql_real_escape_string($message) . "')", $this->db);
		if(mysql_error()){
			throw new Exception('Invalid query: ' . mysql_error());
		}
		
	}
	
	
	// TODO: addslashes
	private function xdeleteResponse() {
		$link = $this->getDBhandle();
		
		$res = mysql_query("DELETE FROM entries WHERE userid='" . addslashes($this->currentuser) . "' AND foodleid='" . $this->getIdentifier() . "'", $this->db);
		if(mysql_error()){
			throw new Exception('Invalid query: ' . mysql_error());
		}
	}
	
	// TODO: addslashes
	private function xsaveResponse( ) {
		/**
		 * Here is the database schema:
	+----------+--------------+------+-----+---------+----------------+
	| Field    | Type         | Null | Key | Default | Extra          |
	+----------+--------------+------+-----+---------+----------------+
	| id       | int(11)      | NO   | PRI | NULL    | auto_increment | 
	| foodleid | varchar(100) | NO   |     |         |                | 
	| userid   | tinytext     | YES  |     | NULL    |                | 
	| username | tinytext     | YES  |     | NULL    |                | 
	| response | tinytext     | YES  |     | NULL    |                | 
	+----------+--------------+------+-----+---------+----------------+
		*/

		$link = $this->getDBhandle();
		
		$notes = 'null';
		if (!empty($this->yourentry['notes']))
			$notes = "'" . addslashes($this->yourentry['notes']) . "'";
		
#		print_r($this->yourentry); exit;
		
		$res = mysql_query("INSERT INTO entries (foodleid, userid, username, email, response, notes) values ('" . 
			addslashes($this->getIdentifier()) . "','" . addslashes($this->currentuser) . "', '" . 
			addslashes($this->yourentry['username']) . "', '" . 
			addslashes($this->yourentry['email']) . "', '" .
			addslashes($this->encodeResponse($this->yourentry['response'])) . "', " .
			$notes . ")", $this->db);
		if(mysql_error()){
			throw new Exception('Invalid query: ' . mysql_error());
		}
		
	}
	
	public function xrequireOwner() {
		if ($this->currentuser === 'andreas@uninett.no') return;
		if ($this->currentuser === 'andreas@rnd.feide.no') return;
		if ($this->getOwner() === $this->currentuser) return;
		throw new Exception('You are user ' . $this->currentuser . ' and you do not have access to edit this foodle. The foodle is owned by ' . $this->getOwner() . '.');
	}

	
	

}

