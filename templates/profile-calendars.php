<?php 


 	$this->data['head'] = '
 	

	<script type="text/javascript" src="/res/js/foodle-data.js"></script>
 	<script type="text/javascript" src="/res/js/foodle-profilecalendars.js"></script>

<!--
 	<script type="text/javascript" src="/res/js/foodle-contacts-api.js"></script>
 	<script type="text/javascript" src="/res/js/foodle-contacts.js"></script>
 	<script type="text/javascript" src="/res/js/foodle-activity.js"></script>
 -->
 	
 	<script type="text/javascript" charset="utf-8">
 		$(document).ready(function() {


//			Foodle_Contacts.getContactlist(currentList);
//			Foodle_API.getData(\'/api/activity/group/\' + currentList , null, updateActivityList);

			Foodle_ProfileCalendars_View();
		});
		

	</script>
 	
 	';
 	
	$this->includeAtTemplateBase('includes/header.php'); 



$user = $this->data['user'];



?>




<div class="columned">
	<div class="gutter"></div>
	<div class="col1">

		<h1 style="margin-bottom: 0px"><?php echo htmlspecialchars($user->username); ?></h1>


<h2>Your calendars</h2>

<div>

	<div>
<!-- 		<h3>Your calendars:</h3> -->
		<div id="usercalendars"></div>
		<p>Uncheck calendars that you want to set inactive (they will not be used by Foodle, but you may activate them later).</p>
	</div>
	<div>
		<h3>Add a calendar</h3>
		<p>You may add an HTTP iCalendar feed. It may be a regular feed with events, or it can be a freebusy feed. The feed must be accessible without authentication.</p>
		<p><input type="text" id="newcalendarurl" name="newcalendarurl" value=""  style="width: 300px" /><input type="submit" id="addcalendarurl" name="addcalendarurl" value="Add new iCalendar" /></p>
	</div>
</div>


	</div>
	<div class="col2">
			
<?php

	if (!empty($user->photol)) {
		echo '<img style="max-width: 250px; border: 1px solid #888; margin: 2em 1em" src="' . htmlspecialchars($user->getPhotoURL('l')) . '" />';
	}

?>


	</div>
	<div class="col3">



			<h2><?php echo $this->t('bc_attribute_check'); ?></h2>
			<ul>
				<li><a href="/attributes"><?php echo $this->t('bc_attribute_check'); ?></a></li>
			</ul>
			


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