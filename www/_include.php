<?php


// echo dirname(dirname(dirname(__FILE__))); exit;

$SIMPLESAMLPATH = dirname(dirname(dirname(__FILE__))) . '/simplesamlphp/';


$path_extra = $SIMPLESAMLPATH . 'lib';
$path = ini_get('include_path');
$path = $path_extra . PATH_SEPARATOR . $path;
ini_set('include_path', $path);

require_once($SIMPLESAMLPATH . 'lib/_autoload.php');

require_once($SIMPLESAMLPATH . 'modules/oauth/libextinc/OAuth.php');

$THISPATH = dirname(dirname(__FILE__)) . '/';

date_default_timezone_set('Europe/Berlin');




/**
 * Loading simpleSAMLphp libraries
 */

/*
 * Loading Foodle libraries
 */

#
require_once($THISPATH . 'lib/TimeZone.php');

require_once($THISPATH . 'lib/FoodleAuth.php');
require_once($THISPATH . 'lib/FoodleUtils.php');
require_once($THISPATH . 'lib/RSS.class.php');


require_once($THISPATH . 'lib/UNINETTDistribute.php');
require_once($THISPATH . 'lib/EmbedDistribute.php');

require_once($THISPATH . 'lib/EMail.php');


require_once($THISPATH . 'lib/FoodleDBConnector.php');


require_once($THISPATH . 'lib/Markdown/markdown.php');


// Calendars...
require_once($THISPATH . 'lib/Calendar/CalendarUser.php');

// Data objects
require_once($THISPATH . 'lib/Data/User.php');
require_once($THISPATH . 'lib/Data/Foodle.php');

require_once($THISPATH . 'lib/Data/FoodleResponse.php');
require_once($THISPATH . 'lib/Data/FoodleListings.php');
require_once($THISPATH . 'lib/Data/ActivityStream.php');
require_once($THISPATH . 'lib/Data/EventStream.php');


// API
require_once($THISPATH . 'lib/API/API.php');
require_once($THISPATH . 'lib/API/Authenticated.php');
require_once($THISPATH . 'lib/API/DiscoJuice.php');


require_once($THISPATH . 'lib/API/Activity.php');
require_once($THISPATH . 'lib/API/Events.php');
require_once($THISPATH . 'lib/API/EventsAnon.php');

require_once($THISPATH . 'lib/API/Foodlelist.php');
require_once($THISPATH . 'lib/API/IdPList.php');
require_once($THISPATH . 'lib/API/Foodle.php');
require_once($THISPATH . 'lib/API/User.php');
require_once($THISPATH . 'lib/API/FoodleAuthenticated.php');


// Pages
require_once($THISPATH . 'lib/Pages/Page.php');
require_once($THISPATH . 'lib/Pages/PageDisco.php');
require_once($THISPATH . 'lib/Pages/Photo.php');
require_once($THISPATH . 'lib/Pages/PageAccountMapping.php');
require_once($THISPATH . 'lib/Pages/PageAccountMappingPrepare.php');
require_once($THISPATH . 'lib/Pages/PageFoodle.php');
require_once($THISPATH . 'lib/Pages/Debug.php');
require_once($THISPATH . 'lib/Pages/FDebug.php');
require_once($THISPATH . 'lib/Pages/PageFront.php');
require_once($THISPATH . 'lib/Pages/PageProfile.php');
require_once($THISPATH . 'lib/Pages/PageAttributes.php');
require_once($THISPATH . 'lib/Pages/PageUser.php');
require_once($THISPATH . 'lib/Pages/PageSupport.php');
require_once($THISPATH . 'lib/Pages/PageStats.php');
require_once($THISPATH . 'lib/Pages/FixDate.php');
require_once($THISPATH . 'lib/Pages/Login.php');
require_once($THISPATH . 'lib/Pages/PageWidget.php');

require_once($THISPATH . 'lib/Pages/EmbedFoodle.php');
require_once($THISPATH . 'lib/Pages/RSSFoodle.php');
require_once($THISPATH . 'lib/Pages/CSVFoodle.php');

require_once($THISPATH . 'lib/Pages/PageCreate.php');
require_once($THISPATH . 'lib/Pages/PageEdit.php');


// Loading icalendar scripts..
require_once($THISPATH . 'lib/cal/Calendar.class.php');
require_once($THISPATH . 'lib/cal/CalendarAggregator.class.php');
require_once($THISPATH . 'lib/cal/Event.class.php');
require_once($THISPATH . 'lib/cal/functions/class.Parser.php');
require_once($THISPATH . 'lib/cal/functions/class.iCalObj.php');
require_once($THISPATH . 'lib/cal/functions/class.Vcalendar.php');
require_once($THISPATH . 'lib/cal/functions/class.Vtimezone.php');
require_once($THISPATH . 'lib/cal/functions/class.Vevent.php');
require_once($THISPATH . 'lib/cal/functions/class.Vfreebusy.php');
require_once($THISPATH . 'lib/cal/functions/class.Daylight.php');
require_once($THISPATH . 'lib/cal/functions/class.Standard.php');

/**
 * Initializating configuration
 */
SimpleSAML_Configuration::init(dirname(dirname(__FILE__)) . '/config', 'foodle');
SimpleSAML_Configuration::init($SIMPLESAMLPATH . 'config');


session_start();

