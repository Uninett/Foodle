<?php
/**
 * File DocBlock.  Documentation here applies to classes, functions, etc. contained in this file,
 * unless overridden below.
 * 
 * @author
 * @since
 * @package		
 * @subpackage
 */
/**
 * [Optional short description of this class]
 * 
 * [Optional long description of this class]
 *
 * @todo	Add methods to override from config.inc.php
 * @todo	Add methods to get/set cookie
 * @todo	Consider changing the 'yes/no' Settings to true boolean values
 * @todo	Consider changing settings that store integers as Strings to
 *			true ints
 * @todo	Consider changing the legal values of $this->language to the
 *			language codes used on the web
 * 			(see http://babelwiki.babelzilla.org/index.php/Language_codes)
 */
class Settings {
		
	/**
	 * Constructs a new Settings object with defaults.
	 * 
	 * Optional long description.
	 *
	 * @access public
	 */
	function Settings() {
		$this->template 				= 'default';						// Template support
		$this->default_view 			= 'day';							// Default view for calendars = 'day', 'week', 'month', 'year'
		$this->minical_view 			= 'current';						// Where do the mini-calendars go when clicked? = 'day', 'week', 'month', 'current'
		$this->default_cal 				= $this->ALL_CALENDARS_COMBINED;	// 
		$this->language 				= 'English';						// Language support 
		$this->week_start_day 			= 'Sunday';		// Day of the week your week starts on
		$this->week_length				= '7';			// Number of days to display in the week view
		$this->day_start 				= '0700';		// Start time for day grid
		$this->day_end					= '2300';		// End time for day grid
		$this->gridLength 				= '15';			// Grid distance in minutes for day view, multiples of 15 preferred
		$this->num_years 				= '1';			// Number of years (up and back) to display in 'Jump to'
		$this->month_event_lines 		= '1';			// Number of lines to wrap each event title in month view, 0 means display all lines.
		$this->tomorrows_events_lines 	= '1';			// Number of lines to wrap each event title in the 'Tommorrow's events' box, 0 means display all lines.
		$this->allday_week_lines 		= '1';			// Number of lines to wrap each event title in all-day events in week view, 0 means display all lines.
		$this->week_events_lines 		= '1';			// Number of lines to wrap each event title in the 'Tommorrow's events' box, 0 means display all lines.
		$this->timezone 				= '';			// Set timezone. Read TIMEZONES file for more information
		$this->calendar_path 			= '';			// Leave this blank on most installs
		$this->second_offset			= '';			// The time in seconds between your time and your server's time.
		$this->bleed_time				= '-1';			// Allows events past midnight to just be displayed on the starting date, only good up to 24 hours. 
														// Range from '0000' to '2359', or '-1' for no bleed time.
		$this->cookie_uri				= ''; 			// The HTTP URL to the PHP iCalendar directory, 
														// ie. http://www.example.com/phpicalendar -- AUTO SETTING -- Only set if you are having cookie issues.
		$this->download_uri				= ''; 			// The HTTP URL to your calendars directory, ie. http://www.example.com/phpicalendar/calendars 
														// -- AUTO SETTING -- Only set if you are having subscribe issues.
		$this->default_path				= ''; 			// The HTTP URL to the PHP iCalendar directory, ie. http://www.example.com/phpicalendar
		$this->charset					= 'UTF-8';		// Character set your calendar is in, suggested UTF-8, or iso-8859-1 for most languages.
		
		// Yes/No questions --- 'yes' means Yes, anything else means no. 'yes' must be lowercase.
		$this->allow_webcals 			= 'no';			// Allow http:// and webcal:// 
		$this->this_months_events 		= 'yes';		// Display "This month's events" at the bottom off the month page.
		$this->enable_rss				= 'yes';		// Enable RSS access to your calendars (good thing).
		$this->show_search				= 'yes';		// Show the search box in the sidebar.
		$this->allow_preferences		= 'yes';		// Allow visitors to change various preferences via cookies.
		$this->printview_default		= 'no';			// Set print view as the default view. 
		$this->show_todos				= 'yes';		// Show your todo list on the side of day and week view.
		$this->show_completed			= 'yes';		// Show completed todos on your todo list.
		$this->allow_login				= 'yes';		// Set to yes to prompt for login to unlock calendars.
		$this->login_cookies			= 'no';			// Set to yes to store authentication information via (unencrypted) cookies. Set to no to use sessions.
		$this->support_ical				= 'no';			// Set to yes to support the Apple iCal calendar database structure.
		$this->recursive_path			= 'no';			// Set to yes to recurse into subdirectories of the calendar path.
		
		// Calendar Caching (decreases page load times)
		$this->save_parsed_cals 		= 'no';			// Saves a copy of the cal in /tmp after it's been parsed. Improves performance.
		$this->tmp_dir					= '/tmp';		// The temporary directory for saving parsed cals
		$this->webcal_hours				= '24';			// Number of hours to cache webcals. Setting to '0' will always re-parse webcals.
		
		// Webdav style publishing
		$this->phpicalendar_publishing = '';			// Set to '1' to enable remote webdav style publish. See 'calendars/publish.php' for complete information;
		
		// Administration settings (/admin/)
		$this->allow_admin				= 'yes';		// Set to yes to allow the admin page 
														// - remember to change the default password if using 'internal' as the $this->auth_method			
		$this->auth_method				= 'internal';	// Valid values are: 'ftp', 'internal', or 'none'. 
														// 'ftp' uses the ftp server's username and password as well as ftp commands to delete and copy files.
														// 'internal' uses $this->auth_internal_username and $this->auth_internal_password defined below 
														// - CHANGE the password. 
														// 'none' uses NO authentication - meant to be used with another form of authentication such as http basic.
		$this->auth_internal_username	= 'admin';		// Only used if $this->auth_method='internal'. The username for the administrator.
		$this->auth_internal_password	= 'admin';		// Only used if $this->auth_method='internal'. The password for the administrator.
		$this->ftp_server				= 'localhost';	// Only used if $this->auth_method='ftp'. The ftp server name. 'localhost' will work for most servers.
		$this->ftp_port					= '21';			// Only used if $this->auth_method='ftp'. The ftp port. '21' is the default for ftp servers.
		$this->ftp_calendar_path		= '';			// Only used if $this->auth_method='ftp'. The full path to the calendar directory on the ftp server. 
														// If = '', will attempt to deduce the path based on $this->calendar_path, 
														// but may not be accurate depending on ftp server config.
		
		/* Calendar colors
		 *
		 * You can increase the number of unique colors by adding additional images (monthdot_n.gif) 
		 * and in the css file (default.css) classes .alldaybg_n, .eventbg_n and .eventbg2_n
		 * Colors will repeat from the beginning for calendars past $this->unique_colors (7 by default), with no limit.
		 */
		$this->unique_colors			= '7';
		$this->blacklisted_cals 		= array();
		$this->list_webcals 			= array();
		#$this->more_webcals['cpath'][] = ''			//add webcals that will show up only for a particular cpath.
		$this->locked_cals 				= array();		// Fill in-between the quotes the names of the calendars you wish to hide
		$this->locked_map				= array();		// Map username:password accounts to locked calendars that should be		
		$this->apache_map		 		= array();		// Map HTTP authenticated users to specific calendars. Users listed here and
	}
	
	
	/**
	 * Sets the language.
	 *
	 * @access public
	 * @todo	Add a check for legal values for $this->language
	 */
	function setLang($language) {
		$this->language = $language;
	}

} ?>