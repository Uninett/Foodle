<?php

session_start();

$SIMPLESAMLPATH = '/var/simplesamlphp-foodle/';

$path_extra = $SIMPLESAMLPATH . 'lib';
$path = ini_get('include_path');
$path = $path_extra . PATH_SEPARATOR . $path;
ini_set('include_path', $path);

require_once($SIMPLESAMLPATH . 'lib/_autoload.php');



date_default_timezone_set('Europe/Berlin');

/**
 * Loading simpleSAMLphp libraries
 */

/*
 * Loading Foodle libraries
 */
#require_once('../lib/Foodle.class.php');

require_once('../lib/FoodleAuth.php');
require_once('../lib/FoodleUtils.php');
require_once('../lib/RSS.class.php');

require_once('../lib/XHTMLCol.php');
require_once('../lib/XHTMLResponseEntry.php');

require_once('../lib/FoodleDBConnector.php');


// Data objects
require_once('../lib/Data/User.php');
require_once('../lib/Data/Foodle.php');
require_once('../lib/Data/FoodleResponse.php');
require_once('../lib/Data/FoodleListings.php');
require_once('../lib/Data/ActivityStream.php');

// Pages
require_once('../lib/Pages/Page.php');
require_once('../lib/Pages/PageFoodle.php');
require_once('../lib/Pages/RSSFoodle.php');
require_once('../lib/Pages/CSVFoodle.php');
require_once('../lib/Pages/Debug.php');
require_once('../lib/Pages/PageFront.php');

require_once('../lib/Pages/PageCreate.php');
require_once('../lib/Pages/PageEdit.php');
require_once('../lib/Pages/PagePreview.php');

// Loading icalendar scripts..
require_once('../lib/cal/Calendar.class.php');
require_once('../lib/cal/Event.class.php');
require_once('../lib/cal/functions/class.Parser.php');
require_once('../lib/cal/functions/class.iCalObj.php');
require_once('../lib/cal/functions/class.Vcalendar.php');
require_once('../lib/cal/functions/class.Vtimezone.php');
require_once('../lib/cal/functions/class.Vevent.php');
require_once('../lib/cal/functions/class.Vfreebusy.php');
require_once('../lib/cal/functions/class.Daylight.php');
require_once('../lib/cal/functions/class.Standard.php');

/**
 * Initializating configuration
 */
SimpleSAML_Configuration::init(dirname(dirname(__FILE__)) . '/config', 'foodle');
SimpleSAML_Configuration::init($SIMPLESAMLPATH . 'config');



