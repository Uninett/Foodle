<?php 


if (!empty($this->data['userToken'])) {
 // 	$this->data['head'] = '

	// <script type="text/javascript" src="/res/js/foodle-data.js"></script>
	// <script type="text/javascript" src="/res/js/foodle-front.js"></script>
	

	// 	<script type="text/javascript" charset="utf-8">
	// 		$(document).ready(function() {
	// 			Foodle_Front_View();
	// 		});
	// 	</script>
 // 	';

}

 	
	$this->includeAtTemplateBase('includes/header.php'); 

?>



<div class="container">



	<div class="row">
		
		<div class="col-md-6">
			<div class="jumbotron uninett-color-lightBlue">
				<h2><?php echo $this->t('welcomeheader'); ?></h2>
				<p><?php echo $this->t('welcometext'); ?></p>
				<div id="createnew" style="">
					<a href="/create" class="btn btn-lg btn-primary"><span class="glyphicon glyphicon-pencil"></span> <?php echo $this->t('createnew'); ?></a>
				</div>
			</div>


			<div class="uninett-color-darkBlue uninett-fontColor-white uninett-padded gutter">

			<div class="showIfAuthenticated">
				
				<h2>Upcoming</h2>

				<div class="uninett-color-white  gutter">
					<div id="upcoming" class="list-group">
					</div>
				</div>

				<div id="upcoming2"></div>

				<p id="upcomingb">
					<a class="uninett-fontColor-grey" href="<?php echo htmlspecialchars($this->data['calendarurl']); ?> ">
						<span class="glyphicon glyphicon-calendar"></span> Subscribe calendar
					</a>
				</p>
			</div>


			<?php
			
				if ($this->data['authenticated']) {			
					
					echo '';
					
					echo '';
				}
			?>

			</div>

		</div>

		<div class="col-md-6">

			<div class="uninett-color-white  gutter">
				<div id="activities" class="list-group">
				</div>
			</div>



		</div>

	</div>







	

			








</div><!-- end container -->

			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>