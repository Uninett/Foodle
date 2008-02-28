<?php 
	$this->includeAtTemplateBase('includes/header.php'); 


?>




<form method="post" action="foodle.php">

<input type="hidden" name="id" value="<?php echo $this->data['identifier']; ?>" />
	<h1><?php if (isset($data['header'])) { echo $data['header']; } else { echo "Some error occured"; } ?></h1>


	<p><?php echo $this->data['descr']; ?></p>
	


	
	
	<h2>Responses</h2>
	
	<table class="list" style="width: 100%"><thead>
		<tr>
			<th rowspan="2">User ID</th>
			<th rowspan="2">Name</th>
	<?php
	
	$secondrow = array();
	foreach ($this->data['columns'] AS $head => $nextrow) {
		
		if ($nextrow == null) {
			echo '<th rowspan="2">' . $head . '</th>';
		} else {
			echo '<th colspan="' . count($nextrow) . '">' . $head . '</th>';
			foreach($nextrow AS $new) {
				$secondrow[] = $new;
			}
		}
	}
	echo '</tr><tr>';
	foreach ($secondrow AS $entry) {
		echo '<th>' . $entry . '</th>';
	}

	?></tr></thead>
	<tbody>
	<?php
	
	$counter = array_fill(0,count($this->data['yourentry']['response']), 0 );
	echo '<tr class="you"><td>' . $this->data['yourentry']['userid']. '</td>';
	echo '<td><input type="text" name="username" value="' . $this->data['yourentry']['username'] . '" /></td>';
	foreach ($this->data['yourentry']['response'] AS $no => $entry) {
		if ($entry == '1') {
			$counter[$no]++;
			echo '<td class="yes"><input type="checkbox" name="myresponse[]" checked="checked" value="' . $no . '" /></td>';
			
		} else {
			echo '<td><input type="checkbox" name="myresponse[]" value="' . $no . '"  /></td>';
		}
	}
	echo '</tr>	';
	

	foreach ($this->data['otherentries'] AS $response) {
		
		echo '<tr><td>' . $response['userid'] . '</td>';
		echo '<td>' . $response['username'] . '</td>';
		
		foreach ($response['response'] AS $no => $entry) {
			if ($entry) {
				$counter[$no]++;
				echo '<td class="yes">Yes</td>';
			} else {
				echo '<td class="no">No</td>';
			}
		}
		
	}
	$highest = max($counter);
	echo '<tr><td style="text-align: right; padding-right: 1em" colspan="2">Sum</td>';
	foreach ($counter AS $num) {
		if ($num == $highest) {
			echo '<td class="highest">' . $num . '</td>';
		} else {
			echo '<td>' . $num . '</td>';
		}
	}
	echo '</tr>';
	
	?>
	</tbody>	
	</table>
	

	
	<input type="submit" name="save" value="Submit your response" />

</form>
	

			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>