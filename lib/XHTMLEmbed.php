<?php

class XHTMLEmbed {
	
	function __construct() {
		
	}
	
	public static function getTable(SimpleSAML_XHTML_Template $t, Data_Foodle $foodle) { 
		
		$coldef = $foodle->getColumnHeadersVertical();
		$coldepth = $foodle->getColumnDepth();
		
		$counts = $foodle->calculateColumns();
		
#		echo '<pre>'; print_r($coldef); exit;

		
		$text .= '<table>';
		
		$text .= '<thead>';
		$text .= '<tr><th colspan="2">Option</th><th>Number of responses</th></tr>';
		$text .= '</thead>';
		$text .= '<tbody>' . "\n";

		$colno = 0;
		foreach($coldef AS $row) {
			$text .= '<tr style="">' . "\n";
			foreach($row AS $col) {
				$colspan =  ((isset($col['colspan']) ? ' colspan="' . $col['colspan'] . '"' : ''));
				$rowspan = ((isset($col['rowspan']) ? ' rowspan="' . $col['rowspan'] . '"' : ''));
				$text .= ' <td' . $rowspan . ' ' . $colspan . ' style="text-align: left; vertical-align: top">' . $col['title'] . '</td>' . "\n";

			}

			$style = '';
#			$colspan =  ((isset($col['colspan']) ? ' rowspan="' . $col['rowspan'] . '"' : ''));
			if (isset($counts[$colno]['style'])) $style = ' class="' . $counts[$colno]['style'] . '"';
			$text .= ' <td' . $style . ' style="text-align: right; vertical-align: top">' . $counts[$colno]['count'] . '</td>' . "\n";
			$colno++;

			$text .= '</tr>' . "\n";
		}
		
		$text .= '</tbody></table>';
		// echo '<pre>';
		// 		print_r($counts);
		return $text;
		

	}
	
	
}



