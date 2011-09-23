<?php 

// 	$this->data['head'] = '<script type="text/javascript" src="/res/js/foodle-contacts-api.js"></script>';
 	$this->data['head'] = '
 	
	<script type="text/javascript" src="/res/js/jquery.dnduploader.js"></script>
	<script type="text/javascript" src="/res/js/foodle-data.js"></script>
	<script type="text/javascript" src="/res/js/foodle-group.js"></script> 

<!--
 	<script type="text/javascript" src="/res/js/foodle-contacts-api.js"></script>
 	<script type="text/javascript" src="/res/js/foodle-contacts.js"></script>
 	<script type="text/javascript" src="/res/js/foodle-activity.js"></script>
 -->
 	
 	<script type="text/javascript" charset="utf-8">
 		$(document).ready(function() {


			// Foodle_Contacts.getContactlist(currentList);
			// Foodle_API.getData(\'/api/activity/group/\' + currentList , null, updateActivityList);

			Foodle_Group_View(' . htmlspecialchars($this->data['groupInfo']['id']) . ');
		});
		

	</script>
 	
 	';

	if (isset($_REQUEST['foodleid'])) {
		Data_Foodle::requireValidIdentifier($_REQUEST['foodleid']);
	}


	$this->includeAtTemplateBase('includes/header.php'); 

	$user = $this->data['user'];

?>


<div class="columned">
	<div class="gutter"></div>
	<div class="col4">

		<h3>Shared documents</h3>
		
		<div class="filelist"></div>
		
		<div id="dropbox" style="">
			<p style="">Drop files here to share with the group</p>
			<div class="progress"></div>
		</div>



	</div>
	<div class="col5">

		<h2><?php echo $this->data['groupInfo']['name'];  ?></h2>
		
		<p>The group page of Foodle is currently in <span style="background: #ffd; border: 1px solid #cca; border-radius: 4px; padding: 4px">Beta</span>.</p>
		
		<p style="color: #555">To make Foodles show up here, check this group in the group tab when you create a new Foodle or edit an old one.</p>

		<div id="activity"></div>

	</div>
	
	<div class="col6">

		<h2>
			Group Members
		</h2>

		<div class="foodle_contacts">
		</div>
		
		<p><a href="/groups">Manage groups</a></p>

	</div><!-- /#col3 -->
	<br style="height: 0px; clear: both">
	
</div>




			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>