<?php 
	$this->includeAtTemplateBase('includes/header.php'); 

?>








<div class="columned">
	<div class="gutter"></div>
	<div class="col1">



<table style="width: 100%" class="statstable">

	<thead>
		<tr>
			<th>Realm</th>
			<th>Total</th>
			<th>Last week</th>
			<th>Last day</th>
		</tr>
	</thead>

<?php

echo '<tr class="totals"><td class="realm">Total</td><td>' . $this->data['totalsrealm']['total'] . '</td><td>' . $this->data['totalsrealm']['week'] . '</td><td>' . $this->data['totalsrealm']['day'] . '</td></tr>';

foreach($this->data['statsrealm'] AS $realm =>  $sr) {
	
	echo '<tr>
			<td class="realm">' . $realm . '</td>
			<td class="counttotal">' . 
				(!empty($sr['total']['c']) ? $sr['total']['c'] : '<span style="color: #ccc">NA</a>') . 
			'</td>
			<td class="countweek">' . 
				(!empty($sr['week']['c']) ? $sr['week']['c'] : '<span style="color: #ccc">NA</a>') . 
			'</td>
			<td class="countday">' . 
				(!empty($sr['day']['c']) ? $sr['day']['c'] : '<span style="color: #ccc">NA</a>') . 
			'</td>
		</tr>';	
	
}

?>
</table>










	</div>
	<div class="col2">
			
....



			
	</div>
	<div class="col3">


...


	</div><!-- /#col3 -->
	<br style="height: 0px; clear: both">
</div>




			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>