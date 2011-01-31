<?php 
	$this->includeAtTemplateBase('includes/header.php'); 

?>




<div class="columned">
	<div class="gutter"></div>
	<div class="col1">






		<h1><?php echo $this->t('welcomeheader'); ?></h1>

		<p><?php echo $this->t('welcometext'); ?></p>

		<p><?php 



		if ($this->data['authenticated']) {
		
			if (isset($this->data['user']->userid)) {
				echo $this->t('authtext', 
					array(
						'%DISPLAYNAME%' => $this->data['user']->username, 
						'%USERID%' => $this->data['user']->userid
					) 
				); 
			}
			
			if ($this->data['user']->hasCalendar()) {
				echo  '.</p><p><img style="" alt="Calendar" title="Calendar" class="" src="/res/calendar-export.png" /> ' .
					$this->t('youhavecalendar') . '';
			}
			
		} else {
			echo($this->t('is_anonymous'));
		}


		?>
		</p>

		<div id="createnew" style="">
			<!-- h2><?php echo $this->t('createnew'); ?></h2 -->
			<form method="post" action="create">
				<input type="submit" value="<?php echo $this->t('createnew'); ?>" />
			</form>
		</div>

		<h2><?php echo($this->t('statusupdates')); ?></h2>

		<?php

		echo('<div class="statusupdates">');

		foreach ($this->data['statusupdate'] AS $su) {

			if ($su['type'] == 'discussion') {
				echo('<div class="statusupdate">');
				echo('<h3><a href="/foodle/' . $su['foodleid'] .'#discussion">' . htmlspecialchars($su['name']) . '</a></h3>');
				echo('<p>' . $su['names'] . ' ' . $this->t('addeddiscussion') . '.</p>');
				echo('<p style="color: #999; font-size: small">' . FoodleUtils::date_diff(time() - $su['recent']) . ' ago</p>');
				echo('</div>');
				
			} else {
				
				echo('<div class="statusupdate">');
				echo('<h3><a href="/foodle/' . $su['foodleid'] .'#responses">' . htmlspecialchars($su['name']) . '</a></h3>');
				echo('<p>' . $su['names'] . ' ' . $this->t('respondedrecent') . '.</p>');
				echo('<p style="color: #999; font-size: small">' . FoodleUtils::date_diff(time() - $su['recent']) . ' ago</p>');
				echo('</div>');

			}

			#print_r($su);

		}
		echo('</div>');

		?>









	</div>
	<div class="col2">
			


		<?php
		if (is_array($this->data['ownerentries']) && count($this->data['ownerentries']) > 0) {
			echo('<h2>' . $this->t('youcreated') . '</h2>');
#			echo '<ul class="statusupdates">';
			foreach ($this->data['ownerentries'] AS $entry) {
				echo '<div class="lentry">';
				echo ' <div class="lheader"><a href="/foodle/' . $entry['id'] . '#responses">' . 
					htmlspecialchars($entry['name']) . '</a></div>';
#				echo ' <div class="lbody">' . mb_substr(strip_tags($entry['descr']), 0, 200, 'utf-8') . ' </div>';
#				echo '<pre>'; print_r($entry); echo '</pre>';
				echo '</div>';
			}
#			echo '</ul>';
		}
		?>







		<?php if (isset($this->data['allentries'])) { ?>
			<h2><?php echo $this->t('recent'); ?></h2>
			<?php
			if (is_array($this->data['allentries'])) {

				foreach ($this->data['allentries'] AS $entry) {
				echo '<div class="lentry">';
				echo ' <div class="lheader"><a href="/foodle/' . $entry['id'] . '#responses">' . 
					htmlspecialchars($entry['name']) . '</a></div>';
#				echo ' <div class="lbody">' . mb_substr(strip_tags($entry['descr']), 0, 200, 'utf-8') . ' </div>';
				echo ' <div class="lowner">' . $entry['owner'] . '</div>';
#				echo '<pre>'; print_r($entry); echo '</pre>';
				echo '</div>';
				}
			}
			?>
		<?php } ?>




			
	</div>
	<div class="col3">




			<h2>
				<?php echo $this->t('statistics'); ?>
			</h2>
			<p>
				<?php echo $this->t('cresponses', array('%NUM%' => $this->data['stats']['total7days']) ); ?>
			</p>
			

			<?php
			if (is_array($this->data['yourentries']) && count($this->data['yourentries']) > 0) {

				echo('<h2>' . $this->t('respondedto') . '</h2>');

				foreach ($this->data['yourentries'] AS $entry) {
					echo '<div class="lentry">';
					echo ' <div class="lheader"><a href="/foodle/' . $entry['id'] . '">' . 
						htmlspecialchars($entry['name']) . '</a></div>';
#					echo ' <div class="lbody">' . mb_substr(strip_tags($entry['descr']), 0, 200, 'utf-8').  ' </div>';
					echo ' <div class="lowner">' . $entry['owner'] . '</div>';
					
					
#				echo '<pre>'; print_r($entry); echo '</pre>';
					echo '</div>';
				}

			}
			?>


			<h2><?php echo $this->t('moreinfo'); ?></h2>
			<ul>
				<li><a href="https://rnd.feide.no/software/foodle/"><?php echo $this->t('foodlesoftware'); ?></a></li>
				<li><a href="https://rnd.feide.no/software/foodle/foodle-privacy-policy/"><?php echo $this->t('privacypolicy'); ?></a></li>
				<li><a href="http://rnd.feide.no"><?php echo $this->t('rndblog'); ?></a></li>
			</ul>





	</div><!-- /#col3 -->
	<br style="height: 0px; clear: both">
</div>




			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>