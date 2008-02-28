<?php 
	$this->includeAtTemplateBase('includes/header.php'); 


?>

<form method="post" action="index.php">

	<h1>Your foodle is now ready</h1>


	<p>Name: <?php if (isset($this->data['name'])) echo $this->data['name']; ?>

	<p>Description: <?php
	if (isset($this->data['descr'])) echo $this->data['descr'];
	?>

	
	<p><a href="<?php echo $this->data['url']; ?>">Visit this link your self</a>, 
		and send it to the people you want to foodle with:<br />
	
	<input type="text" size="50" name="foodleid" value="<?php echo $this->data['url']; ?>" />
	
	
	
			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>