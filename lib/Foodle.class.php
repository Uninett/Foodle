<?php

/**
 *
 */
class Foodle {

	private $identifier;
	private $name;
	private $descr;
	private $columns;
	
	/**
	 * Entires
	 */
	private $yourentry    = array();
	private $otherentries = array();
	
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
	
	function setInfo($name, $descr) {
		$this->name = $name;
		$this->descr = $descr;
		$this->columns = $columns;
	}
	
	function setColumns($columns) {
		$this->columns = $columns;
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
	
		$sql ="SELECT * FROM def WHERE id = '" . $this->getIdentifier() . "'";
		$result = mysql_query($sql, $this->db);
		
		if(!$result){
			throw new Exception ("Could not successfully run query ($sql) fromDB:" . mysql_error());
		}

		if(mysql_num_rows($result) > 0){		
			$row = mysql_fetch_assoc($result);
			
			
			$this->setInfo($row['name'], $row['descr']);
			$this->setColumns($this->parseColumn($row['columns']));
			$this->loadEntriesFromDB();
			$this->loadedFromDB = true;
		} else throw new Exception('Could not find foodle in database with id ' . $this->getIdentifier());
		mysql_free_result($result);
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
		
		$sql ="SELECT * 
			FROM entries
			WHERE foodleid='" . $this->getIdentifier() . "'";

		$result = mysql_query($sql, $this->db);
		
		if(!$result){
			throw new Exception ("Could not successfully run query ($sql) from DB:" . mysql_error());
		}
		
		if(mysql_num_rows($result) > 0){		
			while($row = mysql_fetch_assoc($result)){
				$this->addEntry($row['userid'], $row['username'],
					$this->decodeResponse($row['response']));
			}
		}		
		mysql_free_result($result);
		
	}
	
	public function encodeResponse(array $response) {
		return join(',', $response);	
	}
	
	public function decodeResponse($response) {
		return explode(',', $response);
	}
	
	
	public function addEntry($userid, $username, $response) {
		$newentry = array('userid' => $userid, 'username' => $username, 'response' => $response);
		if ($userid == $this->currentuser)
			$this->yourentry = $newentry;
		else
			$this->otherentries[] = $newentry;

	}
	
	public function getOtherEntries() {
		return $this->otherentries;
	}
	
	public function getYourEntry($name = null) {
		if (!empty($this->yourentry)) 
			return $this->yourentry;
		
		return array('userid' => $this->currentuser, 'username' => (isset($name) ? $name : 'NA'), 
			'response' => array_fill(0, $this->numcols, '0') );
	}
	
	
	public function setDBhandle($db) {
		$this->db = $db;
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

		
		$link = $this->getDBhandle();

		if ($this->isLoaded() ) {
			$sql = "UPDATE def SET 
				name ='" . addslashes($this->getName()) . "', 
				descr ='" . addslashes($this->getDescr()) . "', 
				columns = '" . addslashes($this->encodeColumn($this->getColumns())) . 
				"' WHERE id = '" . addslashes($this->getIdentifier()) . "'";

			$res = mysql_query($sql, $this->db);
			if(mysql_error()){
				throw new Exception('Invalid query: ' . mysql_error());
			}
			$this->deleteACLinDB();
			
		} else {
		
			$res = mysql_query("INSERT INTO def (id, name, descr, columns) values ('" . 
				addslashes($this->getIdentifier()) . "','" . addslashes($this->getName()) . "', '" . 
				addslashes($this->getDescr()) . "', '" . 
				addslashes($this->encodeColumn($this->getColumns())) . "')", $this->db);
			if(mysql_error()){
				throw new Exception('Invalid query: ' . mysql_error());
			}
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
		
#		print_r($this->yourentry); exit;
		
		$res = mysql_query("INSERT INTO entries (foodleid, userid, username, response) values ('" . 
			addslashes($this->getIdentifier()) . "','" . addslashes($this->currentuser) . "', '" . 
			addslashes($this->yourentry['username']) . "', '" . addslashes($this->encodeResponse($this->yourentry['response'])) . "')", $this->db);
		if(mysql_error()){
			throw new Exception('Invalid query: ' . mysql_error());
		}
		
	}
	
	public function setMyResponse($response) {
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
	
	public function getOwner() {
		return $this->owner;
	}
	
	public function setOwner($owner) {
		$this->owner = $owner;
	}
	
	public function getAccess() {
		return $this->access;
	}
	
	

}

?>