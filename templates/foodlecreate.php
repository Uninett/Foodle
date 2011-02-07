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

if(array_key_exists('edit', $this->data)) {
	echo '<h1>' . $this->t('editfoodle') . '</h1>'; 
	$action = '/edit/' . $this->data['identifier'];

} else {
	echo '<h1>' . $this->t('createnew') . '</h1>'; 
	$action = '/create';
}
echo('<form method="post" action="' . $action . '">');
echo(' <input type="hidden" id="coldef" name="coldef" value="" />');
echo(' <input type="hidden" id="settimezone" name="settimezone" value="" />');
echo(' <input type="hidden" id="columntype" name="columntype" value="' . (isset($this->data['columntype']) ? htmlspecialchars($this->data['columntype']) : '') . '" />');


			
if (!empty($this->data['foodle']->datetime)) {
	echo '<input type="hidden" id="includedatefield" name="includedatefield" value="enabled" />';
} else {
	echo '<input type="hidden" id="includedatefield" name="includedatefield" value="disabled" />';
}

?>



<div id="foodletabs"> 


    <ul style=" margin: 0px"> 
        <li><a href="#fdescr"><span><?php echo $this->t('foodledescr'); ?></span></a></li> 
        <li><a id="link_preview" href="#fcols"><span><?php echo $this->t('setupcolumns'); ?></span></a></li> 
        <!-- <li><a  href="#preview"><span><?php echo $this->t('preview'); ?></span></a></li>  -->
        <li><a href="#advanced"><span><?php echo $this->t('advancedoptions'); ?></span></a></li>
    </ul> 
    <div id="fdescr"> 

	
		<p><?php echo $this->t('name'); ?>: 
			<input type="text" name="name" id="foodlename" style="width: 400px; font-size: large" value="<?php
		if (isset($this->data['name'])) echo $this->data['name'];
		?>" /></p>
	
		<p><?php echo $this->t('description'); ?>: <br />
		<textarea id="foodledescr" style="width: 95%; height: 160px" name="descr" rows="80" cols="5"><?php
		if (isset($this->data['descr'])) echo $this->data['descr'];
		?></textarea><br />
		<?php 
			echo $this->t('markdowninfo', 
					array('%Markdown%' => '<a href="http://daringfireball.net/projects/markdown/syntax">Markdown</a>')
				) . ' ' . $this->t('htmlinfo'); 
		?></p>
	

		<p><a class="button" id="btnToColSetup" onclick="$('#foodletabs').tabs('select', 1);">
			<span><?php echo $this->t('next'); ?> » <?php echo $this->t('setupcolumns'); ?></span></a></p>
		<br class="clear" />

    </div> 
    <div id="fcols"> 



		<?php
		
			$columntypesdatesChecked = '';
			$columntypestimezoneChecked = '';
			$columntypestextChecked = ' checked="checked"';
			
			$columntypedisabled = '';
			if (!$this->data['allowChangeColumn']) {
				$columntypedisabled = ' disabled="disabled" ';
			}
			
			if (isset($this->data['columntype']) && $this->data['columntype'] === 'timezone') {
				$columntypesdatesChecked = '';
				$columntypestimezoneChecked = ' checked="checked"';
				$columntypestextChecked = '';

			} else if (isset($this->data['isDates']) && $this->data['isDates']) {
				$columntypesdatesChecked = ' checked="checked"';
				$columntypestimezoneChecked = '';
				$columntypestextChecked = '';
			} 
		?>

		
		<div class="coltypeselector">

		<?php
		
			echo '<p style="margin: 5px 2px" >' . $this->t('qcolumntype') . '</p>';
		
			echo '<p style="margin: 2px"><input type="radio" name="columntypes" id="columntypestext" value="text" '.
				$columntypestextChecked . $columntypedisabled . ' />' . 
				'<label for="columntypestext">' . $this->t('qcolumntypetext') . '</label></p>';


			echo '<p style="margin: 2px"><input type="radio" name="columntypes" id="columntypesdates" value="dates" ' .
				$columntypesdatesChecked . $columntypedisabled .  ' />' .
				'<label for="columntypesdates">' . $this->t('qcolumntypedates') . '</label></p>';
				
			echo '<p style="margin: 2px"><input type="radio" name="columntypes" id="columntypestimezone" value="timezone" ' .
				$columntypestimezoneChecked . $columntypedisabled . ' />' . 
				'<label for="columntypestimezone">' . $this->t('qcolumntypetimezone') . '</label></p>';

		?>

		</div>
		
		
		


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
		<div id="eventdatetime" class="eventdatetime">
			<p>
				<?php
					echo '<input type="checkbox" id="eventtimeopt" name="eventtimeopt" value="enabled" ' . $checkbox_eventtimeopt . '/>';
				?>
				<label for="eventtimeopt"><?php echo $this->t('associate_time'); ?></label></p>
			
			<div id="eventdatetimecontent" style="margin 0px; padding: 0px; display: none">
				
			<?php 
			
				echo('<p>' . $this->t('timezone'). ': ');
				
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
					echo '<label style="margin-right: 2em" for="eventallday">' . $this->t('allday'). '</label>';
					
					echo '<input type="checkbox" id="eventmultipledays" name="eventmultipledays" value="enabled" ' .  $checkbox_eventmultipledays . '/>';
					echo '<label for="eventmultipledays">' . $this->t('multipledays'). '</label>';					
				?>
			</p>
			</div>

		</div>	


		
		
		
		
		
		
		<div class="fcols">




			<!--     ==============  DATES =============== -->
			<div class="columnsetupdates">
				
				<?php 
				
					echo('<div class="timezoneselector"><p>' . $this->t('selecttimezone') . '<br />');
				
					if (isset($this->data['ftimezone'])) {
						echo($this->data['timezone']->getHTMLList($this->data['ftimezone']) . '');
					} else {
						echo($this->data['timezone']->getHTMLList() . '');
					}

					echo('</p></div>');
					
					echo('<p style="color: #888">' . $this->t('timeslotinfo') . '</p>');
#					echo '</div>';
				?>
				
<!-- 			<div></div> -->

				<?php
			
				if (isset($this->data['isDates']) && $this->data['isDates'] && isset($this->data['columns']) &&
					$this->data['columntype'] !== 'timezone'
					) {
					// echo '<pre>';
					// echo(var_export($this->data['columns'], TRUE));
					// echo '</pre>';
				
					$i = 0;
					foreach($this->data['columns'] AS $column) {
						echo '<div class="fcol">';
						echo ' <input class="fcoli wmd-ignore" style="" type="text" name="timeslot[]" placeholder="' . $this->t('date'). '" 
							value="' . htmlspecialchars($column['title']) . '" style="width: 100%" />';
						echo ' <div style="display: inline" class="subcolcontainer">';
					
						if (isset($column['children'])) {
							foreach($column['children'] AS $option) {
								echo '<input class="fscoli wmd-ignore" type="text" value="' . htmlspecialchars($option['title']) . '" name="timeslots[]" placeholder="' . $this->t('time'). '" />';
							}
						} else {
							echo '<input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="' . $this->t('time'). '" />';
						}
						echo ' <a style="float: right" class="minibutton onemoreoption"><span>' . $this->t('addtimeslot'). '</span></a>';
						if ($i++ == 0) {
							echo ' <a style="float: right" class="minibutton duplicate"><span>' . $this->t('duplicate'). '</span></a>';
						}
						echo ' </div>';
						echo '</div>';
					}
				

				} else {
			
				?>
					<div class="fcol"  style="" >
						<input class="fcoli wmd-ignore" type="text" value="" name="timeslot[]" placeholder="<?php echo $this->t('date'); ?>" />
						<div style="display: inline" class="subcolcontainer">
							<input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="<?php echo $this->t('time'); ?>" /><input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="<?php echo $this->t('time'); ?>" /><input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="<?php echo $this->t('time'); ?>" />
	
							<a style="float: right" class="minibutton onemoreoption"><span><?php echo $this->t('addtimeslot'); ?></span></a>
							<a style="float: right" class="minibutton duplicate"><span><?php echo $this->t('duplicate'); ?></span></a>
						</div>
					</div>
		
					<div class="fcol"  style="" >
						<input class="fcoli wmd-ignore" type="text" value="" name="timeslot[]" placeholder="<?php echo $this->t('date'); ?>" />
						<div style="display: inline" class="subcolcontainer">
							<input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="<?php echo $this->t('time'); ?>" /><input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="<?php echo $this->t('time'); ?>" /><input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="<?php echo $this->t('time'); ?>" />
	
							<a style="float: right" class="minibutton onemoreoption"><span><?php echo $this->t('addtimeslot'); ?></span></a>
						</div>
					</div>
				<?php } ?>

			
				<div><a style="float: left" class="minibutton onemorecolumn"><span><?php echo $this->t('onemoredate'); ?></span></a></div>
			
			</div>
			
			
			
			
			
			
			
			
			
			
			
			
			

		
		
			
			
			<!--     ==============  GENERIC  =============== -->
			<div class="columnsetupgeneric" style="clear: both">
			

				<?php
				
				if (isset($this->data['isDates']) && !$this->data['isDates'] && isset($this->data['columns'])) {
					// echo '<pre>';
					// echo(var_export($this->data['columns'], TRUE));
					// echo '</pre>';
					
					foreach($this->data['columns'] AS $column) {
						echo '<div class="fcol">';
						echo ' <input class="fcoli wmd-ignore" style="" type="text" name="timeslot[]" placeholder="' . $this->t('columnheader') . '" 
							value="' . htmlspecialchars($column['title']) . '" style="width: 100%" />';
						echo ' <div style="display: inline" class="subcolcontainer">';
						
						if (isset($column['children'])) {
							foreach($column['children'] AS $option) {
								echo '<input class="fscoli wmd-ignore" type="text" value="' . htmlspecialchars($option['title']) . '" name="timeslots[]" placeholder="' . $this->t('option') . '" />';
							}
						} else {
							echo '<input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="' . $this->t('option') . '" />';
						}
						echo ' <a style="float: right" class="minibutton onemoreoption"><span>' . $this->t('addoption') . '</span></a>';
						echo ' </div>';
						echo '</div>';
					}
					

				} else {
				
				?>
				<div class="fcol" style="" >
					<input class="fcoli wmd-ignore" style="" type="text" value="<?php echo $this->t('attend'); ?>" name="timeslot[]" placeholder="<?php echo $this->t('columnheader'); ?>" style="width: 100%" />
					<div style="display: inline" class="subcolcontainer">
						<input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="<?php echo $this->t('option'); ?>" /><input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="<?php echo $this->t('option'); ?>" /><input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="<?php echo $this->t('option'); ?>" />
						<a style="float: right" class="minibutton onemoreoption"><span><?php echo $this->t('addoption'); ?></span></a>
					</div>
				</div>
				
				<div class="fcol" style="" >
					<input class="fcoli wmd-ignore" style="" type="text" value="" name="timeslot[]" placeholder="<?php echo $this->t('columnheader'); ?>" style="width: 100%" />
					<div style="display: inline" class="subcolcontainer">
						<input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="<?php echo $this->t('option'); ?>" /><input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="<?php echo $this->t('option'); ?>" /><input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="<?php echo $this->t('option'); ?>" />
						<a style="float: right" class="minibutton onemoreoption"><span><?php echo $this->t('addoption'); ?></span></a>
					</div>
				</div>

				<?php } ?>
				<div><a style="float: left" class="minibutton onemorecolumn"><span><?php echo $this->t('addcolumn'); ?></span></a></div>

			
			</div>







			<!--     ==============  TIMEZONE PLANNER =============== -->
			<div class="columnsetuptimezone">
				
				
		
			<?php 
			
				echo('<div class="timezoneselector"><p>' . $this->t('selecttimezone') . '<br />');
			
				if (isset($this->data['ftimezone'])) {
					echo($this->data['timezone']->getHTMLList($this->data['ftimezone']) . '');
				} else {
					echo($this->data['timezone']->getHTMLList() . '');
				}

				echo('</p></div>');
			?>
			

				
<!-- 				<div></div> -->
				
				
				<div class="fcol"  style="" >
					<?php
						
						$date = $this->data['today'];
						if (isset($this->data['isDates']) && $this->data['isDates'] && isset($this->data['columns'])) {
							$date = ($this->data['columns'][0]['title']);
						}
						
					
						echo('<p style="color: #888">' . $this->t('timezonedateinfo') . '</p>');
						
						echo('<input class="fcoli wmd-ignore" type="text" value="' . htmlspecialchars($date) . '" name="timeslot[]" placeholder="' . $this->t('date') . '" />');
					?>

					

				<?php
					echo('<p style="color: #888">' . $this->t('timezonetimeinfo') . '</p>');
					
					
				?>
					<div style="display: inline" class="subcolcontainer">
						<input class="fscoli wmd-ignore" type="text" value="00:00" name="timeslots[]" disabled="disabled"  />
						<input class="fscoli wmd-ignore" type="text" value="01:00" name="timeslots[]" disabled="disabled" />
						<input class="fscoli wmd-ignore" type="text" value="02:00" name="timeslots[]" disabled="disabled" />
						<input class="fscoli wmd-ignore" type="text" value="03:00" name="timeslots[]" disabled="disabled" />
						<input class="fscoli wmd-ignore" type="text" value="04:00" name="timeslots[]" disabled="disabled" />
						<input class="fscoli wmd-ignore" type="text" value="05:00" name="timeslots[]" disabled="disabled" />

						<input class="fscoli wmd-ignore" type="text" value="06:00" name="timeslots[]" disabled="disabled" />
						<input class="fscoli wmd-ignore" type="text" value="07:00" name="timeslots[]" disabled="disabled" />
						<input class="fscoli wmd-ignore" type="text" value="08:00" name="timeslots[]" disabled="disabled" />
						<input class="fscoli wmd-ignore" type="text" value="09:00" name="timeslots[]" disabled="disabled" />
						<input class="fscoli wmd-ignore" type="text" value="10:00" name="timeslots[]" disabled="disabled" />
						<input class="fscoli wmd-ignore" type="text" value="11:00" name="timeslots[]" disabled="disabled" />

						<input class="fscoli wmd-ignore" type="text" value="12:00" name="timeslots[]" disabled="disabled" />
						<input class="fscoli wmd-ignore" type="text" value="13:00" name="timeslots[]" disabled="disabled" />
						<input class="fscoli wmd-ignore" type="text" value="14:00" name="timeslots[]" disabled="disabled" />
						<input class="fscoli wmd-ignore" type="text" value="15:00" name="timeslots[]" disabled="disabled" />
						<input class="fscoli wmd-ignore" type="text" value="16:00" name="timeslots[]" disabled="disabled" />
						<input class="fscoli wmd-ignore" type="text" value="17:00" name="timeslots[]" disabled="disabled" />

						<input class="fscoli wmd-ignore" type="text" value="18:00" name="timeslots[]" disabled="disabled" />
						<input class="fscoli wmd-ignore" type="text" value="19:00" name="timeslots[]" disabled="disabled" />
						<input class="fscoli wmd-ignore" type="text" value="20:00" name="timeslots[]" disabled="disabled" />
						<input class="fscoli wmd-ignore" type="text" value="21:00" name="timeslots[]" disabled="disabled" />
						<input class="fscoli wmd-ignore" type="text" value="22:00" name="timeslots[]" disabled="disabled" />
						<input class="fscoli wmd-ignore" type="text" value="23:00" name="timeslots[]" disabled="disabled" />
					</div>

									
				</div>

			
			</div>
			<!--     ==============  END =============== -->



		</div>




		<br style="height: 0px; clear: both;" />
		<div class="ready">
		
			<p id="readytextname" style="display: none; float: right; margin: 0px">Your Foodle is missing a name!</p>
			<p id="readytextcol" style="display: none; float: right; margin: 0px">You need to enter text for at least one column...</p>
		<?php		
		if(array_key_exists('edit', $this->data)) {
			echo('<input id="save" type="submit" name="save" value="' . $this->t('updatefoodle') . '" />');
		} else {
			echo('<input id="save" type="submit" name="save" value="' . $this->t('completefoodle') . '" />');
		}
		
		?>

		</div>



		<h2 style="clear:both; margin-top: 1.5em"><?php echo $this->t('preview'); ?></h2>
		
		<p><?php echo $this->t('previewinfo3'); ?></p>

    	<div id="previewpane"></div>		    
		    
		    
    </div>
    

	<div id="advanced">

		<h2><?php echo $this->t('expire'); ?></h2>
		<p><?php echo $this->t('expireinfo'); ?></p>
		<input id="deadline" type="text" name="expire" value="<?php
		if (isset($this->data['expire'])) echo $this->data['expire'];
		?>"/><br />
		<?php echo $this->t('format'); ?>: YYYY-MM-DD HH:MM
		
		
		<h2><?php echo $this->t('anonheader'); ?></h2>
		
		<?php
			$checked = '';
			if (array_key_exists('anon', $this->data)) {
				if ($this->data['anon'] == '1') {
					$checked = ' checked="checked" ';
				}
			}
			
			echo('<p><input type="checkbox" id="allowanon" name="anon" ' . $checked . '/> <label for="allowanon">' . $this->t('allowanon') . '</label></p>');
		?>
		
		
		<?php
		
			echo('<h2>' . $this->t('responsetype') . '</h2>');
			echo('<p>' . $this->t('responsetypeinfo') . '</p>');

			
			$responsetypeChecked = array(
				'default' => '',
				'yesno' => '',
				'yesnomaybe' => '',
			);
			if (isset($this->data['responsetype'])) {
				$responsetypeChecked[$this->data['responsetype']] = ' checked="checked" ';
			} else {
				$responsetypeChecked['default'] = ' checked="checked" ';
			}

			echo('<p style="margin: 2px">
				<input type="radio" name="responsetype" id="responsetype_default" value="default" ' . $responsetypeChecked['default'] . '/>
				<label for="responsetype_default">' . $this->t('responsetype_default') . '</label></p>');

			echo('<p style="margin: 2px">
				<input type="radio" name="responsetype" id="responsetype_yesno" value="yesno" ' . $responsetypeChecked['yesno'] . '/>
				<label for="responsetype_yesno">' . $this->t('responsetype_yesno') . '</label></p>');

			echo('<p style="margin: 2px">
				<input type="radio" name="responsetype" id="responsetype_yesnomaybe" value="yesnomaybe" ' . $responsetypeChecked['yesnomaybe'] . '/>
				<label for="responsetype_yesnomaybe">' . $this->t('responsetype_yesnomaybe') . '</label></p>');
				




			echo('<h2>' . $this->t('extrafields') . '</h2>');
			echo('<p>' . $this->t('extrafields_info') . '</p>');
			
			$efChecked = array(
				'photo' => '',
				'org' => '',
				'location' => '',
			);
			
			if (!empty($this->data['extrafields'])) {
				foreach($this->data['extrafields'] AS $ef) {
					$efChecked[$ef] = ' checked="checked" ';
				}
			}

			echo('<p style="margin: 2px">
				<input type="checkbox" name="extrafields_photo" id="extrafields_photo" ' . $efChecked['photo'] . '>
				<label for="extrafields_photo">' . $this->t('extrafields_photo') . '</label></p>');

			echo('<p style="margin: 2px">
				<input type="checkbox" name="extrafields_org" id="extrafields_org" ' . $efChecked['org'] . '>
				<label for="extrafields_org">' . $this->t('extrafields_org') . '</label></p>');
				
			echo('<p style="margin: 2px">
				<input type="checkbox" name="extrafields_location" id="extrafields_location" ' . $efChecked['location'] . '>
				<label for="extrafields_location">' . $this->t('extrafields_location') . '</label></p>');
		
		?>
		



		
		
		
		
		
		<?php
			$maxcol = !empty($this->data['maxcol']) ? $this->data['maxcol'] : '';
			$maxnum = !empty($this->data['maxnum']) ? $this->data['maxnum'] : '';
			
			// echo 'maxcol [' . $maxcol . ']'; 
			// echo 'maxnum [' . $maxnum . ']';

			$maxcoldef = array('', '', '', '', '', '');
			$maxcoldef[$maxcol] = ' selected="selected" ';

		?>
		<h2><?php echo $this->t('maxheader'); ?></h2>
		<p><?php echo $this->t('maxdescr'); ?><br />
		<input id="maxentries" type="text" name="maxentries" size="3" value="<?php echo $maxnum; ?>" /></p>
		<p><?php echo $this->t('maxcolinfo'); ?><br />
			<select id="maxentriescol" name="maxentriescol">
				<option value="0" <?php echo $maxcoldef[0]; ?>><?php echo $this->t('allentries'); ?></option>
				<option value="1" <?php echo $maxcoldef[1]; ?>>Column 1</option>
				<option value="2" <?php echo $maxcoldef[2]; ?>>Column 2</option>
				<option value="3" <?php echo $maxcoldef[3]; ?>>Column 3</option>
				<option value="4" <?php echo $maxcoldef[4]; ?>>Column 4</option>
				<option value="5" <?php echo $maxcoldef[5]; ?>>Column 5</option>
			</select></p>
		
		<p><a class="button buttonUpdatePreview" onclick="$('#foodletabs').tabs('select', 1);">
			<span><?php echo $this->t('next'); ?> » <?php echo $this->t('setupcolumns'); ?></span></a></p>
		<br class="clear" />
		
		
	</div>


</div>

</form>
	

			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>