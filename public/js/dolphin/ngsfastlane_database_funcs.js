/*
 *Author: Nicholas Merowsky
 *Date: 07 May 2015
 *Ascription:
 */

var bad_samples = [];

/*
 *	Checks fastlane input
 *	Returns database_checks on error
 *	or true_sample_ids on success
 *	
 */
function checkFastlaneInput(info_array){
	//	Declare variables
	var barcode_array = [];
	var input_array = [];
	var database_checks = [];
	var sample_ids = [];
	var sample_file_check = [];
	var true_sample_ids = [];
	
	//	Non-database checks
	//	For each input passed
	console.log(info_array);
	console.log(id_array);
	for(var x = 0; x < (id_array.length); x ++){
		//	Left a field blank that is not barcode definitions or amazon_bucket
		if (info_array[x] == '' && id_array[x] != 'amazon_bucket' && id_array[x] != 'Barcode Definitions') {
			database_checks.push(false);
		//	Check Barcode Separation if selected yes for barcode separation
		}else if (id_array[x] == 'barcode_sep' && info_array[x] == 'yes') {
			//	Split barcode submissioned on new lines
			var split_barcodes = info_array[id_array.length - 3].split('\n');
			//	remove blank new lines
			split_barcodes = split_barcodes.filter(function(n){return n != ''});
			var split_check = true;
			for (var y = 0; y < split_barcodes.length; y++) {
				//	If proper characters are not being used
				if (!/^[a-zA-Z0-9\_\.\-\s\t\,]*$/.test(split_barcodes[y])) {
					split_check = false;
				}else{
					var single_barcode_array = split_barcodes[y].split(/[\s\t\,]+/);
					single_barcode_array = single_barcode_array.filter(function(n){return n != ''});
					//	Check for proper barcode input length
					if (single_barcode_array.length != 2) {
						//	Not proper Barcode input
						split_check = false;
					}else{
						barcode_array.push(single_barcode_array);
					}
				}
			}
			//	If a barcode error exists
			if (split_check) {
				database_checks.push(true);
			}else{
				database_checks.push(false);
			}
		//	Input File Checks
		}else if (id_array[x] == 'input_files'){
			//	Paired-end libraries
			var bad_files = [];
			var split_inputs = info_array[6].split('\n');
			//	Check for blank lines and eliminate them
			split_inputs = split_inputs.filter(function(n){return n != ''});
			var input_bool_check = true;
			for (var y = 0; y < split_inputs.length; y++) {
				//	Check for proper characters
				if (!/^[a-zA-Z 0-9\_\.\-\s\t\,]*$/.test(split_inputs[y])) {
					bad_files.push("<font color=\"black\">incorrect file formatting: </font>" + split_inputs[y]);
					input_bool_check = false;
				}else{
					var single_input_array = split_inputs[y].split(/[\s\t\,]+/);
					single_input_array = single_input_array.filter(function(n){return n != ''});
					//	if barcode names specified
					if (info_array[1] == 'yes') {
						if (single_input_array.length != 2  && info_array[2] == 'yes') {
							//	Not proper file input (paired end)
							input_bool_check = false;
						}else if (single_input_array.length != 1  && info_array[2] == 'no') {
							//	Not proper file input (single end)
							input_bool_check = false;
						}else{
							single_input_array.unshift(barcode_array[y][0]);
							input_array.push(single_input_array);
						}
					}else{
						if (single_input_array.length != 3  && info_array[2] == 'yes') {
							//	Not proper file input (paired end)
							input_bool_check = false;
						}else if (single_input_array.length != 2  && info_array[2] == 'no') {
							//	Not proper file input (single end)
							input_bool_check = false;
						}else{
							input_array.push(single_input_array);
						}
					}
				}
			}
			//	If character checks passed, check file perms
			if (input_bool_check) {
				var file_check = '';
				//	Create the file check list
				for(var z = 0; z < input_array.length; z++){
					for(var y = input_array[z].length - 1; y > 0; y--){
						if (file_check == '') {
							file_check = info_array[x-1]+"/"+input_array[z][y]
						}else{
							file_check += ',' + info_array[x-1]+"/"+input_array[z][y]
						}
						
					}
				}
				//	use file check list and check permissions
				console.log(file_check)
				$.ajax({
					type: 	'GET',
					url: 	BASE_PATH+'/public/api/service.php?func=checkFile&username='+username.clusteruser+'&file='+file_check,
					async:	false,
					success: function(s)
					{
						var file_check = JSON.parse(s);
						console.log(file_check);
						if (file_check.Result != 'Ok' ){
							input_bool_check = false;
							bad_files.push("One or more of your files either doesn't exist or you don't have permissions for.  Please make sure the correct files/permissions are given.");
						}
					}
				});
			}
			sendProcessData(bad_files, 'bad_files');
			database_checks.push(input_bool_check);
		//	Directory Checks
		}else if (id_array[x] == 'input_dir' || id_array[x] == 'backup_dir'){
			//	Check inputs that should not contain whitespace
			if ((!/^[a-zA-Z0-9\_\.\/\-]*$/.test(info_array[x])) || info_array[x].indexOf("/") != 0) {
				//	Contains whitespace
				database_checks.push(false);
			}else{
				//	Directory Checks
				var dir_check_1;
				$.ajax({
					type: 	'GET',
					url: 	BASE_PATH+'/public/api/service.php?func=checkPermissions&username='+username.clusteruser,
					async:	false,
					success: function(s)
					{
						console.log(s);
						dir_check_1 = JSON.parse(s);
					}
				});
				var dir_check_2;
				//	Check if root path was not specified
				if (info_array[x].substring(0,1) != '/'  && info_array[x].indexOf('/') > -1) {
					info_array[x] = '/' + info_array[x];
				}
				//	Input directory does not require write permissions, skip this step if id_array[x] is the input directory
				if (id_array[x] == 'input_dir') {
					dir_check_2 = JSON.parse('{"Result":"Ok"}');
				}else{
					console.log(info_array[x]);
					$.ajax({
						type: 	'GET',
						url: 	BASE_PATH+'/public/api/service.php?func=checkPermissions&username='+username.clusteruser+'&outdir=' + info_array[x],
						async:	false,
						success: function(s)
						{
							console.log(s);
							dir_check_2 = JSON.parse(s);
						}
					});
				}
				
				if (dir_check_1.Result != 'Ok' || dir_check_2.Result != 'Ok') {
					//	perms errors
					database_checks.push(false);
				}else{
					//	No errors
					database_checks.push(true);
				}
			}
		//	Series and import character checks
		}else if(id_array[x] == 'series_name' || id_array[x] == 'lane_name'){
			if (/^[a-zA-Z 0-9\_\-\s]*$/.test(info_array[x])) {
				database_checks.push(true);
			}else{
				database_checks.push(false);
			}
		}else{
			//	No errors
			database_checks.push(true);
		}
	}
	
	//	Database checks
	//	Experiment Series
	var experiment_series_id = experimentSeriesCheck(info_array[3]);

	//	Experiments (Lanes)
	if (experiment_series_id > 0) {
		var lane_id = laneCheck(experiment_series_id, info_array[4]);
	}

	//	Samples
	if (experiment_series_id > 0 && lane_id > 0) {
		if (info_array[1] == 'yes') {
			for(z in barcode_array){
				if(sampleCheck(experiment_series_id, lane_id, barcode_array[z][0]) != 0){
					bad_samples.push(barcode_array[z][0]);
				}
			}
		}else{
			for(z in input_array){
				if(sampleCheck(experiment_series_id, lane_id, input_array[z][0]) != 0){
					bad_samples.push(input_array[z][0]);
				}
			}
		}
		if (bad_samples.length > 0) {
			database_checks.push(false);
		}else{
			database_checks.push(true);
		}
	}else{
		database_checks.push(true);
	}
	
	//	Directories
		//	Make sure directories are real?
	//	Files
		//	Make sure files are real?
	
	if (database_checks.indexOf(false) > -1) {
		//	Error in submission, do not submit into database
		return database_checks;
	}else{
		//	Obtain group id
		var gid = document.getElementById('groups').value.toString();
		var perms = obtainPermsFromRadio();
		if (experiment_series_id > 0) {
			//	If adding to a experiment series
			if (lane_id > 0) {
				//	If adding to a lane
				if (info_array[1] == 'yes') {
					//	If seperating barcodes
					for (var a = 0; a < barcode_array.length; a++) {
						if (sample_file_check.indexOf(barcode_array[a][0]) == -1) {
							var true_id = insertSample(experiment_series_id, lane_id, barcode_array[a][0],
										barcode_array[a][1], gid, perms);
							true_sample_ids.push(true_id);
							sample_ids.push(true_id);
							sample_file_check.push(barcode_array[a][0]);
						}else{
							sample_ids.push(sampleCheck(experiment_series_id, lane_id, barcode_array[a][0]));
						}
					}
				}else{
					//	If not separating barcodes
					for (var a = 0; a < input_array.length; a++) {
						if (sample_file_check.indexOf(input_array[a][0]) == -1) {
							var true_id = insertSample(experiment_series_id, lane_id, input_array[a][0],
									'nobarcode', gid, perms);
							true_sample_ids.push(true_id);
							sample_ids.push(true_id);
							sample_file_check.push(input_array[a][0]);
						}else{
							sample_ids.push(sampleCheck(experiment_series_id, lane_id, input_array[a][0]));
						}
					}
				}
			}else{
				//	If creating a lane
				insertLane(experiment_series_id, info_array[4], gid, perms);
				var lane_id = laneCheck(experiment_series_id, info_array[4]);
				if (info_array[1] == 'yes') {
					//	If separating barcodes
					for (var a = 0; a < barcode_array.length; a++) {
						if (sample_file_check.indexOf(barcode_array[a][0]) == -1) {
							var true_id = insertSample(experiment_series_id, lane_id, barcode_array[a][0],
										barcode_array[a][1], gid, perms);
							true_sample_ids.push(true_id);
							sample_ids.push(true_id);
							sample_file_check.push(barcode_array[a][0]);
						}else{
							sample_ids.push(sampleCheck(experiment_series_id, lane_id, barcode_array[a][0]));
						}
					}
				}else{
					//	If not separating barcodes
					for (var a = 0; a < input_array.length; a++) {
						if (sample_file_check.indexOf(input_array[a][0]) == -1) {
							var true_id = insertSample(experiment_series_id, lane_id, input_array[a][0],
									'nobarcode', gid, perms);
						true_sample_ids.push(true_id);
						sample_ids.push(true_id);
							sample_file_check.push(input_array[a][0]);
						}else{
							sample_ids.push(sampleCheck(experiment_series_id, lane_id, input_array[a][0]));
						}
					}
				}
			}
		}else{
			//	If creating an experiment series
			insertExperimentSeries(info_array[3], gid, perms);
			experiment_series_id = experimentSeriesCheck(info_array[3]);
			
			insertLane(experiment_series_id, info_array[4], gid, perms);
			var lane_id = laneCheck(experiment_series_id, info_array[4]);
			
			if (info_array[1] == 'yes') {
				//	If separating barcodes
				for (var a = 0; a < barcode_array.length; a++) {
					if (sample_file_check.indexOf(barcode_array[a][0]) == -1) {
						var true_id = insertSample(experiment_series_id, lane_id, barcode_array[a][0], barcode_array[a][1], gid, perms);
							true_sample_ids.push(true_id);
							sample_ids.push(true_id);
						sample_file_check.push(barcode_array[a][0]);
						
					}else{
						sample_ids.push(sampleCheck(experiment_series_id, lane_id, barcode_array[a][0]));
					}
				}
			}else{
				//	If not separating barcodes
				for (var a = 0; a < input_array.length; a++) {
					if (sample_file_check.indexOf(input_array[a][0]) == -1) {
						var true_id = insertSample(experiment_series_id, lane_id, input_array[a][0],
									'nobarcode', gid, perms);
						true_sample_ids.push(true_id);
						sample_ids.push(true_id);
						sample_file_check.push(input_array[a][0]);
					}else{
						sample_ids.push(sampleCheck(experiment_series_id, lane_id, input_array[a][0]));
					}
				}
			}
		}
		
		//	Insert directory information
		insertDirectories(info_array[5], info_array[7], info_array[8], gid, perms);
		input_directory_id = directoryCheck(info_array[5], info_array[7], info_array[8]);
		
		//	Insert temp files
		for(var g = 0; g < input_array.length; g++){
			if (info_array[1] == 'yes') {
				//	If Barcode sep
				if (input_array[g].length == 2) {
					insertTempLaneFiles(input_array[g][1], lane_id, input_directory_id, gid, perms);
				}else{
					var combined_files = input_array[g][1] + "," + input_array[g][2];
					insertTempLaneFiles(combined_files, lane_id, input_directory_id, gid, perms);
				}
			}else{
				//	If no Barcode sep
				if (input_array[g].length == 2) {
					insertTempSampleFiles(input_array[g][1], sample_ids[g], input_directory_id, gid, perms);
				}else{
					var combined_files = input_array[g][1] + "," + input_array[g][2];
					insertTempSampleFiles(combined_files, sample_ids[g], input_directory_id, gid, perms);
				}
			}
		}
		console.log(experiment_series_id);
		console.log(lane_id);
		console.log(barcode_array);
		console.log(input_array);
		console.log(gid);
		console.log(perms);
		return true_sample_ids;
	}
}

function obtainGroupFromName(name){
	var group_id;
	$.ajax({
			type: 	'GET',
			url: 	BASE_PATH+'/public/ajax/ngsfastlanedb.php',
			data:  	{ p: 'obtainGroupFromName', name: name },
			async:	false,
			success: function(s)
			{
				group_id = s;
			}
	});
	return group_id;
}

function obtainPermsFromRadio(){
	for(var x = 0; x <  document.getElementsByTagName('input').length; x++){
		var option = document.getElementsByTagName('input')[x];
		if(option.checked == true){
			return option.value;
		}
	}
	return "32";
}

function insertExperimentSeries(experiment_name, gid, perms){
	if (gid == "" || gid == "0") {
		gid = "1";
	}
	$.ajax({
			type: 	'POST',
			url: 	BASE_PATH+'/public/ajax/ngsfastlanedb.php',
			data:  	{ p: 'insertExperimentSeries', name: experiment_name, gids: gid, perms: perms },
			async:	false,
			success: function(s)
			{
			}
	});
}

function insertLane(experiment_id, lane_name, gid, perms){
	if (gid == "" || gid == "0") {
		gid = "1";
	}
	$.ajax({
			type: 	'POST',
			url: 	BASE_PATH+'/public/ajax/ngsfastlanedb.php',
			data:  	{ p: 'insertLane', experiment: experiment_id, lane: lane_name, gids: gid, perms: perms },
			async:	false,
			success: function(s)
			{
			}
	});
}

function insertSample(experiment_id, lane_id, sample_name, barcode, gid, perms){
	var id;
	if (gid == "" || gid == "0") {
		gid = "1";
	}
	$.ajax({
			type: 	'GET',
			url: 	BASE_PATH+'/public/ajax/ngsfastlanedb.php',
			data:  	{ p: 'insertSample', experiment: experiment_id, lane: lane_id, sample: sample_name,
					barcode: barcode, gids: gid, perms: perms },
			async:	false,
			success: function(s)
			{
			}
	});
	id = sampleCheck(experiment_id, lane_id, sample_name);
	return id;
}

function insertDirectories(input, backup, amazon, gid, perms){
	if (gid == "" || gid == "0") {
		gid = "1";
	}
	$.ajax({
			type: 	'POST',
			url: 	BASE_PATH+'/public/ajax/ngsfastlanedb.php',
			data:  	{ p: 'insertDirectories', input: input, backup: backup, amazon: amazon, gids: gid, perms: perms },
			async:	false,
			success: function(s)
			{
			}
	});
}

function insertTempSampleFiles(filename, sample_id, input_directory_id, gid, perms){
	if (gid == "" || gid == "0") {
		gid = "1";
	}
	$.ajax({
			type: 	'POST',
			url: 	BASE_PATH+'/public/ajax/ngsfastlanedb.php',
			data:  	{ p: 'insertTempSample', filename: filename, sample_id: sample_id, input: input_directory_id, gids: gid, perms: perms },
			async:	false,
			success: function(s)
			{
			}
	});
}

function insertTempLaneFiles(file_name, lane_id, dir_id, gid, perms){
	if (gid == "" || gid == "0") {
		gid = "1";
	}
	$.ajax({
			type: 	'POST',
			url: 	BASE_PATH+'/public/ajax/ngsfastlanedb.php',
			data:  	{ p: 'insertTempLane', file_name: file_name, lane_id: lane_id, dir_id: dir_id, gids: gid, perms: perms },
			async:	false,
			success: function(s)
			{
			}
	});
}

function experimentSeriesCheck(experiment_name){
	var id;
	$.ajax({
			type: 	'GET',
			url: 	BASE_PATH+'/public/ajax/ngsfastlanedb.php',
			data:  	{ p: 'experimentSeriesCheck', name: experiment_name },
			async:	false,
			success: function(s)
			{
				id = s;
			}
	});
	return id;
}

function laneCheck(experiment_id, lane_name){
	var id;
	$.ajax({
			type: 	'GET',
			url: 	BASE_PATH+'/public/ajax/ngsfastlanedb.php',
			data:  	{ p: 'laneCheck', experiment: experiment_id, lane: lane_name },
			async:	false,
			success: function(s)
			{
				id = s;
			}
	});
	return id;
}

function sampleCheck(experiment_id, lane_id, sample_name){
	var id;
	$.ajax({
			type: 	'GET',
			url: 	BASE_PATH+'/public/ajax/ngsfastlanedb.php',
			data:  	{ p: 'sampleCheck', experiment: experiment_id, lane: lane_id, sample: sample_name },
			async:	false,
			success: function(s)
			{
				id = s;
			}
	});
	return id;
}

function directoryCheck(input, backup, amazon){
	var id;
	$.ajax({
			type: 	'GET',
			url: 	BASE_PATH+'/public/ajax/ngsfastlanedb.php',
			data:  	{ p: 'directoryCheck', input: input, backup: backup, amazon: amazon},
			async:	false,
			success: function(s)
			{
				id = s;
			}
	});
	return id;
}

function sendProcessData(info_array, post_name){
	$.ajax({
			type: 	'GET',
			url: 	BASE_PATH+'/public/ajax/ngsfastlanedb.php',
			data:  	{ p: 'sendProcessData', info_array: info_array, post: post_name},
			async:	false,
			success: function(s)
			{
			}
	});
}

function getBadSamples(){
	return bad_samples;
}

