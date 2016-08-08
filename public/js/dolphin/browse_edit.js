/*
 *Author: Nicholas Merowsky
 *Date: 09 Apr 2015
 *Ascription:
 */

var element_highlighted;
var element_highlighted_table;
var element_highlighted_value;
var element_highlighted_uid;
var element_highlighted_id;
var element_highlighted_type;
var element_highlighted_onclick;

var element_parent_table = '';
var element_parent_table_id = '';
var element_parent_child = '';

var experimentPerms = [];
var lanePerms = [];
var samplePerms = [];

var normalized = ['facility', 'source', 'organism', 'molecule', 'lab', 'organization', 'genotype', 'library_type',
				  'biosample_type', 'instrument_model', 'treatment_manufacturer'];
var fileDatabaseDict = ['ngs_dirs', 'ngs_temp_sample_files', 'ngs_temp_lane_files', 'ngs_fastq_files'];

function editBox(uid, id, type, table, element, parent_table, parent_table_id, parent_child){
	var havePermission = 0;
	console.log([uid, id, type, table, element]);
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/browse_edit.php",
		data: { p: 'checkPerms', id: id, uid: uid, table: table},
		async: false,
		success : function(r)
		{
			havePermission = r;
		}
	});
	
	if (havePermission == 1) {
		if (element_highlighted != null) {
			if (element_highlighted_value != ""){
				element_highlighted.innerHTML = element_highlighted_value;
			}else{
				element_highlighted.innerHTML = '<br>';
			}
			element_highlighted.onclick = element_highlighted_onclick;
			if (fileDatabaseDict.indexOf(table) > -1) {
				document.getElementById('submit_file_changes').remove();
				document.getElementById('cancel_file_changes').remove();
			}
		}
		element_highlighted = element;
		if (element.innerHTML != "<br>"){
			element_highlighted_value = element.innerHTML;
		}else{
			element_highlighted_value = '';
		}
		element_highlighted_uid = uid;
		element_highlighted_id = id;
		element_highlighted_type = type;
		element_highlighted_table = table;
		element_highlighted_onclick = element.onclick;
		element.innerHTML = '';
		if (parent_table != '') {
			element_parent_table = parent_table;
			element_parent_table_id = parent_table_id;
			element_parent_child = parent_child;
		}
		
		if (normalized.indexOf(type) > -1 && window.location.href.split("/").indexOf('encode') == -1) {
			
			element.onclick = '';
			
			var masterDiv = document.createElement('div');
			masterDiv.setAttribute('class','combobox input-group');
			
			var input = document.createElement('input');
			input.setAttribute('type', 'text');
			input.setAttribute('name', 'comboboxfieldname');
			input.setAttribute('id','cb_identifier');
			input.setAttribute('class','cb_identifier');
			input.value = element_highlighted_value;
			
			var button = document.createElement('button');
			button.setAttribute('class', 'btn btn-default btn-group pull-right')
			
			var span = document.createElement('span');
			span.setAttribute('class','caret');
			button.appendChild(span);
			
			var div = document.createElement('div');
			div.setAttribute('class','dropdownlist');
			div.setAttribute('style','z-index: 999');
			
			$.ajax({ type: "GET",
				url: BASE_PATH+"/public/ajax/browse_edit.php",
				data: { p: 'getDropdownValues', type: type},
				async: false,
				success : function(r)
				{
					for(var x = 0; x < r.length; x++){
						var a = document.createElement('a');
						a.innerHTML = (r[x][type]);
						div.appendChild(a);
					}
				}
			});
			
			element.appendChild(masterDiv);
			masterDiv.appendChild(input);
			masterDiv.appendChild(button);
			masterDiv.appendChild(div);
			var no = new ComboBox('cb_identifier');
			console.log('test');
		}else{
			var submitButton;
			var cancelButton;
			var textarea = document.createElement('textarea');
			
			textarea.setAttribute('id', 'inputTextBox');
			textarea.setAttribute('type', 'text');
			textarea.setAttribute('class', 'form-control');
			textarea.setAttribute('rows', '5');
			element.setAttribute('value', element_highlighted_value);
			if (table == 'ngs_dirs' || table == 'ngs_fastq_files' || table == 'ngs_temp_sample_files' || table == 'ngs_temp_lane_files') {
				submitButton = createElement('input', ['id','class','onclick','value','type'],['submit_file_changes','btn btn-primary pull-right margin', 'submitChangesFiles()', 'Submit','button']);
				cancelButton = createElement('input', ['id','class','onclick','value','type'],['cancel_file_changes','btn btn-default pull-right margin', 'cancelChangesFiles()', 'Cancel','button']);
			}else{
				textarea.setAttribute('onkeydown', 'submitChanges(this, event)');
			}
			element.appendChild(textarea);
			if (submitButton != undefined) {
				element.parentNode.parentNode.parentNode.appendChild(cancelButton);
				element.parentNode.parentNode.parentNode.appendChild(submitButton);
			}
			textarea.innerHTML = element_highlighted_value;
			element.onclick = '';
			element_highlighted.onclick = '';
		}
	}
}

function fastlaneDropdown(type, table, html_id){
	var element = document.getElementById(html_id);
	element.innerHTML = '';
	
	var masterDiv = document.createElement('div');
	masterDiv.setAttribute('class','combobox input-group');
	
	var input = document.createElement('input');
	input.setAttribute('type', 'text');
	input.setAttribute('name', 'comboboxfieldname');
	input.setAttribute('id','cb_identifier');
	input.setAttribute('class','cb_identifier');
	
	var button = document.createElement('button');
	button.setAttribute('class', 'btn btn-default btn-group pull-right')
	
	var span = document.createElement('span');
	span.setAttribute('class','caret');
	button.appendChild(span);
	
	var div = document.createElement('div');
	div.setAttribute('class','dropdownlist');
	div.setAttribute('style','z-index: 999');
	
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/browse_edit.php",
		data: { p: 'getDropdownValuesPerms', type: type, table: table, gids: phpGrab.gids},
		async: false,
		success : function(r)
		{
			console.log(r)
			for(var x = 0; x < r.length; x++){
				var a = document.createElement('a');
				a.innerHTML = (r[x][type]);
				div.appendChild(a);
			}
		}
	});
	
	element.appendChild(masterDiv);
	masterDiv.appendChild(input);
	masterDiv.appendChild(button);
	masterDiv.appendChild(div);
	var no = new ComboBox('cb_identifier');
	console.log('test');
}

function cancelChangesFiles(){
	submitChanges('details_cancel', 27);
}

function submitChangesFiles(){
	$('#fileModal').modal({
		show: true
	});
	document.getElementById('confirmFileButton').setAttribute('onclick', 'submitChanges("dir_element", 13)');
}

function submitChanges(ele, event = event) {
	var successBool = false;
    if (ele == 'details_cancel') {
		element_highlighted.innerHTML = element_highlighted_value;
		element_highlighted.onclick = element_highlighted_onclick;
		document.getElementById('submit_file_changes').remove();
		document.getElementById('cancel_file_changes').remove();
		clearElementHighlighted();
	}else if((event.keyCode == 13 && ele.value != '' && ele.value != null) || ele == 'dir_element') {
		if (ele == "dir_element") {
			ele = document.getElementById('inputTextBox');
			console.log(ele.value);
		}
		if (element_parent_table != '' && element_highlighted_id == '<br>') {
			console.log(element_highlighted_id);
			console.log(element_highlighted_type);
			console.log(element_highlighted_table);
			console.log(ele.value);
			console.log(element_parent_table)
			console.log(element_parent_table_id)
			$.ajax({ type: "GET",
				url: BASE_PATH+"/public/ajax/browse_edit.php",
				data: { p: 'insertDatabase', type: element_highlighted_type, table: element_highlighted_table, value: ele.value, parent: element_parent_table, parent_id: element_parent_table_id, parent_child: element_parent_child},
				async: false,
				success : function(r)
				{
					console.log(r)
					if (r == 1) {
						successBool = true;
						element_parent_table = '';
						element_parent_table_id = '';
						element_parent_child = '';
					}
				}
			});
		}else{
			$.ajax({ type: "GET",
				url: BASE_PATH+"/public/ajax/browse_edit.php",
				data: { p: 'updateDatabase', id: element_highlighted_id, type: element_highlighted_type, table: element_highlighted_table, value: ele.value},
				async: false,
				success : function(r)
				{
					console.log(r)
					if (r == 1) {
						successBool = true;
					}
				}
			});
		}
		if (successBool) {
			console.log("Success!")
			element_highlighted.innerHTML = ele.value;
			element_highlighted.onclick = element_highlighted_onclick;
			if (document.getElementById('submit_file_changes') != undefined) {
				document.getElementById('submit_file_changes').remove();
			}
			if (document.getElementById('cancel_file_changes') != undefined) {
				document.getElementById('cancel_file_changes').remove();
			}
			clearElementHighlighted();
		}
    }else if(event.keyCode == 27) {
		if (element_highlighted_value == '') {
			element_highlighted_value = '<br>';
		}
		element_highlighted.innerHTML = element_highlighted_value;
		element_highlighted.onclick = element_highlighted_onclick;
		
		clearElementHighlighted();
	}
}

function deleteButton(){
	$('#deleteModal').modal({
		show: true
	});
	var html = window.location.href.split("/");
	if (html.indexOf("details") == -1 && html.indexOf("browse") == -1) {
		if (checklist_samples.length == 0 && checklist_lanes.length == 0 && checklist_experiment_series.length == 0){
			document.getElementById('myModalLabel').innerHTML = 'Delete Error';
			document.getElementById('deleteLabel').innerHTML = 'You must make a selection to delete to continue.';
			
			document.getElementById('cancelDeleteButton').innerHTML = "OK";
			document.getElementById('confirmDeleteButton').setAttribute('style', 'display:none');
		}else if (checklist_experiment_series.length > 0) {
			document.getElementById('myModalLabel').innerHTML = 'Delete Experiment Series';
			document.getElementById('deleteLabel').innerHTML = 'Warning!  You have selected to remove an experiment series!';
			document.getElementById('deleteAreas').innerHTML = 'Are you sure you want to continue?<br>'+'Experiment series id(s): '+checklist_experiment_series.toString();
			
			document.getElementById('confirmDeleteButton').setAttribute('onclick', 'deletePermsModal()');
			document.getElementById('confirmDeleteButton').setAttribute('data-dismiss', '');
			document.getElementById('confirmDeleteButton').setAttribute('style', 'display:show');
		}else{
			deletePermsModal();
		}
	}else{
		document.getElementById('myModalLabel').innerHTML = 'Delete Error';
		document.getElementById('deleteLabel').innerHTML = 'Samples/Imports/Experiment Series may only be deleted on the main browser page.';
		
		document.getElementById('cancelDeleteButton').innerHTML = "OK";
		document.getElementById('confirmDeleteButton').setAttribute('style', 'display:none');
	}
}

function deletePermsModal(){
	experimentPerms = [];
	lanePerms = [];
	samplePerms = [];	

	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/browse_edit.php",
			data: { p: 'getExperimentPermissions', experiments: checklist_experiment_series.toString() },
			async: false,
			success : function(s)
			{
				for(var x = 0; x < s.length; x++){
					experimentPerms.push(s[x].id);
				}
			}
	});
	
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/browse_edit.php",
			data: { p: 'getLanePermissions', lanes: checklist_lanes.toString() },
			async: false,
			success : function(s)
			{
				for(var x = 0; x < s.length; x++){
					lanePerms.push(s[x].id);
				}
			}
	});
	
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/browse_edit.php",
			data: { p: 'getSamplePermissions', samples: checklist_samples.toString() },
			async: false,
			success : function(s)
			{
				for(var x = 0; x < s.length; x++){
					samplePerms.push(s[x].id);
				}
			}
	});
	console.log(experimentPerms);
	var badExperiments = [];
	var badLanes = [];
	var badSamples = [];
	for (var q = 0; q < checklist_experiment_series.length; q++) {
		if (experimentPerms.indexOf(checklist_experiment_series[q].toString()) == -1) {
			badExperiments.push(checklist_experiment_series[q]);
		}
	}
	for (var q = 0; q < checklist_lanes.length; q++) {
		if (lanePerms.indexOf(checklist_lanes[q].toString()) == -1) {
			badLanes.push(checklist_lanes[q]);
		}
	}
	for (var q = 0; q < checklist_samples.length; q++) {
		if (samplePerms.indexOf(checklist_samples[q].toString()) == -1) {
			badSamples.push(checklist_samples[q]);
		}
	}
	
	document.getElementById('myModalLabel').innerHTML = 'Delete Selected';
	document.getElementById('deleteLabel').innerHTML ='You have permission to delete the following:';
	document.getElementById('deleteAreas').innerHTML = 'Experiment Series id(s): ' + experimentPerms.join(", ") + '<br>Imports id(s): '+ lanePerms.join(", ") + '<br>Samples id(s): ' + samplePerms.join(", ") +
		'<br><br>Experiment Series id(s) lacking permissions: ' + badExperiments.join(", ") + '<br>Import id(s) lacking permissions: ' + badLanes.join(", ") + '<br>Sample id(s) lacking permissions: ' + badSamples.join(", ") +
		'<br><br>If the Import or Sample you want to delete is not accessible, you do not have the correct permissions to remove them.'+
		'<br><br>Be Warned! Deleting Imports/Samples will remove data AND runs accross the system, make sure you have a back up of any of the information you might want to save before deleting.'+
		'<br><br>Data is not recoverable, please make sure you want to delete these.';
		
	document.getElementById('cancelDeleteButton').innerHTML = "Cancel";
	document.getElementById('confirmDeleteButton').setAttribute('style', 'display:show');
	document.getElementById('confirmDeleteButton').setAttribute('onclick', 'confirmDeletePressed()');
}

function confirmDeletePressed(){
		$.ajax({ type: "GET",
				url: BASE_PATH+"/public/ajax/browse_edit.php",
				data: { p: 'deleteSelected', samples: samplePerms.toString(), lanes: lanePerms.toString(), experiments: experimentPerms.toString() },
				async: false,
				success : function(s)
				{
					console.log(s);
					alert();
				}
		});
		
		experimentPerms = [];
		lanePerms = [];
		samplePerms = [];
		
		flushBasketInfo();
		
		location.href = BASE_PATH + '/search';
	}

function cancelDeletePressed(){ 
	experimentPerms = [];
	lanePerms = [];
	samplePerms = [];
}

function initialRunButton(type, id, button){
	$('#deleteModal').modal({
		show: true
	});
	document.getElementById('myModalLabel').innerHTML = 'Information';
	document.getElementById('deleteLabel').innerHTML ='Initial run has not completed yet.';
	document.getElementById('deleteAreas').innerHTML = 'If Import/Sample seems to be taking too long to complete, please contact an Admin at biocore@umassmed.edu';
	
	document.getElementById('cancelDeleteButton').innerHTML = "OK";
	document.getElementById('confirmDeleteButton').setAttribute('style', 'display:none');
}

function clearElementHighlighted(){
	element_highlighted = null;
	element_highlighted_table = null;
	element_highlighted_value = null;
	element_highlighted_id = null;
	element_highlighted_type = null;
	element_highlighted_onclick = null;
}

function resubmitAmazon(){
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/browse_edit.php",
			data: { p: 'amazon_reupload', samples: checklist_samples.toString() },
			async: false,
			success : function(s)
			{
				console.log(s);
				window.location.reload();
			}
	});
}

function recheckChecksum() {
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/browse_edit.php",
		data: { p: 'checksum_recheck', samples: checklist_samples.toString() },
		async: false,
		success : function(s)
		{
			console.log(s);
			window.location.reload();
		}
	});
}

function sendToEncode() {
	window.location.href = BASE_PATH+"/public/encode"
}
