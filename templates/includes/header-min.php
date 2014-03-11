<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="">
	<meta name="author" content="">
	<link rel="shortcut icon" href="/res/uninett-theme/ico/favicon.ico">

	<title>Foodle</title>

	<!-- Bootstrap core CSS -->
	<link href="/res/uninett-theme-bootstrap/css/bootstrap.min.css" rel="stylesheet">

	<!-- DiscoJuice CSS -->
	<link rel="stylesheet" type="text/css" href="https://static.discojuice.org/css/discojuice.css" />

	<!-- Custom styles for this template -->
	<link href="/res/uninett-theme/css/uninett.css" rel="stylesheet">

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
	<![endif]-->


	<link rel="stylesheet" media="screen" type="text/css" href="/res/css/foodle2.css" />
	<link rel="stylesheet" media="screen" type="text/css" href="/res/js2/lib/datepicker3.css" />

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
	// print_r($this->data);

	if (isset($this->data['requirejs-main'])) {
		echo '<script type="text/javascript" data-main="' . $this->data['requirejs-main'] . '" src="/res/js2/lib/require.js"></script>';
	}
?>


</head>



<body>


