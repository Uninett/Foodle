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
 * Refactoring of the ical parser in phpicalendar to make the code more maintainable 
 * gets a calendar object and creates a series of event objects.
 * 
 * This object should probably only be invoked in situations where the input is an ics file,
 * either a local cal or a webcal.  Unserializing a saved cal should go somewhere else.
 *
 * The function process_file is the meat of the operation.  Note that Parser 
 * determines the kind of object that should handle a content line, but delegates 
 * further parsing of that content-line to the object. In other words, the Parser 
 * class just deals with BEGIN and END events, which are involved in creating and 
 * organizing objects.
 *
 */
class Parser {

	var 
		$cal,		#	calendar object
		$fh, 		# 	filehandle for the calendar file being parsed
		$lookahead,	#	buffer for last line read by read_line lookahead
		$mArray;	#	temporary master array entries
		
	
	/**
	 * Constructs a new Parser object.
	 * 
	 * Optional long description.
	 *
	 * @access public
	 */
	function Parser() {
		$this->lookahead = '';
	} // end constructor
	
	
	/**
	 * Sets the calendar to be parsed.
	 *
	 * @access public
	 */
	function set_cal($cal) {
		$this->cal = $cal;
	} // end function set_cal()


	/**
	 * Processes the calendar set for the calling Parser object.
	 *
	 * @access public
	 * @alias process_file
	 */
	function process_cal() {
		process_file($this->cal->filename);
	} // end function process_cal()
	
	
	/**
	 * Processes the specified file.
	 * 
	 * The structure of an ics file is somewhat like xml.  
	 * Objects are hierarchical The top level object is VCALENDAR, which has 
	 * children including VTIMEZONE, VEVENT, VTODO etc. Each of these has child 
	 * objects, such as DAYLIGHT and STANDARD in VTIMEZONE, DTSTART in the 
	 * others, etc. We will use this hiearchy, but not to full granularity.
	 *
	 * @access public
	 * @param string $filename The name of the file to be processed.
	 * @return mixed
	 *		Returns true if file was processed successfully, or
	 *		an error string. 
	 *				
	 * @todo	Instead of returning an error string, implement Exception handling.
	 */ 
	function process_file($filename) {
		$obj = null;
		$obj_stack = array();
		$tz_list = array();
		if (!$this->open_file($filename))
			return "can't open file"; 
		
		$i = 0;
		
		while (!feof($this->fh)) {
			$line = $this->read_line();
			# echo "$i:$line\n";$i++;
			if ($line) {
				$tmp = explode(":",	$line);
				$tmp2 = explode(";", $tmp[0]);
				$key = $tmp2[0]; #want the first string before either a colon or semicolon
				# echo "key:$key\n";
				
				switch ($key){
					case 'BEGIN':
						$type = ucfirst(strtolower($tmp[1]));
						
						if ($type == 'Vcalendar') {
							if (!is_object($this->cal)) $this->cal = new Vcalendar; # echo "Make vcal obj\n";
							$obj = $this->cal;
							$obj_stack[] = $obj;
							# echo "BEGIN: make new obj of type ".get_class($obj)." and push it onto the stack\n";
						} elseif (in_array($type, array('Vtimezone','Daylight','Standard','Vevent','Vtodo','Vfreebusy'))) {
							$obj = new $type; # 
							$obj_stack[] = $obj;
							# echo "BEGIN: make new obj of type ".get_class($obj)." and push it onto the stack\n";
						} else {
							# Handle BEGIN for undefined object types
							# Parser delegates further parsing to the object
							if (is_object($obj))
								$obj->process_line($key,$line);
						}
						break;
					
					case 'END':
						
						
						if ($obj_stack[0] === NULL) break;

						$obj = array_pop($obj_stack);
						
						
						switch (get_class($obj)){
							case 'Vtimezone': $this->tz_list[$obj->tzid] = $obj; break;
							case 'Vevent': $this->event_list[$obj->uid] = $obj; break;
							case 'Vtodo': $this->todo_list[] = $obj; break;
							case 'Vfreebusy': 
								$this->freebusy_list[] = $obj; 
								break;
						}
						if (is_object(end($obj_stack))) {
							$parent_obj = end($obj_stack);
							$parent_obj->process_child($obj); # let the parent object set whatever it needs from the child
						}	
						
						if (is_object($obj))
							$obj->finish();
						
						# "make the working object the last one on the stack\n";
						if (is_object(end($obj_stack)))
							$obj = $parent_obj;
						
						break;
					
					default:
						# Parser delegates further parsing to the object				
						if (is_object($obj)) $obj->process_line($key,$line);
				}
		#	print_r($obj_stack);
			}
		}
		
		# "finished stack on line:$line.  Lookahead:$this->lookahead\n";
		#deal with possible lack of \n at eof
		if (trim($this->lookahead) != ""  && is_object($obj)) {
			$obj = array_pop($obj_stack);
			
			if (is_object(end($obj_stack))) {
				$parent_obj = end($obj_stack);
				$parent_obj->process_child($obj); # let the parent object set whatever it 
			}	
			if (is_null($obj)) return;
			
			$obj->finish();
			
			if (is_object($parent_obj))
				$parent_obj->finish();

		}
		
	#	print_r($this->cal);
	#	print_r($tz_list);
		
		return true;
	} // end function process_file()
	
	
	/**
	 * Opens a file.
	 *
	 * @access public
	 * @return bool Returns whether or not the file handle was opened successfully.
	 */
	function open_file($filename) {
		$this->fh = fopen($filename, "r");
		return ($this->fh == FALSE) ? false : true;
	} // end function open_file() 
	
	
	/**
	 * Takes a filehandle and folds multiple line input to $this->line.
	 *
	 * @access public
	 * @return string A trim()ed $tmp_line.
	 */
	function read_line() {
		
		if (feof($this->fh)) 
			return;
		
		$tmp_line = $this->lookahead;
		$read_more = true;
		
		do { 
			$this->lookahead = fgets($this->fh, 1024); 
			$this->lookahead = ereg_replace("[\r\n]", "", $this->lookahead);

			if (
				(
					$this->lookahead != '' && 
					($this->lookahead{0} == " " || $this->lookahead{0} == "\t")
				) 
				|| $tmp_line == '' 
				|| $tmp_line == "\n"
			)
				$tmp_line = rtrim($tmp_line) . str_replace("\t"," ", $this->lookahead);
			else
				$read_more = false;
			
		} while ($read_more & !feof($this->fh)); 
		
		return trim($tmp_line);
	} // end function read_line()

} ?>