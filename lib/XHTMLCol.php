<?php

class XHTMLCol {
	
	function __construct() {
		
	}
	
	public static function show(SimpleSAML_XHTML_Template $t, Data_Foodle $foodle) { 
		
		$coldef = array();
		$foodle->getColumnHeaders(&$coldef);
		$coldepth = $foodle->getColumnDepth();
		
		echo '<thead>';


		/*
		 * Special handling of the first row
		 */
		echo '<tr>
			<th rowspan="' . $coldepth . '" style="width: 20px; padding: 3px 1px 1px 1px">
				<img alt="notes" src="/res/notes.png" />
			</th>
			<th rowspan="' . $coldepth . '">'.  $t->t('name') . '</th>';
		
		foreach($coldef[0] AS $colcell) {
			$colspan = (isset($colcell['colspan']) ? 'colspan="' . $colcell['colspan'] . '" ' : '');
			$rowspan = (isset($colcell['rowspan']) ? 'rowspan="' . $colcell['rowspan'] . '" ' : '');
			echo '<th ' . $colspan . $rowspan . '>' . htmlspecialchars($colcell['title']) . '</th>';
		}
		echo '<th rowspan="' . $coldepth . '" style="width: 4em">' . $t->t('updated') . '</th>';
		echo('</tr>');
		array_shift($coldef);

		/*
		 * Process the rest of the rows.
		 */
		foreach($coldef AS $colrow) {
			echo '<tr>';
			foreach($colrow AS $colcell) {
				$colspan = (isset($colcell['colspan']) ? 'colspan="' . $colcell['colspan'] . '" ' : '');
				$rowspan = (isset($colcell['rowspan']) ? 'rowspan="' . $colcell['rowspan'] . '" ' : '');
				echo '<th ' . $colspan . $rowspan . '>' . htmlspecialchars($colcell['title']) . '</th>';
			}
			echo '</tr>';
		}

		echo '</thead>';
		
	}
	
	
}



