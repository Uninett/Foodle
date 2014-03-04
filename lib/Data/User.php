<?php

/**
 * This class represents a user in Foodle
 */
class Data_User {

	// Public data fields
	public $userid, $username, $email, $org, $orgunit, $photol, $photom, $photos, $notifications, $features, $timezone, $location, $realm, $language, $role, $idp, $auth, $shaddow, $shaddowed = FALSE;
	
	// More complex structures are protected.
	protected $calendar;



	public $anonymous = TRUE;
	public $loadedFromDB = FALSE;
	
	protected $config;
	protected $sspconfig;
	
	public $db;
	
	function __construct(FoodleDBConnector $db) {
		$this->db = $db;
		
		$this->sspconfig = SimpleSAML_Configuration::getInstance();
		$this->config = SimpleSAML_Configuration::getInstance('foodle');
	}
	
	public function isAdmin() {
		
		if (empty($this->role)) return FALSE;
		if ($this->role === 'admin') return TRUE;
		
		return FALSE;	
	}
	

	public function getView() {

		$opts = array('userid', 'username', 'email', 'org', 'orgunit', 
			'photol', 'photom', 'photos', 'notifications', 'features', 'timezone', 
			'location', 'realm', 'language', 'role', 'idp', 'auth', 'shaddow', 'shaddowed');
		$user = array(
		);
		foreach($opts AS $o) {
			$user[$o] = $this->{$o};
		}

		return $user;


	}



	
	public function getResponseUsernameHTML($response) {
		$userid = $response->userid;
		$username = $response->username;
		
		if (isset($response->user)) {
			$userid = $response->user->userid;
			$username = $response->user->username;			
		}
		
		$includetoken = !$this->isAdmin();
		$nolink = !$this->loadedFromDB;
		
		return Data_User::getUsernameHTMLstatic($userid, $username, isset($response->user), $includetoken, $nolink);
	}
	
	public function getUsernameHTML($userid, $username, $hasprofile = FALSE) {
		
		$includetoken = !$this->isAdmin();
		$nolink = !$this->loadedFromDB;

		return self::getUsernameHTMLstatic($userid, $username, $hasprofile, $includetoken, $nolink);
	}
	
	public static function getUsernameHTMLstatic($userid, $username, $hasprofile = FALSE, $includeToken = TRUE, $nolink = FALSE) {

		$userpage = '/user/' . $userid;
		if ($includeToken) {
			$userpage .= '?token=' . Data_User::getUserToken($userid, 'profile');
		}
		
		$str = ''; 
		
		if ($hasprofile && !$nolink) {
			$str .= '<a href="' . htmlspecialchars($userpage)  . '"><img src="/res/user_grey.png" alt="User profile" />';
		}
	

		$str .= htmlspecialchars($username);
		
		if ($hasprofile && !$nolink) {
			$str .= '</a>';
		}
		
		if (isset($userid)) {
			$str = '<abbr title="' . htmlspecialchars($userid) . '">' . $str  . '</abbr>';
		}
		


		
		if (preg_match('|^@(.*)$|', $userid, $matches)) 
			$str .= ' (<a href="http://twitter.com/' . $matches[1] . '">' . $userid . '</a>)';
		
		return $str;
	}
	
	
	public function validateToken($token, $usage = NULL) {
		// error_log('Comparing input [' . $token . '] with correct [' . $this->getToken($usage) . '] usage [' . $usage . ']');
		return ($token === $this->getToken($usage));
	}
	
	public function getToken($usage = NULL) {
//		if ($this->anonymous) return null;
		return self::getUserToken($this->userid, $usage);
	}
	
	public static function getUserToken($userid, $usage = NULL) {
		$config = SimpleSAML_Configuration::getInstance('foodle');
		
		$str = $config->getString('secret') . '|' . $userid;
		if (!empty($usage)) $str .= '|' . $usage;
		return sha1($str);
	}
	
	public function getPhotoURL($size = 'm') {
		$basepath = $this->config->getPathValue('photodir');
		$basefilename = $this->getToken();
		
		$file  = $basepath . $basefilename . '-' . $size . '.jpeg';
		
		// error_log('Looking for file : ' . $file);
		
		if (!file_exists($file)) return FALSE;
		
		return FoodleUtils::getURL() . 'photo/' . $basefilename . '/' . $size;
	}
	
	public function getOrgHTML() {
		if (empty($this->org)) return '';
		
		$orgtext = array();
		if(!empty($this->org)) {
			$orgtext[] = '<span style="font-weight: bold">' . htmlspecialchars($this->org) . '</span>';
		}
		if(!empty($this->orgunit)) {
			$orgtext[] = htmlspecialchars($this->orgunit);
		}
		$orgtext = join('<br />', $orgtext);
		return $orgtext;
	}
	
	public function getPhotoPath($size = 'm') {
	
		$basepath = $this->config->getPathValue('photodir');
		$basefilename = sha1($this->config->getString('secret') . '|' . $this->userid);
		
		if (!in_array($size, array('s', 'm', 'l'))) throw new Exception('Invalid image size');
		
		return $basepath . $basefilename . '-' . $size . '.jpeg';
		
	}
	
	public function setPhoto($photo) {

		$basepath = $this->config->getPathValue('photodir');
		$basefilename = sha1($this->config->getString('secret') . '|' . $this->userid);
		
		$file_org  = $basepath . $basefilename . '-orig.jpeg';
		$file_large  = $basepath . $basefilename . '-l.jpeg';
		$file_medium = $basepath . $basefilename . '-m.jpeg';
		$file_small  = $basepath . $basefilename . '-s.jpeg';
		
		if (!file_exists($file_org)) {
			// error_log('Storing a new photo for use [' . $this->userid . ']');
			file_put_contents($file_org, base64_decode($photo));
		}
		
		if (!file_exists($file_large) || !file_exists($file_medium) || (!file_exists($file_small)) ) {
			list($width, $height) = getimagesize($file_org);
			$source = imagecreatefromjpeg($file_org);
			
			if ($source === FALSE) {
				// error_log('Image for user [' . $this->userid . '] was invalid format');
				return null;
			//		throw new Exception('Image from ');
			}
		}

		if (!file_exists($file_large)) {
			// error_log('Storing a new photo for use [' . $this->userid . '] large');
			$largeimage = imagecreatetruecolor(200, 200);
			imagecopyresampled($largeimage, $source, 0, 0, 0, 0, 200, 200, $width, $height);
			imagejpeg($largeimage, $file_large);
		}

		if (!file_exists($file_medium)) {
			// error_log('Storing a new photo for use [' . $this->userid . '] medium');
			$mediumimage = imagecreatetruecolor(64, 64);
			imagecopyresampled($mediumimage, $source, 0, 0, 0, 0, 64, 64, $width, $height);
			imagejpeg($mediumimage, $file_medium);
		}
		
		if (!file_exists($file_small)) {
			// error_log('Storing a new photo for use [' . $this->userid . '] small');
			$smallimage = imagecreatetruecolor(32, 32);
			imagecopyresampled($smallimage, $source, 0, 0, 0, 0, 32, 32, $width, $height);
			imagejpeg($smallimage, $file_small);
		}
// 		$smallimage = imagecreatetruecolor(64, 64);
// 		imagecopyresized($newimage, $source, 0, 0, 0, 0, 64, 64, $width, $height);
// 		imagejpeg($newimage);
		
		$this->photol = $basefilename;
	}
	
	public function notification($id, $default) {
		
		if (!is_array($this->notifications)) {
			return $default;
		}
		if (array_key_exists($id, $this->notifications)) return $this->notifications[$id];
		return $default;
	}
	
	public function setNotification($key, $value) {
		if (!is_array($this->notifications)) $this->notifications = array();
		$this->notifications[$key] = $value;
	}
	
	public function setCalendar($str) {
		$data = self::decode($str);
		
		if (is_array($data)) {
			$this->calendar = $data;
		} else {
			$this->calendar = array(array(
				'src' => $data,
				'type' => 'external',
				'include' => TRUE,
			));
		}
	}
	
	public function calendarURLexists($url) {
		if (empty($this->calendar)) return FALSE;
		foreach($this->calendar AS $c) {
			if ($c['src'] === $url) return TRUE;
		}
		return FALSE;	
	}
	
	public function addCalendarURL($url, $type = 'user', $include = true) {
		if ($this->calendarURLexists($url)) return;
		
		$this->calendar[] = array('type' => $type, 'src' => $url, 'include' => $include);
		
	}
	
	public function removeCalendarURL($url) {
		if (!$this->calendarURLexists($url)) return;
		foreach($this->calendar AS $key => $v) {
			if ($v['src'] === $url) {
				unset($this->calendar[$key]);
			}
		}
	}
	
	public function switchCalendarURL($url) {
		if (!$this->calendarURLexists($url)) return;
		foreach($this->calendar AS $key => $v) {
			if ($v['src'] === $url) {
				$this->calendar[$key]['include'] = !$this->calendar[$key]['include'];
			}
		}
	}
		
	
	public function setCalendarsExternal($calendars) {
			
		// error_log('setCalendarsExternal(): ' . var_export($calendars, TRUE));
			
		// Only continue if at least one calendar should be set
		if (empty($calendars)) return;
		
		
		
		// Initiliaize the calendar array.
		if (empty($this->calendar)) {
			$this->calendar = array();
		}
		
		// error_log('setCalendarsExternal() Ready...');


		// Setup an array of calendars to add.
		// 0: is not yet added.
		// 1: is already added.
		$toadd = array();
		foreach($calendars AS $n) {
			$toadd[$n] = 0;
		}
		
		// error_log('setCalendarsExternal() Setup ready... ' . var_export($toadd, TRUE));
		
		// error_log('Walk through : ' . var_export($this->calendar, TRUE));
		
		// Check which calendars already exists - and do not touch those.
		foreach($this->calendar AS $k => $v) {
			if ($this->calendar[$k]['type'] === 'external') {
				if (array_key_exists($this->calendar[$k]['src'], $toadd)) {
					$toadd[$this->calendar[$k]['src']] = 1;
					
					// error_log('Calendar [' . $this->calendar[$k]['src'] . '] is already set, and will not be touched');
					
				} else {
				
					// error_log('Calendar [' . $this->calendar[$k]['src'] . '] will be removed.');
				
					// Remove external calendars that is not scheduled to be added...
					unset($this->calendar[$k]);
				}
			} else {
				// error_log('Type was not external, skipping....');
			}
		}
		
		// Add the remaining (new) calendars
		foreach($toadd AS $k => $v) {
			if ($v === 1) continue; // Already added.
			
			$this->calendar[] = array(
				'src' => $k,
				'type' => 'external',
				'include' => TRUE,
			);
		}
		
		
	}

	public function hasCalendar() {
	
		if (empty($this->calendar)) {
			return FALSE;
		}
		
		foreach($this->calendar AS $c) {
			if ($c['include']) return TRUE;
		}
		return FALSE;
	}


	public function getCalendar() {
		return $this->calendar;
	}
	
	public function getSingleCalendar() {
		if (empty($this->calendar)) return NULL;
		if (empty($this->calendar[0]['src'])) throw new Exception('empty source URL for calendar: ' . var_export($this->calendar, TRUE));
		return $this->calendar[0]['src'];
	}
	
	public function getCalendarURLs($type = NULL) {
		
		$result = array();
		
		if (empty($this->calendar)) return NULL;
		
		foreach($this->calendar AS $c) {
			if (
				($type === NULL) || 
				($c['type'] === $type)
				) {
					
				$result[] = $c['src'];
					
			}
		}
		return $result;
	}
	
	public function getCalendarAggregator() {
		$a = new CalendarAggregator();
		
		if (!$this->hasCalendar()) return NULL;
		foreach($this->calendar AS $c) {
			if ($c['include']) $a->addCalendar($c['src']);
		}
		return $a;
		
	}
	
	public static function decode($s) {
		// Check if the string is not considered to be JSON, then just return the value.
		if (!in_array($s[0], array('{', '[', '"'))) {
			return $s;
		}
	
		if (empty($s)) return null;
		$parsed =  json_decode($s, TRUE);
		if ($parsed === NULL) throw new Exception('Could not decode JSON string [' . $s . ']');
		return $parsed;
	}
	
	public static function encode($s) {
		if (empty($s)) return '';
		return json_encode($s);
	}
	
	public function updateData($from) {
	
		$this->userid = strtolower($this->userid);
		$from->userid = strtolower($from->userid);

		if ($this->userid !== $from->userid) throw new Exception('Trying to update user with a mismatching user id');
		$modified = FALSE;
		
		if (!empty($from->username)) {
			if ($this->username !== $from->username) {
				// error_log('username from [' . $this->username. '] to [' . $from->username . ']');
				$modified = TRUE;
			}
			$this->username = $from->username;
		}
		if (!empty($from->email)) {
			if ($this->email !== $from->email) {
				// error_log('email from [' . $this->email. '] to [' . $from->email . ']');
				$modified = TRUE;
			}
			$this->email = $from->email;
		}
		
		if (!empty($from->org)) {
			if ($this->org !== $from->org) {
				// error_log('org from [' . $this->org. '] to [' . $from->org . ']');
				$modified = TRUE;
			}
			$this->org = $from->org;
		}
		
		if (!empty($from->orgunit)) {
			if ($this->orgunit !== $from->orgunit) {
				// error_log('orgunit from [' . $this->orgunit. '] to [' . $from->orgunit . ']');
				$modified = TRUE;
			}
			$this->orgunit = $from->orgunit;
		}
		
		if (!empty($from->location)) {
			if ($this->location !== $from->location) {
				// error_log('location from [' . $this->location. '] to [' . $from->location . ']');
				$modified = TRUE;
			}
			$this->location = $from->location;
		}
		
		if (!empty($from->realm)) {
			if ($this->realm !== $from->realm) {
				// error_log('Realm from [' . $this->realm. '] to [' . $from->realm . ']');
				$modified = TRUE;
			}
			$this->realm = $from->realm;
		}


		if (!empty($from->idp)) {
			if ($this->idp !== $from->idp) {
				// error_log('IdP entityid from [' . $this->idp. '] to [' . $from->idp . ']');
				$modified = TRUE;
			}
			$this->idp = $from->idp;
		}

		if (!empty($from->auth)) {
			if ($this->auth !== $from->auth) {
				// error_log('auth from [' . $this->auth. '] to [' . $from->auth . ']');
				$modified = TRUE;
			}
			$this->auth = $from->auth;
		}
		
		
		if (!empty($from->photol)) {
			if ($this->photol !== $from->photol) {
				// error_log('photo url from [' . $this->photol. '] to [' . $from->photol . ']');
				$modified = TRUE;
			}
			$this->photol = $from->photol;
		}

		// Calendar requires some special processing...
		if ($from->hasCalendar()) {
		
			$before = $this->getCalendar();
			$this->setCalendarsExternal($from->getCalendarURLs('external'));
			$after = $this->getCalendar();
			
			if ($before !== $after) {
				// error_log('Calendar from [' . var_export($before, TRUE). '] to [' . var_export($after, TRUE) . ']');
				$modified = TRUE;
			}
		}
		
		
		if (empty($this->timezone)) {
			$timezone = new TimeZone(NULL, $this);
			
			$newtimezone = $timezone->getTimezone();
			
			if (!empty($newtimezone)) {
				$this->timezone = $newtimezone;
				// error_log('User had no timezone set. Setting to [' . $newtimezone. ']');
				$modified = TRUE;
			}
		}
#		echo '<pre>'; print_r($from); exit;
#		// error_log('User set..');
		
		// TODO: photos
		// TODO: Calendar check...
		// Timezone not updated.
		// features and notidfications not updated.
		// Language is not updated...
		
		return $modified;
		
	}
	
	
	public static function requireValidUserid($userid) {
		if (!preg_match("/^[a-zA-Z0-9\-\+_!@\.:=\/]+$/", $userid)) {
		    throw new Exception('Invalid characters in userid provided [' . htmlspecialchars($userid) . '].');
		}
	}
	
	public static function debugfield($text, $value) {
		$text = '<dt>' . $text . '</dt><dd><tt>' . var_export($value, TRUE) . '</tt></dd>';
		return $text;
	}
	
	public function debug() {
		$text = '<dl>' .
			self::debugfield('User ID', $this->userid) . 
			self::debugfield('Name', $this->username) . 
			self::debugfield('E-mail', $this->email) . 
			self::debugfield('Calendar URL', var_export($this->getCalendar(), TRUE)) . '</dl>'
			;
		return $text;
	}
		
	public function debugCalendar() {
		$text = '';
		if ($this->hasCalendar() ) {
			
			$aggregator = $this->getCalendarAggregator();
			$fb = $aggregator->getFreeBusy();
			
			
			foreach($fb AS $f) {
			
				$text .= '<p>List of free busy times:</p><ul>';
				foreach($f AS $fe) {
					$text .= '<li>Busy from <i>' . date('r', $fe[0]). '</i> to <i>' . date('r', $fe[1]). '</i>.</li>';
				}
				$text .= '</ul>';
	#			$text .= '<p>Calenar output: ' . var_export($freebusy, TRUE) . '</p>';

			
			}
						
			
		} else {
			$text .= '<p>User has not enabled calendar</p>';
		}
		return $text;
	}
	
}
