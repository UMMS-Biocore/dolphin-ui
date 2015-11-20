/*
 *Author: Nicholas Merowsky
 *Date: 07 May 2015
 *Ascription:
 */

var bad_samples = [];

function checkFastlaneInput(info_array){
	//	Declair variables
	var barcode_array = [];
	var input_array = [];
	var database_checks = [];
	var sample_ids = [];
	var sample_file_check = [];
	var true_sample_ids = [];
	var username;
	
	$.ajax({
		type: 	'GET',
		url: 	BASE_PATH+'/public/ajax/ngsfastlanedb.php',
		data:  	{ p: 'getUserName' },
		async:	false,
		success: function(s)
		{
			username = s;
		}
	});
	
	//	Non-database checks
	//	For each input passed
	console.log(info_array);
	console.log(id_array);
	for(var x = 0; x < (id_array.length); x ++){
		if (info_array[x] == '' && id_array[x] != 'amazon_bucket' && id_array[x] != 'Barcode Definitions') {
			//	Left a field blank
			database_checks.push(false);
		}else if (id_array[x] == 'barcode_sep' && info_array[x] == 'yes') {
			//	Check Barcode Separation
			var split_barcodes = info_array[id_array.length - 3].split('\n');
			split_barcodes = split_barcodes.filter(function(n){return n != ''});
			var split_check = true;
			for (var y = 0; y < split_barcodes.length; y++) {
				split_barcodes[y].replace('\t', '');
				if (/[\\|\!\@\#\$\%\^\&\*\+\=\[\]\;\:\<\>\<\?\{\}\(\)]/g.test(split_barcodes[y])) {
					split_check = false;
				}else{
					var single_barcode_array = split_barcodes[y].split(' ');
					single_barcode_array = single_barcode_array.filter(function(n){return n != ''});
					if (single_barcode_array.length != 2) {
						//	Not proper Barcode input
						split_check = false;
					}else{
						barcode_array.push(single_barcode_array);
					}
				}
			}
			if (split_check) {
				database_checks.push(true);
			}else{
				database_checks.push(false);
			}
			
		}else if (id_array[x] == 'input_files'){
			//	Paired-end libraries
			var split_inputs = info_array[6].split('\n');
			split_inputs = split_inputs.filter(function(n){return n != ''});
			var input_bool_check = true;
			for (var y = 0; y < split_inputs.length; y++) {
				if (/[\\|\!\@\#\$\%\^\&\*\+\=\[\]\;\:\<\>\<\?\{\}\(\)]/g.test(split_inputs[y])) {
					input_bool_check = false;
				}else{
					var single_input_array = split_inputs[y].split(' ');
					single_input_array = single_input_array.filter(function(n){return n != ''});
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
			if (input_bool_check) {
				console.log(input_array);
				var end = 0;
				if (info_array[1] == 'yes') {
					end = input_array[0].length;
				}else{
					end = input_array[0].length - 1;
				}
				for(var z = 0; z < input_array.length; z++){
					for(var y = end; y > -1; y--){
						console.log(info_array[x-1]+"/"+info_array[z][y]);
						$.ajax({
							type: 	'GET',
							url: 	BASE_PATH+'/public/api/service.php',
							data:  	{ func: 'checkFile', username: username, file: info_array[x-1]+"/"+info_array[z][y] },
							async:	false,
							success: function(s)
							{
								var file_check = JSON.parse(s);
								if (file_check.Result != 'OK' ){
									input_bool_check = false;
								}
							}
						});
					}
				}
			}
			database_checks.push(input_bool_check);
			
		}else if (id_array[x] == 'input_dir' || id_array[x] == 'backup_dir'){
			//	Check inputs that should not contain whitespace
			if (/[\\|\ \!\@\#\$\%\^\&\*\(\)\+\=\[\]\;\:\<\>\<	\?\{\}]/g.test(info_array[x])) {
				//	Contains whitespace
				database_checks.push(false);
			}else{
				//	Directory Checks
				var dir_check_1;
				$.ajax({
						type: 	'GET',
						url: 	BASE_PATH+'/public/api/service.php',
						data:  	{ func: 'checkPermissions', username: username },
						async:	false,
						success: function(s)
						{
							dir_check_1 = JSON.parse(s);
						}
				});
				var dir_check_2;
				$.ajax({
						type: 	'GET',
						url: 	BASE_PATH+'/public/api/service.php',
						data:  	{ func: 'checkPermissions', username: username, outdir: info_array[x] },
						async:	false,
						success: function(s)
						{
							dir_check_2 = JSON.parse(s);
						}
				});
				
				if (dir_check_1.Result != 'Ok' || dir_check_2.Result != 'Ok') {
					//	perms errors
					database_checks.push(false);
				}else{
					//	No errors
					database_checks.push(true);
				}
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
		//	May submit into database
		var organism = info_array[0];

		//	Obtain group id
		var gid = obtainGroupFromName(document.getElementById('groups').value);
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
										organism, barcode_array[a][1], gid, perms);
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
									organism, 'nobarcode', gid, perms);
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
										organism, barcode_array[a][1], gid, perms);
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
									organism, 'nobarcode', gid, perms);
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
						var true_id = insertSample(experiment_series_id, lane_id, barcode_array[a][0],
										organism, barcode_array[a][1], gid, perms);
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
									organism, 'nobarcode', gid, perms);
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
		console.log(organism);
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

function insertSample(experiment_id, lane_id, sample_name, organism, barcode, gid, perms){
	var id;
	if (gid == "" || gid == "0") {
		gid = "1";
	}
	$.ajax({
			type: 	'GET',
			url: 	BASE_PATH+'/public/ajax/ngsfastlanedb.php',
			data:  	{ p: 'insertSample', experiment: experiment_id, lane: lane_id, sample: sample_name,
					organism: organism, barcode: barcode, gids: gid, perms: perms },
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

