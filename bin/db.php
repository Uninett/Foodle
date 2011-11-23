#!/usr/bin/env php
<?php

$SIMPLESAMLPHP_DIR = '/var/simplesamlphp-foodle';

/* This is the base directory of the simpleSAMLphp installation. */
$baseDir = dirname(dirname(__FILE__));
require_once($baseDir . '/www/_include.php');

/* Add library autoloader. */
require_once($SIMPLESAMLPHP_DIR . '/lib/_autoload.php');



// Needed in order to make session_start to be called before output is printed.
$session = SimpleSAML_Session::getInstance();
$sspconfig = SimpleSAML_Configuration::getConfig('config.php');
$config = SimpleSAML_Configuration::getInstance('foodle');



echo ('mysql -h ' . $config->getValue('db.host') . '  -u ' . $config->getValue('db.user') . ' --password=' . $config->getValue('db.pass') . '  ' . $config->getValue('db.name') . '   ');




