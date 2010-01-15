<?php 

$headbar = '<a class="button" style="float: right; " 
		title="Comma separated file, works with Excel." href="csv.php?id=' . $_REQUEST['id'] . '">
	<span><!-- <img src="resources/spreadsheet.png" /> -->' . 
		$this->t('open_in_spreadsheet') . '</span></a>';

/*
$headbar .= '<a class="button" type="application/rss+xml" style="float: right" href="rss.php?id=' . $_REQUEST['id'] . '">
	<span><!-- img src="resources/feed-icon-14x14.png"  / -->
	' . $this->t('subscribe_rss') . '</span></a>';
*/

$headbar .= '<a class="button" style="float: right" href="foodle.php?id=' . $_REQUEST['id'] . '"><span>' . $this->t('refresh') . '</span></a>';

$this->data['headbar'] = $headbar;

$this->includeAtTemplateBase('includes/header.php'); 


function show_response($response) {
	global $counter;
	echo '<tr>';
	
	
	if (!empty($response['notes'])) {
		echo '<td rowspan="2" style="text-align: center; vertical-align: top; padding-top: 6px">';
		echo '<img style="margin: 0px" onclick="toggle(\'' . sha1($response['userid']) . '\')" src="resources/notes.png" />';
		echo '</td>';
	} else {
		echo '<td>&nbsp;</td>';
	}
	
	//echo '<td>' . $response['userid'] . '</td>';
	echo '<td style="text-align: left">';
	if (!empty($response['email'])) {
		echo '<img style="float: right" alt="' . htmlspecialchars($response['email']) . '" title="' . htmlspecialchars($response['email']) . '" class="" src="resources/mail16.png" />';
	}
	$userid = $response['userid'];
	if (preg_match('|^@(.*)$|', $userid, $matches))
		$userid = '<a href="http://twitter.com/' . htmlspecialchars($matches[1]) . '">' . htmlspecialchars($userid) . '</a>';
	// echo $response['username'] . ' (<tt>' . $userid . '</tt>)';
	echo htmlspecialchars($response['username']) . ' (' . htmlspecialchars($userid) . ')';
	echo '</td>';
	
	foreach ($response['response'] AS $no => $entry) {
	
		if ($entry) {
			$counter[$no]++;
			echo '<td class="yes center"><img class="yesimg" alt="No" src="resources/yes.png" /></td>';
		} else {
			echo '<td class="no center"><img class="yesimg" alt="Yes" src="resources/no2trans.png" /></td>';
		}

	}
	echo '<td>' . htmlspecialchars($response['updated']) . '</td>';
	echo '</tr>';
	
	if (!empty($response['notes'])) {
		echo '<tr><td id="' . sha1($response['userid']) . '" class="commentline" style="display: none" colspan="' . (count($response['response']) + 2) . '">';
		echo htmlspecialchars($response['notes']);
		echo '</td></tr>';
	}

}
?>

	
	


	<h1><?php if (isset($this->data['header'])) { echo $this->data['header']; } else { echo "Some error occured"; } ?></h1>

	<?php echo str_replace(array("\r\n\r\n", "\n\n", "\r\r"), '<p>' , $this->data['descr']); ?>
	
	<?php 
	
	
	if (array_key_exists('facebookshare', $this->data) && $this->data['facebookshare']) {
		echo '<div style="" id="facebookshare" title="' . $this->t('facebookshareheader'). '">';
		echo '<p>' . $this->t('facebooklinkabout') . '<br /><input type="text" style="width: 90%" name="furl" value="' . htmlentities($this->data['url']) . '&amp;auth=facebook" /></p>';
		echo '<p><a class="button" style="display: block" href="http://www.facebook.com/sharer.php?u=' . urlencode($this->data['url'] . '&amp;auth=facebook') . '&amp;t=' . urlencode('Foodle: ' . $this->data['header']) . '">' . 
				'<span>' . $this->t('linkonfacebook') . '</span></a></p>';
		echo '</div>';	
	}


	$editlocked = FALSE;
	if ($this->data['expired']) $editlocked = TRUE;
	
	
	$maxreached = FALSE;
	if ($this->data['maxnum'] > 0) {
	
		// If restriction is on all entries
		if ($this->data['maxcol'] == 0) {
			if ($this->data['used'] >= $this->data['maxnum']) $maxreached = TRUE;
			
		// If restriction is on one column
		} else {
		
			// If you have not checked the specific column already....
			if ($this->data['yourentry']['response'][$this->data['maxcol']-1] == '0') {
				if ($this->data['used'] >= $this->data['maxnum']) $maxreached = TRUE;
			} 
			
		}
		
	}
	if ($maxreached) $editlocked = TRUE;


		if ($this->data['maxnum'] > 0) { 
			echo '<div class="infobox maxnum">';
#				echo '<img style="float: left" src="resources/closed.png" />';

			if ($editlocked) {
				echo '<img style="float: left" src="resources/closed.png" alt="Closed" />';
			} else {
				echo '<img style="float: left" src="resources/system-users.png" alt="Open" />';
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




	
		if (!empty($this->data['expire'])) { 
		
			echo '<div class="infobox expire">';
			
			if ($this->data['expired']) {
				echo '<img style="float: left" src="resources/closed.png" alt="Closed" />';
				echo '<p style="clear: none; margin: 2px"><strong>' . $this->t('isclosed') . '</strong></p>';
				echo $this->t('closed');
				
			} else {
				echo '<img style="float: left" src="resources/time.png" alt="Open" />';
				echo '<p style="clear: none; margin: 2px"><strong>' . $this->t('hasexpire') . '</strong></p>';
				echo $this->data['expiretext'];
			}
		
			
			
			echo '<br style="clear: both; height: 0px" />';
			echo '</div>';
		} 
	
	?>



<?php

if (!$this->data['authenticated']) {
	if ($this->data['registerEmail']) {
		echo('<div class="infobox registeremail">');
		echo '<form method="post" action="foodle.php">
				<input type="hidden" name="id" value="' . htmlspecialchars($this->data['identifier']) . '" />';
		echo '<p>' . $this->t('register_email') . '</p>';
		echo '<p>' . $this->t('displayname') . ': <input type="text" name="setDisplayName" value="' . 
			(isset($this->data['displayname']) ? htmlentities($this->data['displayname']) : '') . '"/><br />';		
		echo '' . $this->t('email') . ': <input type="text" name="setEmail" /></p>';		
		echo '<input type="submit" name="reg" value="' . $this->t('emailreg_submit') . '" />';
		echo '</form>';
		echo('</div>');
	}
}

?>



<div id="foodletabs"> 
	
	<ul style=" margin: 0px"> 
        <li><a href="#responses"><span><?php echo $this->t('responses'); ?></span></a></li> 
        <li><a href="#discussion"><span><?php 
			echo $this->t('discussion') . ' (' . count($this->data['discussion']). ' ' . $this->t('entries') . ')'; 
		?></span></a></li> 
    </ul> 
    <div id="responses">


	<!-- BEGIN Responses -->
	<form method="post" action="foodle.php">
	<input type="hidden" name="id" value="<?php echo htmlspecialchars($this->data['identifier']); ?>" />
	
		<table class="list" style="width: 100%"><thead>
	
			<tr>
				<th rowspan="2" style="width: 20px; padding: 3px 1px 1px 1px">
					<img alt="notes" src="resources/notes.png" />
				</th>
				<th rowspan="2"><?php echo $this->t('name'); ?></th>
			
			<?php
		
			$secondrow = array();
			foreach ($this->data['columns'] AS $head => $nextrow) {
			
				if ($nextrow == null) {
					echo '<th rowspan="2">' . $head . '</th>';
				} else {
					echo '<th colspan="' . count($nextrow) . '">' . $head . '</th>';
					foreach($nextrow AS $new) {
						$secondrow[] = $new;
					}
				}
			}
			echo '<th rowspan="2" style="width: 4em">' . $this->t('updated') . '</th>';
			echo '</tr>';
		
			if (count($secondrow) > 0) {
				echo '<tr>';
				foreach ($secondrow AS $entry) {
					echo '<th>' . $entry . '</th>';
				}
				echo('</tr>');
			}
			?>
	
	
			</thead>
		<tbody>
		<?php
		global $counter;
		$counter = array_fill(0,count($this->data['yourentry']['response']), 0 );
	
	
	
		if ($editlocked) {
		
			if (!$this->data['yourentry']['updated'] == 'expired') {
				show_response($this->data['yourentry']);
			}
		
	
		} else {
			/*
			if (!$this->data['expired']) { 
				echo '<tr class="you"><td style="text-align: right" colspan="' . (count($this->data['yourentry']['response']) + 3) . '">';
				echo '<input type="submit" name="save" value="Submit your response" />';
				echo '</td></tr>';
			}
			*/
			echo '<tr class="you">';
		
			echo '<td>&nbsp;</td><td>';
		
			// Only show add a comment entry if comment is not already added.
			if (empty($this->data['yourentry']['notes'])) {
				echo '<a style="float: right" id="ac" >' . $this->t('addcomment') . '</a>';
			}
		
			echo '<input type="text" name="username" value="' . htmlspecialchars($this->data['yourentry']['username']) . '" /> (<tt>' . htmlspecialchars($this->data['yourentry']['userid']). '</tt>)';
			echo '</td>';
			//echo '<td><input type="text" name="username" value="' . $this->data['yourentry']['username'] . '" /></td>';
		
		
		
		
			foreach ($this->data['yourentry']['response'] AS $no => $entry) {
				if ($entry == '1') {
					#$counter[$no]++;
					echo '<td class="yes center"><input type="checkbox" name="myresponse[]" checked="checked" value="' . $no . '" /></td>';
				
				} else {
					echo '<td class="no center"><input type="checkbox" name="myresponse[]" value="' . $no . '"  /></td>';
				}
			}

			if (!$this->data['expired']) { 
				if ($this->data['thisisanewentry'] === 0) {
					echo '<td style="text-align: center"><input type="submit" name="save" value="' .  $this->t('update') . '" /></td>';	
				} else {
					echo '<td style="text-align: center"><input type="submit" name="save" value="' . $this->t('submit') . '" /></td>';	
				}
			} else {
				echo '<td>' . $this->data['yourentry']['updated'] . '</td>';
			}
			echo '</tr>	';
		
			$shide = '';	
			if (empty($this->data['yourentry']['notes'])) $shide = 'display: none';
			echo '<tr style="' . $shide . '" id="commentfield" class="you"><td colspan="' . (count($this->data['yourentry']['response']) + 3) . '">
				<input type="text" id="comment" class="comment" name="comment" value="' . htmlspecialchars($this->data['yourentry']['notes']) . '" /></td></tr>';
		

		
		}
	

	
	
	
	#	echo '<pre>';	print_r($this->data); exit;

		foreach ($this->data['otherentries'] AS $response) {
		
			show_response($response);
		

		
		}
		$highest = max($counter);
		echo '<tr><td style="text-align: right; padding-right: 1em" colspan="2">Sum</td>';
		foreach ($counter AS $num) {
			if ($num == $highest) {
				echo '<td class="count highest">' . $num . '</td>';
			} else {
				echo '<td class="count">' . $num . '</td>';
			}
		}
		echo '<td>&nbsp;</td>';
		echo '</tr>';
	
	
		echo '<tr>';
		echo '<td colspan="2"><span style="float: right"><img onclick="showemail(0)" class="email" src="resources/mail24.png" alt="' . $this->t('emailtoall'). '" title="' . $this->t('emailtoall'). '" /></span>
			' . $this->t('emailaddresses'). '</td>';
		foreach ($counter AS $k => $num) {
			echo '<td style="text-align: center" class=""><img onclick="showemail(' . ($k+1) . ')" class="email" alt="' . $this->t('emailtoonecol'). '" title="' . $this->t('emailtoonecol'). '" src="resources/mail24.png" /></td>';
		}
		echo '<td>&nbsp;</td>';
		echo '</tr>';
	
	
		?>
		</tbody>	
		</table>


	
	<div id="emailbox" style="display: none">
		<div class="wmd-ignore" id="inneremailbox" 
			style="font-family: monospace; white-space: pre; border: 1px solid #ccc; padding: .2em 2em; margin: .1em;
			 white-space: pre-wrap;       /* css-3 */
	 white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
	 white-space: -pre-wrap;      /* Opera 4-6 */
	 white-space: -o-pre-wrap;    /* Opera 7 */
	 word-wrap: break-word;       /* Internet Explorer 5.5+ */
			"></div>
		<p><?php echo $this->t('emailinfo'); ?></p>
	</div>



	</form>


	</div>
	<!-- END Responses -->
	
	<?php

		echo('<div style="color: #bbb; font-size: 80%; float: right">' . htmlspecialchars($this->data['ownerid']) . '</div>');

	?>
</div>

<div id="discussion">
<!-- BEGIN Responses -->

<form method="post" action="foodle.php">
	<input type="hidden" name="id" value="<?php echo $this->data['identifier']; ?>" />
	<input type="hidden" name="tab" value="1" />

	<div style="margin: .2em 5em .2em 5em; ">
		<textarea class="wmd-ignore" name="message" style="clear: both; border: 1px solid #ccc; width: 100%; height: 5em"></textarea>
		<p><input type="submit" style="clear: both; " value="<?php echo $this->t('add'); ?>" /></p>
	</div>
</form>
	
<?php

echo '<div style="margin: .2em 5em .2em 5em; ">';
foreach($this->data['discussion'] AS $d) {
	
	echo '<div style="border: 1px solid #bbb; margin-bottom: .7em" >';
	echo '  <div style="margin: 0px; padding: 2px .5em; border-bottom: 1px solid #bbb; font-size: 90%; color: #777; background: #f6f6f6">';
	echo '    <p style="margin: 0px; padding 0px; float: right">' .$d['agotext'] . '</p>';
	echo '    <p style="margin: 0px; padding 0px; float: left">' .htmlspecialchars($d['username']) . '</p>';
	echo '<br style="height: 0px; clear: both" />';
	echo '  </div>';
	echo '  <div style="margin: 0px; padding: 2px .5em;" >';
	echo htmlentities(strip_tags($d['message']));
	echo   '</div>';
	
	echo '</div>';
	
}
echo '</div>';

// echo '<pre>';
// print_r($this->data['discussion']);
// echo '</pre>';


?>

</div>



</div>
			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>