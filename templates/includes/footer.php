
</div>    <!-- /#content -->

<div id="footer">
	<?php
	
	if ($this->data['user']) {
	
		if (isset($this->data['user']->userid)) {
			echo $this->t('authtext', 
				array(
					'%DISPLAYNAME%' => $this->data['user']->name, 
					'%USERID%' => $this->data['user']->userid
				) 
			); 
		}
		
		if ($this->data['user']->hasCalendar()) {
			echo  '. <img style="" alt="Calendar" title="Calendar" class="" src="/res/calendar-export.png" /> ' .
				$this->t('youhavecalendar');
		}
		
	} else {
		echo($this->t('is_anonymous'));
	}




	
	?>

	<!-- <br /><?php echo $this->t('visit'); ?> <a href="http://rnd.feide.no">rnd.feide.no</a> -->
</div><!-- /#footer -->



<script src="https://ssl.google-analytics.com/urchin.js" type="text/javascript"></script>
<script type="text/javascript">
_uacct = "UA-431110-13";
urchinTracker();
</script>

</body>
</html>
