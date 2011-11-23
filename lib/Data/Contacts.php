<?php

/**
 * This class represents Contacts in Foodle
 */
class Data_Contacts {

	const CACHETIME = 3600; // 60 minutes
	
	public $contacts = null;
	protected $fdb;
	protected $user;
	
	protected $store;

	public function __construct(FoodleDBConnector $fdb, $user) {
		$this->fdb = $fdb;
		$this->user = $user;
		
		$this->store = new sspmod_core_Storage_SQLPermanentStorage('foodle_contacts');
	}

	public function load() {
		if ($this->contacts === null) {
		
			if ($this->store->exists('contacts', $this->user->userid, NULL)) {
				// error_log('Contacts: Found contact list for user  [' . $this->user->userid . '] in cache.');
				$this->contacts = $this->store->getValue('contacts', $this->user->userid, NULL);
				return;
			}
			
			$newcontacts = $this->fdb->getContacts($this->user);
			$this->contacts = array();
			
			foreach($newcontacts AS $k => $v) {
				$this->contacts[$v['userid']] = array('userid' => $v['userid']);
				if(!empty($v['username'])) $this->contacts[$v['userid']]['name'] = $v['username'];
				if(!empty($v['email'])) $this->contacts[$v['userid']]['email'] = $v['email'];

				$this->contacts[$v['userid']]['key'] = sha1($v['userid']);
			}
			
			$this->store->set('contacts', $this->user->userid, NULL, $this->contacts, self::CACHETIME);
			
		}
		
	}
	
	public function exclude($foodleid) {
		$this->load();
		$foodle = $this->fdb->readFoodle($foodleid);
		$responses = $foodle->getResponses();
		
		$i = 0;
		foreach($responses AS $userid => $response ) {
			if (array_key_exists($userid, $this->contacts))  {
				unset($this->contacts[$userid]);
				$i++;
			}
		}

		$left = count($this->contacts);
		// error_log('Excluded ' . $i . ' entries from contacts, because part of Foodle ' . $foodleid . '  (' . $left . ' left)');
	
	}
	
	
	public function search($term) {
		$this->load();
		$res = array();
		$term = strtolower($term);
		foreach($this->contacts AS $c) {
			
			if (count($res) > 12) break;
			
			if (strpos(strtolower($c['name']), $term) !== false) {
				$res[] = $c; continue;
			}

			if (!empty($c['email']) && strpos(strtolower($c['email']), $term) !== false) {
				$res[] = $c; continue;
			}
			
		}
		return $res;
	}
	
	public function getContacts($max = NULL) {
		$this->load();
		if (!empty($max)) {
			return array_slice($this->contacts, 0, $max);
		}
		// error_log('REturning ' . count($this->contacts) . ' entries. getContacts() ');
		return $this->contacts;
	}
	
}
	
	
	