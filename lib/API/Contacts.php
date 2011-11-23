<?php

class API_Contacts extends API_Authenticated {

	protected $contacts, $list;
	protected $parameters;

	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
	
		#print_r($parameters); exit;
		
	}
	
	private function isEmail($str) {
		return (filter_var($str, FILTER_VALIDATE_EMAIL) !== FALSE);
	}
	
	
	
	private static function excludeList(&$contacts, $responses) {
		
		if (empty($responses)) return;
		if (empty($contacts)) return;
		
		foreach($responses AS $response) {
			
			foreach($contacts AS $key => $contact) {

				if (!empty($response['userid']) && !empty($contact['userid']) ) { 
					if ($response['userid'] == $contact['userid'] ) {
						$contacts[$key]['disabled'] = TRUE;
					}
				}
				
			}
			
		}
		// error_log(var_export($contacts, TRUE));
	}
	
	private static function exclude(&$contacts, $responses) {
		
		if (empty($responses)) return;
		if (empty($contacts)) return;
		
		foreach($responses AS $response) {
			
			foreach($contacts AS $key => $contact) {

				//error_log(var_export($response, TRUE));
				
				if (!empty($response->email) && !empty($contact['email']) ) { 
				
					//error_log('Comparing [' . $response->email . '] with [' .$contact['email'] . ']');				
					if ($response->email == $contact['email'] ) {
						$contacts[$key]['disabled'] = TRUE;
						// error_log('Disabling email [' . $contact['email']. ']' );

					}
				}
				
				if (!empty($response->userid) && !empty($contact['userid']) ) { 
					//error_log('Comparing [' . $response->userid . '] with [' .$contact['userid'] . ']');				
					if ($response->userid == $contact['userid'] ) {
						$contacts[$key]['disabled'] = TRUE;
						// error_log('Disabling userid [' . $contact['userid']. ']' );

					}
				}
				
			}
			
		}
		//error_log(var_export($contacts, TRUE));
	}
	
	function addme(&$result) {
		
		array_unshift($result, array(
			'userid' => $this->user->userid,
			'name' => $this->user->username,
			'email' => $this->user->email,
			'membership' => 'owner',
		));
		
	}
	

	
	
	function prepareLists() {
		
		$lists =  $this->fdb->getContactlists($this->user);
		
		
		foreach($lists AS $key => $list) {
			if (($list['role'] === 'admin') ||($list['role'] === 'owner')) {
				$lists[$key]['inviteToken'] = FoodleUtils::getInvitationToken($list['id']);
			}
		}
		
// 		if (!empty($this->user->org)) {
// 			$lists[] = array(
// 				'id' => 'myorg',
// 				'name' => $this->user->org,
// 				'role' => 'member'
// 			);
// 		}
		
		return $lists;
		
	}
	
	
	function addMemberToList($identifier, $userid) {
		
		$this->fdb->addToContactlist($identifier, $usier);
		
	}
	
	function prepareOrgList() {
		
		if (empty($this->user->org)) return array();
		
		return $this->fdb->getOrgList($this->user->org);
	}
	
	function prepareContactlist($identifier) {
		$list = $this->fdb->getContactlist($this->user, $identifier);
		
		$result = array();
		foreach($list AS $e) {
			$result[] = array(
				'userid' => $e['userid'],
				'name' => $e['username'],
				'email' => $e['email'],
				'membership' => $e['membership']
			);
		}
		
		$excludes = NULL;
		
		if (!empty($_REQUEST['exclude'])) {
			$foodleid = $_REQUEST['exclude'];		
			$foodle = $this->fdb->readFoodle($foodleid);
			$excludes = $foodle->getResponses();
			
			// echo 'Contact list excludes: ' . var_export(array_keys($excludes));
			
		}
		self::exclude(&$result, $excludes);
		
//		$this->addme(&$result);
		

		
		return array_values($result);
	}

	function requireMembership($identifier, $role = 'member') {
		if (!$this->fdb->isMemberOfContactlist($this->user, $identifier, $role))
			throw new Exception('Access denied. You are not member of the contact list specified.');
	}
	
	function requireOwnership($identifier) {
	
		if (!$this->fdb->isOwnerOfContactlist($this->user, $identifier))
			throw new Exception('Access denied. You are not the owner of the contact list specified.');
	}
	
	
	function prepareAutoContacts() {
		
		
		$this->contacts = new Data_Contacts($this->fdb, $this->user);
		
		$excludes = NULL;
		
		if (!empty($_REQUEST['exclude'])) {
			$foodleid = $_REQUEST['exclude'];		
			$foodle = $this->fdb->readFoodle($foodleid);
			$excludes = $foodle->getResponses();
		}
		
		$excludesList = NULL;
		if (!empty($_REQUEST['excludeList']) && is_numeric($_REQUEST['excludeList']) ) {
			
#			echo 'excludeList ' . $_REQUEST['excludeList'];
		
			$listid = $_REQUEST['excludeList'];	
			$excludesList = $this->fdb->getContactlist($this->user, $listid);
		}		
		
		
		$contacts = NULL;

		if (!empty($_REQUEST['term'])) {
		
			// error_log('Search term was [' . $_REQUEST['term']. ']');
			$contacts = $this->contacts->search($_REQUEST['term']);
		
		
			if ($this->isEmail($_REQUEST['term'])) {
				// error_log('is email');
				$email = $_REQUEST['term'];
				if (empty($contacts)) {
					$contacts = array(
						array(
							'email' => $email, 
							'key' => sha1($email)
						)
					);
				}
			}  else {
				// error_log('is not email');			
			}
		
		


		} else {
			// error_log('Returning all contacts');
			$contacts = $this->contacts->getContacts(10);
		}
		
		self::exclude(&$contacts, $excludes);
		self::excludeList(&$contacts, $excludesList);
		
		// error_log(var_export($contacts, TRUE));
		
		return array_values($contacts);
		
	}
	
	
	
	function prepare() {
		parent::prepare();
		
#		print_r($this->parameters);
		
		if (empty($this->parameters)) {
		
			if (!empty($_REQUEST['newlist'])) {
				$this->fdb->addContactlist($this->user, $_REQUEST['newlist']);
			}
			
			if (!empty($_REQUEST['removelist'])) {
				$list = $_REQUEST['removelist'];
				if (!is_numeric($list)) {
					throw new Exception('List identifier should be an integer. Invalid identifier provided. [' . $list. ']');
				}
				$this->requireOwnership($list);
				$this->fdb->removeContactlist($list);
			}
		
			return $this->prepareLists();
		}
		
		if (!empty($this->parameters[0])) {
		
			$list = $this->parameters[0];

			
			/* Returns all contacts.
			 * 
			 * Supports two parameters
			 *  exclude - exclude members of a foodle.
			 *  term - searching
			 */
			if ($list === 'auto') {
				return $this->prepareAutoContacts();
				
			} else if ($list === 'myorg') {
				
				return $this->prepareOrgList();
				
			
			} else if (preg_match('/^foodle:(.*?)$/', $list, $matches)) {
				$foodleid = $matches[1];
				Data_Foodle::requireValidIdentifier($foodleid);
				$contacts = $this->fdb->readResponders($foodleid);
				$excludesList = NULL;
				if (!empty($_REQUEST['excludeList']) && is_numeric($_REQUEST['excludeList']) ) {
			
		#			echo 'excludeList ' . $_REQUEST['excludeList'];
		
					$listid = $_REQUEST['excludeList'];	
					$excludesList = $this->fdb->getContactlist($this->user, $listid);
					$this->excludeList(&$contacts, $excludesList);
				}	
				return $contacts;

			}

			if (!is_numeric($list)) {
				throw new Exception('List identifier should be an integer. Invalid identifier provided. [' . $list. ']');
			}

			$this->requireMembership($list);
			
			
			if (!empty($_REQUEST['adduser'])) {
				$this->requireMembership($list, 'admin');
				$this->fdb->addToContactlist($list, $_REQUEST['adduser']);
			}
			
			if (!empty($_REQUEST['setrole'])) {
				$this->requireMembership($list, 'admin');
				if (empty($_REQUEST['user'])) throw new Exception('Invalid userid provided');
				if (!in_array($_REQUEST['setrole'], array('admin', 'member'))) {
					throw new Exception('Trying to set role attribute to an invalid value');
				}
				
				$this->fdb->setContactlistMembershipRole($list, $_REQUEST['user'], $_REQUEST['setrole']);
			}
			
			if (!empty($_REQUEST['removeuser'])) {
				$this->requireMembership($list, 'admin');
				$this->fdb->removeFromContactlist($list, $_REQUEST['removeuser']);
			}
			
			return $this->prepareContactlist($list);
		
		}
		
		throw new Exception('Invalid request');
		
	

		
	}


	
}

