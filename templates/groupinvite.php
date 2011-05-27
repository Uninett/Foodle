<?php $this->includeAtTemplateBase('includes/header.php'); ?>

<h2>You are invited to join a group on Foodle</h2>


 
<p><?php echo $this->data['message']; ?></p>

<?php

if ($this->data['alreadymember']) {

	echo '<p>You are <strong>already a member</strong> of this group. <a href="/groups">Go to group management</a></p>';

} else {

	echo '<form action="?" method="get">
	
	<p>Do you want to join the group <strong>' .  htmlspecialchars($this->data['groupinfo']['name']) . '</strong>?</p>

	<p>The list of members of this group restricted to the members of the group.</p>
	
	<input type="hidden" name="token" value="' . $this->data['token'] . '" />
	
	<input type="submit" name="join" value="Yes, I want to join" />
	<a href="/">No, Go to Foodle front page</a>
	
</form>';

}


?>



		
		
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>