<?php
/* 
 * The configuration of wikiplex
 * 
 * 
 */

$config = array (

	'basedir' => '/www/foodle.feide.no/',
	'simplesamlphpdir' 		=> NULL,
	'templatedir'           => 'templates/',
	'dictionarydir' 		=> 'dictionaries/',
	/*
	 * Languages available and what language is default
	 */
	'language.available'	=> array('en', 'no', 'da', 'sv', 'nl', 'de', 'es', 'sl'),
	'language.default'		=> 'no',
	
	
	'db.host'	=> 'sql.example.org',
	'db.user'	=> 'user',
	'db.pass'	=> 'pass',
	'db.name'	=> 'feideopenwiki'

);