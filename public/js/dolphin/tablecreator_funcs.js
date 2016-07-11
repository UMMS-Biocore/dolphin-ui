var selectionHelper = [];
var runHelper = [];
var runIDHelper = {};

function removeTableSamples(id, button){
	var table = $('#jsontable_selected_samples').dataTable();
	var row = $(button).closest('tr');
	table.fnDeleteRow(row);
	table.fnDraw();
	checklist_samples.splice(checklist_samples.indexOf(id), 1);
	selectionHelper.splice(runHelper.indexOf(id), 1);
	runHelper.splice(runHelper.indexOf(id), 1);
	checkCheckedList();
	removeBasketInfo(id);
	reportSelection();
	document.getElementById('sample_checkbox_'+id).checked = false;
}

function manageCreateChecklists(id, tablerow){
	var table = $('#jsontable_selected_samples').dataTable();
	
	var run_ids = [];
	var ids = [];
	if (getBasketInfo() != undefined) {
		ids = getBasketInfo().split(",");
	}
	if (ids.indexOf(id) < 0) {
		//add
		$.ajax({ type: "GET",
				url: BASE_PATH+"/public/ajax/tablegenerator.php",
				data: { p: "getTableRuns", search: id },
				async: false,
				success : function(s)
				{
					for(var i = 0; i < s.length; i ++){
						if (run_ids[s[i].sample_id] == undefined) {
							if (s[i].run_name != null) {
								run_ids[s[i].sample_id] = [s[i].run_id, s[i].run_name, s[i].wkey];
							}
						}else{
							if (s[i].run_name != null) {
								run_ids[s[i].sample_id].push(s[i].run_id);
								run_ids[s[i].sample_id].push(s[i].run_name);
								run_ids[s[i].sample_id].push(s[i].wkey);
							}
						}
					}
				}});
		$.ajax({ type: "GET",
				url: BASE_PATH+"/public/ajax/tablegenerator.php",
				data: { p: "getTableSamples", search: id },
				async: true,
				success : function(s)
				{
					for(var i = 0; i < s.length; i++){
						var run_info = [];
						var run_select = '<select id="'+ s[i].id + '_run_select" multiple class="form-control" size="3" onchange="optionChange(this)"><form>';
						for(var x = 0; x < run_ids[s[i].id].length; x = x+3){
							//Sample id _ Run id _ Run name
							run_select += '<option id="' + run_ids[s[i].id][x]+ '_' + run_ids[s[i].id][x+1] + '" value="'+ run_ids[s[i].id][x+2] + '">Run ' + run_ids[s[i].id][x] + ': ' + run_ids[s[i].id][x+1] + '</option>'
						}
						run_select += '</form></select>';
						
						table.fnAddData([
							s[i].id,
							s[i].samplename,
							run_select,
							'<button id="sample_removal_'+s[i].id+'" class="btn btn-danger btn-xs pull-right" onclick="removeTableSamples(\''+s[i].id+'\', this)"><i class=\"fa fa-times\"></i></button>'
							]);
						runHelper.push(s[i].id);
						selectionHelper.push(0);
					}
					checklist_samples.push(id);
					sendBasketInfo(id);
					reportSelection();
				}});
	}else{
		//remove
		removeTableSamples(id, document.getElementById('sample_removal_'+id));
		console.log(document.getElementById('sample_checkbox_'+id).checked);
	}
}

function reportSelection(){
	wkeys = [];
	reports = [];
	var ids = [];
	if (getBasketInfo() != undefined) {
		ids = getBasketInfo().split(",");
	}
	for(var y = 0; y < ids.length; y++){
		var element = document.getElementById(ids[y] + '_run_select');
		if (element != undefined) {
			for(var r = 0; r < element.selectedOptions.length; r++){
				var option_selected = element.selectedOptions[r]
				option_selected.click();
				if (wkeys.indexOf(option_selected.value) == -1) {
					wkeys.push(option_selected.value);
				}
			}
		}
	}
	console.log(wkeys);
	var wkey_count = wkeys.length;
	
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/tablegenerator.php",
			data: { p: "getTableReportsList", wkey: wkeys.toString() },
			async: false,
			success : function(s)
			{
				reports = s;
			}
	});
	
	var multi_box = document.getElementById('report_multi_box');
	multi_box.innerHTML = '';
	var run_cohesion_check = [];
	var run_cohesion_count_check = [];
	var genome_check = '';
	if (reports.length > 0) {
		genome_check = reports[0].json_parameters.split(',')[0];
	}
	for(var y = 0; y < reports.length; y ++){
		var filename = reports[y].file.split(".")[reports[y].file.split(".").length - 1];
		if (filename != 'pdf' && filename != 'R' && filename != 'txt') {
			if (run_cohesion_check.indexOf(reports[y].file) < 0) {
				run_cohesion_check.push(reports[y].file);
				run_cohesion_count_check.push(1);
				var option = document.createElement("option");
				option.id = reports[y].file;
				option.text = reports[y].file;
				if (wkey_count != run_cohesion_count_check[run_cohesion_check.indexOf(reports[y].file)]) {
					option.disabled = true;
					option.style.opacity = .35;
				}else{
					option.disabled = false;
					option.style.opacity = 1;
				}
				multi_box.add(option);
			}else{
				run_cohesion_count_check[run_cohesion_check.indexOf(reports[y].file)]++;
				if (wkey_count != run_cohesion_count_check[run_cohesion_check.indexOf(reports[y].file)] || genome_check != reports[y].json_parameters.split(",")[0]) {
					document.getElementById(reports[y].file).disabled = true;
					document.getElementById(reports[y].file).style.opacity = .35;
				}else{
					document.getElementById(reports[y].file).disabled = false;
					document.getElementById(reports[y].file).style.opacity = 1;
				}
			}
		}
	}
}

function sendToTableGen(){
	var run_array = [];
	if (getBasketInfo() != undefined) {
		ids = getBasketInfo().split(",");
	}
	for(var y = 0; y < ids.length; y++){
		var keys = Object.keys(runIDHelper);
		for (var x = 0; x < keys.length; x++) {
			if (run_array[runIDHelper[keys[x]]] == undefined) {
				run_array[runIDHelper[keys[x]]] = ids[y];
			}else{
				run_array[runIDHelper[keys[x]]] += "," + ids[y];
			}
		}
	}
	
	var samples_send = 'samples=';
	for(var key in run_array){
		samples_send += run_array[key].toString() + ';' + key + ':';
	}
	console.log(samples_send);
	samples_send = samples_send.substring(0, samples_send.length - 1);
	
	var format_send = '&format=json';
	
	var files_selected = $('#report_multi_box').val();
	var file_send = '&file=' + files_selected.toString();
	
	var type_send = '';
	console.log(file_send);
	if (file_send.indexOf('summary.') > -1) {
		console.log("@@@");
		type_send = '&type=summary';
	}
	var common_send = '';
	var key_send = '';
	var keepcols_send = '';
	if (file_send.indexOf('.counts.') > -1) {
		//counts
		common_send = '&common=id,len'
		key_send = '&key=id';
	}else if (file_send.indexOf('rsem') > -1) {
		//RSEM
		common_send = '&common=gene,transcript';
		if (file_send.indexOf('isoforms') > -1) {
			key_send = '&key=transcript';
		}else{
			key_send = '&key=gene';
		}
	}else if (file_send.indexOf('DESeq') > -1) {
		//DESEQ
		common_send = '&common=name';
		key_send = '&key=name';
		keepcols_send = '&keepcols=padj,log2FoldChange';
	}else if (file_send.indexOf('picard') > -1) {
		//PICARD
		if (file_send.indexOf('.hist.') > -1) {
			common_send = '&common=nt';
			key_send = '&key=nt';
		}else{
			common_send = '&common=metric';
			key_send = '&key=metric';
		}
	}else if (file_send.indexOf('RSeQC') > -1) {
		//RSeQC
		common_send = '&common=region'
		key_send = '&key=region';
	}
	
	var filter_send = '';
	
	return samples_send + file_send +
		common_send + keepcols_send + key_send + type_send + format_send + filter_send;
}

function tableCreatorPage(){
	var file_values = $('#report_multi_box').val();
	var ids;
	var empty_ids = [];
	if (getBasketInfo() != undefined) {
		ids = getBasketInfo().split(",");
		var runparams = $('#jsontable_selected_samples')	
		for (var x = 0; x < ids.length; x++) {
			if (runIDHelper[ids[x]] == undefined) {
				empty_ids.push(ids[x]);
			}
		}
	}
	
	if(file_values == null){
		$('#errorModal').modal({
			show: true
		});
		document.getElementById('errorLabel').innerHTML ='A file must be selected in order to generate a report!';
		document.getElementById('errorAreas').innerHTML = '';
	}else if (ids == undefined){
		$('#errorModal').modal({
			show: true
		});
		document.getElementById('errorLabel').innerHTML ='No Samples Selected! You cannot generate a table with zero samples.';
		document.getElementById('errorAreas').innerHTML = '';
	}else if (empty_ids.length != 0){
		$('#errorModal').modal({
			show: true
		});
		document.getElementById('errorLabel').innerHTML ='No runs have been Selected for samples '+empty_ids.toString()+'! Please select runs for these samples or remove them.';
		document.getElementById('errorAreas').innerHTML = '';
	}else if(file_values.length > 1){
		$('#errorModal').modal({
			show: true
		});
		document.getElementById('errorLabel').innerHTML ='Please select only one file for report generation.';
		document.getElementById('errorAreas').innerHTML = '';
	}else{
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/tablegenerator.php",
			data: { p: 'createCustomTable', params: sendToTableGen() },
			async: true,
			success : function(s)
			{
				window.location.href = BASE_PATH + '/tablecreator/table';
			}
		});
	}
}

function changeTableType(format, query){
	var json_obj;
	var URL = API_PATH+"/public/api/getsamplevals.php?" + query + 'format=' + format;
	window.open(URL);
}

function backToTableIndex(){
	window.location.href = BASE_PATH+"/tablecreator";
}

function toBrowserPage(){
	window.location.href = BASE_PATH+'/search'
}

function toTableListing(){
	window.location.href = BASE_PATH+"/tablecreator/tablereports";
}

function rerunSelected(link) {
	window.location.href = BASE_PATH + '/tablecreator/table/' + link;
}

function saveTable() {
	var name = document.getElementById('input_table_name').value;
	var group = document.getElementById('groups').value;
	var perms = document.getElementById('perms').value;
	var parameters;
	if (window.location.href.split("/").indexOf('table') > -1) {
		$.ajax({ type: "GET",
			url: BASE_PATH +"/public/ajax/tablegenerator.php?",
			data: { p: "getGeneratedTable"},
			async: false,
			success : function(s)
			{
				console.log(s);
				parameters = s.parameters;
			}
		});
	}else{
		parameters = sendToTableGen();
	}
	var file_name = '';
	var beforeFormat = parameters.split('format=')[0];
		
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/tablegenerator.php",
			data: { p: "createTableFile", url: API_PATH+"/public/api/getsamplevals.php?" + beforeFormat + 'format=json' },
			async: false,
			success : function(s)
			{
				file_name = s;
				console.log(s);
				console.log(API_PATH+"/public/api/getsamplevals.php?" + beforeFormat + 'format=json');
			}
	});
	console.log(file_name);
	$.ajax({ type: "GET",
				url: BASE_PATH+"/public/ajax/tablegenerator.php",
				data: { p: "createNewTable", search: parameters, name: name, file: file_name, group: group, perms: perms },
				async: true,
				success : function(s)
				{
					console.log('console true');
					toTableListing();
				}
		});
}

function downloadCreatedTSV(file_name){
	if (file_name != '') {
		var URL = BASE_PATH + '/public/tmp/files/' + file_name; 
		window.open(URL, '_blank');
	}
}

function downloadGeneratedTSV(format){
	var url = API_PATH +"/public/api/getsamplevals.php?" + format + "format=json"
	var file_name = '';
	console.log(url);
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/tablegenerator.php",
			data: { p: "convertToTSV", url: url },
			async: true,
			success : function(s)
			{
				file_name = s;
				if (file_name != '') {
					var URL = BASE_PATH + '/public/tmp/files/' + file_name; 
					window.open(URL, '_blank');
				}
			}
	});
}

function optionSelection(args) {
	console.log(args);
}

function sendTableToPlot(file){
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/sessionrequests.php",
			data: { p: "setPlotToggle", type: 'generated', file: file + "2" },
			async: true,
			success : function(s)
			{
				window.location.href = BASE_PATH + '/plot';
			}
	});
}

function sendTableToDebrowser(file){
	$.ajax({ type: "GET",
                        url: BASE_PATH+"/public/ajax/sessionrequests.php",
                        data: { p: "sendToDebrowser", table: file },
                        async: true,
                        success : function(s)
                        {
							window.location.href = BASE_PATH + '/debrowser';
                        }
        });
}

function deleteTable(id){
	$.ajax({ type: "GET",
				url: BASE_PATH+"/public/ajax/tablegenerator.php",
				data: { p: "deleteTable", id: id },
				async: false,
				success : function(s)
				{
					console.log(s);
				}
		});
}

function optionChange(selector){
	var options_array = [];
	for(var x = 0; x < selector.options.length; x++){
		for(var y = 0; y < selector.selectedOptions.length; y++){
			if (selector.selectedOptions[y] == selector.options[x]) {
				options_array.push(y);
			}
		}
	}
	var selectedOptions = selector.selectedOptions;
	runIDHelper[selector.id.split("_")[0]] = selectedOptions[0].id.split("_")[0];
	selectionHelper[runHelper.indexOf(selector.id.split('_')[0])] = options_array;
	console.log(selector.selectedIndex);
	reportSelection();
}

function changeTableData(id){
	//	Change Experiment Series Owner
	var owner = ''
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/tablegenerator.php",
		data: { p: 'getTableOwner', table: id },
		async: false,
		success : function(s)
		{
			owner = s;
			console.log(s);
		}
	});
	if (owner == phpGrab.uid) {
		document.getElementById('permsOwnerLabel').innerHTML = 'Which user should own this table?';
		document.getElementById('permsOwnerSelect').innerHTML = '';
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/tablegenerator.php",
			data: { p: 'getAllUsers', table: id },
			async: false,
			success : function(s)
			{
				console.log(s);
				for(var x = 0; x < s.length; x++){
					if (s[x].id == phpGrab.uid) {
						document.getElementById('permsOwnerSelect').innerHTML += '<option value="' + s[x].id + '" selected="true">' + s[x].username + '</option>';
					}else{
						document.getElementById('permsOwnerSelect').innerHTML += '<option value="' + s[x].id + '">' + s[x].username + '</option>';
					}
				}
			}
		});
		document.getElementById('permsGroupLabel').innerHTML = 'Which group should be able to view this table?';
		document.getElementById('permsGroupSelect').innerHTML = '';
		$.ajax({ type: "GET",
				url: BASE_PATH+"/public/ajax/tablegenerator.php",
				data: { p: 'changeDataGroupNames', table: id },
				async: false,
				success : function(s)
				{
					console.log(s);
					for(var x = 0; x < s.length; x++){
						if (s[x].id == phpGrab.uid) {
							document.getElementById('permsGroupSelect').innerHTML += '<option value="' + s[x].id + '" selected="true">' + s[x].name + '</option>';
						}else{
							document.getElementById('permsGroupSelect').innerHTML += '<option value="' + s[x].id + '">' + s[x].name + '</option>';
						}
					}
				}
			});
		var perms;
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/ngsquerydb.php",
			data: { p: 'getTablePerms', table: id},
			async: false,
			success : function(s)
			{
				console.log(s);
				perms = s;
			}	
		});
		if (perms == 3) {
			$('#only_me').iCheck('check');
		}else if (perms == 15) {
			$('#only_my_group').iCheck('check');
		}else if (perms == 32) {
			$('#everyone').iCheck('check');
		}else if (perms == 63) {
			$('#everyone').iCheck('check');
		}else{
			$('#only_me').iCheck('check');
		}
		
		if (document.getElementById('permsOwnerSelect').innerHTML != '') {
			document.getElementById('confirmTablePermsButton').setAttribute('style', 'display:show');
			document.getElementById('cancelTablePermsButton').innerHTML = 'Cancel';
			document.getElementById('confirmTablePermsButton').setAttribute('onclick', 'confirmTablePermsPressed(\''+id+'\', \'confirm\')');
		}
		$('#permsModal').modal({
			show: true
		});
	}else{
		confirmTablePermsPressed(0, 'fail');
	}
}

function confirmTablePermsPressed(id, command){
	if (command == 'confirm') {
		document.getElementById('permsConfirmLabel').innerHTML = 'Table\'s permissions have been changed!';
		var owner_id = document.querySelector("#permsOwnerSelect").selectedOptions[0].value;
		var group_id = document.querySelector("#permsGroupSelect").selectedOptions[0].value;
		var perms = $('.checked')[0].children[0].value;
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/tablegenerator.php",
			data: { p: 'changeTableData', table: id, owner_id: owner_id, group_id: group_id, perms: perms, },
			async: false,
			success : function(s)
			{
				console.log(s);
			}
		});
		$('#permsModal').modal({
			show: false
		});
	}else{
		document.getElementById('permsConfirmLabel').innerHTML = 'You do not have permissions to edit this table\'s permissions';
	}
	$('#permsConfirmModal').modal({
		show: true
	});
}

function sendToSavedTable(id){
	console.log('test');
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/tablegenerator.php",
			data: { p: "sendToGeneratedTable", table_id: id },
			async: false,
			success : function(s)
			{
				console.log(s);
				window.location.href = BASE_PATH+'/public/tablecreator/table';
			}
	});
}

$(function() {
	"use strict";
	if(window.location.href.split("/").indexOf('table') < 0 && window.location.href.split("/").indexOf('tablereports') < 0 &&
	   window.location.href.split("/").indexOf('search') == -1){
		//	If within tablecreator page, get basket info
		var sample_ids = getBasketInfo();
		checklist_samples = [];
		if (sample_ids != undefined) {
			checklist_samples =  sample_ids.split(',');
		}
		console.log(checklist_samples);
		var runparams = $('#jsontable_selected_samples').dataTable({ autoFill: true });
		var run_ids = [];
		var samples_with_runs =[];
		
		$.ajax({ type: "GET",
				url: BASE_PATH+"/public/ajax/tablegenerator.php",
				data: { p: "getTableRuns", search: sample_ids },
				async: false,
				success : function(s)
				{
					console.log(s);
					for(var i = 0; i < s.length; i ++){
						if (run_ids[s[i].sample_id] == undefined) {
							if (s[i].run_name != null) {
								run_ids[s[i].sample_id] = [s[i].run_id, s[i].run_name, s[i].wkey];
							}
						}else{
							if (s[i].run_name != null) {
								run_ids[s[i].sample_id].push(s[i].run_id);
								run_ids[s[i].sample_id].push(s[i].run_name);
								run_ids[s[i].sample_id].push(s[i].wkey);
							}
						}
					}
				}});
		console.log(sample_ids);
		var sample_ids_array_int = sample_ids.split(',').map(Number);
		var sample_ids_array = sample_ids_array_int.map(String);
		
		sample_ids_array.sort(function(a, b){return a-b});
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/tablegenerator.php",
			data: { p: "getTableSamples", search: sample_ids },
			async: true,
			success : function(s)
			{
				console.log(s);
				runparams.fnClearTable();
				console.log(run_ids);
				console.log(sample_ids_array);
				console.log(Object.keys(run_ids));
				for(var i in sample_ids_array){
					if (Object.keys(run_ids).indexOf(sample_ids_array[i]) > -1) {
						var run_info = [];
						var wkey_passer = [];
						var run_select = '<select id="'+ sample_ids_array[i] + '_run_select" multiple class="form-control" size="3" onchange="optionChange(this)"><form>';
						for(var x = 0; x < run_ids[sample_ids_array[i]].length; x = x+3){
							if (run_ids[sample_ids_array[i]][x+2] != null) {
								//	Add wkey's to runID
								wkey_passer.push(run_ids[sample_ids_array[i]][x+2]);
								//	Sample id _ Run id _ Run name
								run_select += '<option id="' + run_ids[sample_ids_array[i]][x]+ '_' + run_ids[sample_ids_array[i]][x+1] + '" value="'+ run_ids[sample_ids_array[i]][x+2] + '">Run ' + run_ids[sample_ids_array[i]][x] + ': ' + run_ids[sample_ids_array[i]][x+1] + '</option>'
							}
						}
						run_select += '</form></select>';
						runHelper.push(s[i].id);
						selectionHelper.push(0);
						runparams.fnAddData([
							s[i].id,
							s[i].samplename,
							run_select,
							'<button id="sample_removal_'+s[i].id+'" class="btn btn-danger btn-xs pull-right" onclick="manageCreateChecklists(\''+s[i].id+'\', this)"><i class=\"fa fa-times\"></i></button>'
						]);
					}
				}
				checkCheckedList();
				reportSelection();
			}
		});
	}else if (window.location.href.split("/").indexOf('table') > -1){
		var table_params;
		$.ajax({ type: "GET",
			url: BASE_PATH +"/public/ajax/tablegenerator.php?",
			data: { p: "getGeneratedTable"},
			async: false,
			success : function(s)
			{
				console.log(s);
				table_params = s;
			}
		});
		document.getElementsByClassName('box-title')[0].innerHTML = "Table Generated - File: " + table_params['parameters'].split('file=')[1].split('&')[0]
		var beforeFormat = table_params.parameters.split('format=')[0];
		var json_obj = '';
		var export_table = document.getElementById('downloadOptions');
		var debrowser_string = '';
		if (beforeFormat.indexOf('rsem/') > -1 || beforeFormat.indexOf('mRNA') > -1 || beforeFormat.indexOf('tRNA') > -1) {
			if (table_params.file != null && table_params.file != '') {
				debrowser_string = '<li class="divider"></li>' +
					'<li><a onclick="sendTableToDebrowser(\''+API_PATH+'/public/tmp/files/'+table_params.file+'\')" style="cursor:pointer">Send to DEBrowser</a></li>';
			}else{
				debrowser_string = '<li class="divider"></li>' +
					'<li><a onclick="sendTableToDebrowser(\''+API_PATH+'/public/api/getsamplevals.php?'+beforeFormat+'format=json\')" style="cursor:pointer">Send to DEBrowser</a></li>';
			}
		}
		
		var ul = createElement('ul', ['class','role'],['dropdown-menu','menu']);
		var li = '<li><a onclick="changeTableType(\'json\', \''+beforeFormat+'\')" style="cursor:pointer">JSON link</a></li>';
		li += '<li><a onclick="changeTableType(\'json2\', \''+beforeFormat+'\')" style="cursor:pointer">JSON2 link</a></li>';
		li += '<li><a onclick="changeTableType(\'html\', \''+beforeFormat+'\')" style="cursor:pointer">HTML link</a></li>';
		li += '<li><a onclick="changeTableType(\'XML\', \''+beforeFormat+'\')" style="cursor:pointer">XML link</a></li>';
		li += debrowser_string;
		li += '<li class="divider"></li>';
		if (table_params.file != null && table_params.file != '') {
			li += '<li><a value="Download TSV" onclick="downloadGeneratedTSV(\''+beforeFormat+'\')" style="cursor:pointer">Download TSV</a></li>';
		}else{
			li += '<li><a value="Download TSV" onclick="downloadGeneratedTSV(\''+beforeFormat+'\')" style="cursor:pointer">Download TSV</a></li>';
		}
		
		ul.innerHTML = li;
		export_table.appendChild(ul);
		
		if (table_params.from_table_list == 'true') {
			document.getElementById('permissions_group').remove();
			document.getElementById('save_table').remove();
			document.getElementById('save_table_button').remove();
		}
	}else{
		if (window.location.href.split("/").indexOf('search') == -1) {
			var runparams = $('#jsontable_table_viewer').dataTable({
				//"scrollX": true
			});
			$.ajax({ type: "GET",
					url: BASE_PATH+"/public/ajax/tablegenerator.php",
					data: { p: "getCreatedTables", gids: phpGrab.gids},
					async: true,
					success : function(s)
					{
						console.log(s);
						runparams.fnClearTable();
						for(var x = 0; x < s.length; x++){
							var splitParameters = s[x].parameters.split('&');
							var splitSampleRuns = splitParameters[0].split('samples=')[1].split(":");
							var stringSampleRuns = '';
							for(var run in splitSampleRuns){
								if (run == splitSampleRuns.length - 1) {
									stringSampleRuns += 'Run: ' + splitSampleRuns[run].split(';')[1] + ' Samples: ' + splitSampleRuns[run].split(';')[0];
								}else{
									stringSampleRuns += 'Run: ' + splitSampleRuns[run].split(';')[1] + ' Samples: ' + splitSampleRuns[run].split(';')[0] + ' | ';
								}
							}
							var debrowser_string = '';
							var datafile = splitParameters[1].split('file=')[1].split(',').join(', ');
							if (datafile.indexOf('rsem') > -1 || datafile.indexOf('mRNA') > -1 || datafile.indexOf('tRNA') > -1) {
								if (s[x].file != null && s[x].file != '') {
									debrowser_string = '<li><a id="' + s[x].id+'" onclick="sendTableToDebrowser(\''+API_PATH+'/public/tmp/files/'+s[x].file+'\')">Send to DEBrowser</a></li>' +
										'<li class="divider"></li>';
								}else{
									debrowser_string = '<li><a id="' + s[x].id+'" onclick="sendTableToDebrowser(\''+API_PATH+'/public/api/getsamplevals.php?'+s[x].parameters+'\')">Send to DEBrowser</a></li>' +
										'<li class="divider"></li>';
								}
							}
							var file_str = ""
							if (s[x].file != '' && s[x].file != undefined) {
								file_str = '<li><a value="Download TSV" onclick="downloadCreatedTSV(\''+s[x].file+'\')">Download TSV</a></li>';
							}else{
								file_str = '<li><a value="Download TSV" onclick="downloadGeneratedTSV(\''+s[x].parameters+'\')">Download TSV</a></li>';
							}
							runparams.fnAddData([
								s[x].id,
								s[x].name,
								stringSampleRuns,
								splitParameters[1].split('file=')[1].split(',').join(', '),
								'<div class="btn-group pull-right">' +
									'<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Options <span class="fa fa-caret-down"></span></button>' +
									'<ul class="dropdown-menu" role="menu">' + 
										'<li><a id="' + s[x].id+'" onclick="sendToSavedTable(this.id)">View</a></li>' +
										'<li><a id="' + s[x].id+'" onclick="sendTableToPlot(\''+s[x].file+'\')">Plot Table</a></li>' +
										file_str + 
										'<li class="divider"></li>' +
										debrowser_string +
										'<li><a id="' + s[x].id+'" onclick="changeTableData(this.id)">Change Table Permissions</a></li>' +
										'<li class="divider"></li>' +
										'<li><a href="" id="' + s[x].id+'" onclick="deleteTable(this.id)">Delete</a></li>' +
									'</ul>'+
								'</div>'
							]);
						}
					}
			});
		}
	}
});
