<?php 
	$this->includeAtTemplateBase('includes/header.php'); 


?>

<form method="post" action="index.php">

	<h1><?php echo $this->t('ready'); ?></h1>


	<p><?php echo $this->t('name'); ?>: <?php if (isset($this->data['name'])) echo $this->data['name']; ?>

	<p><?php echo $this->t('description'); ?>: <?php
	if (isset($this->data['descr'])) echo $this->data['descr'];
	?>

	
	<p><a href="<?php echo $this->data['url']; ?>"><?php echo $this->t('visitlink'); ?></a>.
		<?php echo $this->t('sendlink'); ?>:<br />
	
	<input type="text" size="50" name="foodleid" value="<?php echo $this->data['url']; ?>" />
	
	
	
			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>