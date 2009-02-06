<?php 
	$this->includeAtTemplateBase('includes/header.php'); 

?>


<div style="text-align: left; width: 300px; clear: right; float: right; border: 1px solid #ccc; margin: .2em; padding: .2em"
	<h2><?php echo $this->t('youcreated'); ?></h2>
	<?php
	if (is_array($this->data['ownerentries'])) {
		echo '<ul>';
		foreach ($this->data['ownerentries'] AS $entry) {
			echo '<li><a href="foodle.php?id=' . $entry['id'] . '">' . 
				$entry['name'] . '</a> - ' . substr(strip_tags($entry['descr']), 0, 200) .
			'</li>';
		}
		echo '</ul>';
	}
	?>
</div>
<div style="text-align: left; width: 300px; clear: right; float: right; border: 1px solid #ccc; margin: .2em; padding: .2em"
	<h2><?php echo $this->t('respondedto'); ?></h2>
	<?php
	if (is_array($this->data['yourentries'])) {
		echo '<ul>';
		foreach ($this->data['yourentries'] AS $entry) {
			echo '<li><a href="foodle.php?id=' . $entry['id'] . '">' . 
				$entry['name'] . '</a> - ' . substr(strip_tags($entry['descr']), 0, 100) .
			'</li>';
		}
		echo '</ul>';
	}
	?>
</div>

<?php if (isset($this->data['allentries'])) { ?>
<div style="text-align: left; width: 300px; clear: right; float: right; border: 1px solid #ccc; margin: .2em; padding: .2em"
	<h2><?php echo $this->t('recent'); ?></h2>
	<?php
	if (is_array($this->data['allentries'])) {
		echo '<ul>';
		foreach ($this->data['allentries'] AS $entry) {
			echo '<li><a href="foodle.php?id=' . $entry['id'] . '">' . 
				$entry['name'] . '</a> - ' . substr(strip_tags($entry['descr']), 0, 100) .
			'</li>';
		}
		echo '</ul>';
	}
	?>
</div>
<?php }Ê?>

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

<h2><?php echo $this->t('createnew'); ?></h2>

<form method="get" action="schedule.php"><input type="submit" value="<?php echo $this->t('createnew'); ?>" /></form>


<h2><?php echo $this->t('moreinfo'); ?></h2>
<ul>
	<li><a href="http://rnd.feide.no"><?php echo $this->t('rndblog'); ?></a></li>
	<li><a href="http://rnd.feide.no/content/foodle-users-guide"><?php echo $this->t('usermanual'); ?></a></li>
</ul>



<br style="clear: both" />
			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>