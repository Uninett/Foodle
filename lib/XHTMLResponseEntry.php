<?php

class XHTMLResponseEntry {
	
	function __construct() {
		
	}
	
	public static function showEditable(SimpleSAML_XHTML_Template $t, Data_FoodleResponse $response, $editable = TRUE, Data_FoodleResponse $responsecal = NULL, $authenticated = TRUE) { 
		
#		echo '<tr id="responserowmanual" class="you"><pre>'; print_r($response); exit;
		
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
			echo '<abbr title="' . $response->userid . '">' . htmlspecialchars($response->username) . '</abbr>' . $extra;	
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
		
		
		
		
#		echo htmlspecialchars($response->username);
		# echo ' <input type="text" name="username" value="' . htmlspecialchars($response->username) . '" /> (<tt>' . htmlspecialchars($response->userid). '</tt>)';
		echo '</td>';
	
		#echo '<pre>'; print_r($response); exit;
	
		if ($response->response['type'] === 'ical') {
			foreach ($response->response['data'] AS $no => $entry) {
				if ($entry == '1') {
					echo '<td class="yes center"><img class="yesimg" alt="No" src="/res/yes.png" /></td>';
				} else {
					echo '<td class="no center"><img class="yesimg" alt="Yes" src="/res/busy.png" /></td>';
				}
			}
		} else if($response->foodle->responseType() === 'yesnomaybe') {
						
			foreach ($response->response['data'] AS $no => $entry) {
			
				$checked = array('', '', '');
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
			echo '<abbr title="' . $responsecal->userid . '">' . htmlspecialchars($responsecal->username) . '</abbr>' . $extra;
	#		echo htmlspecialchars($response->username);
			# echo ' <input type="text" name="username" value="' . htmlspecialchars($response->username) . '" /> (<tt>' . htmlspecialchars($response->userid). '</tt>)';
			echo '</td>';



			if ($responsecal->response['type'] === 'ical') {
				foreach ($responsecal->response['data'] AS $no => $entry) {
					if ($entry == '1') {
						echo '<td class="yes center"><img class="yesimg" alt="No" src="/res/yes.png" /></td>';
					} else {
						echo '<td class="no center"><img class="yesimg" alt="Yes" src="/res/busy.png" /></td>';
					}
				}
			} else if($responsecal->foodle->responseType() === 'yesnomaybe') {
							
				foreach ($responsecal->response['data'] AS $no => $entry) {
				
					$checked = array('', '', '');
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
		
		
		
	
		$shide = '';	
		if (empty($response->notes)) $shide = 'display: none';
		echo '<tr style="' . $shide . '" id="commentfield" class="you"><td colspan="' . (count($response->response['data']) + 3) . '">
			<input type="text" id="comment" class="comment" name="comment" value="' . htmlspecialchars($response->notes) . '" /></td></tr>';
	

		
	}
	
	
	public static function show(SimpleSAML_XHTML_Template $t, Data_FoodleResponse $response) { 
		
		echo '<tr>';

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

		$userid = htmlspecialchars($response->userid);
		$username = preg_replace('/ /', ' ', $response->username);
		
		$extra = '';
		if (preg_match('|^@(.*)$|', $userid, $matches))
			$extra = ' (<a href="http://twitter.com/' . $matches[1] . '">' . $userid . '</a>)';

		echo '<abbr title="' . $userid . '">' . $username . '</abbr>' . $extra;
		echo '</td>';
		
		

		// foreach ($response->response['data'] AS $no => $entry) {
		// 
		// 	if ($entry == '1') {
		// 		echo '<td class="yes center"><img class="yesimg" alt="No" src="/res/yes.png" /></td>';
		// 	} else {
		// 		echo '<td class="no center"><img class="yesimg" alt="Yes" src="/res/no2trans.png" /></td>';
		// 	}
		// 
		// }
		// 
		// 
		if ($response->response['type'] === 'ical') {
			foreach ($response->response['data'] AS $no => $entry) {
				if ($entry == '1') {
					echo '<td class="yes center"><img class="yesimg" alt="No" src="/res/yes.png" /></td>';
				} else {
					echo '<td class="no center"><img class="yesimg" alt="Yes" title="' . $t->t('calendarcollision') . ': '. $response->response['crash'][$no] . '" src="/res/busy.png" /></td>';
				}
			}
		} else {
		
			foreach ($response->response['data'] AS $no => $entry) {
				if ($entry == '1') {
					echo '<td class="yes center"><img class="yesimg" alt="No" src="/res/yes.png" /></td>';
				} elseif ($entry == '0') {
					echo '<td class="no center"><img class="yesimg" alt="Yes" src="/res/no2trans.png" /></td>';
				} elseif ($entry == '2') {
					echo '<td class="maybe center"><img class="maybeimg" alt="Maybe" src="/res/maybe.png" /></td>';
				} else {
					echo '<td class="no center grey"><img class="yesimg" alt="Invalid entry" src="/res/error.png" /></td>';
				}
			}

		}
		
		
		
		echo '<td>' . htmlspecialchars($response->getAgo()) . '</td>';
		echo '</tr>';

		if (!empty($response->notes)) {
			echo '<tr><td id="' . sha1($response->userid) . '" class="commentline" style="display: none" colspan="' . 
				(count($response->response['data']) + 2) . '">';
			echo htmlspecialchars($response->notes);
			echo '</td></tr>';
		}
		
	}
	
	
}



