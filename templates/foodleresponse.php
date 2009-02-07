<?php 

$headbar = '<a class="button" style="float: right; " 
		title="Comma separated file, works with Excel." href="csv.php?id=' . $_REQUEST['id'] . '">
	<span><!-- <img src="resources/spreadsheet.png" /> -->' . 
		$this->t('open_in_spreadsheet') . '</span></a>';
$headbar .= '<a class="button" style="float: right" href="rss.php?id=' . $_REQUEST['id'] . '">
	<span><!-- img src="resources/feed-icon-14x14.png"  / -->
	' . $this->t('subscribe_rss') . '</span></a>';
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
		echo '<img style="float: right" alt="' . $response['email'] . '" title="' . $response['email'] . '" class="" src="resources/mail16.png" />';
	}
	echo $response['username'] . ' (<tt>' . $response['userid'] . '</tt>)';
	echo '</td>';
	
	foreach ($response['response'] AS $no => $entry) {
	
		if ($entry) {
			$counter[$no]++;
			echo '<td class="yes center"><img class="yesimg" alt="No" src="resources/yes.png" /></td>';
		} else {
			echo '<td class="no center"><img class="yesimg" alt="Yes" src="resources/no2trans.png" /></td>';
		}

	}
	echo '<td>' . $response['updated'] . '</td>';
	echo '</tr>';
	
	if (!empty($response['notes'])) {
		echo '<tr><td id="' . sha1($response['userid']) . '" class="commentline" style="display: none" colspan="' . (count($response['response']) + 2) . '">';
		echo $response['notes'];
		echo '</td></tr>';
	}

}
?>

	
	


	<h1><?php if (isset($this->data['header'])) { echo $this->data['header']; } else { echo "Some error occured"; } ?></h1>

	<?php echo str_replace(array("\r\n\r\n", "\n\n", "\r\r"), '<p>' , $this->data['descr']); ?>
	
	<?php 
	
	
	echo '<div style="" id="facebookshare" title="' . $this->t('facebookshareheader'). '">';
	echo '<p>' . $this->t('facebooklinkabout') . '<br /><input type="text" style="width: 90%" name="furl" value="' . htmlentities($this->data['url']) . '&amp;auth=facebook" /></p>';
	echo '<p><a class="button" style="display: block" href="http://www.facebook.com/sharer.php?u=' . urlencode($this->data['url'] . '&amp;auth=facebook') . '&amp;t=' . urlencode('Foodle: ' . $this->data['header']) . '">' . 
			'<span>' . $this->t('linkonfacebook') . '</span></a></p>';
	echo '</div>';	



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
			echo '<div class="expire">';
#				echo '<img style="float: left" src="resources/closed.png" />';

			if ($editlocked) {
				echo '<img style="float: left" src="resources/closed.png" alt="Closed" />';
			} else {
				echo '<img style="float: left" src="resources/system-users.png" alt="Open" />';
			}


			echo '<p style="clear: none; margin: 2px"><strong>There is a maximum limit of number of users on this Foodle.</strong></p>';
			echo '<p>Currently ' . $this->data['used'] . ' out of ' . $this->data['maxnum'] . ' is reached.</p>';

			echo '<br style="clear: both; height: 0px" />';
			echo '</div>';
		} 




	
		if (!empty($this->data['expire'])) { 
		
			echo '<div class="expire">';
			
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
		echo '<form method="post" action="foodle.php" style="display: block; border: 1px solid #999; background: #eee; padding: 1em 2em 0em 2em;">
				<input type="hidden" name="id" value="' . $this->data['identifier'] . '" />';
		echo '<p>' . $this->t('register_email') . '</p>';
		echo '<p>' . $this->t('displayname') . ': <input type="text" name="setDisplayName" value="' . 
			(isset($this->data['displayname']) ? $this->data['displayname'] : '') . '"/><br />';		
		echo '' . $this->t('email') . ': <input type="text" name="setEmail" /></p>';		
		echo '<p><input type="submit" name="reg" value="' . $this->t('emailreg_submit') . '" /></p>';		
		echo '</form>';
	}
}




?>






<form method="post" action="foodle.php">
<input type="hidden" name="id" value="<?php echo $this->data['identifier']; ?>" />

	
	<h2><?php echo $this->t('responses'); ?></h2>
	
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
		
		echo '<input type="text" name="username" value="' . $this->data['yourentry']['username'] . '" /> (<tt>' . $this->data['yourentry']['userid']. '</tt>)';
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
			<input type="text" id="comment" class="comment" name="comment" value="' .$this->data['yourentry']['notes'] . '" /></td></tr>';
		

		
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
		style="font-family: monospace; white-space: pre; border: 1px solid #ccc; padding: .2em 2em; margin: .1em
		 white-space: pre-wrap;       /* css-3 */
 white-space: -moz-pre-wrap;  /* Mozilla, since 1999 */
 white-space: -pre-wrap;      /* Opera 4-6 */
 white-space: -o-pre-wrap;    /* Opera 7 */
 word-wrap: break-word;       /* Internet Explorer 5.5+ */
		"></div>
	<p><?php echo $this->t('emailinfo'); ?></p>
</div>



</form>
	

			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>