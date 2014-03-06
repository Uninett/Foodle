<?php

class API_FoodleAuthenticated extends API_Authenticated {

	protected $contacts, $list;
	protected $parameters;
	protected $foodleid, $foodle, $responses;

	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
		
		
	}
	
	function prepare() {

		self::optionalAuth();



		// All requests point at a specific Foodle
		if (self::route(false, '^/api/foodle/([^/]+)(/|$)', &$parameters, &$object)) {

			Data_Foodle::requireValidIdentifier($parameters[1]);
			$this->foodleid = $parameters[1];


			$this->foodle = $this->fdb->readFoodle($this->foodleid);

			if (self::route('get', '^/api/foodle/([^/]+)$', &$parameters, &$object)) {

				return $this->foodle->getView();

			// Update existing foodle
			} else if (self::route('post', '^/api/foodle/([^/]+)$', &$parameters, &$object)) {

				// $newFoodle = new Data_Foodle($this->fdb);

				$this->foodle->acl($this->user, 'write');
				$this->foodle->updateFromPostAPI($this->user, $object);

				$this->fdb->saveFoodle($this->foodle);

				$this->foodle = $this->fdb->readFoodle($this->foodle->identifier);
				return $this->foodle;


			// Update existing foodle
			} else if (self::route('delete', '^/api/foodle/([^/]+)$', &$parameters, &$object)) {

				// $newFoodle = new Data_Foodle($this->fdb);

				$this->foodle->acl($this->user, 'write');
				$this->fdb->deleteFoodle($this->foodle);

				return true;


			} else if (self::route('get', '^/api/foodle/([^/]+)/responders$', &$parameters, &$object)) {

				$this->responses = $this->fdb->readResponses($this->foodle, NULL, FALSE);

				$respobj = array();
				foreach($this->responses AS $key => $r) {
					$respobj[$key] = $r->getView();
				}

				return $respobj;

			} else if (self::route('get', '^/api/foodle/([^/]+)/discussion$', &$parameters, &$object)) {

				$discussion = $this->fdb->readDiscussion($this->foodle);
				return $discussion;


			} else if (self::route('post', '^/api/foodle/([^/]+)/discussion$', &$parameters, &$object)) {

				$comment = strip_tags($object);


				// addDiscussionEntry(Data_Foodle $foodle, Data_User $user, $message) {
				$this->fdb->addDiscussionEntry($this->foodle, $this->user, $comment);
				return $comment;

				// $currentResponse = $this->foodle->getMyResponse($this->user);
				// if (isset($object['response']) && isset($object['response']['data'])) {
				// 	$currentResponse->response = $object['response'];
				// }
				// $this->fdb->saveFoodleResponse($currentResponse);
				// return true;




			} else if (self::route('post', '^/api/foodle/([^\/]+)/myresponse$', &$parameters, &$object)) {


				// echo 'about to update response. User is'; print_r($this->user); exit;


				$currentResponse = $this->foodle->getMyResponse($this->user);
				if (isset($object['response']) && isset($object['response']['data'])) {
					$currentResponse->response = $object['response'];
				}
				if (isset($object['notes'])) {
					// $tz = filter_var($object, FILTER_SANITIZE_EMAIL);
					$currentResponse->notes = filter_var($object['notes'], FILTER_SANITIZE_SPECIAL_CHARS);
				} else {
					$currentResponse->notes = null;
				}
				$this->fdb->saveFoodleResponse($currentResponse);
				return true;



			} else {
				throw new Exception('Invalid request');
			}




			
		} else if (self::route('post', '^/api/foodle$', &$parameters, &$object)) {

			// header('Content-type: text/plain; charset=utf-8');
			// print_r($object); 


			$newFoodle = new Data_Foodle($this->fdb);
			$newFoodle->updateFromPostAPI($this->user, $object);
			
			$this->fdb->saveFoodle($newFoodle);
			$this->foodle = $this->fdb->readFoodle($newFoodle->identifier);

			// print_r($newFoodle); 
			// exit;

			return $this->foodle;

		// if ($subrequest === 'discussion') {
			

			
		}





		throw new Exception('Invalid request parameters');
	}


	
}

