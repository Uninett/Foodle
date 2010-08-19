<?php 

$this->includeAtTemplateBase('includes/header.php'); 

if(array_key_exists('edit', $this->data)) {
	echo '<h1>' . $this->t('editfoodle') . '</h1>'; 
	$action = '/edit/' . $this->data['foodle']->identifier;

} else {
	echo '<h1>' . $this->t('createnew') . '</h1>'; 
	$action = '/create';
}
echo('<form method="post" action="' . $action . '">');


?>



<div id="foodletabs"> 
     <!--
        <input type="button" onclick="$('#tabsEx1 > ul').tabs('add', '#appended-tab', 'New Tab');" value="Add new tab"> 
        <input type="button" onclick="$('#tabsEx1 > ul').tabs('add', '#inserted-tab', 'New Tab', 1);" value="Insert tab"> 
        <input type="button" onclick="$('#tabsEx1 > ul').tabs('disable', 1);" value="Disable tab 2"> 
        <input type="button" onclick="$('#tabsEx1 > ul').tabs('enable', 1);" value="Enable tab 2"> 
        <input type="button" onclick="$('#tabsEx1 > ul').tabs('select', 2);" value="Select tab 3"> 
         -->
    <ul style=" margin: 0px"> 
        <li><a href="#fdescr"><span><?php echo $this->t('foodledescr'); ?></span></a></li> 
        <li><a id="link_preview" href="#fcols"><span><?php echo $this->t('setupcolumns'); ?></span></a></li> 
        <!-- <li><a  href="#preview"><span><?php echo $this->t('preview'); ?></span></a></li>  -->
        <li><a href="#advanced"><span><?php echo $this->t('advancedoptions'); ?></span></a></li>
    </ul> 
    <div id="fdescr"> 

	
		<p><?php echo $this->t('name'); ?>: 
			<input type="text" name="name" style="width: 400px; font-size: large" value="<?php
		if (isset($this->data['name'])) echo $this->data['name'];
		?>" /></p>
	
		<p><?php echo $this->t('description'); ?>: <br />
		<textarea id="foodledescr" style="width: 95%; height: 160px" name="descr" rows="80" cols="5"><?php
		if (isset($this->data['descr'])) echo $this->data['descr'];
		?></textarea><br />
		<?php echo $this->t('htmlinfo'); ?></p>
	

		<p><a class="button" onclick="$('#foodletabs').tabs('select', 1);">
			<span><?php echo $this->t('next'); ?> » <?php echo $this->t('setupcolumns'); ?></span></a></p>
		<br class="clear" />

    </div> 
    <div id="fcols"> 

		<table class="layout"><tr class="layout"><td class="layout">

		<h2><?php echo $this->t('columns'); ?></h2>
	
		<p><?php echo $this->t('columnsdescr'); ?></p>
		
		
		<div class="fcols">
		<?php

if (isset($this->data['columns'])) {
	foreach($this->data['columns'] AS $header => $subitems) {
		echo('<div class="fcol" style="" >
				<input class="fcoli" style="" 
					value="' . htmlspecialchars($header) . '"
					type="text" name="timeslot[]" />
				<div class="subcolcontainer">' );
		if(!empty($subitems)) {
			foreach($subitems AS $subitem) {
				echo('<input class="fscoli" 
					type="text" name="timeslots[]" value="' . htmlspecialchars($subitem) . '" />');
			}
		} else {
			echo('<input style="" type="text" value="" name="timeslots[]" />
			<input style="" type="text" value="" name="timeslots[]" />
			<input style="" type="text" value="" name="timeslots[]" />
			<input style="" type="text" value="" name="timeslots[]" />');
		}
		echo('</div>
	</div>');
}
}
		
		?>
		
		

			<div class="fcol" style="" >
				<!-- <p style="float: right; text-align: right"> <a style="margin: .5em" href="">delete</a> </p> -->
				<input class="fcoli wmd-ignore" style="" type="text" value="" name="timeslot[]" placeholder="Date" />			
				<div class="subcolcontainer">
					<!-- <?php echo $this->t('suboptions'); ?> -->
					<input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="Time" />
					<input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="Time" />
					<input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="Time" />
				</div>
			</div>
	
			
			<div class="fcol"  style="" >
				<!-- <p style="float: right; text-align: right"> <a style="margin: .5em" href="">delete</a> </p> -->
				<input class="fcoli wmd-ignore" style="" type="text" value="" name="timeslot[]" placeholder="Date" />
				<div  class="subcolcontainer">
					<!-- <?php echo $this->t('suboptions'); ?> -->
					<input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="Time" />
					<input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="Time" />
					<input class="fscoli wmd-ignore" type="text" value="" name="timeslots[]" placeholder="Time" />
				</div>
			</div>

		</div>



		<!-- <p>
			<a class="button buttonUpdatePreview" onclick="$('#foodletabs').tabs('select', 2);">
				<span><?php echo $this->t('next'); ?> » <?php echo $this->t('preview'); ?></span></a>
			<a class="button" onclick="$('#foodletabs').tabs('select', 3);">
				<span><?php echo $this->t('advancedoptions'); ?></span></a>
		</p> -->
		
		</td><td class="layout">

			<!-- <div style="float: right" id="inline"><p><?php echo $this->t('add_dates'); ?></p></div> 	 -->


		
		</td></tr></table>









<!--     </div> 
    
    <div id="preview"> -->

		<h2><?php echo $this->t('preview'); ?></h2>

		<p><?php echo $this->t('previewinfo3'); ?></p>
    
		<?php
		
		if(array_key_exists('edit', $this->data)) {
			echo('<input style="display: block; margin: 2em" type="submit" name="save" value="' . $this->t('updatefoodle') . '" />');
		} else {
			echo('<input style="display: block; margin: 2em" type="submit" name="save" value="' . $this->t('completefoodle') . '" />');
		}
		
		?>

		
		
		<input type="hidden" id="coldef" name="coldef" value="" />

    	<!-- <p><?php echo $this->t('previewinfo2'); ?>:</p> -->
    	

    
		    <!-- <div style="margin: 1em; padding: 1em; border: 1px solid #eee"> -->
		    	
		    	<h1 id="previewheader"></h1>
		    	<div class="wmd-preview"></div>
		    	
		    	<div id="previewpane"></div>
		    	
		    <!-- </div> -->
		    
		    
		    
    </div>
    

	<div id="advanced">

		<h2><?php echo $this->t('expire'); ?></h2>
		<p></p>
		<input id="deadline" type="text" name="expire" value="<?php
		if (isset($this->data['expire'])) echo $this->data['expire'];
		?>"/><br />
		<?php echo $this->t('format'); ?>: YYYY-MM-DD HH:MM
		
		
		<h2 style="margin-top: 2em"><?php echo $this->t('anonheader'); ?></h2>
		<?php
			$checked = '';
			if (array_key_exists('anon', $this->data)) {
				if ($this->data['anon'] == '1') {
					$checked = ' checked="checked" ';
				}
			}
			
			echo('<p><input type="checkbox" name="anon" ' . $checked . '/> ' . $this->t('allowanon') . '</p>');
		?>
		
		
		
		<?php
			$maxcol = 0;
			$maxnum = '';
			if (!empty($this->data['maxdef'])) {
				$maxdefc = split(':', $this->data['maxdef']);
				$maxcol = (int)$maxdefc[0];
				$maxnum = (int)$maxdefc[1];
			}
			$maxcoldef = array('', '', '', '', '', '');
			$maxcoldef[$maxcol] = ' selected="selected" ';

		?>
		<h2 style="margin-top: 2em"><?php echo $this->t('maxheader'); ?></h2>
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