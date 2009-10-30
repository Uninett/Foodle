<?php

/**
 *
 */
class Foodle {

	private $identifier;
	private $name;
	private $descr;
	private $columns;
	private $maxdef;
	private $expire;
	private $owner;
	private $allowanonymous;
	
	/**
	 * Entires
	 */
	private $yourentry    = array();
	private $otherentries = array();
	
	private $discussion = array();
	
	private $currentuser;
	
	private $loadedFromDB = false;
	
	private $numcols = 0;

	function __construct($identifier, $currentuser, $db = null) {
		if (!empty($identifier)) {
			$this->identifier = $identifier;
		} else {
			$this->setRandomIdentifier();
		}
		$this->currentuser = $currentuser;
		if (!empty($db)) {
			$this->db = $db;
			$this->loadFromDB();
		}
	}
	
	function setCurrentUser($currentuser) {
		$this->currentuser = $currentuser;
	}
	
	
	function setRandomIdentifier() {

		$length = 8;
		// start with a blank password
		$identifier = "";
		
		// define possible characters
		$possible = "0123456789abcdefghijkmnpqrstuvwxyz"; 
		
		// set up a counter
		$i = 0; 
		while ($i < $length) { 
			// pick a random character from the possible ones
			$identifier .=  substr($possible, mt_rand(0, strlen($possible)-1), 1);
			$i++;		
		}
		$this->identifier = $identifier;
	}
	
	function setInfo($name, $descr, $expire, $maxdef, $allowanonymous) {
		$this->name = $name;
		$this->descr = $descr;
		$this->expire = $expire;
		$this->maxdef = $maxdef;
		$this->allowanonymous = $allowanonymous;

#		$this->columns = $columns;
	}
	
	function setColumns($columns) {
		$this->columns = $columns;
	}
	
	function setColumnsByDef($coldef) {
		$this->columns = $this->parseColumn($coldef);
	}
	
	function getColumns() {
		return $this->columns;
	}
	
	function getNumCols() {
		return $this->numcols;
	}
	

	public function isLoaded() {
		return $this->loadedFromDB;
	}
	public function loadFromDB() {
	
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
		$expire = $this->getExpire();
		if (empty($expire)) return 'This foodle will not expire';
		
		if ($this->expired()) return 'This foodle is expired';
		
		return date("Y-m-d H:i", $expire) . ' (expires in ' . $this->date_diff($expire - time()) . ')';
	}
	
	public function getExpireTextField() {
		$expire = $this->getExpire();
		if (empty($expire)) return '';
		return date("Y-m-d H:i", $expire);
	}
	
	
	public function getExpire() {
		return $this->expire;
	}
	
	public function expired() {
		if (!empty($this->expire))
			return $this->expire < time();
		
		return false;
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
	private function loadEntriesFromDB() {
				
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
	private function loadDiscussion() {

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
	function date_diff($secondsago)
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
	
	public function encodeResponse(array $response) {
		return join(',', $response);	
	}
	
	public function decodeResponse($response) {
		return explode(',', $response);
	}
	
	
	public function addEntry($userid, $username, $email, $response, $updated, $notes, $created = NULL) {
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

	public function getDiscussion() {
		return $this->discussion;
	}
	
	public function getOtherEntries() {
		return $this->otherentries;
	}
	
	public function getYourEntry($name = null) {
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
	
	
	public function setDBhandle($db) {
		$this->db = $db;
	}
	
	
	public static function parseColumnUtil($string) {
	
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
	
	
	public function parseColumn($string) {
	
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
	
	public function encodeColumn(array $column) {
	
		$colstrings = array();
		foreach ($column AS $key => $col) {
			$colstrings[] = empty($col) ? $key : $key . '(' . join(',', $col) . ')';
		}
		return join('|', $colstrings);
	
	}
	
	
	// TODO: addslashes
	public function savetoDB() {

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
				updated = now(),
				owner = '" . addslashes($this->getOwner()) . "' WHERE id = '" . addslashes($this->getIdentifier()) . "'";

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
	
	public function addDiscussion($name, $message) {
		
		$link = $this->getDBhandle();
		
		$res = mysql_query("INSERT INTO discussion (foodleid,username,message) values ('" . 
			mysql_real_escape_string($this->getIdentifier()) . "','" . mysql_real_escape_string($name) . "', '" . 
			mysql_real_escape_string($message) . "')", $this->db);
		if(mysql_error()){
			throw new Exception('Invalid query: ' . mysql_error());
		}
		
	}
	
	
	// TODO: addslashes
	private function deleteResponse() {
		$link = $this->getDBhandle();
		
		$res = mysql_query("DELETE FROM entries WHERE userid='" . addslashes($this->currentuser) . "' AND foodleid='" . $this->getIdentifier() . "'", $this->db);
		if(mysql_error()){
			throw new Exception('Invalid query: ' . mysql_error());
		}
	}
	
	// TODO: addslashes
	private function saveResponse( ) {
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
	
	public function setMyResponse($response) {
		#print_r($response); exit;
		$this->yourentry = $response;
		$this->deleteResponse();
		$this->saveResponse();
	}
	
	
	private function getDBhandle() {

		return $this->db;
	}
	
	
	public function getIdentifier() {
		return $this->identifier;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function getDescr() {
		return $this->descr;
	}
	public function getMaxDef() {
		return $this->maxdef;
	}
	public function getAnon() {
		return $this->allowanonymous;
	}
	
	public function getOwner() {
		return $this->owner;
	}
	
	public function setOwner($owner) {
		$this->owner = $owner;
	}
	
	public function requireOwner() {
		if ($this->currentuser === 'andreas@uninett.no') return;
		if ($this->currentuser === 'andreas@rnd.feide.no') return;
		if ($this->getOwner() === $this->currentuser) return;
		throw new Exception('You are user ' . $this->currentuser . ' and you do not have access to edit this foodle. The foodle is owned by ' . $this->getOwner() . '.');
	}
	
	public function getAccess() {
		return $this->access;
	}
	
	

}

?>