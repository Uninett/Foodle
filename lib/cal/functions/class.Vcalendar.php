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
 * This class is for calendars.
 * 
 * [Optional long description of this class]
 *
 */
class Vcalendar extends iCalObj {
	
	/**
	 * Constructs a new Vcalendar object.
	 * 
	 * Optional long description.
	 *
	 * @access public
	 */
	function Vcalendar() {}

	function process_child($obj) {
		#echo "\t".get_class($this)." object processing child of type ".get_class($obj)."\n";
			
	#	$this->children[] = $obj;
	}

} ?>