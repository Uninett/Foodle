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


<?php


if (isset($this->data['loginurl'])) {
	// echo '<a class="button signin" style="float: right" href="' . htmlentities($this->data['loginurl']) . '"><span>' . $this->t('login') . '</span></a>';

	echo '<button type="button" class="signin btn btn-default uninett-login-btn btn-lg" data-toggle="modal" data-target="#myModal">' . 
		'<span class="glyphicon glyphicon-user uninett-fontColor-red"></span> ' . $this->t('login') . '</button>';

}


?>

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

					</p>
				</div>


			</div>

		</div>

		<div class=" col-md-6" >

			<div class="uninett-color-darkBlue uninett-pattern1 uninett-fontColor-white uninett-padded gutter" style="">

					
				<h2>The New Foodle</h2>

				<p>Welcome to a brand new implementation of Foodle. We ask you to kindly report any issues you have with the new version as soon as possible, to help us get rid of bugs.</p>

				<p>There is a few lacking features that we hope to <strong>reintroduce</strong> as soon as possible, but are need to wait a bit because of lack of resources. This includes multi-lingual support, 
					and the support for having individual timeslots that differs from the date candidates. Contact us about how important these featutes are, in order for us to better prioritize.</p>

				<ul class="uninett-ul">
					<li class="uninett-ul-li"><a style="color: #fce" href="mailto:andreas.solberg@uninett.no">Please let us know</a> what you think.</li>
					<li class="uninett-ul-li"><a style="color: #fce" target="_blank" href="https://github.com/UNINETT/Foodle/issues">Please report any issues</a> as soon as you encounter them. You may also provide feature requests.</li>
				</ul>



			</div>

			<div class="showIfAuthenticated uninett-color-white  gutter" style="display: none">
				<div id="activities" class="list-group">
				</div>
			</div>

		</div>

	</div>




</div><!-- end container -->

			
<?php $this->includeAtTemplateBase('includes/footer.php'); ?>