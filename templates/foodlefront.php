<?php 

	

if (!empty($this->data['userToken'])) {
 	$this->data['head'] = '

	<script type="text/javascript" src="/res/js/foodle-data.js"></script>
	<script type="text/javascript" src="/res/js/foodle-front.js"></script>
	

		<script type="text/javascript" charset="utf-8">
			$(document).ready(function() {
				Foodle_Front_View();
			});
		</script>
 	';

}

 	
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


		<h2>
			<?php echo $this->t('statistics'); ?>
		</h2>
		<p>
			<?php echo $this->t('cresponses', array('%NUM%' => $this->data['stats']['total7days']) ); ?>
		</p>



		<h2><?php echo $this->t('moreinfo'); ?></h2>
		<ul>
			<li><a href="https://rnd.feide.no/software/foodle/"><?php echo $this->t('foodlesoftware'); ?></a></li>
			<li><a href="https://rnd.feide.no/software/foodle/foodle-privacy-policy/"><?php echo $this->t('privacypolicy'); ?></a></li>
			<li><a href="http://rnd.feide.no"><?php echo $this->t('rndblog'); ?></a></li>
		</ul>



	</div>
	
	
	
	<div class="col2">

		<?php
			
			if (!empty($this->data['mygroups'])) {
				echo '<h2>' . $this->t('groups') . '</h2>';
				foreach($this->data['mygroups'] AS $group) {
					echo '<div><img src="/res/group.png" /> <a href="group/' . htmlspecialchars($group['id']) . '">' . htmlspecialchars($group['name']) . '</a></div>';
				}
				echo '<p><a href="/groups">Manage groups</a>.</p>';
			
			}
		
// 			echo '<pre>';
// 			print_r($this->data['mygroups']);
// 			echo '</pre>';
		
		?>

			
	</div>
	<div class="col3">

			<?php
			
				if ($this->data['authenticated']) {			
					
					echo '<h2>Upcomming</h2><div id="upcomming"></div>';
					
					echo '<p id="upcommingb">[ <a href="' . htmlspecialchars($this->data['calendarurl']) . '">iCalendar feed</a> <span style="color: #aaa">(beta)</a> ] </p>';
				}
			?>





	</div><!-- /#col3 -->
	<br style="height: 0px; clear: both">
</div>

<div class="activitystream">
	
	<div style="margin: 2em;">
		<div id="activity"></div>
	</div>
</div>


			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>