<?php 

$this->data['head'] = '

	<script type="text/javascript" src="/res/markitup/jquery.markitup.js"></script>
	<script type="text/javascript" src="/res/markitup/sets/markdown/set.js"></script>
	<link rel="stylesheet" media="screen" type="text/css" href="/res/markitup/skins/markitup/style.css" />
	<link rel="stylesheet" media="screen" type="text/css" href="/res/markitup/sets/markdown/style.css" />

	<script type="text/javascript" >
	   $(document).ready(function() {
	      $("#foodledescr").markItUp(mySettings);
	   });
	</script>
';

$this->includeAtTemplateBase('includes/header.php'); 



echo '<h1>' .  $this->data['name'] . '</h1>'; 

echo('<form method="post" action="/fixdate/' . $this->data['foodle']->identifier . '">');



?>


<p><?php echo $this->t('fixdate_descr'); ?></p>

<p><?php echo $this->t('description'); ?>: <br />

<textarea id="foodledescr" style="width: 95%; height: 160px" name="descr" rows="80" cols="5"><?php
if (isset($this->data['descr'])) echo $this->data['descr'];
?></textarea><br />
<?php 
	echo $this->t('markdowninfo', 
			array('%Markdown%' => '<a href="http://daringfireball.net/projects/markdown/syntax">Markdown</a>')
		) . ' ' . $this->t('htmlinfo'); 
?></p>









<?php

$datefrom = $this->data['tomorrow'];
$dateto   = $this->data['tomorrow'];
$timefrom = '08:00';
$timeto   = '16:00';

if (!empty($this->data['foodle']->datetime['datefrom'])) {
	$datefrom = $this->data['foodle']->datetime['datefrom'];
}
if (!empty($this->data['foodle']->datetime['dateto'])) {
	$dateto = $this->data['foodle']->datetime['dateto'];
}
if (!empty($this->data['foodle']->datetime['timefrom'])) {
	$timefrom = $this->data['foodle']->datetime['timefrom'];
}
if (!empty($this->data['foodle']->datetime['timeto'])) {
	$timeto = $this->data['foodle']->datetime['timeto'];
}

$checkbox_eventtimeopt = '';
$checkbox_eventallday = '';
$checkbox_eventmultipledays = '';

if (!empty($this->data['foodle'])) {
	$checkbox_eventtimeopt = $this->data['foodle']->datetimeCheckbox('eventtimeopt');
	$checkbox_eventallday = $this->data['foodle']->datetimeCheckbox('eventallday');
	$checkbox_eventmultipledays = $this->data['foodle']->datetimeCheckbox('eventmultipledays');
}

?>
<div class="eventdatetime">
<p>
	<?php
		echo '<input type="checkbox" id="eventtimeopt" name="eventtimeoptdummy" value="enabled" checked="checked" disabled="disabled" />';
	?>
	<input type="hidden" name="eventtimeopt" value="enabled" />
	<label for="eventtimeopt">Associate the Foodle with a specific date and time</label></p>

	<div id="eventdatetimecontent" style="margin 0px; padding: 0px; display: none">
		
	<?php 
	
		echo('<p>Timezone: ');
		
		if (isset($this->data['ftimezone'])) {
			echo($this->data['timezone']->getHTMLList($this->data['ftimezone']) . '');
		} else {
			echo($this->data['timezone']->getHTMLList() . '');
		}
		echo('</p>');
		
	?>
	
		
	<p>
		<?php
			echo '<input type="input" id="eventdatefrom" name="eventdatefrom" value="' . $datefrom . '" />';
			echo '<input type="input" id="eventtimefrom" name="eventtimefrom" value="' .  $timefrom . '" />';
			echo ' <span id="todelimiter">&mdash;</span>';
			echo '<input type="input" id="eventtimeto" name="eventtimeto" value="' . $timeto . '" /> ';
			echo '<input type="input" id="eventdateto" name="eventdateto" value="' . $dateto . '" />';
		?>
	</p>
	
	<p>
		<?php
			echo '<input type="checkbox" id="eventallday" name="eventallday" value="enabled" ' . $checkbox_eventallday . '/>';
			echo '<label style="margin-right: 2em" for="eventallday">All day</label>';
			
			echo '<input type="checkbox" id="eventmultipledays" name="eventmultipledays" value="enabled" ' .  $checkbox_eventmultipledays . '/>';
			echo '<label for="eventmultipledays">Multiple days</label>';					
		?>
	</p>
	</div>

</div>	

			
<div class="ready">
<?php
	echo('<input id="savefix" type="submit" name="save" value="' . $this->t('save_fixdate') . '" />');
?>
</div>




</form>
	

			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>