<?php

class XHTMLResponseEntry {
	
	function __construct() {
		
	}
	
	public static function showEditable(Data_User $user, SimpleSAML_XHTML_Template $t, Data_FoodleResponse $response, $editable = TRUE, Data_FoodleResponse $responsecal = NULL, $authenticated = TRUE) { 


		
		$extrafields = $response->foodle->getExtraFields();
		
		echo '<tr id="responserowmanual" class="you">';
		echo '<td> </td>';
		
		// Field with user name (and link to add comment)
		echo '<td>';
		echo ' <input type="hidden" name="setresponse" value="1" />';
		// Only show add a comment entry if comment is not already added.
		if (empty($response->notes)) {
			echo '<a style="float: right" class="ac" >' . $t->t('addcomment') . '</a>';
		}
		
		if ($authenticated) {
			echo $user->getResponseUsernameHTML($response);
		} else {
			echo '<p style="margin: 2px">' . $t->t('name') . ': <input type="text" name="username" value="'  . htmlspecialchars($response->username). '" placeholder="' . $t->t('displayname'). '..." /></p>';
#			echo '<form method="post" action="' . $this->data['foodlepath'] . '">';
#			echo '<p>' . $this->t('register_email') . '</p>';
#			echo '<p>' . $this->t('displayname') . ': <input type="text" name="setDisplayName" value="' . 
#				(isset($this->data['displayname']) ? htmlentities($this->data['displayname']) : '') . '"/><br />';		
#			echo '' . $this->t('email') . ': 
			if (empty($response->email)) {
				echo '<p  style="margin: 2px">' . $t->t('email') . ': <input type="text" name="setEmail"  placeholder="' . $t->t('email'). '..."/></p>';						
			}

#			echo '<input type="submit" name="reg" value="' . $this->t('emailreg_submit') . '" />';
#			echo '</form>';
		}
		
#		echo '<pre>f'; print_r($extrafields);
		
#		echo htmlspecialchars($response->username);
		# echo ' <input type="text" name="username" value="' . htmlspecialchars($response->username) . '" /> (<tt>' . htmlspecialchars($response->userid). '</tt>)';
		echo '</td>';



	
		foreach($extrafields AS $extrafield) {
		
			switch($extrafield) {
				case 'photo':
					$photourl = false;
					if(isset($response->user)) {
						$photourl = $response->user->getPhotoURL('s');
					}
					
					if ($photourl !== false) {
						echo '<td style="padding: 0px; width: 32px">';
						echo ' <img src="' . htmlspecialchars($photourl) . '" alt="Photo of user" style="margin: 0px; padding: 0px" />';
						echo '</td>';
					} else {
						echo '<td  style="text-align: center; vertical-align: top; padding-top: 6px"></td>';
					}
					break;
					
				case 'org':
					$orgtext = '';
					if(isset($response->user)) {
						$orgtext = $response->user->getOrgHTML();
					}
					echo '<td style="text-align: center; vertical-align: top;">' . $orgtext . '</td>';
					break;

				case 'location':
					$loc = '';
					if(isset($response->user)) {
						if (!empty($response->user->location)) $loc = $response->user->location;
					}
					echo '<td style="text-align: center; vertical-align: top;">' . $loc . '</td>';
					break;
					
				case 'timezone':
					$timezone = '';
					if(isset($response->user)) {
						if (!empty($response->user->timezone)) $timezone = $response->user->timezone;
					}
// 					if (isset($response->timezone)) {
// 						$timezone = $response->timezone;
// 					}
					echo '<td style="text-align: center; vertical-align: top;">' . $timezone . '</td>';
					break;

					
				default:
					echo '<td></td>';
					
			}
		
		}
		
	
		if ($response->response['type'] === 'ical') {
			foreach ($response->response['data'] AS $no => $entry) {
				if ($entry == '1') {
					echo '<td class="yes center"><img class="yesimg" alt="No" src="/res/yes.png" /></td>';
				} else if ($entry == '2') { 
					echo '<td class="maybe center"><img class="maybeimg" alt="Maybe" src="/res/maybe.png" /></td>';
				} else {
					echo '<td class="no center"><img class="yesimg" alt="Yes" src="/res/busy.png" /></td>';
				}
			}
		} else if($response->foodle->responseType() === 'yesnomaybe') {
						
			foreach ($response->response['data'] AS $no => $entry) {
			
// 				echo '<pre>';
// 				print_r($entry);
// 			exit;
			
				$checked = array('', ' checked="checked" ', '');
				if (isset($entry)) {
					$checked = array('', '', '');
					$checked[$entry] = ' checked="checked" ';
				}
				echo '<td class="center">
					<div class="ryes"><input type="radio" name="myresponse[' . $no . ']" value="' . $no . '-1" ' . $checked[1] . '/></div>
					<div class="rmaybe"><input type="radio" name="myresponse[' . $no . ']" value="' . $no . '-2" ' . $checked[2] . '/></div>
					<div class="rno"><input type="radio" name="myresponse[' . $no . ']" value="' . $no . '-0" ' . $checked[0] . '/></div>
				</td>';
			}
			
		} else {
			foreach ($response->response['data'] AS $no => $entry) {
				if ($entry == '1') {
					echo '<td class="yes center"><input type="checkbox" name="myresponse[]" checked="checked" value="' . $no . '" /></td>';
				} elseif($entry == '0') {
					echo '<td class="no center"><input type="checkbox" name="myresponse[]" value="' . $no . '"  /></td>';
				} else {
					echo '<td class="no center grey"><input type="checkbox" name="myresponse[]" value="' . $no . '"  /></td>';
				}
			}
		}


		if ($response->loadedFromDB) {
			if ($editable) {
				echo '<td style="text-align: center"><input type="submit" name="save" value="' .  $t->t('update') . '" /></td>';	
			} else {
				echo '<td style="text-align: center"><input type="submit" name="save"  disabled="disabled" value="' .  $t->t('update') . '" /></td>';	
			}
		} else {
			if ($editable) {
				echo '<td style="text-align: center"><input type="submit" name="save" value="' . $t->t('submit') . '" /></td>';	
			} else {
				echo '<td style="text-align: center"><input type="submit" name="save" disabled="disabled" value="' . $t->t('submit') . '" /></td>';	
			}
		}

		echo '</tr>	';
		
		if (isset($responsecal)) {
			echo '<tr id="responserowcal" class="you">';
	
			echo '<td> </td>';

			// Field with user name (and link to add comment)
			echo '<td>';
			echo ' <input type="hidden" name="setresponse" value="1" />';
			// Only show add a comment entry if comment is not already added.
			if (empty($responsecal->notes)) {
				echo '<a style="float: right" class="ac" >' . $t->t('addcomment') . '</a>';
			}
			echo $user->getResponseUsernameHTML($response);
			
	#		echo htmlspecialchars($response->username);
			# echo ' <input type="text" name="username" value="' . htmlspecialchars($response->username) . '" /> (<tt>' . htmlspecialchars($response->userid). '</tt>)';
			echo '</td>';

	
	
		
			foreach($extrafields AS $extrafield) {
			
				switch($extrafield) {
					case 'photo':
						$photourl = false;
						if(isset($response->user)) {
							$photourl = $response->user->getPhotoURL('s');
						}
						
						if ($photourl !== false) {
							echo '<td style="padding: 0px; width: 32px">';
							echo ' <img src="' . htmlspecialchars($photourl) . '" alt="Photo of user" style="margin: 0px; padding: 0px" />';
							echo '</td>';
						} else {
							echo '<td  style="text-align: center; vertical-align: top; padding-top: 6px"></td>';
						}
						break;
							
					case 'org':
						$orgtext = '';
						if(isset($response->user)) {
							$orgtext = $response->user->getOrgHTML();
						}
						echo '<td style="text-align: center; vertical-align: top;">' . $orgtext . '</td>';
						break;
	
					case 'location':
						$loc = '';
						if(isset($response->user)) {
							if (!empty($response->user->location)) $loc = $response->user->location;
						}
						echo '<td style="text-align: center; vertical-align: top;">' . $loc . '</td>';
						break;

				case 'timezone':
					$timezone = '';
					if(isset($response->user)) {
						if (!empty($response->user->timezone)) $timezone = $response->user->timezone;
					}
// 					if (isset($response->timezone)) {
// 						$timezone = $response->timezone;
// 					}
					echo '<td style="text-align: center; vertical-align: top;">' . $timezone . '</td>';
					break;

						
					default:
						echo '<td></td>';
						
				}
			
			}
			
			


			if ($responsecal->response['type'] === 'ical') {
				foreach ($responsecal->response['data'] AS $no => $entry) {
					if ($entry == '1') {
						echo '<td class="yes center"><img class="yesimg" alt="No" src="/res/yes.png" /></td>';
					} else if ($entry == '2') { 
						echo '<td class="maybe center"><img class="maybeimg" alt="Maybe" src="/res/maybe.png" /></td>';

					} else {
						echo '<td class="no center"><img class="yesimg" alt="Yes" src="/res/busy.png" /></td>';
					}
				}
			} else if($responsecal->foodle->responseType() === 'yesnomaybe') {
							
				foreach ($responsecal->response['data'] AS $no => $entry) {
				
					$checked = array('', ' checked="checked" ', '');
					if (isset($entry)) {
						$checked = array('', '', '');
						$checked[$entry] = ' checked="checked" ';
					}
					if (isset($entry)) $checked[$entry] = ' checked="checked" ';
					echo '<td class="center">
						<div class="ryes"><input type="radio" name="myresponse[' . $no . ']" value="' . $no . '-1" ' . $checked[1] . '/></div>
						<div class="rmaybe"><input type="radio" name="myresponse[' . $no . ']" value="' . $no . '-2" ' . $checked[2] . '/></div>
						<div class="rno"><input type="radio" name="myresponse[' . $no . ']" value="' . $no . '-0" ' . $checked[0] . '/></div>
					</td>';
					
	// 				if ($entry == '1') {
	// 					echo '<td class="yes center"><input type="checkbox" name="myresponse[]" checked="checked" value="' . $no . '" /></td>';
	// 				} elseif($entry == '0') {
	// 					echo '<td class="no center"><input type="checkbox" name="myresponse[]" value="' . $no . '"  /></td>';
	// 				} else {
	// 					echo '<td class="no center grey"><input type="checkbox" name="myresponse[]" value="' . $no . '"  /></td>';
	// 				}
				}
			
			} else {
				foreach ($responsecal->response['data'] AS $no => $entry) {
					if ($entry == '1') {
						echo '<td class="yes center"><input type="checkbox" name="myresponse[]" checked="checked" value="' . $no . '" /></td>';
					} else {
						echo '<td class="no center"><input type="checkbox" name="myresponse[]" value="' . $no . '"  /></td>';
					}
				}
			}


			if ($responsecal->loadedFromDB && $t->data['defaulttype'] === 'ical') {
				echo '<td style="text-align: center"><input type="submit" name="savecal" value="' .  $t->t('update') . '" /></td>';	
			} else {
				if ($editable) {
					echo '<td style="text-align: center"><input type="submit" name="savecal" value="' . $t->t('submit') . '" /></td>';	
				} else {
					echo '<td style="text-align: center"><input type="submit" name="savecal" disabled="disabled" value="' . $t->t('submit') . '" /></td>';	
				}
			}

			echo '</tr>	';
		
		}
		
		
		$colspan = (count($response->response['data']) + 3) + count($extrafields);
	
		$shide = '';	
		if (empty($response->notes)) $shide = 'display: none';
		echo '<tr style="' . $shide . '" id="commentfield" class="you"><td colspan="' . $colspan . '">
			<input type="text" id="comment" class="comment" name="comment" value="' . htmlspecialchars($response->notes) . '" /></td></tr>';
	

		
	}
	
	
	
	
	
	
	
	
	
	
	public static function showEditableConfirm(Data_User $user, SimpleSAML_XHTML_Template $t, Data_FoodleResponse $response, $editable = TRUE, Data_FoodleResponse $responsecal = NULL, $authenticated = TRUE) { 

		$extrafields = $response->foodle->getExtraFields();
		
#		error_log(' ======= showEditableConfirm()');
		
		echo '<tr id="responseconfirm" class="you">';
		echo '<td> </td>';
		
		// Field with user name (and link to add comment)
		echo '<td>';
		echo ' <input type="hidden" name="setresponse" value="1" />';
		// Only show add a comment entry if comment is not already added.
		if (empty($response->notes)) {
			echo '<a style="float: right" class="ac" >' . $t->t('addcomment') . '</a>';
		}
		
		if ($authenticated) {
			echo $user->getResponseUsernameHTML($response);
		} else {
			echo '<p style="margin: 2px">' . $t->t('name') . ': <input type="text" name="username" value="'  . htmlspecialchars($response->username). '" placeholder="' . $t->t('displayname'). '..." /></p>';

			if (empty($response->email)) {
				echo '<p  style="margin: 2px">' . $t->t('email') . ': <input type="text" name="setEmail"  placeholder="' . $t->t('email'). '..."/></p>';						
			}

		}		
		echo '</td>';



	
		foreach($extrafields AS $extrafield) {
		
			switch($extrafield) {
				case 'photo':
					$photourl = false;
					if(isset($response->user)) {
						$photourl = $response->user->getPhotoURL('s');
					}
					
					if ($photourl !== false) {
						echo '<td style="padding: 0px; width: 32px">';
						echo ' <img src="' . htmlspecialchars($photourl) . '" alt="Photo of user" style="margin: 0px; padding: 0px" />';
						echo '</td>';
					} else {
						echo '<td  style="text-align: center; vertical-align: top; padding-top: 6px"></td>';
					}
					break;
					
				case 'org':
					$orgtext = '';
					if(isset($response->user)) {
						$orgtext = $response->user->getOrgHTML();
					}
					echo '<td style="text-align: center; vertical-align: top;">' . $orgtext . '</td>';
					break;

				case 'location':
					$loc = '';
					if(isset($response->user)) {
						if (!empty($response->user->location)) $loc = $response->user->location;
					}
					echo '<td style="text-align: center; vertical-align: top;">' . $loc . '</td>';
					break;
					
				case 'timezone':
					$timezone = '';
					if(isset($response->user)) {
						if (!empty($response->user->timezone)) $timezone = $response->user->timezone;
					}
// 					if (isset($response->timezone)) {
// 						$timezone = $response->timezone;
// 					}
					echo '<td style="text-align: center; vertical-align: top;">' . $timezone . '</td>';
					break;

					
				default:
					echo '<td></td>';
					
			}
		
		}
		

		$entry = null;
		if(isset($response->response['confirm'])) {
			$entry = $response->response['confirm'];
		}

		$checked = array('', ' checked="checked" ', '');
		if (isset($entry)) {
			$checked = array('', '', '');
			$checked[$entry] = ' checked="checked" ';
		}

		echo '<td class="center">
			<div class="ryes"><input type="radio" id="setconfirm1" name="setconfirm" value="1" ' . $checked[1] . '/>
			<label for="setconfirm1" >' . $t->t('yes') . '</label>
			</div>
			<div class="rmaybe"><input type="radio" id="setconfirm2" name="setconfirm" value="2" ' . $checked[2] . '/>
			<label for="setconfirm2" >' . $t->t('maybe') . '</label>
			</div>
			<div class="rno"><input type="radio" id="setconfirm0" name="setconfirm" value="0" ' . $checked[0] . '/>
			<label for="setconfirm0" >' . $t->t('no') . '</label>
			</div>
		</td>';
				


		if (isset($response->response['confirm'])) {
			if ($editable) {
				echo '<td style="text-align: center"><input type="submit" name="save" value="' .  $t->t('update') . '" /></td>';	
			} else {
				echo '<td style="text-align: center"><input type="submit" name="save"  disabled="disabled" value="' .  $t->t('update') . '" /></td>';	
			}
		} else {
			if ($editable) {
				echo '<td style="text-align: center"><input type="submit" name="save" value="' . $t->t('submit') . '" /></td>';	
			} else {
				echo '<td style="text-align: center"><input type="submit" name="save" disabled="disabled" value="' . $t->t('submit') . '" /></td>';	
			}
		}
	

		echo '</tr>	';
		
		
		$colspan = (1 + 3) + count($extrafields);
	
		$shide = '';	
		if (empty($response->notes)) $shide = 'display: none';
		echo '<tr style="' . $shide . '" id="commentfield" class="you"><td colspan="' . $colspan . '">
			<input type="text" id="comment" class="comment" name="comment" value="' . htmlspecialchars($response->notes) . '" /></td></tr>';
	




		
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public static function show(SimpleSAML_XHTML_Template $t, Data_FoodleResponse $response, $user) { 
		
		$extrafields = $response->foodle->getExtraFields();
		
		
#		echo '<pre>'; print_r($response); exit;
		
		$class = '';
		if (!empty($response->notes)) {
			$class = 'hasnotes';
		}
		echo '<tr class="' . $class . '">';

		/*
		 * Notes field
		 */
		if (!empty($response->notes)) {
			echo '<td rowspan="2" style="text-align: center; vertical-align: top; padding-top: 6px">';
			echo ' <img style="margin: 0px" onclick="toggle(\'' . sha1($response->userid) . '\')" src="/res/notes.png" />';
			echo '</td>';
		} else {
			echo '<td> </td>';
		}

#			echo '<pre>'; print_r($response); echo '</pre>';
		
		
		/*
		 * Name field
		 */
		echo '<td style="text-align: left">';
			
			

		
		if ($response->response['type'] === 'ical') {
			echo '<img style="float: right" alt="' . $t->t('issyncedwithcalendar') . '" title="' . $t->t('issyncedwithcalendar') . '" class="" src="/res/calendar-export.png" />';
		}
		if (!empty($response->email)) {
			echo '<img style="float: right" alt="' . htmlspecialchars($response->email) . '" title="' . htmlspecialchars($response->email) . '" class="" src="/res/mail16.png" />';
		}
		if ($response->invalid) {
			echo '<img style="float: right" 
				alt="Entry was made with a different number of columns. This might happen when the Foodle was edited after this user responded."
				title="Entry was made with a different number of columns. This might happen when the Foodle was edited after this user responded." class="" src="/res/error.png" />';
		}
		
		
		echo $user->getResponseUsernameHTML($response);
//		echo $response->getUsernameHTML(!$user->isAdmin());
		echo '</td>';
		
		
		

		foreach($extrafields AS $extrafield) {
		
			switch($extrafield) {
				case 'photo':
					$photourl = false;
					if(isset($response->user)) {
						$photourl = $response->user->getPhotoURL('s');
					}
					
					if ($photourl !== false) {
						echo '<td style="padding: 0px; width: 32px">';
						echo ' <img src="' . htmlspecialchars($photourl) . '" alt="Photo of user" style="margin: 0px; padding: 0px" />';
						echo '</td>';
					} else {
						echo '<td  style="text-align: center; vertical-align: top; padding-top: 6px"></td>';
					}
					break;
				
				case 'org':
					$orgtext = '';
					if(isset($response->user)) {
						$orgtext = $response->user->getOrgHTML();
					}
					echo '<td style="text-align: center; vertical-align: top;">' . $orgtext . '</td>';
					break;

				case 'location':
					$loc = '';
					if(isset($response->user)) {
						if (!empty($response->user->location)) $loc = $response->user->location;
					}
					echo '<td style="text-align: center; vertical-align: top;">' . $loc . '</td>';
					break;
				
				case 'timezone':
					$timezone = '';
					if(isset($response->user)) {
						if (!empty($response->user->timezone)) $timezone = $response->user->timezone;
					}
// 					if (isset($response->timezone)) {
// 						$timezone = $response->timezone;
// 					}
					echo '<td style="text-align: center; vertical-align: top;">' . $timezone . '</td>';
					break;
				
				default:
					echo '<td></td>';
					
			}
		
		}
		
		if ($response->invitation) {
			
			echo '<td class="invitation" style="padding: 0px 2em; color: #777" colspan="' . $response->foodle->getNofColumns() . '">
			Invited. Waiting for response...
			</td>';
			
		} else {
			
			
			if ($response->response['type'] === 'ical') {
				foreach ($response->response['data'] AS $no => $entry) {
					if ($entry == '1') {
						echo '<td class="yes center"><img class="yesimg" alt="Yes" src="/res/yes.png" /></td>';
					} else if ($entry == '2') { 
						echo '<td class="maybe center"><img class="maybeimg" alt="Maybe" title="Tentative event" src="/res/maybe.png" /></td>';
						
					} else if ($entry == '9') { 
						echo '<td class="no center grey"><img class="yesimg" alt="Invalid entry" title="Error loading calendar" src="/res/error.png" /></td>';


					} else {
						echo '<td class="no center"><img class="yesimg" alt="Yes" title="' . $t->t('calendarcollision') . ': '. $response->response['crash'][$no] . '" src="/res/busy.png" /></td>';
					}
				}
			} else {
	
				foreach ($response->response['data'] AS $no => $entry) {
					if ($entry == '1') {
						echo '<td class="yes center"><img class="yesimg" alt="Yes" src="/res/yes.png" /></td>';
					} elseif ($entry == '0') {
						echo '<td class="no center"><img class="yesimg" alt="No" src="/res/no2trans.png" /></td>';
					} elseif ($entry == '2') {
						echo '<td class="maybe center"><img class="maybeimg" alt="Maybe" src="/res/maybe.png" /></td>';
					} else {
						echo '<td class="no center grey"><img class="yesimg" alt="Invalid entry" src="/res/error.png" /></td>';
					}
				}
	
			}
		
		
		}


		
		echo '<td>' . htmlspecialchars($response->getAgo()) . '</td>';
		echo '</tr>';

		$colspan = (count($response->response['data']) + 2) + count($extrafields);
		if (!empty($response->notes)) {
			echo '<tr><td id="' . sha1($response->userid) . '" class="commentline" style="display: none" colspan="' . 
				$colspan . '">';
			echo htmlspecialchars($response->notes);
			echo '</td></tr>';
		}
		
	}
	
	
	
	
	
	
	public static function showConfirm(SimpleSAML_XHTML_Template $t, Data_FoodleResponse $response, $user) { 
		
		$extrafields = $response->foodle->getExtraFields();
		
		$class = '';
		if (!empty($response->notes)) {
			$class = 'hasnotes';
		}
		echo '<tr class="' . $class . '">';

		/*
		 * Notes field
		 */
		if (!empty($response->notes)) {
			echo '<td rowspan="2" style="text-align: center; vertical-align: top; padding-top: 6px">';
			echo ' <img style="margin: 0px" onclick="toggle(\'' . sha1($response->userid) . '\')" src="/res/notes.png" />';
			echo '</td>';
		} else {
			echo '<td> </td>';
		}
		
		/*
		 * Name field
		 */
		echo '<td style="text-align: left">';
		
		if (!empty($response->email)) {
			echo '<img style="float: right" alt="' . htmlspecialchars($response->email) . '" title="' . htmlspecialchars($response->email) . '" class="" src="/res/mail16.png" />';
		}
#		echo $response->getUsernameHTML();
		echo $user->getResponseUsernameHTML($response);
		echo '</td>';

		foreach($extrafields AS $extrafield) {
		
			switch($extrafield) {
				case 'photo':
					$photourl = false;
					if(isset($response->user)) {
						$photourl = $response->user->getPhotoURL('s');
					}
					
					if ($photourl !== false) {
						echo '<td style="padding: 0px; width: 32px">';
						echo ' <img src="' . htmlspecialchars($photourl) . '" alt="Photo of user" style="margin: 0px; padding: 0px" />';
						echo '</td>';
					} else {
						echo '<td  style="text-align: center; vertical-align: top; padding-top: 6px"></td>';
					}
					break;
				
				case 'org':
					$orgtext = '';
					if(isset($response->user)) {
						$orgtext = $response->user->getOrgHTML();
					}
					echo '<td style="text-align: center; vertical-align: top;">' . $orgtext . '</td>';
					break;

				case 'location':
					$loc = '';
					if(isset($response->user)) {
						if (!empty($response->user->location)) $loc = $response->user->location;
					}
					echo '<td style="text-align: center; vertical-align: top;">' . $loc . '</td>';
					break;
				
				default:
					echo '<td></td>';
					
			}
		
		}
		
		#echo '<pre>'; print_r($response); exit;
		$entry = NULL;
		if(isset($response->response['confirm'])) {
			$entry = $response->response['confirm'];
		}
		if ($entry == '1') {
			echo '<td class="yes center"><img class="yesimg" alt="Yes" src="/res/yes.png" /></td>';
		} elseif ($entry == '0') {
			echo '<td class="no center"><img class="yesimg" alt="No" src="/res/no2trans.png" /></td>';
		} elseif ($entry == '2') {
			echo '<td class="maybe center"><img class="maybeimg" alt="Maybe" src="/res/maybe.png" /></td>';
		} else {
			echo '<td class="center"><img class="maybeimg" alt="Awaiting response" src="/res/hourglass.png" /></td>';
		}		
		
		echo '<td>' . htmlspecialchars($response->getAgo()) . '</td>';
		echo '</tr>';


		$colspan = 3 + count($extrafields);
		
		if (!empty($response->notes)) {
			echo '<tr><td id="' . sha1($response->userid) . '" class="commentline" style="display: none" colspan="' . 
				$colspan . '">';
			echo htmlspecialchars($response->notes);
			echo '</td></tr>';
		}
		
	}
	
	
	
	
}



