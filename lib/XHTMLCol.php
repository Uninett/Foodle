<?php

class XHTMLCol {
	
	function __construct() {
		
	}
	
	public static function show(SimpleSAML_XHTML_Template $t, Data_Foodle $foodle, $confirm = FALSE) { 
		
		$coldef = array();
		$foodle->getColumnHeaders(&$coldef);
		$coldepth = $foodle->getColumnDepth();
		
		if($confirm === TRUE) {
			$coldef = array(array(array('title' => $t->t('attend'))));
			$coldepth = 1;
		}
		
		
		$extrafields = $foodle->getExtraFields();
		
		
#		echo '<pre>'; print_r($coldef); exit;
		
		echo '<thead>';

		/*
		 * Special handling of the first row
		 */
		echo '<tr>
			<th rowspan="' . $coldepth . '" style="width: 20px; padding: 3px 1px 1px 1px">
				<img alt="notes" src="/res/notes.png" />
			</th>
			<th rowspan="' . $coldepth . '" style="min-width: 140px">'.  $t->t('name') . '</th>';
			
		
		foreach($extrafields AS $extrafield) {
		
			switch($extrafield) {
				case 'photo':
						echo '<th rowspan="2" style="max-width: 32px; font-size: x-small">' . $t->t('extrafields_photo') . '</th>';
					break;
					
				case 'org':
						echo '<th rowspan="2" style="">' . $t->t('extrafields_org') . '</th>';
					break;
					
				case 'location':
						echo '<th rowspan="2" style="">' . $t->t('extrafields_location') . '</th>';
					break;

				case 'timezone':
						echo '<th rowspan="2" style="">' . $t->t('extrafields_timezone') . '</th>';
					break;
					
				default:
					echo '<th rowspan="2"></th>';
			}
		
		}
		
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


