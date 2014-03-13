<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="A simple tool for doing polls, invitation to events and scheduling meetings.">
	<meta name="author" content="Andreas Åkre Solberg">
	<link rel="shortcut icon" href="/res/uninett-theme/ico/favicon.ico">

	<title>Foodle</title>


	<style>

	body {
		padding-top: 0px;
		background-color: #fff;
	}
	</style>
	

	<?php

		echo '<script type="text/javascript">';

		if (!empty($this->data['foodle'])) {
			echo "\n" . 'var foodle_id = "' . htmlspecialchars($this->data['foodle']->identifier) . '"; ' . "\n";
		} else if (!empty($this->data['foodleid'])) {
			echo "\n" . 'var foodle_id = "' . htmlspecialchars($this->data['foodleid']) . '"; ' . "\n";
		}
		echo '</script>';


		if (!empty($this->data['gmapsAPI'])) {
			echo '<script type="text/javascript" ' .
			 'src="https://maps.googleapis.com/maps/api/js?key=' . $this->data['gmapsAPI'] . '&amp;sensor=false"></script>';
		}


	?>
	

	
<?php 


	echo '<script type="text/javascript" data-main="main" src="/res/js2/lib/require.js"></script>';

	if ($this->data['optimize']) {
		echo '<!-- Running optimized javascript -->';

		echo '<link rel="stylesheet" media="screen" type="text/css" href="/res/css/foodle.build.css" />';
		
	} else {
		echo '<!-- Running javascript that is not optimized. This is better for debugging. -->';
		echo '<script type="text/javascript">';
		echo 'requirejs.config({"paths": { "main": "main" }});';
		echo '</script>';
		echo '<link rel="stylesheet" media="screen" type="text/css" href="/res/css/foodle.css" />';

	}


?>


</head>



<body>


