<?php

class FoodleUtils {
	
	
	public static function getURL() {
		$config = SimpleSAML_Configuration::getInstance('foodle');
		return SimpleSAML_Utilities::selfURLhost() . '/' . $config->getValue('baseurlpath', '');
	}
	
	
	
}

?>