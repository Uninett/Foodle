<?php
/**
 * Refactoring of the ical parser in phpicalendar to make the code more
 * maintainable.
 * 
 * @author
 * @since
 * @package		
 * @subpackage
 */
/**
 * Base class for iCalendar objects. Some methods used by all, others only for
 * timed events.
 * 
 * [Optional long description of this class]
 *
 * @todo	Since version3 of this project is "OBJECT-oriented", is it necessary
 *			for this class to have the 'Obj' suffix at the end of its name?
 */
class iCalObj {

#	var $var; # comment
	var 
		$children; # comment

	/** 
	 * Creates a new iCalObj.
	 * 
	 * @access public
	 */
	function iCalObj() {
		$this->children = array();
	}
	
	/**
	 * Process a line.
	 *
	 * The parser makes passes as follows:
	 * 
	 *		key - everything before the first colon or semicolon
	 *		line - the whole line
	 * 
	 * From the icalendar spec page 13:
	 * contentline        = name *(";" param ) ":" value CRLF
	 *
	 * @example
	 * ATTENDEE;CUTYPE=GROUP:MAILTO:ietf-calsch@imc.org
	 * RRULE:FREQ=MONTHLY;BYDAY=MO,TU,WE,TH,FR;BYSETPOS=-1
	 * 
	 * Thus, note that key and value are both complex entities.  
	 * (They can have multipart info.)
	 *
	 * @access public
	 */
	function process_line($key, $line) {
		#echo "\tfeed key= $key line=$line to the object of type ".	get_class($this)."\n";
		
		switch ($key)
		{
			case '':
				break;
			default:
				$line = str_replace("$key:","",$line);
				$varname = strtolower($key);
				$this->$varname = $this->clean_string($line);
		}	
	}
	
	
	/**
	 * @access public
	 */
	function process_child($obj) {
		#echo "\t".get_class($this)." object processing child of type ".	get_class($obj)."\n";
			
		$this->children[] = $obj;
	}
	
	
	/**
	 * Writes a string which "tells" the calling object to finish and pop it from the stack.
	 *
	 * @access public
	 */
	function finish() {
		#echo "END:tell the ".get_class($this)." object to finish up, pop it off the stack.\n";
	}
	
	
	/**
	 * Cleans a string for use as HTML.
	 *
	 * @access public
	 * @param string $data The data to be transformed to HTML.
	 * @return string The $data with several HTML search-and-replacements performed.
	 */
	function clean_string($data) {
		$data = str_replace("\\n", "<br />", $data);
		$data = str_replace("\\t", "&nbsp;", $data);
		$data = str_replace("\\r", "<br />", $data);
		$data = str_replace('$', '&#36;', $data);
		$data = stripslashes($data);
		return $data;
	}

	/**
	 * Dumps object state for debugging.
	 *
	 * @access public
	 * prints object
	 */
	function dump() {
		echo "<pre>";print_r($this);echo "</pre>";
		return true;
	}

} ?>