(function( $ ){
 
 	var 
 		progress = null,
 		callback = null;
 
 	var callbackComplete = function( pbar) {
		return function(ev) {
			console.log('Upload complete');
			pbar.progressbar('value', 100);
			if (callback) callback();
			$(pbar).hide().prev().hide();
		};
 	};

 	var callbackProgress = function( pbar) {
		return function(ev) {
			console.log('Progress ' + ev.loaded + '/' + ev.total);
			if (ev.lengthComputable) {
				pbar.progressbar('value', (ev.loaded / ev.total) * 100);
			}
		};
 	};

 
 	var methods = {
	
		init : function( options ) {
	
			if(options.progress) {
				progress = options.progress;
			}

			if(options.callback) {
				callback = options.callback;
			}

	
			console.log('Init DND Uploader 1');
			console.log(this);
	
			return this.each( function () {
			
				console.log('Init DND Uploader');
				
				var $this = $(this);
				
				$.each(options, function( label, setting ) {
					$this.data(label, setting);
				});
				
				$this.bind('dragenter', methods.dragEnter);
				$this.bind('dragover', methods.dragOver);
				$this.bind('drop', methods.drop);
			
			});
		},
		
		dragEnter : function ( event ) {    
			event.stopPropagation();
			event.preventDefault();
			
			return false;
		},
		
		dragOver : function ( event ) {      
			event.stopPropagation();
			event.preventDefault();
				  
			return false;
		},
	
		drop : function( event ) {    
		
			console.log('drop');
			
			event.stopPropagation();
			event.preventDefault();
			
			var $this = $(this);
			var dataTransfer = event.originalEvent.dataTransfer;
			var generatedCallback = null;
		  
			if (dataTransfer.files.length > 0) {
				console.log('One or more files dropped');
				$.each(dataTransfer.files, function ( i, file ) {
					var xhr    = new XMLHttpRequest();
					var upload = xhr.upload;
					
					if (progress) {
						// Setting up progressbar

//						var filebar = $(progress).append('<div class=""></div>').progressbar();
						var filebar = $('<div class=""></div>').progressbar().prependTo(progress);
						$(progress).prepend('<p>Uploading <tt>' + file.fileName + '</tt>.</p>');
						
						// generatedCallback = ;
						
						upload.addEventListener("load", callbackComplete(filebar), false);
						upload.addEventListener("progress", callbackProgress(filebar), false);
//						xhr.onprogress = generatedCallback;
					}

					//if (callbackProgress) upload.addEventListener("progress", callbackProgress, false);
					
					
// 					upload.addEventListener("progress", function (ev) {
// 						if (ev.lengthComputable) {
// 							$("#fileStatus").html((ev.loaded / ev.total) * 100 + "%");
// 						}
// 					}, false);
// 					upload.addEventListener("load", function (ev) {
// 							$("#fileStatus").html("Upload complete");
// 							Foodle_Group.getFilelist(currentList);
// 					}, false);
						
						
					console.log('Preparing ' + file.fileName + ' for upload');
					
					xhr.open($this.data('method') || 'POST', $this.data('url'), true);
					xhr.setRequestHeader('X-Filename', file.fileName);
					
					xhr.send(file);
				});
			};
			
			return false;
		}
	};
  
	$.fn.dndUploader = function( method ) {
		if ( methods[method] ) {
			return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
		} else if ( typeof method === 'object' || ! method ) {
			return methods.init.apply( this, arguments );
		} else {
			$.error( 'Method ' +  method + ' does not exist on jQuery.dndUploader' );
		}
	};	
	
})( jQuery );