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
 */
class Vtimezone extends iCalObj {
	
	/**
	 * Creates a new Vtimezone object.
	 *
	 * @access public
	 */
	function Vtimezone() {}

	function process_child($obj) {
		#echo "\t".get_class($this)." object processing child of type ".get_class($obj)."\n";			
		$this->{get_class($obj)} = $obj;
	}


} ?>