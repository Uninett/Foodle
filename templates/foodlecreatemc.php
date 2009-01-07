<?php 
	$this->includeAtTemplateBase('includes/header.php'); 

?>

<form method="post" action="multiplechoice.php">


	<h1><?php echo $this->t('createnew'); ?></h1>
	

	<p>
		[ <a href="schedule.php"><?php echo $this->t('schedule'); ?></a> | <?php echo $this->t('choice'); ?> ]
	</p>

	
	<p><?php echo $this->t('name'); ?>: 
	<input type="text" name="name" value=""<?php
	if (isset($this->data['name'])) echo $this->data['name'];
	?>"/>

	<p><?php echo $this->t('description'); ?>: <br />
	<textarea style="width: 400px; height: 100px" name="descr"><?php
	if (isset($this->data['descr'])) echo $this->data['descr'];
	?></textarea><br />
	<?php echo $this->t('htmlinfo'); ?>

	<p><?php echo $this->t('expire'); ?>: 
	<input type="text" name="expire" value=""<?php
	if (isset($this->data['expire'])) echo $this->data['expire'];
	?>"/><br />
	<?php echo $this->t('format'); ?>: YYYY-MM-DD HH:MM
	

	
	<h2><?php echo $this->t('createchoices'); ?></h2>
	
	<table>
		<thead>
		<tr>
			<th><?php echo $this->t('firstlevel'); ?></th>
			<th colspan="8"><?php echo $this->t('suboptions'); ?></th>
		</tr>
		</thead>
		<tr>
			<th><input type="text" name="c1" size="10" /></th>
			<td><input type="text" name="c11" size="10" /></td>
			<td><input type="text" name="c12" size="10" /></td>
			<td><input type="text" name="c13" size="10" /></td>
			<td><input type="text" name="c14" size="10" /></td>
			<td><input type="text" name="c15" size="10" /></td>
			<td><input type="text" name="c16" size="10" /></td>
			<td><input type="text" name="c17" size="10" /></td>
			<td><input type="text" name="c18" size="10" /></td>
		</tr>
		<tr>
			<th><input type="text" name="c2"  size="10" /></th>
			<td><input type="text" name="c21"  size="10" /></td>
			<td><input type="text" name="c22"  size="10" /></td>
			<td><input type="text" name="c23"  size="10" /></td>
			<td><input type="text" name="c24"  size="10" /></td>
			<td><input type="text" name="c25"  size="10" /></td>
			<td><input type="text" name="c26"  size="10" /></td>
			<td><input type="text" name="c27"  size="10" /></td>
			<td><input type="text" name="c28"  size="10" /></td>
		</tr>
		<tr>
			<th><input type="text" name="c3"  size="10" /></th>
			<td><input type="text" name="c31"  size="10" /></td>
			<td><input type="text" name="c32"  size="10" /></td>
			<td><input type="text" name="c33"  size="10" /></td>
			<td><input type="text" name="c34"  size="10" /></td>
			<td><input type="text" name="c35"  size="10" /></td>
			<td><input type="text" name="c36"  size="10" /></td>
			<td><input type="text" name="c37"  size="10" /></td>
			<td><input type="text" name="c38"  size="10" /></td>
		</tr>
		<tr>
			<th><input type="text" name="c4"  size="10" /></th>
			<td><input type="text" name="c41"  size="10" /></td>
			<td><input type="text" name="c42"  size="10" /></td>
			<td><input type="text" name="c43" size="10"  /></td>
			<td><input type="text" name="c44"  size="10" /></td>
			<td><input type="text" name="c45"  size="10" /></td>
			<td><input type="text" name="c46"  size="10" /></td>
			<td><input type="text" name="c47" size="10"  /></td>
			<td><input type="text" name="c48"  size="10" /></td>
		</tr>
		<tr>
			<th><input type="text" name="c5"  size="10" /></th>
			<td><input type="text" name="c51"  size="10" /></td>
			<td><input type="text" name="c52"  size="10" /></td>
			<td><input type="text" name="c53" size="10"  /></td>
			<td><input type="text" name="c54"  size="10" /></td>
			<td><input type="text" name="c55"  size="10" /></td>
			<td><input type="text" name="c56"  size="10" /></td>
			<td><input type="text" name="c57" size="10"  /></td>
			<td><input type="text" name="c58"  size="10" /></td>
		</tr>
		<tr>
			<th><input type="text" name="c6"  size="10" /></th>
			<td><input type="text" name="c61"  size="10" /></td>
			<td><input type="text" name="c62"  size="10" /></td>
			<td><input type="text" name="c63" size="10"  /></td>
			<td><input type="text" name="c64"  size="10" /></td>
			<td><input type="text" name="c65"  size="10" /></td>
			<td><input type="text" name="c66"  size="10" /></td>
			<td><input type="text" name="c67" size="10"  /></td>
			<td><input type="text" name="c68"  size="10" /></td>
		</tr>
	</table>
	


	<p>
	<input type="submit" name="save" value="<?php echo $this->t('createnew'); ?>" />

</form>
	

			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>