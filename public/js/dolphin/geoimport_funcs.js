var username;
var search_term_count = 0;

function searchGeoTerm(){
	search_term_count++;
	var geo_input = document.getElementById('geo_search_term').value
	var sra_avail;
	
	$('#loadingModal').modal('show');
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/geoImport_funcs.php",
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
				var disabled = '';
				if (sra_avail[k] == "true") {
					avail_button = '<button class="btn btn-success" disabled>Available</button>'
				}else{
					avail_button = '<button class="btn btn-danger" disabled>Not Available</button>'
					disabled = 'disabled'
				}
				search_table.fnAddData([
					k,
					avail_button,
					'<button class="btn btn-primary pull-right" id="'+k+'_select" onclick="selectSRA(\''+k+'\', '+search_term_count+', this)" '+disabled+'>Select</button>'
				]);
			}
		}
		document.getElementById("searched_inner_div").hidden = false;
		document.getElementById("searched_select_all_div").hidden = false;
		$('#loadingModal').modal('hide');
	});	
}

function selectSRA(sample, term_count, button){
	var searched_table = $('#jsontable_geo_searched').dataTable();
	var row = $(button).closest('tr');
	searched_table.fnDeleteRow(row);
	searched_table.fnDraw();
	
	document.getElementById("selected_inner_div").hidden = false;
	var selected_table = $('#jsontable_geo_selected').dataTable();
	selected_table.fnAddData([
		'<input type="text" id="'+term_count+'_'+sample+'" size="50" class="col-mid-12" value="'+sample+'">',
		sample+'.sra',
		'<button class="btn btn-danger pull-right" id="'+sample+'_remove" onclick="removeSRA(\''+sample+'\', '+term_count+', this)">Remove</button>'
	])
}

function selectAllSRA() {
	var searched_table = $('#jsontable_geo_searched').dataTable();
	var table_nodes = searched_table.fnGetNodes()
	for(var x = 0; x < table_nodes.length; x++){
		if (table_nodes[x].children[2].children[0].disabled == false) {
			table_nodes[x].children[2].children[0].click()
		}
	}
}

function removeSRA(sample, term_count, button){
	var selected_table = $('#jsontable_geo_selected').dataTable();
	var row = $(button).closest('tr');
	selected_table.fnDeleteRow(row);
	selected_table.fnDraw();
	if (term_count == search_term_count) {	
		var searched_table = $('#jsontable_geo_searched').dataTable();
		searched_table.fnAddData([
			sample,
			'<button class="btn btn-success" disabled>Available</button>',
			'<button class="btn btn-primary pull-right" id="'+sample+'_select" onclick="selectSRA(\''+sample+'\', '+term_count+', this)">Select</button>'
		])
	}
}

function getAccessions(geo_input){
	var sra_avail;
	console.log(geo_input)
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/geoImport_funcs.php",
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
	var sra_dir = document.getElementById('download_sra_dir').value
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
	var table_data = selected_table.fnGetData()
	var table_nodes = selected_table.fnGetNodes()
	var sample_names = []
	var sample_files = []
	if (table_data == []) {
		error_out.push("Selected Samples cannot be empty.")
	}else{
		for(var x = 0; x < table_nodes.length; x++){
			var sample = table_nodes[x].children[0].children[0].value
			var sra = table_nodes[x].children[1].innerHTML
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
			}
		}
	}
	if (sra_dir == "") {
		error_out.push("Download SRA Directory cannot be empty.")
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
		var dir_check_2;
		$.ajax({
			type: 	'GET',
			url: 	API_PATH+'/public/api/service.php',
			data: { func: "checkPermissions", username: username.clusteruser, outdir: sra_dir},
			async:	false,
			success: function(s)
			{
				console.log(s);
				dir_check_1 = JSON.parse(s);
			}
		});
		
		$.ajax({
			type: 	'GET',
			url: 	API_PATH+'/public/api/service.php',
			data: { func: "checkPermissions", username: username.clusteruser, outdir: outdir},
			async:	false,
			success: function(s)
			{
				console.log(s);
				dir_check_2 = JSON.parse(s);
			}
		});
		
		if (dir_check_1.Result != 'Ok'){
			error_out.push("(Download SRA Directory) " + dir_check_1.ERROR);
		}
		if (dir_check_2.Result != 'Ok') {
			error_out.push("(Import Process Directory) " + dir_check_2.ERROR);
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
		finalizeSRASubmission();
	}
}

function finalizeSRASubmission(){
	
}

$(function() {
	"use strict";
	var selected_table = $('#jsontable_geo_selected').dataTable({"autoWidth":false});
	selected_table.fnClearTable();
	var search_table = $('#jsontable_geo_searched').dataTable({"autoWidth":false});
	search_table.fnClearTable();
});