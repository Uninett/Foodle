<?php 
	$this->includeAtTemplateBase('includes/header.php'); 

?>

<div style="text-align: left; width: 300px; clear: right; float: right; border: 1px solid #ccc; margin: .2em; padding: .2em">
<h2>
	<?php echo $this->t('statistics'); ?>
</h2>
<p>
	<?php echo $this->t('cresponses', array('%NUM%' => $this->data['stats']['total7days']) ); ?>
</p>
</div>


<?php
if (is_array($this->data['ownerentries']) && count($this->data['ownerentries']) > 0) {
	echo('<div style="text-align: left; width: 300px; clear: right; float: right; border: 1px solid #ccc; margin: .2em; padding: .2em">');
	echo('<h2>' . $this->t('youcreated') . '</h2>');
	echo '<ul>';
	foreach ($this->data['ownerentries'] AS $entry) {
		echo '<li><a href="foodle.php?id=' . $entry['id'] . '">' . 
			htmlspecialchars($entry['name']) . '</a> - ' . mb_substr(strip_tags($entry['descr']), 0, 200, 'utf-8') .
		'</li>';
	}
	echo '</ul></div>';
}
?>



	
<?php
if (is_array($this->data['yourentries']) && count($this->data['yourentries']) > 0) {
	echo('<div style="text-align: left; width: 300px; clear: right; float: right; border: 1px solid #ccc; margin: .2em; padding: .2em">');
	echo('<h2>' . $this->t('respondedto') . '</h2>');
	echo '<ul>';
	foreach ($this->data['yourentries'] AS $entry) {
		echo '<li><a href="foodle.php?id=' . $entry['id'] . '">' . 
			htmlspecialchars($entry['name']) . '</a> - ' . mb_substr(strip_tags($entry['descr']), 0, 100, 'utf-8') .
		'</li>';
	}
	echo '</ul></div>';
}
?>



<?php if (isset($this->data['allentries'])) { ?>
<div style="text-align: left; width: 300px; clear: right; float: right; border: 1px solid #ccc; margin: .2em; padding: .2em"
	<h2><?php echo $this->t('recent'); ?></h2>
	<?php
	if (is_array($this->data['allentries'])) {
		echo '<ul>';
		foreach ($this->data['allentries'] AS $entry) {
			echo '<li><a href="foodle.php?id=' . $entry['id'] . '">' . 
				htmlspecialchars($entry['name']) . '</a> - ' . mb_substr(strip_tags($entry['descr']), 0, 100, 'utf-8') .
			'</li>';
		}
		echo '</ul>';
	}
	?>
</div>
<?php } ?>

<h1><?php echo $this->t('welcomeheader'); ?></h1>

<p><?php echo $this->t('welcometext'); ?></p>

<p><?php 

if ($this->data['authenticated']) {
	echo $this->t('authtext', 
	array(
		'%DISPLAYNAME%' => $this->data['displayname'], 
		'%USERID%' => $this->data['userid']
	) ); 
} else {
	echo($this->t('is_anonymous'));
	echo '<a class="button" style="" href="' . htmlentities($this->data['loginurl']) . '"><span>' . $this->t('login') . '</span></a>';
	if ($this->data['enableFacebookAuth']) {
		echo '<a class="button" style="" href="?auth=facebook"><span>' . $this->t('facebooklogin') . '</span></a>';
	}
}
?>
</p>

<div style="clear: left; border: 1px solid #ccc; background: #f4f4f4; padding:1em; width: 300px; margin: 3em .5em .5em .5em">
	<!-- h2><?php echo $this->t('createnew'); ?></h2 -->
	<form method="get" action="schedule.php"><input type="submit" value="<?php echo $this->t('createnew'); ?>" /></form>
</div>

<h2><?php echo($this->t('statusupdates')); ?></h2>

<?php

echo('<ul>');
foreach ($this->data['statusupdate'] AS $su) {
	
	
	if ($su['type'] == 'discussion') {
		echo('<li><a href="foodle.php?id=' . $su['foodleid'] .'&amp;tab=1">' .  date('j. M, H:i (l)', strtotime($su['created'])) . 
			' <strong>' . $su['username'] . '</strong> ' . 
			$this->t('has_messaged') . 
			' <strong>' . $su['name'] . 
			'</strong></a></li>');
		
	} else {
		echo('<li><a href="foodle.php?id=' . $su['foodleid'] .'">' .  date('j. M, H:i (l)', strtotime($su['created'])) . 
			' <strong>' . $su['username'] . '</strong> ' . 
			$this->t('has_responded') . 
			' <strong>' . $su['name'] . 
			'</strong></a></li>');
		
	}
	
	#print_r($su);
	
}
echo('</ul>');

?>

<h2><?php echo $this->t('moreinfo'); ?></h2>
<ul>
	<li><a href="http://rnd.feide.no"><?php echo $this->t('rndblog'); ?></a></li>
	<li><a href="http://rnd.feide.no/content/foodle-users-guide"><?php echo $this->t('usermanual'); ?></a></li>
</ul>



<br style="clear: both" />
			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>