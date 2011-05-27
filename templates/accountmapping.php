<?php 
	$this->includeAtTemplateBase('includes/header.php'); 

?>

<script>

	function setUserid(el, user, where) {
		$("#" + where).attr('value', user);
		$(el).attr('disabled', 'disabled');
	}

</script>

<div id="content">

	<h2>Account Mapping</h2>

<form action="/accountmapping" method="post">

	<p>User ID From: <input type="text" size="80" name="useridFrom" id="useridFrom" value="" />
	<p>User ID To  : <input type="text" size="80" name="useridTo" id="useridTo" value="" />
	<p><input type="submit" name="submit" value="Submit" />

</form>


<style>
div.shaddow {
	background: #ccc;
	border: 1px solid #000 ! important;
}
</style>
<?php


function presentUser($user) {

	echo '<div class="' . (empty($user['shaddow']) ? 'clean' : 'shaddow') . '" style="border: 1px solid #eee; margin: 1em; padding: .5em">';
	echo '<input style="float: right" type="submit" onclick="setUserid(this, \'' . $user['userid'] . '\', \'useridTo\')" value="Map to this" />';	
	echo '<input style="float: right" type="submit" onclick="setUserid(this, \'' . $user['userid'] . '\', \'useridFrom\')" value="Map from this" />';

	echo '<h3 style="color: green">' . $user['username'] . '</h3>';
	echo '<dl>
		<dt>UserID</dt>
			<dd><tt>' . $user['userid'] . '</tt></dd>

		<dt>realm</dt>
			<dd><tt>' . $user['realm'] . '</tt></dd>

		<dt>email</dt>
			<dd>' . $user['email'] . '</dd>

		<dt>org</dt>
			<dd>' . $user['org'] . '</dd>

		<dt>orgunit</dt>
			<dd>' . $user['orgunit'] . '</dd>

		<dt>idp</dt>
			<dd><tt>' . $user['idp'] . '</tt></dd>


		<dt>shaddow</dt>
			<dd>' . $user['shaddow'] . '</dd>
			
		</dl>';
	echo '</div>';
	
	
}




// echo '<pre>';
// print_r($this->data['hits']);

echo '<h1>Email matches</h1>';
foreach($this->data['hits'] AS $h) {
	echo '<h2>' . $h[0]['email'] . '</h2>';
	foreach($h AS $hm) {
		presentUser($hm);
	}
}

echo '<h1>Name matches</h1>';
foreach($this->data['hitsname'] AS $h) {
	echo '<h2>' . $h[0]['username'] . '</h2>';
	foreach($h AS $hm) {
		presentUser($hm);
	}
}





?>
</div>
		
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>