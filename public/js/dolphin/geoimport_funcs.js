var username;
var search_term_count = 0;

function searchGeoTerm(){
	search_term_count++;
	var geo_input = document.getElementById('geo_search_term').value
	var sra_avail;
	
	$('#loadingModal').modal('show');
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/geoimport_funcs.php",
		data: { p:'getAccessions', term:geo_input},
		async: true,
		success : function(s)
		{
			console.log(s)
			if (s == "") {
				$('#errorModal').modal({
					show: true
				});
				document.getElementById('errorLabel').innerHTML = "There was an error in your GEO query:";
				document.getElementById('errorArea').innerHTML = "Search term '" + geo_input + " cannot be found";
			}else{
				sra_avail = JSON.parse(s)
				console.log(sra_avail)
			}
		}
	});
	$( document ).ajaxComplete(function() {
		if (sra_avail != undefined) {
			var es = document.getElementById("series_name");
			var lane = document.getElementById("lane_name");
			if (es.value == "") {
				es.value = geo_input.replace(",", " ");
			}
			if (lane.value == "") {
				lane.value = geo_input.replace(",", " ");
			}
			var search_table = $('#jsontable_geo_searched').dataTable();
			search_table.fnClearTable();
			for(var k in sra_avail){
				var avail_button;
				var paired_button;
				var disabled = '';
				var pair = "None"
				if (sra_avail[k] != "none") {
					avail_button = '<button class="btn btn-success" disabled>Available</button>'
					if (sra_avail[k] == "single") {
						pair = "Single-End"
					}else if (sra_avail[k] == "paired") {
						pair = "Paired-End"
					}
					paired_button = '<button class="btn btn-success" disabled>'+pair+'</button>'
				}else{
					avail_button = '<button class="btn btn-danger" disabled>Not Available</button>'
					paired_button = '<button class="btn btn-danger" disabled>No Information</button>'
					disabled = 'disabled'
				}
				search_table.fnAddData([
					k,
					avail_button,
					paired_button,
					'<button class="btn btn-primary pull-right" id="'+k+'_select" onclick="selectSRA(\''+k+'\', '+search_term_count+', \''+pair+'\', this)" '+disabled+'>Select</button>'
				]);
			}
		}
		document.getElementById("searched_inner_div").hidden = false;
		$('#loadingModal').modal('hide');
	});	
}

function selectSRA(sample, term_count, pair, button){
	var searched_table = $('#jsontable_geo_searched').dataTable();
	var row = $(button).closest('tr');
	searched_table.fnDeleteRow(row);
	searched_table.fnDraw();
	document.getElementById("selected_inner_div").hidden = false;
	var selected_table = $('#jsontable_geo_selected').dataTable();
	selected_table.fnAddData([
		'<input type="text" id="'+term_count+'_'+sample+'" size="50" class="col-mid-12" value="'+sample+'">',
		'<button class="btn btn-primary" disabled>'+sample+'.sra</button>',
		'<button class="btn btn-success" disabled>'+pair+'</button>',
		'<button class="btn btn-danger pull-right" id="'+sample+'_remove" onclick="removeSRA(\''+sample+'\', '+term_count+', \''+pair+'\', this)">Remove</button>'
	])
}

function selectAllSRA() {
	var searched_table = $('#jsontable_geo_searched').dataTable();
	var table_nodes = searched_table.fnGetNodes()
	for(var x = 0; x < table_nodes.length; x++){
		if (table_nodes[x].children[3].children[0].disabled == false) {
			table_nodes[x].children[3].children[0].click()
		}
	}
}

function removeSRA(sample, term_count, pair, button){
	var selected_table = $('#jsontable_geo_selected').dataTable();
	var row = $(button).closest('tr');
	selected_table.fnDeleteRow(row);
	selected_table.fnDraw();
	if (term_count == search_term_count) {	
		var searched_table = $('#jsontable_geo_searched').dataTable();
		searched_table.fnAddData([
			sample,
			'<button class="btn btn-success" disabled>Available</button>',
			'<button class="btn btn-success" disabled>'+pair+'</button>',
			'<button class="btn btn-primary pull-right" id="'+sample+'_select" onclick="selectSRA(\''+sample+'\', '+term_count+', \''+pair+'\', this)">Select</button>'
		])
	}
}

function getAccessions(geo_input){
	var sra_avail;
	console.log(geo_input)
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/geoimport_funcs.php",
		data: { p:'getAccessions', term:geo_input},
		async: true,
		success : function(s)
		{
			console.log('success')
			sra_avail = JSON.parse(s)
			console.log(sra_avail)
		}
	});
	return sra_avail
}

function submitSRA(){
	var error_out = [];
	var series= document.getElementById('series_name').value
	var lane = document.getElementById('lane_name').value
	var outdir = document.getElementById('import_process_dir').value
	
	//	Basic Checks
	if ( series == "") {
		error_out.push("Experiment Series Name cannot be empty.")
	}
	if (!/^[a-zA-Z 0-9\_\-\s]*$/.test(series)) {
		error_out.push("Series name does not have correct formatting.  Please use Alpha-numerics with dashes/underscores/spaces only.");
	}
	if (lane == "") {
		error_out.push("Import Name cannot be empty.")
	}
	if (!/^[a-zA-Z 0-9\_\-\s]*$/.test(lane)) {
		error_out.push("Import name does not have correct formatting.  Please use Alpha-numerics with dashes/underscores/spaces only.");
	}
	var selected_table = $('#jsontable_geo_selected').dataTable();
	var table_nodes = selected_table.fnGetNodes()
	var sample_names = []
	var sample_files = []
	var sample_paired = []
	if (table_nodes.length < 1) {
		error_out.push("Selected Samples cannot be empty.")
	}else{
		for(var x = 0; x < table_nodes.length; x++){
			var sample = table_nodes[x].children[0].children[0].value
			var sra = table_nodes[x].children[1].children[0].innerHTML
			var pair = table_nodes[x].children[2].children[0].innerHTML
			if (sample ==  "") {
				error_out.push("Row " + (x + 1) + " of the samples being submitted has no name assigned to it.")
			}else if (!/^[a-zA-Z 0-9\_\-\s]*$/.test(sample)) {
				error_out.push("<b>" + sample + " (Row " + (x + 1) + ")</b> does not have correct formatting.  Please use Alpha-numerics with dashes/underscores/spaces only.");
			}else if (/^[0-9]*$/.test(sample.substr(0,1))) {
				error_out.push("<b>" + sample + " (Row " + (x + 1) + ")</b> cannot have the starting character be a number for the sample name.");
			}else if (sample_names.indexOf(sample) > -1) {
				error_out.push("<b>" + sample + " (Row " + (x + 1) + ")</b> is used twice as a sample name.")
			}else if (sample_files.indexOf(sra) > -1) {
				error_out.push("<b>" + sra + " (Row " + (x + 1) + ")</b> is used twice as the supplied SRA file.")
			}else{
				sample_names.push(sample)
				sample_files.push(sra)
				sample_paired.push(pair)
			}
		}
		if (sample_paired.length > 0) {
			var pairtest = sample_paired[0]
			for (var x = 0; x < sample_paired.length; x++) {
				var sample = table_nodes[x].children[0].children[0].value
				if (pairtest != sample_paired[x]) {
					error_out.push("You can only submit samples of one pair-end type.  Please split Single-end and Paired-end samples into two different imports.")
				}
			}
		}
	}
	if (outdir == "") {
		error_out.push("Import Process Directory cannot be empty.")
	}
	
	//	Permission Checks
	if (error_out.length == 0) {
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
		var dir_check_1;
		$.ajax({
			type: 	'GET',
			url: 	BASE_PATH+'/public/api/service.php',
			data: { func: "checkPermissions", username: username.clusteruser, outdir: outdir},
			async:	false,
			success: function(s)
			{
				console.log(s);
				dir_check_1 = JSON.parse(s);
			}
		});
		if (dir_check_1.Result != 'Ok') {
			error_out.push("(Import Process Directory) " + dir_check_1.ERROR);
		}
	}
	
	
	//	Output to Modal if Errors Exist
	if (error_out.length > 0) {
		//	Error in submission, do not submit into database
		console.log(error_out);
		$('#errorModal').modal({
			show: true
		});
		document.getElementById('errorLabel').innerHTML = "There was an error in your submission:";
		document.getElementById('errorArea').innerHTML = "";
		for(var x = 0; x < error_out.length; x++){
			document.getElementById('errorArea').innerHTML += error_out[x] + "<br><br>";
		}
	}else{
		databaseChecksGEO(series, lane, sample_names, sample_files, sample_paired, outdir);
	}
}

function databaseChecksGEO(series, lane, sample_names, sample_files, sample_paired, outdir){
	var sample_check = true
	//	Database checks
	//	Experiment Series
	var experiment_series_id = experimentSeriesCheck(series);
	//	Experiments (Lanes)
	if (experiment_series_id > 0) {
		var lane_id = laneCheck(experiment_series_id, lane);
	}
	
	if (experiment_series_id > 0 && lane_id > 0) {
		var bad_samples = []
		for(z in sample_names){
			var id_check = sampleCheck(experiment_series_id, lane_id, z)
			if (id_check > 0) {
				bad_samples.push(z)
			}
		}
		
		if (bad_samples.length > 0) {
			sample_check = false
			$('#errorModal').modal({
				show: true
			});
			document.getElementById('errorLabel').innerHTML = "There was an error in your submission:";
			document.getElementById('errorArea').innerHTML = "";
			for(var x = 0; x < error_out.length; x++){
				document.getElementById('errorArea').innerHTML += "Sample '" + error_out[x] + "' already exists for this import.<br><br>";
			}
		}
	}
	if (sample_check) {
		var sample_ids = []
		var gid = document.getElementById('groups').value.toString();
		var perms = obtainPermsFromRadio();
		insertDirectories(outdir, outdir, "", gid, perms);
		input_directory_id = directoryCheck(outdir, outdir, "");
		
		//	Insert ES, Import, and Samples
		if (experiment_series_id > 0) {
			//	If adding to a experiment series
			if (!lane_id > 0) {
				//	If creating a lane
				insertLane(experiment_series_id, lane, gid, perms);
				lane_id = laneCheck(experiment_series_id, lane);
			}
		}else{
			//	If creating an experiment series
			insertExperimentSeries(series, gid, perms);
			experiment_series_id = experimentSeriesCheck(series);
			
			insertLane(experiment_series_id, lane, gid, perms);
			lane_id = laneCheck(experiment_series_id, lane);
		}
		for (var a = 0; a < sample_names.length; a++) {
				var true_id = insertSampleGEO(experiment_series_id, lane_id, sample_names[a], sample_files[a], 'nobarcode', gid, perms);
				sample_ids.push(true_id);
		}
		//	Insert temp files
		var paired = "no"
		for(var g = 0; g < sample_names.length; g++){
			var fastq = sample_names[g] + "_1.fastq"
			if (sample_paired[g] == "Paired-End") {
				paired = "paired"
				fastq += "," + sample_names[g] + "_2.fastq"
			}
			insertTempSampleFiles(fastq, sample_ids[g], input_directory_id, gid, perms);
		}
		var value_array = [series, lane, paired, sample_names.join(":"), outdir, gid, perms];
		console.log(value_array)
		sendProcessData(value_array, 'geo_values');
		finalizeSRASubmission()
	}
}

function finalizeSRASubmission(){
	window.location.href = BASE_PATH + "/geoimport/process"
}

function backToGeo(){
	window.location.href = BASE_PATH + "/geoimport"
}

function sendToStatus(){
	window.location.href = BASE_PATH + "/stat/status"
}

function insertSampleGEO(experiment_id, lane_id, sample_name, sample_file, barcode, gid, perms){
	var id;
	if (gid == "" || gid == "0") {
		gid = "1";
	}
	$.ajax({
			type: 	'GET',
			url: 	BASE_PATH+'/public/ajax/geoimport_funcs.php',
			data:  	{ p: 'insertSampleGEO', experiment: experiment_id, lane: lane_id, sample: sample_name,
					file: sample_file, barcode: barcode, gids: gid, perms: perms },
			async:	false,
			success: function(s)
			{
			}
	});
	id = sampleCheck(experiment_id, lane_id, sample_name);
	return id;
}

$(function() {
	"use strict";
	if (window.location.href.split("/").indexOf('process') < 0) {
		var selected_table = $('#jsontable_geo_selected').dataTable({"autoWidth":false});
		selected_table.fnClearTable();
		var search_table = $('#jsontable_geo_searched').dataTable({"autoWidth":false});
		search_table.fnClearTable();
	}
});
