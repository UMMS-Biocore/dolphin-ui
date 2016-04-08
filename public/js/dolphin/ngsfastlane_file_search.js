
var username;

//	Function called when 'Search Directory' button is clicked
function queryDirectory() {
	//	Grab directory
	var directory = document.getElementById('input_dir').value;
	//	If not empty, search directory
	if (directory != '') {
		directoryGrab(directory);
		
		//	After successful query
		document.getElementById('Directory_toggle').setAttribute('data-toggle', 'tab');
		$('.nav-tabs a[href="#Directory"]').tab('show')
	}
}

//	Function called to search the directory
function directoryGrab(directory){
	var file_array = [];
	$.ajax({
		type: 	'GET',
		url: 	BASE_PATH+'/public/api/service.php?func=directoryContents&directory='+directory+'&username='+username.clusteruser,
		async:	false,
		success: function(s)
		{
			console.log(s);
			file_array = s.split('\n');
			console.log(file_array);
			file_array.pop();
			console.log(file_array);
		}
	});
	return file_array;
}

//	On Open of fastlane
$(function() {
	"use strict";
	//	Disable directory tab
	document.getElementById('Directory_toggle').setAttribute('data-toggle', 'none');
	
	//	Get cluster username for file checks
	$.ajax({
		type: 	'GET',
		url: 	BASE_PATH+'/public/ajax/ngsfastlanedb.php',
		data:  	{ p: 'getClusterName' },
		async:	false,
		success: function(s)
		{
			username = s[0];
		}
	});
});