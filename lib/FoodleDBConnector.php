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
	
	public function q($sql, $field = null) {
		$rows = array();
		$result = mysql_query($sql, $this->db);
		if(!$result)
			throw new Exception ("Could not successfully run query ($sql) fromDB:" . mysql_error());
		while($row = mysql_fetch_assoc($result)){
			if ($field === null) {
				$rows[] = $row;
			} else {
				if (isset($row[$field])) $rows[] = $row[$field];
			}
		}
		return $rows;
	}
	
	
	public function getChangesOwners($ago = 86400) {
		
		$owners = array();
		$owners1 = $this->q('
			SELECT distinct def.owner, entries.foodleid
			from entries, def 
			where UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(entries.updated) < ' . $ago . ' AND
			entries.foodleid = def.id
			order by entries.updated desc ');
		
		$owners2 = $this->q('
			SELECT distinct def.owner, discussion.foodleid
			from discussion, def 
			where UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(discussion.created) < ' . $ago . ' AND
			discussion.foodleid = def.id');
	
		foreach($owners1 AS $o) $owners[$o['owner']][$o['foodleid']] = 1;
		foreach($owners2 AS $o) $owners[$o['owner']][$o['foodleid']] = 1;

#		print_r($owners);

		$po = array();
		foreach($owners AS $ow => $v) {
			$n = array();
			foreach($v AS $f => $d) {
				$n[] = $this->readFoodle($f);
			}
			$po[$ow] = $n;
		}

		
#		print_r($po);
	
		return $po;
	}
	
	public function getChangesFoodle($foodle, $ago = 86400) {
	
		$updates = array(
			'responses' => array(),
			'discussion' => array(),
		);
		
		$updates['responses'] = $this->readResponses($foodle, $ago);
		$updates['discussion'] = $this->readDiscussion($foodle, $ago);
		
#		print_r($updates);
		return $updates;
	}

	/*
	 * Loads a foodle with a given $id from database, as a Foodle object.
	 */
	public function readFoodle($id) {
		Data_Foodle::requireValidIdentifier($id);
		$sql ="
			SELECT *,
			IF(expire=0,null,UNIX_TIMESTAMP(expire)) AS expire_unix 
			FROM def WHERE id = '" . mysql_real_escape_string($id) . "'";
		
		$result = mysql_query($sql, $this->db);
		
		if(!$result)
			throw new Exception ("Could not successfully run query ($sql) fromDB:" . mysql_error());

		if(mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			
			// echo '<pre>'; print_r($row); echo '</pre>'; exit;
			
			$foodle = new Data_Foodle($this);
			$foodle->identifier = $id;
			$foodle->name = $row['name'];
			$foodle->descr = stripslashes($row['descr']);
			$foodle->expire = $row['expire_unix'];
			$foodle->owner = $row['owner'];
			$foodle->allowanonymous = (boolean) ($row['anon'] == '1');
			$foodle->columntype = isset($row['columntype']) ? $row['columntype'] : null;
			$foodle->responsetype = isset($row['responsetype']) ? $row['responsetype'] : 'default';
			$foodle->extrafields = Data_Foodle::decode($row['extrafields']);
			
			if (!empty($row['timezone'])) $foodle->timezone = $row['timezone'];
			
			
			if(self::isJSON($row['columns'][0])) {
				#echo 'Use new encoding format';
				$foodle->columns = json_decode($row['columns'], TRUE);
			} else {
				#echo 'Using old decoding.';
				$foodle->columns = FoodleUtils::parseOldColDef($row['columns']);
			}
			
			#echo '<pre>'; print_r($foodle->columns); echo '</pre>'; exit;
			

			
			$maxdef = self::parseMaxDef($row['maxdef']);
			#echo '<pre>Maxdef: ' . $row['maxdef']; echo "\n"; print_r( $maxdef); exit;
			
			if ($maxdef[0]) {
				$foodle->maxentries = $maxdef[0];
				$foodle->maxcolumn = $maxdef[1];
			}
			
			$foodle->loadedFromDB = TRUE;
			mysql_free_result($result);
			
			#echo '<pre>'; print_r($row); exit;
			
			return $foodle;
		} 
		
		throw new Exception('Could not find foodle in database with id ' . $id);
	}
	

	public static function sqlParameter($name, $value, $default = null, $includeName = TRUE, $addcomma = TRUE) {
		$comma = '';
		if($addcomma) $comma = ',';
		
		$namest = '';
		if ($includeName) $namest =  $name . " = ";
		
		if (!empty($value)) {
			return $namest . "'" . mysql_real_escape_string($value) . "'" . $comma . " \n";
		} 
		if (empty($default)) {
			throw new Exception('Cannot create SQL statement for attribute [' . $name . '] where the value is empty and there is no default value');
		}
		return $namest . $default . $comma . " \n";
	}
	

	public function readUser($userid) {
		/*
			| userid        | varchar(100) | NO   | PRI |         |       |
			| username      | tinytext     | YES  |     | NULL    |       |
			| email         | tinytext     | YES  |     | NULL    |       |
			| org           | tinytext     | YES  |     | NULL    |       |
			| orgunit       | tinytext     | YES  |     | NULL    |       |
			| photol        | text         | YES  |     | NULL    |       |
			| photom        | text         | YES  |     | NULL    |       |
			| photos        | text         | YES  |     | NULL    |       |
			| notifications | text         | YES  |     | NULL    |       |
			| features      | text         | YES  |     | NULL    |       |
			| calendar      | text         | YES  |     | NULL    |       |
			| timezone      | tinytext     | YES  |     | NULL    |       |
			| location      | tinytext     | YES  |     | NULL    |       |
			| realm         | tinytext     | YES  |     | NULL    |       |
				language
			
			userid, username, email, org, orgunit, photol, photom, photos, notifications, features, calendar, timezone, location, realm, language
		*/
		Data_User::requireValidUserid($userid);
		$sql ="
			SELECT * 
			FROM user WHERE userid = '" . mysql_real_escape_string($userid) . "'";
		
		$result = mysql_query($sql, $this->db);
		
		if(!$result)
			throw new Exception ("Could not successfully run query ($sql) fromDB:" . mysql_error());

		if(mysql_num_rows($result) > 0) {
			$row = mysql_fetch_assoc($result);
			
			// echo '<pre>'; print_r($row); echo '</pre>'; exit;
			
			$user = new Data_User($this);
			$user->userid = $row['userid'];
			$user->username = $row['username'];
			$user->email = $row['email'];
			$user->org = $row['org'];
			$user->orgunit = $row['orgunit'];
			$user->photol = $row['photol'];
			$user->photom = $row['photom'];
			$user->photos = $row['photos'];
			$user->notifications = Data_User::decode($row['notifications']);
			$user->features = Data_User::decode($row['features']);
			$user->calendar = $row['calendar'];
			$user->timezone = $row['timezone'];
			$user->location = $row['location'];
			$user->realm = $row['realm'];
			$user->language = $row['language'];

			$user->loadedFromDB = TRUE;
			mysql_free_result($result);
			
			#echo '<pre>'; print_r($row); exit;
			
			return $user;
		} 
		return false;
		//throw new Exception('Could not find user in database with username ' . $userid);
	}
	
	public function userExists($userid) {
		Data_User::requireValidUserid($userid);
		$sql ="
			SELECT userid
			FROM user WHERE userid = '" . mysql_real_escape_string($userid) . "'";
		
		$result = mysql_query($sql, $this->db);
		
		if(!$result)
			throw new Exception ("Could not successfully run query ($sql) fromDB:" . mysql_error());

		if(mysql_num_rows($result) > 0) {
			return TRUE;
		}
		
		return FALSE;
	
	}

	public function saveUser(Data_User $user) {
		/*
			| userid      | varchar(100) | NO   | PRI |         |       |
			| username          | tinytext     | YES  |     | NULL    |       |
			| email         | tinytext     | YES  |     | NULL    |       |
			| org           | tinytext     | YES  |     | NULL    |       |
			| orgunit       | tinytext     | YES  |     | NULL    |       |
			| photol        | text         | YES  |     | NULL    |       |
			| photom        | text         | YES  |     | NULL    |       |
			| photos        | text         | YES  |     | NULL    |       |
			| notifications | text         | YES  |     | NULL    |       |
			| features      | text         | YES  |     | NULL    |       |
			| calendar      | text         | YES  |     | NULL    |       |
			| timezone      | tinytext     | YES  |     | NULL    |       |
			| location      | tinytext     | YES  |     | NULL    |       |
			| realm         | tinytext     | YES  |     | NULL    |       |
				language
			
			userid, , username, email, org, orgunit, photol, photom, photos, notifications, features, calendar, timezone, location, realm, language
		*/
		
		
#		print_r($user->notifications); exit;
		
		if ($user->loadedFromDB) {
			error_log('FoodleDB: Updating user data');
			$sql = "
				UPDATE user SET " .
#					self::sqlParameter('userid', $user->username) . 
					self::sqlParameter('username', $user->username, 'null') . 
					self::sqlParameter('email', $user->email, 'null') . 
					self::sqlParameter('org', $user->org, 'null') . 
					self::sqlParameter('orgunit', $user->orgunit, 'null') .
					self::sqlParameter('photol', $user->photol, 'null') . 
					self::sqlParameter('photom', $user->photom, 'null') . 
					self::sqlParameter('photos', $user->photos, 'null') . 
					self::sqlParameter('notifications', Data_User::encode($user->notifications), 'null') . 
					self::sqlParameter('features', Data_User::encode($user->features), 'null') . 
					self::sqlParameter('calendar', $user->calendar, 'null') . 
					self::sqlParameter('timezone', $user->timezone, 'null') . 
					self::sqlParameter('location', $user->location, 'null') . 

					self::sqlParameter('realm', $user->realm, 'realm') . 
					self::sqlParameter('language', $user->language, 'null') . "
					updated = NOW()	
				WHERE userid = '" . $user->userid. "' 
			";
			
		} else {
			error_log('FoodleDB: Adding a new user');
			$sql = "
				INSERT INTO user (userid, username, email, org, orgunit, photol, photom, photos, notifications, features, calendar, timezone, location, realm, language) values (" . 
					self::sqlParameter('userid', $user->userid, null, FALSE) . 
					self::sqlParameter('username', $user->username, 'null', FALSE) . 
					self::sqlParameter('email', $user->email, 'null', FALSE) . 
					self::sqlParameter('org', $user->org, 'null', FALSE) . 
					self::sqlParameter('orgunit', $user->orgunit, 'null', FALSE) .
					self::sqlParameter('photol', $user->photol, 'null', FALSE) . 
					self::sqlParameter('photom', $user->photom, 'null', FALSE) . 
					self::sqlParameter('photos', $user->photos, 'null', FALSE) . 
					self::sqlParameter('notifications', Data_User::encode($user->notifications), 'null', FALSE) . 
					self::sqlParameter('features', Data_User::encode($user->features), 'null', FALSE) . 
					self::sqlParameter('calendar', $user->calendar, 'null', FALSE) . 
					self::sqlParameter('timezone', $user->timezone, 'null', FALSE) . 
					self::sqlParameter('location', $user->location, 'null', FALSE) . 
					self::sqlParameter('realm', $user->realm, 'realm', FALSE) . 
					self::sqlParameter('language', $user->language, 'null', FALSE, FALSE) . ")
			";
			
		}
		
		#echo '<pre>'; echo $sql; exit;
		$res = mysql_query($sql, $this->db);
		
		if(mysql_error()){
			throw new Exception('Invalid query: <pre>' . $sql . '</pre>' . mysql_error());
		}
	}



	
	public function saveFoodle(Data_Foodle $foodle) {
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
		*/
		
		
		
		if ($foodle->loadedFromDB) {
			$sql = "
				UPDATE def SET 
					name = '" . mysql_real_escape_string($foodle->name) . "', 
					descr = '" . mysql_real_escape_string($foodle->descr) . "', 
					columns = '" . mysql_real_escape_string(json_encode($foodle->columns))  . "',
					expire = '" . mysql_real_escape_string($foodle->expire) . "',
					maxdef = '" . mysql_real_escape_string($foodle->getMaxDef()) . "',
					anon = '" . ($foodle->allowanonymous ? '1' : '0') . "',
					columntype = " . (isset($foodle->columntype) ? "'" . mysql_real_escape_string($foodle->columntype) . "'" : 'null') . ",
					responsetype = " . (isset($foodle->responsetype) ? "'" . mysql_real_escape_string($foodle->responsetype) . "'" : "'default'") . ",
					timezone = '" . mysql_real_escape_string($foodle->getTimeZone()) . "',
					extrafields = '" . mysql_real_escape_string(Data_Foodle::encode($foodle->extrafields)) . "',
					updated = NOW()	
				WHERE id = '" . $foodle->identifier. "' 
			";
			
		} else {
			$sql = "
				INSERT INTO def (id, name, descr, columns, expire, maxdef,  owner, anon, timezone, columntype, responsetype, extrafields) values (" . 
					"'" . mysql_real_escape_string($foodle->identifier) . "'," . 
					"'" . mysql_real_escape_string($foodle->name) . "', " . 
					"'" . mysql_real_escape_string($foodle->descr) . "', " . 
					"'" . mysql_real_escape_string(json_encode($foodle->columns)) . "', " . 
					"'" . $foodle->expire . "', " . 
					"'" . mysql_real_escape_string($foodle->getMaxDef()) . "', " . 
					"'" . mysql_real_escape_string($foodle->owner) . "', " . 
					"'" . ($foodle->allowanonymous ? '1' : '0') . "', " . 
					"'" . mysql_real_escape_string($foodle->getTimeZone()) . "', " . 
					(isset($foodle->columntype) ? "'" . mysql_real_escape_string($foodle->columntype) . "'" : 'null') . ", " .
					(isset($foodle->responsetype) ? "'" . mysql_real_escape_string($foodle->responsetype) . "'" : "'default'") .
					"'" . mysql_real_escape_string(Data_Foodle::encode($foodle->extrafields)) . "'" . 
					")
			";
			
		}
		
		#echo '<pre>'; echo $sql; exit;
		$res = mysql_query($sql, $this->db);
		
		if(mysql_error()){
			throw new Exception('Invalid query: <pre>' . $sql . '</pre>' . mysql_error());
		}
	}
	
	private function execute($sql) {
	
		# echo '<pRE>SQL: ' . $sql . "\n\n" ; return;
		$result = mysql_query($sql, $this->db);
		if(!$result)
			throw new Exception ("Could not successfully run query ($sql) fromDB:" . mysql_error());
		return mysql_num_rows($result);
	}
	
	public function deleteFoodle(Data_Foodle $foodle) {
	
		if (empty($foodle)) throw new Exception('deleteFoodle() not provided with a foodle to delete');
		if (empty($foodle->identifier)) throw new Exception('deleteFoodle() asked to delete a foodle with an empty identifier');
		
		$this->execute("DELETE FROM discussion WHERE foodleid = '" . mysql_real_escape_string($foodle->identifier) . "'");
		$this->execute("DELETE FROM entries WHERE foodleid = '" . mysql_real_escape_string($foodle->identifier) . "'");
		$this->execute("DELETE FROM def WHERE id = '" . mysql_real_escape_string($foodle->identifier) . "'");
	
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
		if (!is_int((int)$split[0])) return $result;
		if (!is_int((int)$split[1])) return $result;
		
		$result[0] = (int) $split[0];
		$result[1] = (int) $split[1];
		
		return $result;
	}
	



	/*
	 * Collect all responses calendar urls
	 */
	public function getCalendarURLs() {
		
		$urls = array();
		
		$sql ="
			SELECT response, 
				UNIX_TIMESTAMP(created) AS createdu,
				UNIX_TIMESTAMP(updated) AS updatedu
			FROM entries
			ORDER BY updated desc, created desc
			LIMIT 2000
			";

		$result = mysql_query($sql, $this->db);
		if(!$result){
			throw new Exception ("Could not successfully run query ($sql) from DB:" . mysql_error());
		}
		if(mysql_num_rows($result) > 0){		
			while($row = mysql_fetch_assoc($result)){
				if (self::isJSON($row['response'][0])) {
					#echo 'Decoded resposne as json: <pre>' . $row['response'] . '</pre>';
					
					$response = json_decode($row['response'], TRUE);
					
					if($response['type'] !== 'ical') continue;
					$urls[$response['calendarURL']] = 1;
				}
			}
		}
		mysql_free_result($result);
		
		return array_keys($urls);
	}



	/*
	 * Collect all responses from a Foodle
	 */
	public function readResponses(Data_Foodle $foodle, $maxago = NULL) {
		
// SELECT entries.*, user.username, user.email, user.org, user.orgunit, user.photol, user.location, user.realm,
// UNIX_TIMESTAMP(created) AS createdu,
// UNIX_TIMESTAMP(updated) AS updatedu
// FROM entries
// WHERE foodleid='Tester-kalender-i-fremtid-4ca1d' order by updated desc, created desc;
		
		$maxclause = '';
		if ($maxago !== null) {
			$maxclause = ' AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(updated) < ' . mysql_real_escape_string($maxago) ;
		}
		
		$sql ="
			SELECT *, 
				UNIX_TIMESTAMP(created) AS createdu,
				UNIX_TIMESTAMP(updated) AS updatedu
			FROM entries
			WHERE foodleid='" . $foodle->identifier . "' " . $maxclause . "
			ORDER BY updated desc, created desc";

		$result = mysql_query($sql, $this->db);
		
		if(!$result){
			throw new Exception ("Could not successfully run query ($sql) from DB:" . mysql_error());
		}
		
		$responses = array();
		
		if(mysql_num_rows($result) > 0){		
			while($row = mysql_fetch_assoc($result)){

				$newResponse = new Data_FoodleResponse($this, $foodle);
				$newResponse->loadedFromDB = TRUE;
				$newResponse->userid = $row['userid'];
				$newResponse->username = $row['username'];
				$newResponse->email = $row['email'];
				$newResponse->notes = $row['notes'];
				$newResponse->updated = $row['updatedu'];
				$newResponse->created = $row['createdu'];
				
				$ruser = $this->readUser($row['userid']);
				if ($ruser !== false) {
					$newResponse->user = $ruser;
				}
				
				#echo '<pre>'; print_r($row); #exit;
				
				
				if (self::isJSON($row['response'][0])) {
					#echo 'Decoded resposne as json: <pre>' . $row['response'] . '</pre>';

					$newResponse->response = json_decode($row['response'], TRUE);
				} else {
					#echo 'Decoded resposne not as json: <pre>' . $row['response'] . '';
					#print_r($newResponse);
					#echo '</pre>';
					$newResponse->response = self::parseOldResponse($row['response']);
				}
				
				#$newResponse->icalfill();
				
				$nof = $foodle->getNofColumns();
				if ($newResponse->response['type'] == 'manual' && count($newResponse->response['data']) !== $nof) {
						
					$newResponse->invalid = TRUE;
					if (count($newResponse->response['data']) < $nof) {
						$remaining = $nof - count($newResponse->response['data']);
						for($i = 0; $i < $remaining; $i++) {
							$newResponse->response['data'][] = NULL;
						}
					}
					if (count($newResponse->response['data']) > $nof) {
						$newResponse->response['data'] = array_slice($newResponse->response['data'], 0, $nof);
					}
					//	echo '<pre>'; print_r($newResponse); exit;
				}
				
				$responses[$row['userid']] = $newResponse;
			}
		}
		mysql_free_result($result);
		
		return $responses;
	}
	
	public static function isJSON($text) {
		if ($text[0] == '[') return TRUE;
		if ($text[0] == '{') return TRUE;
		return FALSE;
	}
	
	
	/*
	 * Collect all 
	 */
	public function readDiscussion(Data_Foodle $foodle, $maxago = null) {
		
		$maxclause = '';
		if ($maxago !== null) {
			$maxclause = ' AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(created) < ' . mysql_real_escape_string($maxago) ;
		}


		$sql ="
			SELECT *, UNIX_TIMESTAMP(created) AS createdu
			FROM discussion 
			WHERE foodleid = '" . $foodle->identifier . "' " . $maxclause . "
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
				
				
				try {
					if (!empty($row['userid'])) {
						$ruser = $this->readUser($row['userid']);
						if ($ruser !== false) {
							$row['user'] = $ruser;
						}
					}
				} catch(Exception $e) {}

				
				$row['agotext'] = FoodleUtils::date_diff(time() - $row['createdu']);
				$discussion[] = $row;
				
			}
		}
		mysql_free_result($result);
		
		return $discussion;
	}
	
	public function addDiscussionEntry(Data_Foodle $foodle, Data_User $user, $message) {
		
		$sql = "
			INSERT INTO discussion (foodleid,userid, username,message) values (
				'" . $foodle->identifier . "'," . 
				"'" . mysql_real_escape_string($user->userid) . "', " . 
				"'" . mysql_real_escape_string($user->username) . "', " . 
				"'" . mysql_real_escape_string(utf8_decode($message)) . "')";
		
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
	public function saveFoodleResponse(Data_FoodleResponse $response) {
		
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

	public function getActivityStream(Data_User $user, $foodleids, $no = 20) {
		$statusupdates = $this->getStatusUpdate($user, $foodleids, $no);
#		print_r($statusupdates);
		$stream = new Data_ActivityStream($this);
		$stream->activity = $statusupdates;
		
		return $stream->compact();
	}

	protected function getStatusUpdate(Data_User $user, $foodleids, $no = 100) {
		
		$userid = $user->userid;
		
		$resarray = array();	
		$fidstr = "('" . join("', '", $foodleids) . "')"; 
		
		$sql ="
			SELECT entries.*,def.name, UNIX_TIMESTAMP(IFNULL(entries.updated, entries.created)) AS unix
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
			SELECT discussion.*,def.name, UNIX_TIMESTAMP(discussion.created) AS unix
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
	
	public function getYourEntries(Data_User $user) {

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
	
	public function getOwnerEntries(Data_User $user, $no = 20) {
				
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

