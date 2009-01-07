<?php

$path_extra = '/var/simplesamlphp-openwiki/lib';
$path = ini_get('include_path');
$path = $path_extra . PATH_SEPARATOR . $path;
ini_set('include_path', $path);


include('/var/simplesamlphp-openwiki/www/_include.php');


/**
 * Loading simpleSAMLphp libraries
 */
require_once('SimpleSAML/Configuration.php');
require_once('SimpleSAML/Utilities.php');
require_once('SimpleSAML/Session.php');
require_once('SimpleSAML/Metadata/MetaDataStorageHandler.php');
require_once('SimpleSAML/XHTML/Template.php');

/*
 * Loading Foodle libraries
 */
require_once('../lib/Foodle.class.php');
#require_once('../lib/OpenWikiDictionary.class.php');

/**
 * Initializating configuration
 */
SimpleSAML_Configuration::init(dirname(dirname(__FILE__)) . '/config', 'foodle');
$config = SimpleSAML_Configuration::getInstance('foodle');

$coldef = $_POST['def'];
#$coldef = '13. Jan(s,d)|14. Jan';

$columns = Foodle::parseColumnUtil( $coldef);



$colnum = 0;
foreach ($columns AS $k => $v) {
	if ($v == NULL) {
		$colnum++;
	} elseif(is_array($v)) {
		$colnum += count($v);
	}
}

	
?>





<form method="post" action="foodle.php">

	
	
	<table class="list" style="width: 100%"><thead>
	
		<tr>
			<th rowspan="2" style="width: 20px; padding: 3px 1px 1px 1px"><img src="resources/notes.png" /></th>
			<th rowspan="2">Name</th>
	<?php
	
	$secondrow = array();
	foreach ($columns AS $head => $nextrow) {
		
		if ($nextrow == null) {
			echo '<th rowspan="2">' . $head . '</th>';
		} else {
			echo '<th colspan="' . count($nextrow) . '">' . $head . '</th>';
			foreach($nextrow AS $new) {
				$secondrow[] = $new;
			}
		}
	}
	echo '<th rowspan="2" style="width: 4em">Updated</th>';
	echo '</tr><tr>';
	foreach ($secondrow AS $entry) {
		echo '<th>' . $entry . '</th>';
	}

	?>
		
		</tr>
		
		
		</thead>
	<tbody>
	<?php


	echo '<tr class="you">';
	
	echo '<td>&nbsp;</td><td>';				
	echo '<input type="text" name="username" value="John Doe" /> (<tt>john.doe@acme.org</tt>)';
	echo '</td>';
	
	
	for ($i = 0; $i < $colnum; $i++) {
		echo '<td class="no center"><input type="checkbox" name="myresponse[]" value=""  /></td>';
	}

	echo '<td style="text-align: center"><input disabled="disabled" type="submit" name="save" value="Submit" /></td>';	

	echo '</tr>	';
	
	echo '<tr style="' . $shide . '" id="commentfield" class="you"><td colspan="' . ($colnum + 3) . '">
		<input type="text" id="comment" class="comment" name="comment" value="Some note..." /></td></tr>';
	

	
	
	?>
	</tbody>	
	</table>
	


</form>