<?php 
	$this->includeAtTemplateBase('includes/header.php'); 

$user = $this->data['user'];



?>




<div class="columned">
	<div class="gutter"></div>
	<div class="col1">

		<h1 style="margin-bottom: 0px"><?php echo $this->t('contacts'); ?></h1>


<?php


echo '<ul>';
foreach($this->data['contacts'] AS $c) {
	echo '<li>' . $this->data['user']->getUsernameHTML($c['userid'], $c['username'], TRUE) . '</li>';
}
echo '</ul>';

// echo '<pre>';
// print_r($this->data['contacts']);
// echo '</pre>';



?>


	
	



	</div>
	<div class="col2">
			
<?php


	if (!empty($user->photol)) {
	
		echo '<img style="max-width: 250px; border: 1px solid #888; margin: 2em 1em" src="' . htmlspecialchars($user->getPhotoURL('l')) . '" />';
		
	}



?>



			
	</div>
	<div class="col3">




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





	</div><!-- /#col3 -->
	<br style="height: 0px; clear: both">
</div>




			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>