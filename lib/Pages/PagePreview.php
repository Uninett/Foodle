<?php

class Pages_PagePreview extends Pages_Page {
	
	function __construct($config, $parameters) {
		parent::__construct($config, $parameters);
	}
	
	// Process the page.
	function show() {
		$parameters = array('def');
		foreach($parameters AS $parameter) {
			$_REQUEST[$paramter] = strip_tags($_REQUEST[$paramter]);
		}

		$foodle = new Foodle($this->fdb);
		$foodle->columns = FoodleUtils::parseOldColDef(strip_tags($_REQUEST['def']));

		echo '<table class="list" style="width: 100%">';

		$t = new SimpleSAML_XHTML_Template($this->config, 'foodleresponse.php', 'foodle_foodle');

		XHTMLCol::show($t, $foodle);
						
		echo '<tbody>';
			

		/*
		 * Include demo response
		 */
		$demoresponse = new FoodleResponse($this->fdb, $foodle);
		$demoresponse->userid = 'you@acme.org';
		$demoresponse->username = 'John Doe';
		$demoresponse->email = 'you@acme.org';
		$demoresponse->response = array('type' => 'manual', 'data' => array_fill(0, $foodle->getNofColumns(), '0') );
		
		XHTMLResponseEntry::showEditable($t, $demoresponse, FALSE);

		echo '
		</tbody>	
		</table>';
		#echo '</div>'; // END outline box...
		
		echo '<p style="margin-top: 2em"><strong>Calendar integration</strong></p>';
	
		$columns = array();
		$foodle->getColumnList(&$columns);
		$columnDates = $foodle->getColumnDates();
		
		$ce = $foodle->calendarEnabled();
		
		if ($ce) {
			echo '<div><img src="/res/yes.png" alt="yes" /> ';
			echo 'All columns are reckognized as dates, and this Foodle will then be calendar-enabled. This means that users may connect the foodle response to their calaendar, to keep the response automatically updated. [ <a id="datecoldetailsbutton" href="#" onclick="$(\'#datecoldetails\').show();">Show details</a> ]</div>';
		} else {
			echo '<div><img src="/res/no2trans.png" alt="no" /> ';
			echo 'One or more of the columns are not reckognized as dates. This means that users may <strong>not</strong> connect the foodle response to their calaendar. [ <a id="datecoldetailsbutton" href="#" onclick="$(\'#datecoldetails\').show();">Show details</a> ]</div>';
		}
		

		echo '<table id="datecoldetails" style="display: none"><tr><th>Column header</th><th>Reckognized as date</th></tr>';

		foreach($columns AS $i => $column) {

			echo '<tr>';
			echo '<td>'. htmlspecialchars($column) . '</td>';
			if (!empty($columnDates[$i])) {
				echo '<td>' . 
					'<img src="/res/yes.png" alt="yes" /> ' .
					date('j. M Y H:i', $columnDates[$i]) . '</td>';				
			} else {
				echo '<td>' . 
					'<img src="/res/no2trans.png" alt="no" /> ' .
					'Not reckognized as a date'. '</td>';
			}

			echo '</tr>';
		}
		// echo '<p>Columns: <pre>'; print_r($columns); echo '</pre>';
		// echo '<p>Column dates: <pre>'; print_r($columnDates); echo '</pre>';
		echo '</table>';


	}
	
}

