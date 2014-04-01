<?php 
	$this->includeAtTemplateBase('includes/header.php'); 

?>


<div class="container gutter">
	<div class="row">
		<div class="col-lg-12"> 
			<div class="jumbotron uninett-color-orange">
				<h1>Error</h1>
				<p><?php echo $this->data['message']; ?></p>

				<p> <a class="btn btn-lg btn-default" href="/" role="button">Go to frontpage &raquo;</a> </p>
			</div>
		</div>
	</div>
</div>

	

		
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>