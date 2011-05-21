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
	
		$begin = microtime(TRUE);
	
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
		
		$end = microtime(TRUE);
		
		$dur = $end - $begin;
		if ((float)$dur > 0.05)
			error_log(' :SQL: Query time : ' . number_format($dur, 6, '.', ' ') . '  ' . $sql);
		
		return $rows;
	}
	
	public function q1($sql, $field = null) {
		
		$rows = $this->q($sql, $field);
		if (count($rows) < 1) {
			throw new Exception('SQL query did not return any result: ' . $sql);
		}
		return $rows[0];
	}
	
	
	private function execute($sql) {

		$begin = microtime(TRUE);
		
		$result = mysql_query($sql, $this->db);
		if(!$result)
			throw new Exception ("Could not successfully run query ($sql) fromDB:" . mysql_error());
		
		$end = microtime(TRUE);
		
		$dur = $end - $begin;
		if ((float)$dur > 0.05)
			error_log(' :SQL: Execute Query time : ' . number_format($dur, 6, '.', ' ') . '  ' . $sql);
		
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
		
		$updates['responses'] = $this->readResponses($foodle, $ago, FALSE);
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
			IF(expire=0,null,UNIX_TIMESTAMP(expire)) AS expire_unix, 
			IF(created=0,null,UNIX_TIMESTAMP(created)) AS createdu, 
			IF(updated=0,null,UNIX_TIMESTAMP(updated)) AS updatedu 
			FROM def WHERE id = '" . mysql_real_escape_string($id) . "'";

		try {
			$row = $this->q1($sql);
		} catch(Exception $e) {
			throw new Exception('Could not lookup Foodle with id [' . $id . ']. May be it was deleted?');
		}
		
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
		
		$foodle->created = $row['createdu'];
		$foodle->updated = $row['updatedu'];
		
		$foodle->datetime = Data_Foodle::decode($row['datetime']);
		
		if (!empty($row['timezone'])) $foodle->timezone = $row['timezone'];
		
		
		if(self::isJSON($row['columns'][0])) {
			#echo 'Use new encoding format';
			$foodle->columns = json_decode($row['columns'], TRUE);
		} else {
			#echo 'Using old decoding.';
			$foodle->columns = FoodleUtils::parseOldColDef($row['columns']);
		}
		
		
		$maxdef = self::parseMaxDef($row['maxdef']);
		
		if ($maxdef[0]) {
			$foodle->maxentries = $maxdef[0];
			$foodle->maxcolumn = $maxdef[1];
		}
		
		$foodle->loadedFromDB = TRUE;
		
		return $foodle;
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
	
	
	public function lookupEmail($email) {
	
		$sql ="
			SELECT userid 
			FROM user WHERE email = '" . mysql_real_escape_string($email) . "'";
		$users = $this->q($sql, 'userid');
		
		if (count($users) > 0) return $users[0];
		return null;
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

		try {
			$row = $this->q1($sql);
		} catch(Exception $e) {	
			return false;
		}

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
		$user->role = $row['role'];
		$user->idp = $row['idp'];
		$user->auth = $row['auth'];

		$user->loadedFromDB = TRUE;

		return $user;

	}
	
	public function userExists($userid) {
		Data_User::requireValidUserid($userid);
		$sql ="
			SELECT userid
			FROM user WHERE userid = '" . mysql_real_escape_string($userid) . "'";

		$rows = $this->q($sql);

		if(count($rows) > 0) {
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
		
		if ($user->loadedFromDB) {
			error_log('FoodleDB: Updating user data');
			$sql = "
				UPDATE user SET " .
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
					self::sqlParameter('language', $user->language, 'null') . 
					self::sqlParameter('auth', $user->auth, 'null') . 
					self::sqlParameter('idp', $user->idp, 'null') . "
					updated = NOW()	
				WHERE userid = '" . $user->userid. "' 
			";
			
		} else {
			error_log('FoodleDB: Adding a new user');
			$sql = "
				INSERT INTO user (userid, username, email, org, orgunit, photol, photom, photos, notifications, features, calendar, timezone, location, realm, language, auth, idp) values (" . 
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
					self::sqlParameter('realm', $user->realm, 'null', FALSE) . 
					self::sqlParameter('language', $user->language, 'null', FALSE) . 
					self::sqlParameter('auth', $user->auth, 'null', FALSE) . 
					self::sqlParameter('idp', $user->idp, 'null', FALSE, FALSE) . 
					")
			";
			
		}
		
		$this->execute($sql);

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
					datetime = '" . mysql_real_escape_string(Data_Foodle::encode($foodle->datetime)) . "',
					updated = NOW()	
				WHERE id = '" . $foodle->identifier. "' 
			";
			
		} else {
			$sql = "
				INSERT INTO def (id, name, descr, columns, expire, maxdef,  owner, anon, timezone, columntype, responsetype, extrafields, datetime) values (" . 
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
					(isset($foodle->responsetype) ? "'" . mysql_real_escape_string($foodle->responsetype) . "'" : "'default'") . ", " .
					"'" . mysql_real_escape_string(Data_Foodle::encode($foodle->extrafields)) . "', " . 
					"'" . mysql_real_escape_string(Data_Foodle::encode($foodle->datetime)) . "'" . 
					")
			";
			
		}
		$this->execute($sql);

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
		
		$result = $this->q($sql);
		
		if(count($result) > 0) return FALSE;
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
	public function readResponses(Data_Foodle $foodle, $maxago = NULL, $includeInvites = TRUE) {
		
		$maxclause = '';
		if ($maxago !== null) {
			$maxclause = ' AND UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(entries.updated) < ' . mysql_real_escape_string($maxago) ;
		}
		if (!$includeInvites) {
			$maxclause = ' AND invitation = false ';
		}
		
		$sql ="
			SELECT entries.*, 
				UNIX_TIMESTAMP(entries.created) AS createdu,
				UNIX_TIMESTAMP(entries.updated) AS updatedu,
				user.userid AS profile
				FROM entries LEFT JOIN user ON (entries.userid = user.userid)
			WHERE foodleid='" . $foodle->identifier . "' " . $maxclause . "
			ORDER BY entries.invitation, entries.updated desc, entries.created desc";

		$rows = $this->q($sql);
		$responses = array();
		
		if(!empty($rows)){		
			foreach($rows AS $row) {

				$newResponse = new Data_FoodleResponse($this, $foodle);
				$newResponse->loadedFromDB = TRUE;
				$newResponse->userid = $row['userid'];
				$newResponse->username = $row['username'];
				$newResponse->email = $row['email'];
				$newResponse->notes = $row['notes'];
				$newResponse->updated = $row['updatedu'];
				$newResponse->created = $row['createdu'];
				
				$newResponse->hasprofile = (!empty($row['profile']));
				
				$ruser = $this->readUser($row['userid']);
				if ($ruser !== false) {
					$newResponse->user = $ruser;
				}
				
#				echo '<pre>'; print_r($row); #exit;
				
				$newResponse->invitation = (!empty($row['invitation']));
				
				if (empty($row['response'])) {
					$newResponse->response = NULL;
				} else if (self::isJSON($row['response'][0])) {
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

		$result = $this->q($sql);

		$discussion = array();
		
		if(!empty($result)){		
			foreach($result AS $row) {

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
		return $discussion;
	}
	
	public function addDiscussionEntry(Data_Foodle $foodle, Data_User $user, $message) {
		
		$sql = "
			INSERT INTO discussion (foodleid,userid, username,message) values (
				'" . $foodle->identifier . "'," . 
				"'" . mysql_real_escape_string($user->userid) . "', " . 
				"'" . mysql_real_escape_string($user->username) . "', " . 
				"'" . mysql_real_escape_string(utf8_decode($message)) . "')";
		
		$this->execute($sql);

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


	public function removeEmailInvites($foodleid, $email) {
		
		$sql = "DELETE FROM entries 
			WHERE foodleid = '" . mysql_real_escape_string($foodleid, $this->db) . "' AND 
				invitation = true AND
				email = '" .mysql_real_escape_string($email, $this->db) . "'";
		$this->execute($sql);
	}


	/*
	 * Add or update response to a foodle
	 */
	public function saveFoodleResponse(Data_FoodleResponse $response) {
		
		$response->foodle->updateResponses($response);

		// $sql = "DELETE FROM entries WHERE foodleid = '" . $response->foodle->identifier. "' AND userid = '" . addslashes($response->userid) . "'";
		// mysql_query($sql, $this->db);
		// 
		
		$invitation = ($response->invitation ? 'true' : 'false');

		if ($response->loadedFromDB) {
			$sql = "
				UPDATE entries SET 
					username = '" . addslashes($response->username) . "', 
					email = '" . addslashes($response->email) . "', 
					response = '" . $response->asJSON() . "', 
					notes = '" . addslashes($response->notes)  . "',
					invitation = " . $invitation . ",
					updated = NOW()		
				WHERE foodleid = '" . $response->foodle->identifier. "' AND userid = '" . addslashes($response->userid) . "'
			";
			
		} else {
			if (!empty($response->email)) $this->removeEmailInvites($response->foodle->identifier, $response->email);
			$sql = "
				INSERT INTO entries (foodleid, userid, username, email, invitation, response, updated) values (
					'" . addslashes($response->foodle->identifier) . "',
					'" . addslashes($response->userid) . "', 
					'" . addslashes($response->username) . "', 
					'" . addslashes($response->email) . "', 
					" . $invitation . ", 
					'" . $response->asJSON() . "', now())";
			
		}


		$this->execute($sql);

	}
	
	
	
	
	public function getIdPList() {
		$sql = "select distinct idp from user where idp is not null";
		return $this->q($sql, 'idp');
	}
	
	
	


	public function getStats() {
	
		$sql = 'select count(*) as total7days from (select UNIX_TIMESTAMP(now()) - UNIX_TIMESTAMP(created) as d from entries WHERE invitation = false having d < 7*60*60*24 ) as a';
		
		return $this->q1($sql);
	}
	
	public function getStatsRealm($recent = NULL) {
	
		$wh = '';
		if (!empty($recent)) {
			$wh = ' WHERE created > (NOW() - INTERVAL ' . (int) $recent . ' SECOND) ';
		}
		$sql = 'SELECT realm, count(*) c FROM user ' . $wh . ' GROUP BY realm ORDER BY c DESC';
		return $this->q($sql);
	}
	

	
	public function getRecentUsers($realm = NULL) {
	
		$wh = '';
		if (!empty($realm)) {
			$wh = ' WHERE realm = \'' . mysql_real_escape_string($realm, $this->db) . '\' ';
		}
		$sql = 'SELECT * FROM user ' . $wh . ' ORDER BY created DESC LIMIT 60';
		return $this->q($sql);
	}
	

	public function getAllEntries($no = 20) {

		$sql ="
			SELECT def.*, user.username ownername
			FROM def LEFT JOIN user ON (def.owner = user.userid)
			ORDER BY def.created DESC 
			LIMIT " . $no;
		
		return $this->q($sql);
	}

	public function getActivityStream(Data_User $user, $foodleids, $no = 20) {
		$statusupdates = $this->getStatusUpdate($user, $foodleids, $no);

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
				and entries.invitation = false
			ORDER BY entries.created DESC 
			LIMIT " . $no;

		$result = $this->q($sql);
		if(!empty($result)){		
			foreach($result AS $row) {
				$row['type'] = 'response';
				$resarray[$row['created']] = $row;
			}
		}		


		$sql ="
			SELECT discussion.*,def.name, UNIX_TIMESTAMP(discussion.created) AS unix
			FROM discussion, def 
			WHERE foodleid IN " . $fidstr . "
				and def.id = discussion.foodleid
			ORDER BY discussion.created DESC 
			LIMIT " . $no;

		$result = $this->q($sql);
		if(!empty($result)){		
			foreach($result AS $row) {

				$row['type'] = 'discussion';
				$resarray[$row['created']] = $row;
			}
		}		

		krsort($resarray);
		
		return $resarray;
	}
	
	public function getYourEntries(Data_User $user) {

		$sql ="
			SELECT entries.*, def.*, user.username ownername 
			FROM entries, def LEFT JOIN user ON (def.owner = user.userid)
			WHERE entries.userid = '" . $user->userid . "' and entries.foodleid = def.id 
			ORDER BY def.created DESC";

		$resarray = array();
		$result = $this->q($sql);
		if(!empty($result)){

			foreach($result AS $row) {
				$resarray[] = $row;
			}
		}		
		
		return $resarray;
	}
	
	public function getOwnerEntries(Data_User $user, $no = 20) {
				
		$sql ="
			SELECT * 
			FROM def 
			WHERE owner = '" . addslashes($user->userid) . "'
			ORDER BY created DESC 
			LIMIT " . $no;

		$resarray = array();
		$result = $this->q($sql);
		if(!empty($result)){		
			foreach($result AS $row) {
				$resarray[] = $row;
			}
		}		

		return $resarray;
	}



	public function getSharedEntries(Data_User $user1, Data_User $user2, $no = 20) {
		
		$sql ="
			SELECT def.id, def.name
FROM entries e1 JOIN entries e2 ON (e1.foodleid = e2.foodleid)
JOIN def ON (def.id = e1.foodleid)
WHERE 
e1.userid = '" . addslashes($user1->userid) . "' AND
e2.userid = '" . addslashes($user2->userid) . "'
ORDER BY e1.created DESC LIMIT " . $no;

		return $this->q($sql);
	}
	
	

// SELECT count(user.userid) c, user.username
// FROM entries e1 INNER JOIN entries e2 ON (e1.foodleid = e2.foodleid) JOIN user ON (e2.userid = user.userid)
// WHERE e1.userid = 'hatlen@hit.no' AND e2.userid != 'hatlen@hit.no'
// GROUP BY user.userid 
// ORDER BY c desc, user.username;
	

	public function getContacts(Data_User $user) {

		$sql ="
SELECT count(user.userid) c, user.userid, user.email, user.username
FROM entries e1 INNER JOIN entries e2 ON (e1.foodleid = e2.foodleid) JOIN user ON (e2.userid = user.userid)
WHERE e1.userid = '" . addslashes($user->userid) . "' AND e2.userid != '" . addslashes($user->userid) . "'
GROUP BY user.userid
ORDER BY c desc";

		return $this->q($sql);
		
	}



























}

