<?php


$SIMPLESAMLPATH = '/var/simplesamlphp-foodle/';


$path_extra = $SIMPLESAMLPATH . 'lib';
$path = ini_get('include_path');
$path = $path_extra . PATH_SEPARATOR . $path;
ini_set('include_path', $path);

include($SIMPLESAMLPATH . 'www/_include.php');

/**
 * Loading simpleSAMLphp libraries
 */

/*
 * Loading Foodle libraries
 */
require_once('../lib/Foodle.class.php');
require_once('../lib/FoodleListings.php');
require_once('../lib/FoodleAuth.php');
require_once('../lib/FoodleUtils.php');
require_once('../lib/RSS.class.php');




/**
 * Initializating configuration
 */
SimpleSAML_Configuration::init(dirname(dirname(__FILE__)) . '/config', 'foodle');
SimpleSAML_Configuration::init($SIMPLESAMLPATH . 'config');



?>