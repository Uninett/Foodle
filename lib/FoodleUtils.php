<?php

class FoodleUtils {
	
	
	public static function getURL() {
		$config = SimpleSAML_Configuration::getInstance('foodle');
		$url = $config->getString('url', SimpleSAML_Utilities::selfURLhost());
		return $url . '/' . $config->getValue('baseurlpath', '');
	}
	
	public static function cleanUsername($username) {
		$username = preg_replace('/[\'"]/', '', $username);
		return $username;
	}
	
	// The parameters of this function are the dates to be compared.
	// The first should be prior to the second. The dates are in
	// the form of: 1978-04-26 02:00:00.
	// They also can come from a web form using the global $_POST['start']
	// and $_POST['end'] variables.
	public static function date_diff($secondsago) {
		
#		echo 'comparing ' . $secondsago;
#		return $secondsago . ' seconds';

		if (is_null($secondsago)) return 'NA';

		$nseconds = abs($secondsago); // Number of seconds between the two dates
		
		$ndays = round($nseconds / 86400); // One day has 86400 seconds
		$nseconds = $nseconds % 86400; // The remainder from the operation
		$nhours = round($nseconds / 3600); // One hour has 3600 seconds
		$nseconds = $nseconds % 3600;
		$nminutes = round($nseconds / 60); // One minute has 60 seconds, duh!
		$nseconds = $nseconds % 60;

		if ($ndays > 0) 
			return $ndays . " days";
		elseif ($nhours > 0) 
			return $nhours . "h " . $nminutes . "m";
		elseif ($nminutes > 0) 
			return $nminutes . " min";
		else 
			return $nseconds . " sec";
	} 

	/*
	 * Parses the deprecated foodle columne format, that looks like this:
	 * 		Column1|Col2(foo,bar)
	 */
	public static function parseOldColDef($string) {
		$result = array();
		$level1 = explode('|', $string);
		foreach($level1 AS $head) {
			if (preg_match('/(.*)\((.*)\)/', $head, $matches)) {
				$children = array();
				foreach(explode(',', $matches[2]) AS $child) 
					$children[] = array('title' => strip_tags($child));
				$entry = array('title' => strip_tags($matches[1]), 'children' => $children);
			} else {
				$entry = array('title' => strip_tags($head));
			}
			$result[] = $entry;
		}
		return $result;
	}

	
}

