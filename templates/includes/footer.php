
    <!-- wikipage stop -->
</div>

<div class="clearer">&nbsp;</div>

	<div class="stylefoot">

	<?php
	
	if ($this->data['authenticated']) {
	
		if (isset($this->data['userid'])) {
			echo $this->t('authtext', 
				array(
					'%DISPLAYNAME%' => $this->data['displayname'], 
					'%USERID%' => $this->data['userid']
				) 
			); 
		}
	} else {
		echo($this->t('is_anonymous'));
	}
	
	?>

	<br /><?php echo $this->t('visit'); ?> <a href="http://rnd.feide.no">rnd.feide.no</a>
	</div><!-- end stylefoot -->


</div><!-- dokuwiki -->

<script src="https://ssl.google-analytics.com/urchin.js" type="text/javascript"></script>

<script type="text/javascript">
_uacct = "UA-431110-13";
urchinTracker();
</script>




</body>
</html>
