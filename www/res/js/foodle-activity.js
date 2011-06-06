

var Foodle_Frontpage_View = function() {

	Foodle_Group.setGroupid(groupid);
	Foodle_Group.refresh(showResults);
	


	/*
	 * Show a list of files in the <div class="filelist"> container.
	 */
	function showResults(files) {
		$("div.filelist").empty();	
		for(i = 0; i < files.length; i++) {
			$("div.filelist").append(Foodle_Group_Utils.fileHTML(files[i]));
		}
	}

};


function updateActivityList(data) {
	
	console.log('Got data');
	console.log(data);
	
	var 
		len = data.length,
		i;
	
	$("div#activity").empty();
	for(i = 0; i < len; i++) {
		$("div#activity").append(activityHTML(data[i]));	
	}
	
}


