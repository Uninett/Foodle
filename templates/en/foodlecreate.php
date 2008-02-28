<?php 
	$this->includeAtTemplateBase('includes/header.php'); 

?>

<form method="post" action="index.php">


	<h1>Create new foodle</h1>

	<p>
		[ schedule an event | <a href="multiplechoice.php">multiple choice</a> ]
	</p>


	<p>Name: 
	<input type="text" name="name" value=""<?php
	if (isset($this->data['name'])) echo $this->data['name'];
	?>"/>

	<p>Description: <br />
	<textarea style="width: 400px; height: 100px" name="descr"><?php
	if (isset($this->data['descr'])) echo $this->data['descr'];
	?></textarea>

	
	<h2>Select dates</h2>
	
	<?php
	
	
	
	foreach ($this->data['calendar'] AS $month ) {
	
		echo '<table><tr><th colspan="7">' . $month['title'] . '</th></tr>';
		echo '<tr><td>Mon</td><td>Tue</td><td>Wed</td><td>Thu</td><td>Fri</td><td>Sat</td><td class="sunday">Sun</td></tr>';
		#foreach ($month AS $row) {
		for($r = 0; $r <= 4; $r++) {
		
			$row = $month[$r];
			#print_r($row);
			echo '<tr>';			
			for ($i = 0; $i <= 6; $i++) {
				if (array_key_exists($i, $row)) {
					echo '<td class="' . ($i == 6 ? 'sunday' : '') . ' ' . $row[$i]['class'] . 
						'"><input type="checkbox" name="date[]" value="' . 
						$row[$i]['text'] . '" />&nbsp;' . $row[$i]['day'] . '</td>';
				} else {
					echo '<td class="grey">&nbsp;</td>';
				}
				
			}
			echo '</tr>';			
		}
		echo '</table>';
	
	}
	
	
	
	?>
	
	<h2>Time slots</h2>

	<p>You can optionally add some timeslots that will be added to these days:<br />
	<input type="text" name="timeslot[]" /><br />
	<input type="text" name="timeslot[]" /><br />
	<input type="text" name="timeslot[]" /><br />
	<input type="text" name="timeslot[]" /><br />
	<input type="text" name="timeslot[]" /><br />
	<input type="text" name="timeslot[]" />

	<p>
	<input type="submit" name="save" value="Create a new foodle" />

</form>
	

			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>