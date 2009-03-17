<?php

/**
 *
 */
class FoodleListings {


	private $db;
	private $currentuser;

	function __construct($currentuser, $db = null) {
		$this->currentuser = $currentuser;
		if (!empty($db)) {
			$this->db = $db;
			//$this->loadFromDB();
		}
	}

/*
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
	
	*/
	
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
			WHERE foodleid='" . $this->getIdentifier() . "' order by id desc";

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
	

	/**
	 * Here is the database schema:
mysql> show columns from def;
+---------+--------------+------+-----+---------+-------+
| Field   | Type         | Null | Key | Default | Extra |
+---------+--------------+------+-----+---------+-------+
| id      | varchar(100) | NO   | PRI |         |       | 
| name    | tinytext     | YES  |     | NULL    |       | 
| descr   | text         | YES  |     | NULL    |       | 
| columns | text         | YES  |     | NULL    |       | 
+---------+--------------+------+-----+---------+-------+
	*/
	public function getYourEntries() {
				
		$link = $this->db;
		
		$sql ="
			SELECT * 
			FROM entries,def 
			WHERE userid = '" . $this->currentuser . "' and entries.foodleid = def.id
			ORDER BY def.created DESC";
			
			#echo $sql;

		$result = mysql_query($sql, $this->db);
		
		if(!$result){
			throw new Exception ("Could not successfully run query ($sql) from DB:" . mysql_error());
		}
		
		$resarray = array();
		
		if(mysql_num_rows($result) > 0){		
			while($row = mysql_fetch_assoc($result)){
				$resarray[] = $row;
			}
		}		
		mysql_free_result($result);
		
		return $resarray;
	}
	
	public function getAllEntries($no = 20) {
				
		$link = $this->db;
		
		$sql ="
			SELECT * 
			FROM def 
			ORDER BY created DESC 
			LIMIT " . $no;
			
			#echo $sql;

		$result = mysql_query($sql, $this->db);
		
		if(!$result){
			throw new Exception ("Could not successfully run query ($sql) from DB:" . mysql_error());
		}
		
		$resarray = array();
		
		if(mysql_num_rows($result) > 0){		
			while($row = mysql_fetch_assoc($result)){
				$resarray[] = $row;
			}
		}		
		mysql_free_result($result);
		
		return $resarray;
	}
	
	
	
	
	public function getOwnerEntries($userid, $no = 20) {
				
		$link = $this->db;
		
		$sql ="
			SELECT * 
			FROM def 
			WHERE owner = '" . addslashes($userid) . "'
			ORDER BY created DESC 
			LIMIT " . $no;
			
			#echo $sql;

		$result = mysql_query($sql, $this->db);
		
		if(!$result){
			throw new Exception ("Could not successfully run query ($sql) from DB:" . mysql_error());
		}
		
		$resarray = array();
		
		if(mysql_num_rows($result) > 0){		
			while($row = mysql_fetch_assoc($result)){
				$resarray[] = $row;
			}
		}		
		mysql_free_result($result);
		
		return $resarray;
	}
	
	
	public function getStatusUpdate($userid, $foodleids, $no = 20) {
		
		$link = $this->db;
		
		
		$fidstr = "('" . join("', '", $foodleids) . "')"; 
		
		$sql ="
			SELECT entries.*,def.name 
			FROM entries, def 
			WHERE foodleid IN " . $fidstr . "
				and userid != '" . addslashes($userid) . "'
				and def.id = entries.foodleid
			ORDER BY entries.created DESC 
			LIMIT " . $no;
			
			#echo $sql;
		#echo $sql; exit;
		$result = mysql_query($sql, $this->db);
		
		if(!$result){
			throw new Exception ("Could not successfully run query ($sql) from DB:" . mysql_error());
		}
		
		$resarray = array();
		
		if(mysql_num_rows($result) > 0){		
			while($row = mysql_fetch_assoc($result)){
				$resarray[] = $row;
			}
		}		
		mysql_free_result($result);
		
		return $resarray;
	}
	
	public function getStats($userid) {
	
		$sql = 'select count(*) as num from (select UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(created) as d from entries  having d < 7*60*60*24 ) as a';
		$result = mysql_query($sql, $this->db);		
		if(!$result) throw new Exception ("Could not successfully run query ($sql) from DB:" . mysql_error());
		
		$resarray = array();
		if(mysql_num_rows($result) === 1){		
			$row = mysql_fetch_assoc($result);
			$resarray['total7days'] = $row['num'];
		}		
		mysql_free_result($result);
		return $resarray;
	}
	
	
	
	public function getIdentifier() {
		return $this->identifier;
	}

	public function setDBhandle($db) {
		$this->db = $db;
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


}

?>