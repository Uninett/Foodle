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
	
	function parseKV($line) {
		if (preg_match('/^(.*?)=(.*?)$/', $line, $matches)) {
			return array($matches[1], $matches[2]);
		}
		return null;
	}
	
	function getMetaParams($line) {
		$meta = array();
		
		$split = explode(';', $line);
		if (count($split) === 1) return $meta;
		
		array_shift($split);
		foreach($split AS $entry) {
			$ep = $this->parseKV($entry);
			if ($ep === null) continue;
			
			$meta[$ep[0]] = $ep[1];
		}
		
#		echo '<p>DATA: <pre>'; print_r($meta); echo '</pre>';
		return $meta;
	}
	
	function process_line($key, $line) {
#		echo "<p>\tfeed: key= $key line=$line to the object of type ".	get_class($this)."\n";
		
		$varname = strtolower($key);
		
		if (preg_match('/^(.*?):(.*?)$/', $line, $matches)) {
			$name = $matches[1];
			$value = $matches[2];
			$meta = $this->getMetaParams($name);
		}

		
		switch ($key)
		{
			case 'FREEBUSY':
				$meta['data'] = $this->clean_string($value);
				
				$this->pushVar($varname, $meta);
				break;
				
			default:
				parent::process_line($key, $line);
		}	
	}

} ?>