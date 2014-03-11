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


	<!-- Fixed navbar -->
	<div class="navbar navbar-default navbar-fixed-top" role="navigation">
		<div class="container">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse"> <span class="sr-only">Toggle navigation</span> <span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </button>
				<a class="navbar-brand" href="/#!/"><img src="/res/uninett-theme/images/UNINETT_logo.svg" alt="Uninett logo" type="image/svg+xml"></a>
				
			</div>
			<div class="navbar-department">
				<div class="department">Foodle</div>
			</div>
			<div class="navbar-collapse collapse">
				<ul class="nav navbar-nav">
			
				<li><a href="/create">Create new</a></li>

<?php



	

	
	// if (array_key_exists('facebookshare', $this->data) && $this->data['facebookshare']) {
	// 	echo '<li><a class="button" style="float: right" onclick="showFacebookShare()"><span>' . $this->t('facebookshare') . '</span></a></li>';
	// }

	// if (array_key_exists('twittershare', $this->data) && $this->data['twittershare']) {
	// 	echo '<li><a title="Share this foodle on Twitter" href="' . 
	// 		htmlspecialchars(
	// 			SimpleSAML_Utilities::addURLparameter('http://twitter.com/home', array(
	// 					'status' => 
	// 						'#foodle ' . $title . ': ' . SimpleSAML_Utilities::addURLparameter(SimpleSAML_Utilities::selfURL(), array('auth' => 'twitter'))
	// 				)
	// 			)
	// 		) . 
	// 		'">Tweet</a></li>';
	// }

	// if (isset($this->data['showcontacts'])) {
	// 	echo '<li><a class="button" style="float: right" href="/groups"><span>' . $this->t('groups') . '</span></a></li>';
	// }
	
	// if (array_key_exists('showedit', $this->data)) {
	// 	echo('<li><a class="button" href="/edit/' .$this->data['foodle']->identifier . '" style="float: right"><span>' . $this->t('editfoodle') . '</span></a></li>');
	// }



	require_once(dirname(__FILE__) . '/login-item.php');
	// require_once(dirname(__FILE__) . '/language-selector.php');


?>


					<li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown">About Foodle <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><a href="https://github.com/UNINETT/Foodle/issues">Report bugs or ask questions</a></li>
							<!-- <li><a href="https://rnd.feide.no/software/foodle/foodle-privacy-policy/"><?php echo $this->t('privacypolicy'); ?></a></li> -->
							<li><a href="http://rnd.feide.no"><?php echo $this->t('rndblog'); ?></a></li>
							<li><a id="news" target="_blank" href="http://rnd.feide.no/category/foodle/">
								<?php echo $this->t('read_news'); ?></a></li>
							<li><a id="mailinglist" target="_blank" href="http://rnd.feide.no/software/foodle/">
								<?php echo $this->t('join_mailinglist'); ?></a></li>
						</ul>
					</li>

				</ul>
			</div>
		</div>
	</div>








	<div class="container">

		<div id="headerbar" style="clear: both">
			<?php 

				echo '<ol class="breadcrumb">';
				if (isset($this->data['bread'])) {
					$first = TRUE;
					foreach ($this->data['bread'] AS $item) {
						// if (!$first) echo ' » ';		
						if (isset($item['href'])) {
							
							if (strstr($item['title'],'bc_') == $item['title'] ) {
								echo '<li><a href="' . $item['href'] . '">' . $this->t($item['title']) . '</a></li>';
							} else {
								echo '<li><a href="' . $item['href'] . '">' . $item['title'] . '</a></li>';
							}
						} else {
							if (strstr($item['title'],'bc_') == $item['title'] ) {
								echo '<li class="active">' . $this->t($item['title']) . '</li>';
							} else {
								echo '<li class="active">' . $item['title'] . '</li>';
							}
							
						}
						$first = FALSE;
					}
				}
				echo '</ol>';


				if (isset($this->data['headbar'])) {
					echo $this->data['headbar'];
				}

			?>

			<p style="height: 0px; clear: both"></p>
		</div><!-- /#headerbar -->

  

	</div>

