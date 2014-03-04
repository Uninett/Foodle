<?php 
	$this->includeAtTemplateBase('includes/header.php'); 

$user = $this->data['user'];



?>


<div class="container">
	<div class="row">
		<div class="col-lg-12 uninett-color-white uninett-padded"> 


		<h1 style="margin-bottom: 0px"><?php echo htmlspecialchars($user->username); ?></h1>


<?php

echo '<p style="color: #999; margin-top: 2px; margin-bottom: 35px"><tt>' . htmlspecialchars($user->userid) . '</tt></p>';



echo '<dl>';

echo ' <dt>Organization</dt>';
echo ' <dd>' . htmlspecialchars($user->org) ;
if (!empty($user->orgunit)) {
	echo ' › ' . htmlspecialchars($user->orgunit);
}

echo ' </dd>';



echo ' <dt>Email</dt>';
echo ' <dd>' . htmlspecialchars($user->email) . '</dd>';


echo ' <dt>Location</dt>';
echo ' <dd>' . htmlspecialchars($user->location) . '</dd>';

echo ' <dt>Timezone</dt>';
// echo ' <dd>' . htmlspecialchars($user->timezone) . '</dd>';
$current = $this->data['timezone']->getTimeZone();
echo( '<dd>' . $current . '</dd>');



echo '</dl>';


if ($this->data['user']->hasCalendar()) {
	echo  '<p><img style="" alt="Calendar" title="Calendar" class="" src="/res/calendar-export.png" /> ' .
		$this->t('userhascalendar') . '</p>';
}


echo '<h2>' . $this->t('shared_entries') . '</h2>';

if (!empty($this->data['sharedentries'])) {
	
	echo '<ul>';
	foreach($this->data['sharedentries'] AS $e) {
		
		echo '<li><a href="/foodle/' . htmlspecialchars($e['id']) . '">' . htmlspecialchars($e['name']) . '</a></li>';
		
		
	}
	echo '</ul>';
	
} else {
	echo '<p>None.</p>';
}




?>


	
	

<?php


	if (!empty($user->photol)) {
		$photourl = $user->getPhotoURL('l');
		if (!empty($photourl) ) {
			echo '<img style="max-width: 250px; border: 1px solid #888; margin: 2em 1em" src="' . htmlspecialchars($photourl) . '" />';
		}
		
	}



?>





			
	


			<h2>
				<?php echo $this->t('statistics'); ?>
			</h2>
			<p>
				....
			</p>
			


			<h2><?php echo $this->t('moreinfo'); ?></h2>
			<ul>
				<li><a href="https://rnd.feide.no/software/foodle/"><?php echo $this->t('foodlesoftware'); ?></a></li>
				<li><a href="https://rnd.feide.no/software/foodle/foodle-privacy-policy/"><?php echo $this->t('privacypolicy'); ?></a></li>
				<li><a href="http://rnd.feide.no"><?php echo $this->t('rndblog'); ?></a></li>
			</ul>



</div></div></div>

			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>