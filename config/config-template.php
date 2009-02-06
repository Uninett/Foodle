<?php
/* 
 * The configuration of wikiplex
 * 
 * 
 */

$config = array (

	'simplesamlphpdir' 		=> NULL,
	'basedir' => '/www/foodle.feide.no/',
	'baseurlpath' => '',
	
	'templatedir'           => 'templates/',
	'dictionarydir' 		=> 'dictionaries/',
	/*
	 * Languages available and what language is default
	 */
	'language.available'	=> array('en', 'no', 'da', 'sv', 'nl', 'de', 'es', 'sl'),
	'language.default'		=> 'en',
	
	'adminUsers' => array('andreas@rnd.feide.no', 'andreas@uninett.no'),
	
	'db.host'	=> 'sql.example.org',
	'db.user'	=> 'user',
	'db.pass'	=> 'pass',
	'db.name'	=> 'feideopenwiki'

);