<?php 
	$this->includeAtTemplateBase('includes/header.php'); 

?>




<div class="columned">

<h1>Support</h1>

<?php

if ($this->data['authenticated']) {
	echo $this->data['getsatisfactionscript'];
} else {
	echo '<p style="border: 1px solid #eee; margin: 1em; padding: 1em">Please <a href="' . $this->data['loginurl'] . '">login first</a>, to enable the Single Sign-On to our partner site for user feedback getsatisfaction.com.</p>';
}

?>


<script type="text/javascript" charset="utf-8">
  var is_ssl = ("https:" == document.location.protocol);
  var asset_host = is_ssl ? "https://s3.amazonaws.com/getsatisfaction.com/" : "http://s3.amazonaws.com/getsatisfaction.com/";
  document.write(unescape("%3Cscript src='" + asset_host + "javascripts/feedback-v2.js' type='text/javascript'%3E%3C/script%3E"));
</script>

<script type="text/javascript" charset="utf-8">
  var feedback_widget_options = {};

  feedback_widget_options.display = "inline";  
  feedback_widget_options.company = "ecampus";
  
  
  feedback_widget_options.style = "question";
  feedback_widget_options.product = "ecampus_foodle";
  
  feedback_widget_options.limit = "5";
  
  GSFN.feedback_widget.prototype.local_base_url = "http://tjenester.ecampus.no";
  GSFN.feedback_widget.prototype.local_ssl_base_url = "http://tjenester.ecampus.no";
  

  var feedback_widget = new GSFN.feedback_widget(feedback_widget_options);
</script>



</div>




			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>