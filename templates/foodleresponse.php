<?php 

// $headbar = '<a class="button" style="float: right; " 
// 		title="Comma separated file, works with Excel." href="/foodle/' . $this->data['foodle']->identifier . '?output=csv">
// 	<span><!-- <img src="resources/spreadsheet.png" /> -->' . 
// 		$this->t('open_in_spreadsheet') . '</span></a>';


// //$headbar .= '<a class="button" onclick="alert(\'foo\') && return false" style="float: right" ><span>' . $this->t('refresh') . '</span></a>';

// $this->data['headbar'] = $headbar;

// // $this->data['head'] = '<script type="text/javascript" src="/res/js/foodle-contacts-api.js"></script>';
// // $this->data['head'] = '<script type="text/javascript" src="/res/js/foodle-response.js"></script>';

// if (!empty($this->data['showsharing'])) {
// 	if (empty($this->data['head'])) $this->data['head'] = '';
// 	$this->data['head'] .= '<script type="text/javascript" src="/res/js/foodle-data.js"></script>';
// 	$this->data['head'] .= '<script type="text/javascript" src="/res/js/foodle-invite.js"></script>
// 	<script type="text/javascript">
// 		$(document).ready(function() {
// 			Foodle_Invitation_View("' . htmlspecialchars($this->data['foodle']->identifier) . '");
// 		});
// 	</script>
	
// 	';
// }

$this->includeAtTemplateBase('includes/header.php'); 
?>




<div class="container">
	<div class="row">
		<div class="col-lg-12 uninett-color-white uninett-padded"> 

			<div id="foodleResponse"  data-foodleid="<?php echo htmlentities($this->data['foodleid']); ?>"></div>

		</div>
	</div>
</div>



			
<?php $this->includeAtTemplateBase('includes/footer.php'); 
























