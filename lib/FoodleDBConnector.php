<?php

/**
 * Reads and writes from database
 */
class FoodleDBConnector {


	private $db;

	function __construct($config) {
		
		$this->db = mysql_connect(
			$config->getValue('db.host', 'localhost'), 
			$config->getValue('db.user'),
			$config->getValue('db.pass'));
		if(!$this->db){
			throw new Exception('Could not connect to database: '.mysql_error());
		}
		mysql_select_db($config->getValue('db.name','feidefoodle'));
	}
	

	/*
	 * Loads a foodle with a given $id from database, as a Foodle object.
	 */
	public function readFoodle($id) {
		$sql ="
			SELECT *,
			IF(expire=0,null,UNIX_TIMESTAMP(expire)) AS expire_unix 
			FROM def WHERE id = '" . $id . "'";
		
		$result = mysql_query($sql, $this->db);
		
		if(!$result)
			throw new Exception ("Could not successfully run query ($sql) fromDB:" . mysql_error());

		if(mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			
			// echo '<pre>'; print_r($row); echo '</pre>'; exit;
			
			$foodle = new Foodle($this);
			$foodle->identifier = $id;
			$foodle->name = $row['name'];
			$foodle->descr = $row['descr'];
			$foodle->expire = $row['expire_unix'];
			$foodle->owner = $row['owner'];
			$foodle->allowanonymous = (boolean) ($row['anon'] == '1');
			
			
			if($row['columns'][0] == '{') {
			// if ($decodedColDef !== NULL) {
				#echo 'Use new encoding format';
				$foodle->columns = json_decode($row['columns'], TRUE);
			} else {
				#echo 'Using old decoding.';
				$foodle->columns = FoodleUtils::parseOldColDef($row['columns']);
			}
			
			#echo '<pre>'; print_r($foodle->columns); echo '</pre>'; exit;
			
			$maxdef = self::parseMaxDef($row['maxdef']);
			if ($maxdef[0]) {
				$foodle->maxentries = $maxdef[0];
				$foodle->maxcolumn = $maxdef[1];
			}
			
			$foodle->loadedFromDB = TRUE;
			mysql_free_result($result);
			
			#echo '<pre>'; print_r($row); exit;
			
			return $foodle;
		} 
		
		throw new Exception('Could not find foodle in database with id ' . $this->getIdentifier());
	}
	
	public function saveFoodle(Foodle $foodle) {
		
		/*
		
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
		
					name ='" . addslashes($this->getName()) . "', 
					descr ='" . addslashes($this->getDescr()) . "', 
					columns = '" . addslashes($this->encodeColumn($this->getColumns())) . "',
					expire = " . $expire . ",
					maxdef = '" . addslashes($this->getMaxDef()) . "',
					anon = '" . addslashes($this->allowanonymous) . "',
					updated = now(),
					owner = '" . addslashes($this->getOwner()) . "' WHERE id = '" . addslashes($this->getIdentifier()) . "'";

				$res = mysql_query("INSERT INTO def (id, name, descr, maxdef, columns, expire, owner, anon) values ('" . 
					addslashes($this->getIdentifier()) . "','" . addslashes($this->getName()) . "', '" . 
					addslashes($this->getDescr()) . "', '" . 
					addslashes($this->getMaxDef()) . "', '" .
					addslashes($this->encodeColumn($this->getColumns())) . "', " . $expire . ", '" . 
					addslashes($this->currentuser) . "', '" . 
					addslashes($this->allowanonymous) . "')", $this->db);
			
		*/
		
		
		
		if ($foodle->loadedFromDB) {
			$sql = "
				UPDATE def SET 
					name = '" . mysql_real_escape_string($foodle->name) . "', 
					descr = '" . json_encode($foodle->descr) . "', 
					columns = '" . mysql_real_escape_string(json_encode($response->columns))  . "',
					expire = " . mysql_real_escape_string($expire) . ",
					maxdef = '" . mysql_real_escape_string($this->getMaxDef()) . "',
					anon = '" . ($this->allowanonymous ? '1' : '0') . "',
					updated = NOW()	
				WHERE id = '" . $response->foodle->identifier. "' 
			";
			
		} else {
			$sql = "
				INSERT INTO def (id, name, descr, columns, expire, maxdef,  owner, anon) values (" . 
					"'" . mysql_real_escape_string($foodle->identifier) . "'," . 
					"'" . mysql_real_escape_string($foodle->name) . "', " . 
					"'" . mysql_real_escape_string($foodle->descr) . "', " . 
					"'" . mysql_real_escape_string(json_encode($foodle->columns)) . "', " . 
					"'" . $foodle->expire . "', " . 
					"'" . mysql_real_escape_string($foodle->getMaxDef()) . "', " . 
					"'" . mysql_real_escape_string($foodle->owner) . "', " . 
					"'" . ($foodle->allowanonymous ? '1' : '0') . "')
			";
			
		}
		
		#echo '<pre>'; echo $sql; exit;
		
		$res = mysql_query($sql, $this->db);
		
		
		if(mysql_error()){
			throw new Exception('Invalid query: <pre>' . $sql . '</pre>' . mysql_error());
		}
	}
	
	
	public function checkIdentifier($id) {
		$sql ="
			SELECT *
			FROM def WHERE id = '" . mysql_real_escape_string($id) . "'";
		
		$result = mysql_query($sql, $this->db);
		
		if(!$result)
			throw new Exception ("Could not successfully run query ($sql) fromDB:" . mysql_error());

		if(mysql_num_rows($result) > 0) return FALSE;
		return TRUE;
	}


	/* Parses maxdef row. Looks like this:
	 *   3:2 
	 */
	public static function parseMaxDef($string) {
		$result = array(NULL, NULL);
		if (empty($string)) return $result;
		$split = explode(':', $string);
		if (count($split) !== 2) return $result;
		if (!is_int($split[0])) return $result;
		if (!is_int($split[1])) return $result;
		
		$result[0] = (int) $split[0];
		$result[1] = (int) $split[1];
		
		return $result;
	}
	






	/*
	 * Collect all responses from a Foodle
	 */
	public function readResponses(Foodle $foodle) {
		
		$sql ="
			SELECT *, 
				UNIX_TIMESTAMP(created) AS createdu,
				UNIX_TIMESTAMP(updated) AS updatedu
			FROM entries
			WHERE foodleid='" . $foodle->identifier . "' order by updated desc, created desc";

		$result = mysql_query($sql, $this->db);
		
		if(!$result){
			throw new Exception ("Could not successfully run query ($sql) from DB:" . mysql_error());
		}
		
		$responses = array();
		
		if(mysql_num_rows($result) > 0){		
			while($row = mysql_fetch_assoc($result)){

				$newResponse = new FoodleResponse($this, $foodle);
				$newResponse->loadedFromDB = TRUE;
				$newResponse->userid = $row['userid'];
				$newResponse->username = $row['username'];
				$newResponse->email = $row['email'];
				$newResponse->notes = $row['notes'];
				$newResponse->updated = $row['updatedu'];
				$newResponse->created = $row['createdu'];
				
				#echo '<pre>'; print_r($row); #exit;
				
				
				if ($row['response'][0] == '{') {
					#echo 'Decoded resposne as json: <pre>' . $row['response'] . '</pre>';

					$newResponse->response = json_decode($row['response'], TRUE);
				} else {
					#echo 'Decoded resposne not as json: <pre>' . $row['response'] . '';
					#print_r($newResponse);
					#echo '</pre>';
					$newResponse->response = self::parseOldResponse($row['response']);
				}
				
				#$newResponse->icalfill();
				
				$responses[$row['userid']] = $newResponse;
			}
		}
		mysql_free_result($result);
		
		return $responses;
	}
	
	
	
	/*
	 * Collect all 
	 */
	public function readDiscussion(Foodle $foodle) {
		
		
		$sql ="
			SELECT *, UNIX_TIMESTAMP(created) AS createdu
			FROM discussion 
			WHERE foodleid = '" . $foodle->identifier . "'
			ORDER BY discussion.created DESC 
			";

#		echo 'sql: ' . $sql; exit;
		$result = mysql_query($sql, $this->db);
		
		if(!$result){
			throw new Exception ("Could not successfully run query ($sql) from DB:" . mysql_error());
		}
		
		$discussion = array();
		
		if(mysql_num_rows($result) > 0){		
			while($row = mysql_fetch_assoc($result)){
			
				$row['agotext'] = FoodleUtils::date_diff(time() - $row['createdu']);
				$discussion[] = $row;
				
			}
		}
		mysql_free_result($result);
		
		return $discussion;
	}
	
	public function addDiscussionEntry(Foodle $foodle, User $user, $message) {
		
		$sql = "
			INSERT INTO discussion (foodleid,username,message) values (
				'" . $foodle->identifier . "'," . 
				"'" . mysql_real_escape_string($user->name) . "', " . 
				"'" . mysql_real_escape_string($message) . "')";
		
		$res = mysql_query($sql, $this->db);
		
		if(mysql_error()){
			throw new Exception('Invalid query: ' . mysql_error());
		}	
	}
	
	

	/*
	 * Parses deprecated response format. Looked like:
	 * 		1,0,1,1,1,0,1
	 */
	public static function parseOldResponse($string) {
		$strarray = explode(',', $string);
		
		$result = array(
			'type' => 'response',
			'data' => $strarray,
		);
		return $result;
	}


	/*
	 * Add or update response to a foodle
	 */
	public function saveFoodleResponse(FoodleResponse $response) {
		
		$response->foodle->updateResponses($response);

		// $sql = "DELETE FROM entries WHERE foodleid = '" . $response->foodle->identifier. "' AND userid = '" . addslashes($response->userid) . "'";
		// mysql_query($sql, $this->db);
		// 

		if ($response->loadedFromDB) {
			$sql = "
				UPDATE entries SET 
					username = '" . addslashes($response->username) . "', 
					email = '" . addslashes($response->email) . "', 
					response = '" . $response->asJSON() . "', 
					notes = '" . addslashes($response->notes)  . "',
					updated = NOW()		
				WHERE foodleid = '" . $response->foodle->identifier. "' AND userid = '" . addslashes($response->userid) . "'
			";
			
		} else {
			$sql = "
				INSERT INTO entries (foodleid, userid, username, email, response, updated) values (
					'" . addslashes($response->foodle->identifier) . "',
					'" . addslashes($response->userid) . "', 
					'" . addslashes($response->username) . "', 
					'" . addslashes($response->email) . "', 
					'" . $response->asJSON() . "', now())";
			
		}
		
		#echo '<pre>'; echo $sql; exit;
		
		$res = mysql_query($sql, $this->db);
		
		
		if(mysql_error()){
			throw new Exception('Invalid query: ' . mysql_error());
		}
	}


	public function getStats() {
	
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

	public function getAllEntries($no = 20) {
				
		$sql ="
			SELECT * 
			FROM def 
			ORDER BY created DESC 
			LIMIT " . $no;
			
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


	public function getStatusUpdate(User $user, $foodleids, $no = 20) {
		
		$userid = $user->userid;
		
		$resarray = array();	
		$fidstr = "('" . join("', '", $foodleids) . "')"; 
		
		$sql ="
			SELECT entries.*,def.name 
			FROM entries, def 
			WHERE foodleid IN " . $fidstr . "
				and userid != '" . addslashes($userid) . "'
				and def.id = entries.foodleid
			ORDER BY entries.created DESC 
			LIMIT " . $no;

		$result = mysql_query($sql, $this->db);		
		if(!$result) throw new Exception ("Could not successfully run query ($sql) from DB:" . mysql_error());

		if(mysql_num_rows($result) > 0){		
			while($row = mysql_fetch_assoc($result)){
				$row['type'] = 'response';
				$resarray[$row['created']] = $row;
			}
		}		
		mysql_free_result($result);


		$sql ="
			SELECT discussion.*,def.name 
			FROM discussion, def 
			WHERE foodleid IN " . $fidstr . "
				and def.id = discussion.foodleid
			ORDER BY discussion.created DESC 
			LIMIT " . $no;

		$result = mysql_query($sql, $this->db);		
		if(!$result) throw new Exception ("Could not successfully run query ($sql) from DB:" . mysql_error());

		if(mysql_num_rows($result) > 0){		
			while($row = mysql_fetch_assoc($result)){
				$row['type'] = 'discussion';
				$resarray[$row['created']] = $row;
			}
		}		
		mysql_free_result($result);

		krsort($resarray);
		
		return $resarray;
	}
	
	public function getYourEntries(User $user) {

		$sql ="
			SELECT * 
			FROM entries,def 
			WHERE userid = '" . $user->userid . "' and entries.foodleid = def.id
			ORDER BY def.created DESC";

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
	
	public function getOwnerEntries(User $user, $no = 20) {
				
		$sql ="
			SELECT * 
			FROM def 
			WHERE owner = '" . addslashes($user->userid) . "'
			ORDER BY created DESC 
			LIMIT " . $no;

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

	
	
	
	

























// ---- o ---- o ---- o ---- o ---- o ---- o ---- o ---- o ---- o ---- o ---- o ---- o 




	
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
	


	
	
	

	

	


}

?>