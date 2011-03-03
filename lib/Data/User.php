<?php

/**
 * This class represents a user in Foodle
 */
class Data_User {

	public $userid, $username, $email, $org, $orgunit, $photol, $photom, $photos, $notifications, $features, $calendar, $timezone, $location, $realm, $language, $role, $idp;


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
	
	
	
	public function getToken($usage = NULL) {
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
			error_log('Storing a new photo for use [' . $this->userid . ']');
			file_put_contents($file_org, base64_decode($photo));
		}
		
		if (!file_exists($file_large) || !file_exists($file_medium) || (!file_exists($file_small)) ) {
			list($width, $height) = getimagesize($file_org);
			$source = imagecreatefromjpeg($file_org);
			
			if ($source === FALSE) {
				error_log('Image for user [' . $this->userid . '] was invalid format');
				return null;
			//		throw new Exception('Image from ');
			}
		}

		if (!file_exists($file_large)) {
			error_log('Storing a new photo for use [' . $this->userid . '] large');
			$largeimage = imagecreatetruecolor(200, 200);
			imagecopyresampled($largeimage, $source, 0, 0, 0, 0, 200, 200, $width, $height);
			imagejpeg($largeimage, $file_large);
		}

		if (!file_exists($file_medium)) {
			error_log('Storing a new photo for use [' . $this->userid . '] medium');
			$mediumimage = imagecreatetruecolor(64, 64);
			imagecopyresampled($mediumimage, $source, 0, 0, 0, 0, 64, 64, $width, $height);
			imagejpeg($mediumimage, $file_medium);
		}
		
		if (!file_exists($file_small)) {
			error_log('Storing a new photo for use [' . $this->userid . '] small');
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
	
	public static function decode($s) {
		if (empty($s)) return null;
		return json_decode($s, TRUE);
	}
	
	public static function encode($s) {
		if (empty($s)) return '';
		return json_encode($s);
	}
	
	public function updateData($from) {

		if ($this->userid !== $from->userid) throw new Exception('Trying to update user with a mismatching user id');
		$modified = FALSE;
		
		if (!empty($from->username)) {
			if ($this->username !== $from->username) {
				error_log('username from [' . $this->username. '] to [' . $from->username . ']');
				$modified = TRUE;
			}
			$this->username = $from->username;
		}
		if (!empty($from->email)) {
			if ($this->email !== $from->email) {
				error_log('email from [' . $this->email. '] to [' . $from->email . ']');
				$modified = TRUE;
			}
			$this->email = $from->email;
		}
		
		if (!empty($from->org)) {
			if ($this->org !== $from->org) {
				error_log('org from [' . $this->org. '] to [' . $from->org . ']');
				$modified = TRUE;
			}
			$this->org = $from->org;
		}
		
		if (!empty($from->orgunit)) {
			if ($this->orgunit !== $from->orgunit) {
				error_log('orgunit from [' . $this->orgunit. '] to [' . $from->orgunit . ']');
				$modified = TRUE;
			}
			$this->orgunit = $from->orgunit;
		}
		
		if (!empty($from->location)) {
			if ($this->location !== $from->location) {
				error_log('location from [' . $this->location. '] to [' . $from->location . ']');
				$modified = TRUE;
			}
			$this->location = $from->location;
		}
		
		if (!empty($from->realm)) {
			if ($this->realm !== $from->realm) {
				error_log('Realm from [' . $this->realm. '] to [' . $from->realm . ']');
				$modified = TRUE;
			}
			$this->realm = $from->realm;
		}


		if (!empty($from->idp)) {
			if ($this->idp !== $from->idp) {
				error_log('IdP entityid from [' . $this->idp. '] to [' . $from->idp . ']');
				$modified = TRUE;
			}
			$this->idp = $from->idp;
		}
		
		if (empty($this->timezone)) {
			$timezone = new TimeZone(NULL, $this);
			
			$newtimezone = $timezone->getTimezone();
			
			if (!empty($newtimezone)) {
				$this->timezone = $newtimezone;
				error_log('User had no timezone set. Setting to [' . $newtimezone. ']');
				$modified = TRUE;
			}
		}
#		echo '<pre>'; print_r($from); exit;
#		error_log('User set..');
		
		// TODO: photos
		// TODO: Calendar check...
		// Timezone not updated.
		// features and notidfications not updated.
		// Language is not updated...
		
		return $modified;
		
	}
	
	public function hasCalendar() {
		return ($this->calendar !== NULL);
	}
	
	public static function requireValidUserid($userid) {
		if (!preg_match("/^[a-zA-Z0-9\-_!@\.]+$/", $userid)) {
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
			self::debugfield('Calendar URL', $this->calendar) . '</dl>'
			;
		return $text;
	}
		
	public function debugCalendar() {
		$text = '';
		if ($this->hasCalendar() ) {
			
			$cal = new Calendar($this->calendar, TRUE);
			$freebusy = $cal->getFreeBusy();
			
			$text .= '<p>List of free busy times:</p><ul>';
			foreach($freebusy AS $fb) {
				$text .= '<li>Busy from <i>' . date('r', $fb[0]). '</i> to <i>' . date('r', $fb[1]). '</i>.</li>';
			}
			$text .= '</ul>';
#			$text .= '<p>Calenar output: ' . var_export($freebusy, TRUE) . '</p>';
			
		} else {
			$text .= '<p>User has not enabled calendar</p>';
		}
		return $text;
	}
	
}
