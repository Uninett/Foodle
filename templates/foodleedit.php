<?php 
	$this->includeAtTemplateBase('includes/header.php'); 

?>

<form method="post" action="edit.php">

	<h1><?php echo $this->t('editfoodle'); ?></h1>

	<p><?php echo $this->t('foodleid'); ?>: [<tt><?php echo $this->data['identifier']; ?></tt>]
	</p>

	<input type="hidden" name="id" value="<?php echo $this->data['identifier']; ?>" />

	<p><?php echo $this->t('name'); ?>: 
	<input type="text" name="name" value="<?php
	if (isset($this->data['header'])) echo $this->data['header'];
	?>"/>

	<p><?php echo $this->t('description'); ?>: <br />
	<textarea style="width: 400px; height: 100px" name="descr"><?php
	if (isset($this->data['descr'])) echo $this->data['descr'];
	?></textarea><br />
	<?php echo $this->t('htmlinfo'); ?>
	
	<p><?php echo $this->t('expire'); ?>: 
	<input type="text" name="expire" value="<?php
	if (isset($this->data['expiretextfield'])) echo $this->data['expiretextfield'];
	?>"/><br />
	<?php echo $this->t('format'); ?>: YYYY-MM-DD HH:MM
	


	<p>
	<input type="submit" name="save" value="Update foodle" />
	
	<p>[ <a href="foodle.php?id=<?php echo $this->data['identifier']; ?>">Go to this foodle</a> ]

</form>
	

			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>