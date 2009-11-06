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

		<div id="createnew" style="">
			<!-- h2><?php echo $this->t('createnew'); ?></h2 -->
			<form method="get" action="schedule.php"><input type="submit" value="<?php echo $this->t('createnew'); ?>" /></form>
		</div>

		<h2><?php echo($this->t('statusupdates')); ?></h2>

		<?php

		echo('<ul class="statusupdates">');
		foreach ($this->data['statusupdate'] AS $su) {


			if ($su['type'] == 'discussion') {
				echo('<li>');
				echo ('<a href="foodle.php?id=' . $su['foodleid'] .'&amp;tab=1">');

				echo ('' . date('j. M, H:i (l)', strtotime($su['created'])) );
				echo (' <strong>' . $su['username'] . '</strong> ' . 
					$this->t('has_messaged') . 
					' <strong>' . $su['name'] . 
					'</strong>');
					
				echo ('</a>');
				echo ('</li>');

			} else {
				
				echo('<li>');
				echo ('<a href="foodle.php?id=' . $su['foodleid'] .'&amp;tab=0">');

				echo ('' . date('j. M, H:i (l)', strtotime($su['created'])) );
				echo (' <strong>' . $su['username'] . '</strong> ' . 
					$this->t('has_responded') . 
					' <strong>' . $su['name'] . 
					'</strong>');
					
				echo ('</a>');
				echo ('</li>');
				

			}

			#print_r($su);

		}
		echo('</ul>');

		?>









	</div>
	<div class="col2">
			


		<?php
		if (is_array($this->data['ownerentries']) && count($this->data['ownerentries']) > 0) {
			echo('<h2>' . $this->t('youcreated') . '</h2>');
#			echo '<ul class="statusupdates">';
			foreach ($this->data['ownerentries'] AS $entry) {
				echo '<div class="lentry">';
				echo ' <div class="lheader"><a href="foodle.php?id=' . $entry['id'] . '">' . 
					htmlspecialchars($entry['name']) . '</a></div>';
				echo ' <div class="lbody">' . mb_substr(strip_tags($entry['descr']), 0, 200, 'utf-8');
				echo ' </div>';
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
				echo ' <div class="lheader"><a href="foodle.php?id=' . $entry['id'] . '">' . 
					htmlspecialchars($entry['name']) . '</a></div>';
				echo ' <div class="lbody">' . mb_substr(strip_tags($entry['descr']), 0, 200, 'utf-8');
				echo ' </div>';
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
					echo ' <div class="lheader"><a href="foodle.php?id=' . $entry['id'] . '">' . 
						htmlspecialchars($entry['name']) . '</a></div>';
					echo ' <div class="lbody">' . mb_substr(strip_tags($entry['descr']), 0, 200, 'utf-8');
					echo ' </div>';
					echo ' <div class="lowner">' . $entry['owner'] . '</div>';
	#				echo '<pre>'; print_r($entry); echo '</pre>';
					echo '</div>';
				}

			}
			?>


			<h2><?php echo $this->t('moreinfo'); ?></h2>
			<ul>
				<li><a href="http://rnd.feide.no"><?php echo $this->t('rndblog'); ?></a></li>
				<li><a href="http://rnd.feide.no/content/foodle-users-guide"><?php echo $this->t('usermanual'); ?></a></li>
			</ul>





	</div><!-- /#col3 -->
	<br style="height: 0px; clear: both">
</div>





			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>