<?php 
	$this->includeAtTemplateBase('includes/header.php'); 

?>

<script>

	function setUserid(el, user, to) {
		$("#useridFrom").attr('value', user);
		$("#useridTo").attr('value', to);
	}

</script>

<div id="content">

	<h2>Account Mapping</h2>

<form action="/accountmappingprepare" method="post">

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

function getStats($stats) {
	
	$map = array(
		'owner' => 'Created Foodles',
		'discussion' => 'Discussion entries',
		'responses' => 'Foodle Responses',
		'groupdef' => 'Group owner',
		'groupmember' => 'Group member',
		'createdago' => 'Created',
		'updatedago' => 'Updated',
	);
	
	$html = '<table >';
	foreach($stats AS $key => $value) {
		switch ($key) {
			
			case 'createdago':
			case 'updatedago':
			
			//	$value = FoodleUtils::date_diff(($value/1000)); 
			break;
		}
		
		$html .= '<tr> <td style="text-align: right; width: 200px">' . $map[$key] . '</td> <td style="text-align: right; width: 150px">' . $value . '</td>  </tr>';
	}
	$html .= '</table>';
	return $html;
}


function presentUser($user, $realmTo) {
$to = '';
	if (preg_match('/^(.*?)@/', $user['userid'], $matches)) {
		$to = $matches[1] . '@' . $realmTo;
	}

	echo '<div class="' . (empty($user['shaddow']) ? 'clean' : 'shaddow') . '" style="border: 1px solid #eee; margin: 1em; padding: .5em">';
	echo '<input style="float: right" type="submit" onclick="setUserid(this, \'' . $user['userid'] . '\', \'' . $to . '\')" value="Map this user" />';	


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
			
		<dt>Stats</dt>
			<dd>' . getStats($user['stats']) . '</dd>
			
		</dl>';
	echo '</div>';
	
	
}




// echo '<pre>';
// print_r($this->data['hits']);

echo '<h1>Users from [' . $this->data['realmFrom'] . ']</h1>';
foreach($this->data['allusers'] AS $h) {

	presentUser($h, $this->data['realmTo']);


}





?>
</div>
		
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>