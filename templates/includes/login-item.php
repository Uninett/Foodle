<?php

if (isset($this->data['loginurl'])) {
	// echo '<a class="button signin" style="float: right" href="' . htmlentities($this->data['loginurl']) . '"><span>' . $this->t('login') . '</span></a>';

	echo '<li class="uninett-login"><button type="button" class="signin btn btn-default uninett-login-btn" data-toggle="modal" data-target="#myModal">' . 
		'<span class="glyphicon glyphicon-user uninett-fontColor-red"></span> ' . $this->t('login') . '</button></li>';



} elseif(isset($this->data['logouturl'])) {


	echo '<li class="dropdown pull-right"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class="glyphicon glyphicon-user"></span> ' . 
		$this->data['user']->username . 
		' <b class="caret"></b></a>';
	echo '<ul class="dropdown-menu">';


	if (isset($this->data['showprofile'])) {
		echo '<li><a href="' . htmlentities('/profile') . '">' . $this->t('myprofile') . '</a></li>';
	}


	echo '	<li class="divider"></li>';
	// echo '	<li><a href="#">Separated link</a></li>';
	echo '	<li><a class="button" href="' . htmlentities($this->data['logouturl']) . '"><span>' . $this->t('logout') . '</span></a>';
	echo '</ul>';
					
						

	
}

