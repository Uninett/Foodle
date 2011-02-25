<?php 

$headbar = '<a class="button" style="float: right; " 
		title="Comma separated file, works with Excel." href="/foodle/' . $this->data['foodle']->identifier . '?output=csv">
	<span><!-- <img src="resources/spreadsheet.png" /> -->' . 
		$this->t('open_in_spreadsheet') . '</span></a>';


$headbar .= '<a class="button" style="float: right" href="/foodle/' . $this->data['foodle']->identifier . '"><span>' . $this->t('refresh') . '</span></a>';

$this->data['headbar'] = $headbar;

$this->includeAtTemplateBase('includes/header.php'); 



if (isset($this->data['timezone'])) {
	echo('<div id="timezone">');
	
	echo('<form onchange="this.submit()" style="display: inline; margin: 0px; padding; 0px" action="?" method="get"');
	echo('<span>' . $this->t('selecttimezone') . ': ');
	
	$current = $this->data['timezone']->getTimeZone();
	if (isset($this->data['stimezone'])) {
		$current = $this->data['stimezone'];
	}
	echo($this->data['timezone']->getHTMLList($current, TRUE) . '');
	
	if ($current !== $this->data['foodle']->timezone) {
		echo('<br />This Foodle was created in the <a href="?timezone=' . urlencode($this->data['foodle']->timezone) . '"><strong>' . htmlspecialchars($this->data['foodle']->timezone) . '</strong></a> timezone.');
	}
	if ($current !== $this->data['timezone']->getTimeZone()) {
		echo('<br />Foodle detects your local timezone to be <a href="?timezone=' . urlencode($this->data['timezone']->getTimeZone()) . '"><strong>' . htmlspecialchars($this->data['timezone']->getTimeZone()) . '</strong></a>.');
	}
	
	
	echo('</span>');
	echo('</form>');
	echo('</div>');
}





$maxreached = FALSE;
if ($this->data['maxnum'] > 0) {

	// If restriction is on all entries
	if ($this->data['maxcol'] == 0) {
		if ($this->data['used'] >= $this->data['maxnum']) $maxreached = TRUE;
		
	// If restriction is on one column
	} else {
	
		// If you have not checked the specific column already....
		
		// echo '<pre>My response:';
		// print_r($this->data['myresponse']->response['data'][$this->data['maxcol']-1]);
		// echo '</pre>';
		
		
		if ($this->data['myresponse']->response['data'][$this->data['maxcol']-1] == '0') {
			if ($this->data['used'] >= $this->data['maxnum']) {
				$maxreached = TRUE;
			}
		}
		
	}
	
}
#echo 'max reached: ' . var_export($maxreached, TRUE);





if (!empty($this->data['datetimetext']) ||
	isset($this->data['foodle']->maxentries) ||
	!empty($this->data['expire'])) {


	echo ('<div class="datetimebox">');
		
	// ----- Event datetime ------ 
	if (!empty($this->data['datetimetext'])) {

		echo ('<img src="/res/datetime.png" style="margin-top: 5px; float: right" />');
		echo (' <p>' . $this->data['datetimetext'] . '</p>');
		
		echo(' <p style="font-size: small">[ <a href="/foodle/' . $this->data['foodle']->identifier . '?output=ical">Download iCalendar file</a> ]</p>');

	}
	
	// ----- Max entries ------ 
	if (isset($this->data['foodle']->maxentries)) { 
		echo '<div class="infobox maxnum">';
	#				echo '<img style="float: left" src="resources/closed.png" />';
	
		if (isset($maxreached) && $maxreached) {
			echo '<img style="float: left" src="/res/closed.png" alt="Closed" />';
		} else {
			echo '<img style="float: left" src="/res/system-users.png" alt="Open" />';
		}
	
	
		echo '<p><strong>' . $this->t('maxlimit') . '</strong></p>'; 
		#echo '<p style="clear: none; margin: 2px"><strong>There is a maximum limit of number of users on this Foodle.</strong></p>';
		
		$used = ' 0 ';
		if (!empty($this->data['used'])) $used = $this->data['used'];#  echo '<pre>Used:  ' . $this->data['used']; exit;
		echo '<p>' . $this->t('maxlimittext', 
			array(
				'%NUM%' => $used, 
				'%OF%' => $this->data['maxnum']
			) 
		) . '</p>'; 
		#echo '<p>Currently ' . $this->data['used'] . ' out of ' . $this->data['maxnum'] . ' is reached.</p>';
	
		echo '<div style="clear: both; height: 0px" ></div>';
		echo '</div>';
	}
	
		
	// ----- Expiration date time ------ 
	if (!empty($this->data['expire'])) { 
	
		echo '<div class="infobox expire">';
		
		#echo '<pre>Expire [' . $this->data['expire'] . ']  now[' . time() . ']</pre>';
		
		if ($this->data['expired']) {
			echo '<img style="float: left" src="/res/closed.png" alt="Closed" />';
			echo '<p style="clear: none; margin: 2px"><strong>' . $this->t('isclosed') . '</strong></p>';
			echo '<p>' . $this->t('closed') . '</p>';
			
		} else {
			echo '<img style="float: left" src="/res/time.png" alt="Open" />';
			echo '<p style="clear: none; margin: 2px"><strong>' . $this->t('hasexpire') . '</strong></p>';
			echo '<p>' . $this->data['expiretext']  . '</p>';
		}
	
		echo '<br style="clear: both; height: 0px" />';
		echo '</div>';
	} 
	
	echo ('</div>');

}



























echo '<h1>' . htmlspecialchars($this->data['foodle']->name) . '</h1>';

echo $this->data['foodle']->getDescription();

	
	
if (array_key_exists('facebookshare', $this->data) && $this->data['facebookshare']) {
	echo '<div style="" id="facebookshare" title="' . $this->t('facebookshareheader'). '">';
	echo '<p>' . $this->t('facebooklinkabout') . '<br /><input type="text" style="width: 90%" name="furl" value="' . htmlentities($this->data['url']) . '&amp;auth=facebook" /></p>';
	echo '<p><a class="button" style="display: block" href="http://www.facebook.com/sharer.php?u=' . urlencode($this->data['url'] . '&amp;auth=facebook') . '&amp;t=' . urlencode('Foodle: ' . $this->data['header']) . '">' . 
			'<span>' . $this->t('linkonfacebook') . '</span></a></p>';
	echo '</div>';
}











if (!$this->data['authenticated']) {

	if (array_key_exists('registerEmail', $this->data)) {
	
		echo('<div class="infobox registeremail">');
		echo '<form method="post" action="' . $this->data['foodlepath'] . '">';
		echo '<p>' . $this->t('register_email') . '</p>';
		echo '<p>' . $this->t('displayname') . ': <input type="text" name="setDisplayName" value="' . 
			(isset($this->data['displayname']) ? htmlentities($this->data['displayname']) : '') . '"/><br />';		
		echo '' . $this->t('email') . ': <input type="text" name="setEmail" /></p>';		
		echo '<input type="submit" name="reg" value="' . $this->t('emailreg_submit') . '" />';
		echo '</form>';
		echo('</div>');
	}
}



$discussion = $this->data['foodle']->getDiscussion();

?>



<div id="foodletabs"> 
	
	<ul style=" margin: 0px"> 
        <li><a href="#myresponse"><span><?php echo $this->t('myresponse'); ?></span></a></li> 
        <li><a href="#responses"><span><?php echo $this->t('allresponses'); ?></span></a></li> 
        <li><a href="#discussion"><span><?php 
			echo $this->t('discussion') . ' (' . count($discussion). ' ' . $this->t('entries') . ')'; 
		?></span></a></li> 
		<?php
		if (!empty($this->data['showsharing'])) {
			echo '<li><a href="#distribute"><span>' . $this->t('distribute') . '</span></a></li>';
		}
		if (!empty($this->data['showdebug'])) {
			echo '<li><a href="#showdebug"><span>' . $this->t('debug') . '</span></a></li>';
		}
		if (!empty($this->data['showdelete'])) {
			echo '<li><a href="#delete"><span>' . $this->t('delete') . '</span></a></li>';
		}
		
		?>
    </ul> 


    <div id="myresponse">
	<!-- BEGIN Responses -->
	<form method="post" action="<?php echo $this->data['foodlepath'] ?>">
		
<?php

if (isset($_REQUEST['timezone'])) {
	echo(' <input type="hidden" id="keeptimezone" name="timezone" value="' . htmlspecialchars($_REQUEST['timezone']) . '" />');	
}


?>
	
		<table class="list" style="width: 100%">
			
		<?php
			XHTMLCol::show($this, $this->data['foodle'], $this->data['showconfirmcolumn']);
		?>
			
						
		<tbody>

			<?php
		

		/*
		 * Include my response
		 */
		
		// if (!$editlocked) {
		// 	XHTMLResponseEntry::showEditable($this, $myresponse);
		// }
		$editable = TRUE;
		if($this->data['expired']) $editable = FALSE;
		if($maxreached) {
			if ($this->data['maxnum'] != 0) $editable = FALSE;
			if (!$this->data['myresponse']->loadedFromDB) { 
				$editable = FALSE; 
			}
		}
// 		echo '<pre>';		
// 		print_r($this->data); exit;
		
		
		if ($this->data['showconfirmcolumn']) {
#			if ($this->data['calenabled']) {
#				XHTMLResponseEntry::showEditableConfirm($this, $this->data['myresponsecal'], $editable, NULL, $this->data['authenticated']);
#			} else {
				XHTMLResponseEntry::showEditableConfirm($this, $this->data['myresponse'], $editable, NULL, $this->data['authenticated']);
#			}
		} else if ($this->data['calenabled']) {
			XHTMLResponseEntry::showEditable($this, $this->data['myresponse'], $editable, $this->data['myresponsecal'], $this->data['authenticated']);
		} else {
			XHTMLResponseEntry::showEditable($this, $this->data['myresponse'], $editable, NULL, $this->data['authenticated']);
		}
	
	
		echo '
		</tbody>	
		</table>';


		if (!$this->data['showconfirmcolumn']) {
		if (isset($this->data['responsetype']) && $this->data['responsetype'] === 'yesnomaybe') {
		
			echo('
				<div style="float: left">
					<table class="info"><tr>
						<td class="center yes">' . $this->t('yes') . '</td>
						<td class="center maybe">' . $this->t('maybe') . '</td>
						<td class="center no">' . $this->t('no') . '</td>
					</tr></table>
				</div>
			');
		
		}
		
		
		
		if ($this->data['calenabled']) {
			echo '<div>';

		
			echo '<div style="float: right; width: 400px" id="responsetyperadio">';

			echo '<p>' . $this->t('calendarenabled'). '</p>';
			
			if ($this->data['defaulttype'] == 'ical') {
				echo '	<input type="radio" id="radio1" name="radio" /><label for="radio1">' . $this->t('manualentry') . '</label>';
				echo '	<input type="radio" id="radio2" name="radio" checked="checked" /><label for="radio2">' . $this->t('calendarsync'). '</label>';				
			} else {
				echo '	<input type="radio" id="radio1" name="radio" checked="checked" /><label for="radio1">' . $this->t('manualentry') . '</label>';
				echo '	<input type="radio" id="radio2" name="radio" /><label for="radio2">' . $this->t('calendarsync'). '</label>';				
				
			}
			echo '<p>' . $this->t('calendardescr'). '</p>';
			echo ' </div>';

			echo '</div>';

		}
		}
		echo '<br style="height: 0px; clear: both;"/>';		
?>

	</form>
	

	


	</div> <!-- end #responses tab -->
	<!-- END Responses -->



    <div id="responses">




	<!-- BEGIN Responses -->
	<form method="post" action="<?php echo $this->data['foodlepath'] ?>">
	
		<table class="list" style="width: 100%">
			
		<?php
		
		
		XHTMLCol::show($this, $this->data['foodle'], $this->data['showconfirmcolumn']);
		?>
			
						
		<tbody>

		<?php

		/*
		 * Include others responses
		 */
		$responses = $this->data['foodle']->getResponses();	
		foreach($responses AS $response) {
			if ($this->data['showconfirmcolumn']) {
				XHTMLResponseEntry::showConfirm($this, $response);
			} else {
				XHTMLResponseEntry::show($this, $response ,$this->data['user']->isAdmin());
			}

		}

		$calculated = $this->data['foodle']->calculateColumns();
		$emailaddrs = $this->data['foodle']->getEmail();

		$colspan = 2 + count($this->data['foodle']->getExtraFields());
		
		echo '<tr><td style="text-align: right; padding-right: 1em" colspan="' . $colspan . '">Sum</td>';
		foreach ($calculated AS $calc) {
			echo '<td class="count '. (isset($calc['style']) ? $calc['style'] : '') . '">' . $calc['count'] . '</td>';
		}
		echo '<td> </td>';
		echo '</tr>';
	
		echo '</form>';
	
		if ($this->data['showfixtimeslow']) {
		

	
			$colspan = 2 + count($this->data['foodle']->getExtraFields());
			echo '<tr class="fixdaterow">
				<td style="text-align: right; padding-right: 1em" colspan="' . $colspan . '">' . $this->t('select_time') . '</td>';
			$i = 0;
			foreach ($calculated AS $calc) {
				echo '<td style="text-align: center">
					<form action="/fixdate/' . $this->data['foodle']->identifier . '" method="post">
						<input type="submit" name="fixdate" value="' . $this->t('select') . '" />
						<input type="hidden" name="col" value="' . $i . '">
					</form>
					</td>';
				$i++;
			}
			echo '<td> </td>';
			echo '</tr>';
		
		}	
		
			
		if (!$this->data['showconfirmcolumn']) {

	
			echo '<tr>';
			echo '<td colspan="' . $colspan . '"><span style="float: right"><img onclick="showemail(0)" class="email" src="/res/mail24.png" alt="' . $this->t('emailtoall'). '" title="' . $this->t('emailtoall'). '" /></span>
				' . $this->t('emailaddresses'). '</td>';
			foreach ($calculated AS $key => $calc) {
				echo '<td style="text-align: center" class=""><img onclick="showemail(' . ($key+1) . ')" class="email" alt="' . $this->t('emailtoonecol'). '" title="' . $this->t('emailtoonecol'). '" src="/res/mail24.png" /></td>';
			}
			echo '<td> </td>';
			echo '</tr>';
			
		}
	
	
		?>
		</tbody>	
		</table>
		
		<?php
		
		// echo '<pre>';
		// print_r($responses);
		// echo '</pre>';
		
		?>


	
	<?php
	
		if (!$this->data['showconfirmcolumn']) {
	
	?>
	
	<div id="emailbox">
		
		<?php
		
		foreach ($emailaddrs AS $key => $emails) {
			echo '<div class="wmd-ignore inneremailbox" style="display: none" id="inneremailbox' . $key . '" >';
			if (!empty($emails)) {
				echo htmlspecialchars(join(', ', $emails));
			} else {
				echo 'No e-mail addresses available';
			}
			echo '</div>';
		}

		?>
		<p><?php echo $this->t('emailinfo'); ?></p>
	</div><!-- end #emailbox -->

	<?php
	
	}
	?>





	</div> <!-- end #responses tab -->
	<!-- END Responses -->
	
	<?php

		echo('<div style="color: #bbb; font-size: 80%; float: right">' . htmlspecialchars($this->data['ownerid']) . '</div>');

	?>
<!-- </div> -->

<div id="discussion">
<!-- BEGIN discussion -->

<!-- 
<form method="post" action="<?php echo $this->data['foodlepath']; ?>">
	<input type="hidden" name="tab" value="1" />
	<input type="hidden" name="discussionentry" value="1" />

	<div style="margin: .2em 5em .2em 5em; ">
		<textarea class="wmd-ignore" name="message" style="clear: both; border: 1px solid #ccc; width: 100%; height: 5em"></textarea>
		<p><input type="submit" style="clear: both; " value="<?php echo $this->t('add'); ?>" /></p>
	</div>
</form>
 -->
	
<?php





echo '<form method="post" action="' .  $this->data['foodlepath'] . '">';
echo '	<input type="hidden" name="tab" value="1" />
	<input type="hidden" name="discussionentry" value="1" />';
echo '<div id="discussionouterbox" style="margin: .2em 5em .2em 5em; ">';

// A discussion entry
echo '<div style="border: 1px solid #bbb; margin-bottom: .7em" >';

// A discussion meta info box
echo '  <div style="float: left; 
	margin: 0px 0px 10px 0px; 
	padding: 2px .5em; 
	border-bottom: 1px solid #bbb; 
	border-right: 1px solid #bbb; 
	font-size: 90%; color: #777; background: #f6f6f6; width: 300px">';

if(isset($this->data['user'])) {

	echo '    <p style="margin: 0px; padding 0px; ">' . htmlspecialchars($this->data['user']->username) . '</p>';	

	$photourl = $this->data['user']->getPhotoURL('m');
	if ($photourl !== false) {
		echo '<img src="' . htmlspecialchars($photourl) . '" alt="Photo of user" style="float: right; border: 1px solid #777" />';
	}
	echo '<p>' . $this->data['user']->getOrgHTML() . '</p>';
}

#	echo '<br style="height: 0px; clear: both" />';
echo '  </div>';


// The discussion entry content (the text)
echo '  <div style="margin: 0px 0px 0px 350px; padding: 2px .5em;" >';

echo '<textarea class="wmd-ignore" name="message" style="clear: both; border: 1px solid #ccc; width: 100%; height: 5em"></textarea>';
echo '<p><input type="submit" style="clear: both; " value="' . $this->t('add') . '" /></p>';


echo   '</div>';	


echo '<br style="height: 0px; clear: both" />';	

echo '</div>';


echo '</div><!-- end #discussionouterbox -->';
echo '</form>';















echo '<div id="discussionouterbox" style="margin: .2em 5em .2em 5em; ">';
foreach($discussion AS $d) {
	
	// A discussion entry
	echo '<div style="border: 1px solid #bbb; margin-bottom: .7em" >';
	
	

	
	
	// A discussion meta info box
	echo '  <div style="float: left; 
		margin: 0px 0px 10px 0px; 
		padding: 2px .5em; 
		border-bottom: 1px solid #bbb; 
		border-right: 1px solid #bbb; 
		font-size: 90%; color: #777; background: #f6f6f6; width: 300px">';

	echo '    <p style="margin: 0px; padding 0px; ">' . htmlspecialchars($d['username']) . '</p>';	

	
	if(isset($d['user'])) {
		$photourl = $d['user']->getPhotoURL('m');
		if ($photourl !== false) {
			echo '<img src="' . htmlspecialchars($photourl) . '" alt="Photo of user" style="float: right; border: 1px solid #777" />';
		}
		echo '<p>' . $d['user']->getOrgHTML() . '</p>';
	}


	echo '    <p style="margin: 0px; padding 0px; ">' .$d['agotext'] . '</p>';
	
#	echo '<br style="height: 0px; clear: both" />';
	echo '  </div>';
	
	
	// The discussion entry content (the text)
	echo '  <div style="margin: 0px 0px 0px 350px; padding: 2px .5em;" >';
	echo htmlentities(strip_tags($d['message']));
	echo   '</div>';	
	

	echo '<br style="height: 0px; clear: both" />';	
	
	echo '</div>';
	
}
echo '</div><!-- end #discussionouterbox -->';



?>

</div> <!-- end #discussion tab -->


<?php
if ($this->data['showsharing']) {
	echo '<div id="distribute" style="margin: .2em 5em .2em 5em; ">';

	echo( '<p>' . $this->t('sharing') . '</p>');
	echo( '<p>' . $this->t('sharinglink') . '</p>');
	echo('<div class="sharinglink">' . 
		htmlentities($this->data['url']) . 
		'</div>');
	echo( '<p>' . $this->t('sharing2') . '</p>');
	
	if (isset($this->data['customDistribute']) && count($this->data['customDistribute']) > 0) {	
		foreach($this->data['customDistribute'] AS $cd) {
			$cd->show();
		}
	}

	echo '</div>';
}

if (!empty($this->data['showdebug'])) {
	echo '<div id="showdebug" style="margin: .2em 5em .2em 5em; ">';

	echo( '<h2>Page loading time</h2>');	
	$list = $this->data['timer'];
	
	echo '<ul>';
	foreach($list AS $l) {
		echo('<li>' .  number_format((float)$l[0] * (float)1000, 2) . ' ms ' . $l[1] . '</li>');
	}
	echo('</ul>');
	
	echo($this->data['debugUser']);


	echo( '<h2>Current User</h2>');	
	echo($this->data['debugUser']);

	echo( '<h2>Current Foodle</h2>');	
	echo($this->data['debugFoodle']);

	echo( '<h2>User calendar data</h2>');	
	echo($this->data['debugCalendar']);
	

	echo '</div>';
}

if ($this->data['showdelete']) {
	echo '<div id="delete" style="margin: .2em 5em .2em 5em; ">';

	echo( '<h2>' . $this->t('delete_this') . '</h2>');	
	
	echo('
			<form method="post" action="/delete/' . $this->data['foodle']->identifier . '">
				<p><input type="checkbox" id="confirmdelete" name="confirmdelete" value="yes">
				<label for="confirmdelete">' . $this->t('delete_confirm') . '</label></p>
				<p><input type="submit" id="deletefoodle" name="deletefoodle" value="' . $this->t('delete'). '" /></p>
			</form>
			
		');

	echo '</div>';
}

?>




</div><!-- end foodletabs -->
			
<?php $this->includeAtTemplateBase('includes/footer.php'); 
























