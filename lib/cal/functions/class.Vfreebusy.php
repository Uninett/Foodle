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
class Vfreebusy extends iCalObj {

	
	/**
	 * Creates a new Vfreebusy object.
	 *
	 * @access public
	 */
	function Vfreebusy() {}
	
	private function pushVar($varname, $value) {
		if (!is_array($this->$varname)) {
			$this->$varname = array();
		}
		$temp = $this->$varname;
		$temp[] = $value;
		$this->$varname = $temp;
	}
	
	function process_line($key, $line) {
		#echo "\tfeed key= $key line=$line to the object of type ".	get_class($this)."\n";
		
		#echo 'processing key [' . $key . ']';
		
		switch ($key)
		{
			case 'FREEBUSY':
				$line = str_replace("$key:","",$line);
				$varname = strtolower($key);
				$this->pushVar($varname, $this->clean_string($line));
				break;
			default:
				parent::process_line($key, $line);
		}	
	}

} ?>