
</div>    <!-- /#content -->



<div class="container" style="margin-top: 2em">


	<?php 

		if (isset($this->data['stats'])) {
			echo '<p>' . $this->t('cresponses', array('%NUM%' => $this->data['stats']['total7days']) ) . '</p>';
		}


	?>

		<hr style="margin-bottom: 5px; margin-top: 5px" class="uninett-hr-divider">

			<div class="col-lg-12">




				<div class="footer-uninett">

					<div class="footer-content-uninett">

							<div class="footer-logo"> <img src="/res/uninett-theme/images/Uninett_pil_rod.svg" alt="Uninett logo" type="image/svg+xml"></div>
							<div class="footer-uninett-department">UNINETT AS &copy; 2008-2014</div>
					</div>
					<div class="clearfix"></div>
				</div>




		</div>
		<div class="">
			<div class="col-lg-12">
				<p>&nbsp;</p>
			</div>
		</div>



</div>

<!--
<div id="footer">
	<?php
	
	if (!empty($this->data['authenticated'])) {
	
		if (isset($this->data['user']->userid)) {
			echo $this->t('authtext', 
				array(
					'%DISPLAYNAME%' => $this->data['user']->username, 
					'%USERID%' => $this->data['user']->userid
				) 
			); 
			
			if ($this->data['user']->hasCalendar()) {
				echo  '. <img style="" alt="Calendar" title="Calendar" class="" src="/res/calendar-export.png" /> ' .
					$this->t('youhavecalendar');
			}

			
		}
		
		
	} else {
		echo($this->t('is_anonymous'));
	}




	
	?>

</div>
-->


	<script src="https://ssl.google-analytics.com/urchin.js" type="text/javascript"></script>
	<script type="text/javascript">
	_uacct = "UA-431110-13";
	urchinTracker();
	</script>

	<!-- Bootstrap core JavaScript --> 
	<!-- Placed at the end of the document so the pages load faster --> 

<!--
	<script src="/res/uninett-theme-bootstrap/js/bootstrap.min.js"></script> 
	<script src="/res/uninett-theme-bootstrap/js/holder.js"></script>
-->


</body>
</html>