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

				<p>Welcome to a brand new implementation of Foodle! We kindly ask you to
report any issues you might have concerning this new edition as soon as
possible, in order to help us get rid of bugs.
</p>

				<p>Due to a lack of resources, some features have not been included as part
of this release. We hope to reintroduce them as soon as possible. These
features include multi lingual support, as well as support for
individual timeslots that differ from the date candidates.
Please tell us how important these specific features are to you, in
order for us to prioritize among them.</p>

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