<?php 
	$this->includeAtTemplateBase('includes/header.php'); 

$user = $this->data['user'];



?>


<div class="container">
	<div class="row uninett-color-white">
		<div class="col-md-8  uninett-padded"> 


		<h1 style="margin-bottom: 0px"><?php echo htmlspecialchars($this->t('bc_attribute_check')); ?></h1>

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


echo '<h2>' . $this->t('attribute_validation') . '</h2>';


if (!empty($this->data['validate'])) {
foreach($this->data['validate'] AS $v) {
	echo '<div>';
	switch($v[0]) {
		case 'fatal':
			echo '<span style="color: #" class="glyphicon glyphicon-remove"></span><strong>Fatal Error</strong> ';
			break;

		case 'error':
			echo '<span style="color: #900" class="glyphicon glyphicon-remove"></span>';
			break;

		case 'ok':
			echo '<span style="color: #080" class="glyphicon glyphicon-ok"></span>';
			break;

		case 'warning':
		default:
			echo '<span style="color: #FF8C00" class="glyphicon glyphicon-remove"></span>';
			break;

	}
	echo ' ' . htmlspecialchars($v[1]);
	echo '</div>';
}
}

echo '<h2>' . $this->t('attribute_dump') . '</h2>';

if (empty($this->data['attributes'])) {
	echo '<p>No attributes</p>';
} else {

	echo '<dl class="attributelist">';
	foreach($this->data['attributes'] AS $key => $values) {
		
		echo '<dt><tt>' .  htmlspecialchars($key) . '</tt></dt>';
		echo '<dd><ul>';
		foreach($values AS $value) {
			if (strlen($value) > 100) {
				echo '<li><tt>' . htmlspecialchars(substr($value, 0, 100)) . ' <span style="color: #999">[trunctated]</span></tt></li>';
			} else {
				echo '<li><tt>' . htmlspecialchars($value) . '</tt></li>';
			}

		}
		echo '</ul></dd>';
		
		
	}
	echo '</dl>';
}
?>

	</div>
	<div class="col-md-4">
			
<?php


	if (!empty($user->photol)) {
		$photourl = $user->getPhotoURL('l');
		if (!empty($photourl) ) {
			echo '<img style="max-width: 250px; border: 1px solid #888; margin: 2em 1em" src="' . htmlspecialchars($photourl) . '" />';
		}
		
	}



?>



			
	</div>

</div>




			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>