<?php 
	$this->includeAtTemplateBase('includes/header.php'); 

?>


<div id="content">

	<h2>Error</h2>


	<p><?php echo $this->data['message']; ?></p>
		

	<div style="border: 1px solid #ccc; padding: 2em; margin: 0.5em;">

	<script type="text/javascript" charset="utf-8">
	  var is_ssl = ("https:" == document.location.protocol);
	  var asset_host = is_ssl ? "https://s3.amazonaws.com/getsatisfaction.com/" : "http://s3.amazonaws.com/getsatisfaction.com/";
	  document.write(unescape("%3Cscript src='" + asset_host + "javascripts/feedback-v2.js' type='text/javascript'%3E%3C/script%3E"));
	</script>

	<script type="text/javascript" charset="utf-8">
	  var feedback_widget_options = {};

	  feedback_widget_options.display = "inline";  
	  feedback_widget_options.company = "uninett";


	  feedback_widget_options.style = "problem";








	  var feedback_widget = new GSFN.feedback_widget(feedback_widget_options);
	</script>


	</div>
		
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>