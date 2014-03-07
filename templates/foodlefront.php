<?php 

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


			<div class="showIfAuthenticated uninett-color-darkBlue uninett-fontColor-white uninett-padded gutter" style="display: none">

				<div class="">
					
					<h2>Upcoming</h2>

					<div class="uninett-color-white  gutter">
						<div id="upcoming" class="list-group">
						</div>
					</div>

					<div id="upcoming2"></div>

					<p id="upcomingb">
					<!--
						<a class="uninett-fontColor-grey" href="<?php echo htmlspecialchars($this->data['calendarurl']); ?> ">
							<span class="glyphicon glyphicon-calendar"></span> Subscribe calendar
						</a>
						-->
					</p>
				</div>


			</div>

		</div>

		<div class=" col-md-6" >

			<div class="showIfAuthenticated uninett-color-white  gutter" style="display: none">
				<div id="activities" class="list-group">
				</div>
			</div>

		</div>

	</div>




</div><!-- end container -->

			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>