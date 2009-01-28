<?php 
	$this->includeAtTemplateBase('includes/header.php'); 

?>

<form method="post" action="schedule.php">

<h1><?php echo $this->t('createnew'); ?></h1>


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
        <li><a href="#fcols"><span><?php echo $this->t('setupcolumns'); ?></span></a></li> 
        <li><a id="link_preview" href="#preview"><span><?php echo $this->t('preview'); ?></span></a></li> 
        <li><a href="#advanced"><span><?php echo $this->t('advancedoptions'); ?></span></a></li>
    </ul> 
    <div id="fdescr"> 

	
		<p><?php echo $this->t('name'); ?>: 
		<input type="text" name="name" style="width: 400px; font-size: large" value=""<?php
		if (isset($this->data['name'])) echo $this->data['name'];
		?>"/>
	
		<p><?php echo $this->t('description'); ?>: <br />
		<textarea id="foodledescr" style="width: 95%; height: 160px" name="descr"><?php
		if (isset($this->data['descr'])) echo $this->data['descr'];
		?></textarea><br />
		<?php echo $this->t('htmlinfo'); ?>
	

		<p><a class="button" onclick="$('#foodletabs > ul').tabs('select', 1);">
			<span><?php echo $this->t('next'); ?> » <?php echo $this->t('setupcolumns'); ?></span></a></p>
		<br class="clear" />

    </div> 
    <div id="fcols"> 

		<div style="float: right" id="inline"><p><?php echo $this->t('add_dates'); ?></p></div> 	


		<h2><?php echo $this->t('columns'); ?></h2>
	
		<p><?php echo $this->t('columnsdescr'); ?></p>
		
		<!-- p><?php echo $this->t('timeslotsinfo'); ?><br / -->
		
		<div class="fcols">
			<div class="fcol" style="border-top: 3px solid #eee; border-left: 3px solid #eee; margin: 5px 2em 1em 5px; padding: 4px" >
				<!-- <p style="float: right; text-align: right"> <a style="margin: .5em" href="">delete</a> </p> -->
				<input class="fcoli" style="display: block; font-size: large; width: 600px" type="text" name="timeslot[]" />			
				<div class="subcolcontainer">
					<?php echo $this->t('suboptions'); ?>
					<input style="display: inline; margin: 3px; width: 80px" type="text" name="timeslots[]" />
					<input style="display: inline; margin: 3px; width: 80px" type="text" name="timeslots[]" />
					<input style="display: inline; margin: 3px; width: 80px" type="text" name="timeslots[]" />
					<input style="display: inline; margin: 3px; width: 80px" type="text" name="timeslots[]" />
				</div>
			</div>
	
			
			<div class="fcol"  style="border-top: 3px solid #eee; border-left: 3px solid #eee;  margin: 5px 2em 1em 5px; padding: 4px" >
				<!-- <p style="float: right; text-align: right"> <a style="margin: .5em" href="">delete</a> </p> -->
				<input class="fcoli" style="display: block; font-size: large; width: 600px" type="text" name="timeslot[]" />			
				<div  class="subcolcontainer">
					<?php echo $this->t('suboptions'); ?>
					<input style="display: inline; margin: 3px; width: 80px" type="text" name="timeslots[]" />
					<input style="display: inline; margin: 3px; width: 80px" type="text" name="timeslots[]" />
					<input style="display: inline; margin: 3px; width: 80px" type="text" name="timeslots[]" />
					<input style="display: inline; margin: 3px; width: 80px" type="text" name="timeslots[]" />
				</div>
			</div>

		</div>




		<p style="clear:both"></p>

		<p>
			<a class="button" onclick="$('#foodletabs > ul').tabs('select', 2);">
				<span><?php echo $this->t('next'); ?> » <?php echo $this->t('preview'); ?></span></a>
			<a class="button" onclick="$('#foodletabs > ul').tabs('select', 3);">
				<span><?php echo $this->t('advancedoptions'); ?></span></a>
		</p>
		<br class="clear" />





    </div> 
    
    <div id="preview">

		<p><?php echo $this->t('previewinfo'); ?></p>
    
		<input style="display: block; margin: 2em" type="submit" name="save" value="<?php echo $this->t('completefoodle'); ?>" />
		
		<input type="hidden" id="coldef" name="coldef" value="" />

    	<p><?php echo $this->t('previewinfo2'); ?>:</p>
    	

    
		    <div style="margin: 1em; padding: 1em; border: 1px solid #eee">
		    	
		    	<h1 id="previewheader"></h1>
		    	<div class="wmd-preview"></div>
		    	
		    	<div id="previewpane"></div>
		    	
		    </div>
		    
		    
		    
    </div>
    

	<div id="advanced">

		<h2><?php echo $this->t('expire'); ?></h2>
		<p></p>
		<input id="deadline" type="text" name="expire" value="<?php
		if (isset($this->data['expire'])) echo $this->data['expire'];
		?>"/><br />
		<?php echo $this->t('format'); ?>: YYYY-MM-DD HH:MM
		
		
		<h2 style="margin-top: 2em"><?php echo $this->t('anonheader'); ?></h2>
		<p><input type="checkbox" name="anon"> <?php echo $this->t('allowanon'); ?><br />
		
		<h2 style="margin-top: 2em"><?php echo $this->t('maxheader'); ?></h2>
		<p><?php echo $this->t('maxdescr'); ?><br />
		<input id="maxentries" type="text" name="maxentries" size="3" value="" /></p>
		<p><?php echo $this->t('maxcolinfo'); ?><br />
			<select id="maxentriescol" name="maxentriescol">
				<option value="0"><?php echo $this->t('allentries'); ?></option>
				<option value="1">Column 1</option>
				<option value="2">Column 2</option>
				<option value="3">Column 3</option>
				<option value="4">Column 4</option>
				<option value="5">Column 5</option>
			</select></p>
		
		<p><a class="button" onclick="$('#foodletabs > ul').tabs('select', 2);">
			<span><?php echo $this->t('next'); ?> » <?php echo $this->t('preview'); ?></span></a></p>
		<br class="clear" />
		
		
	</div>


</div>















</form>
	

			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>