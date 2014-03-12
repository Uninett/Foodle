define(function(require, exports) {
	var 
		$ = require('jquery');

	$(document).ready(function() {

		console.log("Checking browser");

		var suported = true;
		var text = '';

		if (($.browser.msie && $.browser.version.substr(0,1) == '6')
		    || ($.browser.mozilla && $.browser.version.substr(0,1) != '3')) {
		        supported = false;
		    	text '';
		}


		var str = '<div class="container"><p style="background: #c44; color: white; padding: 1em; margin-top: 1em; border-radius: 10px" class="bg-danger">' + 
			'<strong>Warning: Unsupported browser.</strong></p>' +
			'<p>' + text + '</p>' +
			'</div>';

		if (!supported) {
			$('body').prepend(str);		
		}

		

	});



});