define(function(require) {

	var
		DiscoJuice = require('lib/discojuice'),
		$ = require('jquery')
		;


	// console.log("Loading DiscoJuice", DiscoJuice);

	var loaded = false;

	window.DiscoJuice = DiscoJuice;

	var DJ = {
		"load": function() {
			if (loaded) return;
			loaded = true;

			$.getJSON('/api/discojuice', function(dj) {


				// console.log("Loaded DJ config", dj);

				var djc = DiscoJuice.Hosted.getConfig(
					dj.title, dj.entityid, dj.responseurl, dj.feeds, dj.returnurl
				);

				djc.overlay = true;
				// djc.always = true;
				djc.disco.subIDstores = dj.subIDstores;
				djc.disco.subIDwritableStores =  dj.subIDwritableStores;

				djc.metadata.push(dj.extrafeed);

				djc.callback = function(e) {
					// console.log(e);

					var auth = e.auth || null;
					var returnto = window.location.href || dj.baseURL + '/';
					switch(auth) {

						case 'twitter':
							window.location = dj.baseURL + '/simplesaml/module.php/core/as_login.php?AuthId=twitter&ReturnTo=' + escape(returnto);
						break;


						case 'saml':
						default:
							window.location = dj.baseURL + '/simplesaml/module.php/core/as_login.php?AuthId=saml&ReturnTo=' + escape(returnto) + '&saml:idp=' + escape(e.entityID);
						break;							

					}
				}
				$(document).ready(function() {
					$(".signin").DiscoJuice(djc);
				});

			});

			

		}
	};

	return DJ;

});