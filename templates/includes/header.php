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

	if (isset($this->data['optimize']) && $this->data['optimize']) {
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



	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
	<![endif]-->

</head>



<body>


	<!-- Fixed navbar -->
	<div id="headerbar" class="navbar navbar-default navbar-fixed-top" role="navigation">
	</div>



	<div class="container">
  
		<noscript>
			<div style="background: #c44; color: white; padding: 1em; margin-top: 1em; border-radius: 10px" class="bg-danger">
				<p><strong>Javascript required.</strong></p>
				<p>It seems that javascript is turned off in your browser. Foodle is a web application making heavy use of javascript.
					If you get this error, even if you are sure javascript is turned on, please contact support.
				</p>
			</div>
		</noscript>

		<!--[if lt IE 9]>
			<div style="background: #c44; color: white; padding: 1em; margin-top: 1em; border-radius: 10px" class="bg-danger">
				<p><strong>Warning: Unsupported browser.</strong></p>
				<p>It seems that you are running an old version of Internet Explorer. 
					Foodle supports Internet Explorer version 9 and higher. Alternatively use another supported browser, such as Chrome, Safari, Opera or Firefox. 
				</p>
			</div>
		<![endif]-->

	</div>

