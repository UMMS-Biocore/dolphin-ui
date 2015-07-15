/*
 *Author: Nicholas Merowsky
 *Date: 09 Apr 2015
 *Ascription:
 */

var wkey = '';
var lib_checklist = [];
var libraries = [];
var table_array = [];
var currentResultSelection = '--- Select a Result ---';
var tableDirectionNum = 0;

function parseTSV(jsonName, url_path){
	var parsedArray = [];
	$.ajax({ type: "GET",
			url: BASE_PATH + "/public/api/?source=" + API_PATH + "/public/pub/" + wkey + "/" + url_path,
			async: false,
			success : function(s)
			{
				for( var j = 0; j < s.length; j++){
					var parsed = [];
					parsed.push(s[j][jsonName]);
					parsedArray.push(parsed);
				}
			}
	});
	return parsedArray;
}

function parseMoreTSV(jsonNameArray, url_path){
	var parsedArray = [];
	console.log(BASE_PATH + "/public/api/?source=" + API_PATH + "/public/pub/" + wkey + "/" + url_path);
	$.ajax({ type: "GET",
			url: BASE_PATH + "/public/api/?source=" + API_PATH + "/public/pub/" + wkey + "/" + url_path,
			async: false,
			success : function(s)
			{
				for( var j = 0; j < s.length; j++){
					var parsed = [];
					for(var k = 0; k < jsonNameArray.length; k++){
						parsed.push(s[j][jsonNameArray[k]]);
					}
					parsedArray.push(parsed);
				}
			}
	});
	return parsedArray;
}

function createSummary(fastqc_summary) {
	if (fastqc_summary) {
		var linkRef = [ '/per_base_quality.html', '/per_base_sequence_content.html', '/per_sequence_quality.html'];
		var linkRefName = ['Per Base Quality Summary', 'Per Base Sequence Content Summary', 'Per Sequence Quality Summary'];
	
		var masterDiv = document.getElementById('summary_exp_body');
	
		for(var x = 0; x < linkRefName.length; x++){
			var link = createElement('a', ['href'], [BASE_PATH + '/public/pub/' + wkey + '/fastqc/UNITED' + linkRef[x]]);
			link.appendChild(document.createTextNode(linkRefName[x]));
			masterDiv.appendChild(link);
			masterDiv.appendChild(createElement('div', [],[]));
		}
	}
}

function createDetails(libraries) {
	var masterDiv = document.getElementById('details_exp_body');
	var hrefSplit = window.location.href.split("/");
	var runId = hrefSplit[hrefSplit.length - 2];
	var pairCheck = findIfMatePaired(runId);
	
	for(var x = 0; x < libraries.length; x++){
		if (pairCheck) {
			var link1 = createElement('a', ['href'], [BASE_PATH + '/public/pub/' + wkey + '/fastqc/' + libraries[x] + '.1/' + libraries[x] + '.1_fastqc/fastqc_report.html']);
			link1.appendChild(document.createTextNode(libraries[x] + ".1"));
			var link2 = createElement('a', ['href'], [BASE_PATH + '/public/pub/' + wkey + '/fastqc/' + libraries[x] + '.2/' + libraries[x] + '.2_fastqc/fastqc_report.html']);
			link2.appendChild(document.createTextNode(libraries[x] + ".2"));
			masterDiv.appendChild(link1);
			masterDiv.appendChild(createElement('div', [],[]));
			masterDiv.appendChild(link2);
			masterDiv.appendChild(createElement('div', [],[]));
		}else{
			var link = createElement('a', ['href'], [BASE_PATH + '/public/pub/' + wkey + '/fastqc/' + libraries[x] + '/' + libraries[x] + '_fastqc/fastqc_report.html']);
			link.appendChild(document.createTextNode(libraries[x]));
			masterDiv.appendChild(link);
			masterDiv.appendChild(createElement('div', [],[]));
		}
		
		
	}
}

/* checkFrontAndEndDir function
 *
 * checks to make sure that the outdir specified has
 * both '/'s on either end in order to be used by whichever
 * function requires the addition of the outdir
 */

function checkFrontAndEndDir(wkey){
	if (wkey[0] != '/') {
		wkey = '/' + wkey;
	}
	if (wkey[wkey.length - 1] != '/') {
		wkey = wkey + '/';
	}
	return wkey;
}

function cleanReports(reads, totalReads){
	var perc = (reads/totalReads).toFixed(3);
	var stringPerc = "" + reads + " (" + perc + "%)";
	return stringPerc;
}

function storeLib(name){
	if (lib_checklist.indexOf(name) > -1) {
		lib_checklist.splice(lib_checklist.indexOf(name), 1);
	}else{
		lib_checklist.push(name);
	}
}

function createDropdown(mapping_list, type){
	var masterDiv = document.getElementById(type+'_exp_body');
	var childDiv = createElement('div', ['id', 'class'], ['select_'+ type + '_div', 'input-group margin col-md-12']);
	var selectDiv = createElement('div', ['id', 'class'], ['inner_select_' + type + '_div', 'margin col-md-4']);

	selectDiv.appendChild( createElement('select',
					['id', 'class', 'onchange', 'OPTION_DIS_SEL'],
					['select_' + type + '_report', 'form-control', 'showTable("'+type+'")', '--- Select a Result ---']));
	childDiv.appendChild(selectDiv);
	masterDiv.appendChild(childDiv);
	for (var x = 0; x < mapping_list.length; x++){
		var opt = createElement('option', ['id','value'], [mapping_list[x], mapping_list[x]]);
		opt.innerHTML = mapping_list[x];
		document.getElementById('select_' + type + '_report').appendChild(opt);
	}
}

function obtainObjectKeys(obj){
	var keys = [];
	for (var key in obj) {
		if (obj.hasOwnProperty(key)) {
			keys.push(key)
		}
	}
	return keys;
}

function showTable(type){
	var ordered_lib_checklist = [];
	for (var x = 0; x < libraries.length; x++){
		if (lib_checklist.indexOf(libraries[x]) != -1) {
			ordered_lib_checklist.push(libraries[x]);
		}
	}
	lib_checklist = ordered_lib_checklist;
	temp_libs = [null];
	if (lib_checklist.length <= 0) {
		temp_libs = lib_checklist;
		lib_checklist = libraries;
	}
	
	currentResultSelection = document.getElementById('select_' + type + '_report').value;
	var temp_currentResultSelection;
	var objList;
	
	if (type == 'initial_mapping') {
		temp_currentResultSelection = 'counts/' + currentResultSelection + '.counts.tsv&fields=id,' + lib_checklist.toString();
	}else if (type == 'RSEM'){
		temp_currentResultSelection = currentResultSelection;
	}else if (type == 'DESEQ') {
		temp_currentResultSelection = currentResultSelection;
	}
	console.log(BASE_PATH + "/public/api/?source=" + API_PATH + '/public/pub/' + wkey + '/' + temp_currentResultSelection);
	$.ajax({ type: "GET",
			url: BASE_PATH + "/public/api/?source=" + API_PATH + '/public/pub/' + wkey + '/' + temp_currentResultSelection,
			async: false,
			success : function(s)
			{
				objList = s;
			}
	});
	var keys = obtainObjectKeys(objList[0]);
	
	if(currentResultSelection.split(".")[currentResultSelection.split(".").length - 1] == "tsv" || currentResultSelection.substring(currentResultSelection.length - 3, currentResultSelection.length) == "RNA" || currentResultSelection == 'ercc'){
		var masterDiv = document.getElementById(type+'_exp_body');
		var tableDiv = createElement('div', ['id', 'class'], [type+'_table_div', 'panel panel-default margin']);
		var selectDiv = document.getElementById('select_'+type+'_div');
		if (document.getElementById('jsontable_' + type + '_results') == null) {
			var previous_button = false;
			if (document.getElementById('clear_' + type + '_button_div') != null) {
				previous_button = true;
			}
			var buttonDiv = createElement('div', ['id', 'class'], ['clear_' + type + '_button_div', 'margin col-md-4']);
			buttonDiv.appendChild(createDownloadReportButtons(currentResultSelection, type));
			if (previous_button) {
				$('#clear_' + type + '_button_div').replaceWith(buttonDiv);
			}else{
				selectDiv.appendChild(buttonDiv);
			}
			var table = generateSelectionTable(keys, type);
			tableDiv.appendChild(table)
			masterDiv.appendChild(tableDiv);
		}else{
			document.getElementById(type+'_table_div').remove();
			document.getElementById('template_'+type).remove();
			
			tableDiv = createElement('div', ['id', 'class'], [type+'_table_div', 'panel panel-default margin']);
			var table = generateSelectionTable(keys, type);
			tableDiv.appendChild(table)
			masterDiv.appendChild(tableDiv);
		}
		
		createStreamScript(keys, type)
		var data = objList, html = $.trim($("#template_"+type).html()), template = Mustache.compile(html);
		var view = function(record, index){
			return template({record: record, index: index});
		};
		
		var callbacks = {
			after_add: function(){
				//Only for example: Stop ajax streaming beacause from localfile data size never going to empty.
				if (this.data.length == objList.length){
					this.stopStreaming();
				}
		
			}
		}
		st = StreamTable('#jsontable_' + type + '_results',
		  { view: view, 
			per_page: 10, 
			data_url: BASE_PATH + "/public/api/?source=" + API_PATH + '/public/pub/' + wkey + '/' + temp_currentResultSelection,
			stream_after: 0.2,
			fetch_data_limit: 100,
			callbacks: callbacks,
			pagination:{
				span: 5,                              
				next_text: 'Next &rarr;',              
				prev_text: '&larr; Previous',
				ul_class: type,
			},
		  },
		 data, type);
		
		var search = document.getElementById('st_search');
		search.id = 'st_search_' + type;
		search.setAttribute('class',"st_search margin pull-right");
		
		var num_search = document.getElementById('st_num_search');
		num_search.id = 'st_num_search_' + type;
		
		var newlabel = createElement('label', ['class'], ['margin']);
		newlabel.setAttribute("for",'st_num_search_'+type);
		newlabel.innerHTML = " entries per page";
		document.getElementById(type+'_table_div').insertBefore(newlabel, num_search);
		
		num_search.setAttribute('class',"st_per_page margin pull-left");
		
		document.getElementById('st_pagination').id = 'st_pagination_' + type;
		document.getElementById('st_pagination_'+type).setAttribute('class',"st_pagination_"+type+" margin");
		document.getElementById('st_pagination_'+type).setAttribute('style',"text-align:right");
		
		if (temp_libs.length <= 0) {
			lib_checklist = [];
		}
	}else{
		var masterDiv = document.getElementById('select_'+type+'_div');
		if (document.getElementById('clear_' + type + '_button_div') == null) {
			var buttonDiv = createElement('div', ['id', 'class'], ['clear_' + type + '_button_div', 'margin col-md-4']);
			buttonDiv.appendChild(createDownloadReportButtons(currentResultSelection, type));
			masterDiv.appendChild(buttonDiv);
			if (document.getElementById('jsontable_' + type + '_results_wrapper') != null) {
				document.getElementById('jsontable_' + type + '_results_wrapper').remove();
			}
		}else{
			var buttonDiv = createElement('div', ['id', 'class'], ['clear_' + type + '_button_div', 'margin col-md-4']);
			buttonDiv.appendChild(createDownloadReportButtons(currentResultSelection, type));
			$('#clear_' + type + '_button_div').replaceWith(buttonDiv);
			if (document.getElementById('jsontable_' + type + '_results_wrapper') != null) {
				document.getElementById('jsontable_' + type + '_results_wrapper').remove();
			}
		}
	}
	
}

function createStreamScript(keys, type){
	var masterScript = createElement('script', ['id', 'type'], ['template_'+type, 'text/html']);
	var tr = createElement('tr', [], []);
	
	for(var x = 0; x < keys.length; x++){
		var td = createElement('td', [], []);
		td.innerHTML = "{{record."+keys[x]+"}}";
		tr.appendChild(td);
	}
	masterScript.appendChild(tr);
	document.getElementsByTagName('body')[0].appendChild(masterScript);
}

function clearSelection(type){
	if (document.getElementById('jsontable_' + type + '_results_wrapper') != null) {
		document.getElementById('jsontable_' + type + '_results_wrapper').remove();
	}
	document.getElementById('clear_' + type + '_button_div').remove();
	document.getElementById(type+'_table_div').remove();
	document.getElementById('select_' + type + '_report').value = '--- Select a Result ---';
}

function generateSelectionTable(keys, type){
	var newTable = createElement('table', ['id', 'class'], ['jsontable_' + type + '_results', 'table table-hover compact']);
	var thead = createElement('thead', [], []);
	var tbody = createElement('tbody', [], []);
	var header = createElement('tr', ['id'], [type + '_header']);
	var temp_lib_checklist;
	if (lib_checklist.length == 0) {
		temp_lib_checklist = libraries;
	}else{
		temp_lib_checklist = lib_checklist;
	}
	
	if (type == 'initial_mapping') {
		for(var x = 0; x < keys.length; x++){
			if (temp_lib_checklist.indexOf(keys[x]) > -1) {
				var th = createElement('th', ['data-sort', 'onclick'], [keys[x]+'::number', 'shiftColumns(this)']);
				th.innerHTML = keys[x];
				th.appendChild(createElement('i', ['id', 'class'], [keys[x], 'pull-right fa fa-unsorted']));
				header.appendChild(th);
			}else if (keys[x] == "id" || keys[x] == "name" || keys[x] == "len" || keys[x] == "gene" || keys[x] == "transcript") {
				var th = createElement('th', ['data-sort', 'onclick'], [keys[x]+'::string', 'shiftColumns(this)']);
				th.innerHTML = keys[x];
				th.appendChild(createElement('i', ['id', 'class'], [keys[x], 'pull-right fa fa-unsorted']));
				header.appendChild(th);
			}
		}
	}else{
		for(var x = 0; x < keys.length; x++){
			if (libraries.indexOf(keys[x]) > -1 || keys[x] == 'padj' || keys[x] == 'log2FoldChange' || keys[x] == 'foldChange') {
				var th = createElement('th', ['data-sort', 'onclick'], [keys[x]+'::number', 'shiftColumns(this)']);
			}else{
				var th = createElement('th', ['data-sort', 'onclick'], [keys[x]+'::string', 'shiftColumns(this)']);
			}
			th.innerHTML = keys[x];
			th.appendChild(createElement('i', ['id', 'class'], [keys[x], 'pull-right fa fa-unsorted']));
			header.appendChild(th);
		}
	}
	
	thead.appendChild(header);
	newTable.appendChild(thead);
	newTable.appendChild(tbody);
	return newTable;
}

function shiftColumns(id){
	if (id.childNodes[1].getAttribute('class') == 'pull-right fa') {
		id.childNodes[1].setAttribute('class', 'pull-right fa fa-sort-asc');
	}else if (id.childNodes[1].getAttribute('class') == 'pull-right fa fa-sort-asc') {
		id.childNodes[1].setAttribute('class','pull-right fa fa-sort-desc');
	}else{
		id.childNodes[1].setAttribute('class','pull-right fa fa-sort-asc');
	}
	
}

function createDownloadReportButtons(currentSelection, type){
	var downloadDiv = createElement('div', ['id', 'class'], ['downloads_' + type + '_div', 'btn-group']);
	var ul = createElement('ul', ['class', 'role'], ['dropdown-menu', 'menu']);
	var button = createElement('button', ['type', 'class', 'data-toggle', 'aria-expanded'], ['button', 'btn btn-primary dropdown-toggle', 'dropdown', 'true'])
	button.innerHTML = 'Select Data Options ';
	var span = createElement('span', ['class'], ['fa fa-caret-down']);
	button.appendChild(span);
	
	if(currentResultSelection.split(".")[currentResultSelection.split(".").length - 1] == "tsv" || currentResultSelection.substring(currentResultSelection.length - 3, currentResultSelection.length) == "RNA" || currentResultSelection == 'ercc'){
		var buttonType = ['JSON','JSON2', 'XML', 'HTML'];
		for (var x = 0; x < buttonType.length; x++){
			var li = createElement('li', [], []);
			var a = createElement('a', ['onclick', 'style'], ['downloadReports("'+buttonType[x]+'", "'+type+'")', 'cursor:pointer']);
			a.innerHTML = buttonType[x] + ' link';
			li.appendChild(a);
			ul.appendChild(li);
		}
		ul.appendChild(createElement('li', ['class'], ['divider']));
	}
	var TSV = createElement('li', [], []);
	var TSV_a = createElement('a', ['value', 'onclick', 'style'], ['Download File', 'downloadTSV("'+type+'")', 'cursor:pointer']);
	TSV_a.innerHTML = 'Download File';
	TSV.appendChild(TSV_a);
	ul.appendChild(TSV);
	ul.appendChild(createElement('li', ['class'], ['divider']));
	var clear = createElement('li', [], []);
	var clear_a = createElement('a', ['value', 'onclick', 'style'], ['Clear Selection', 'clearSelection("'+type+'")', 'cursor:pointer']);
	clear_a.innerHTML = 'Clear Selection';
	clear.appendChild(clear_a);
	ul.appendChild(clear);
	
	downloadDiv.appendChild(button);
	downloadDiv.appendChild(ul);

	return downloadDiv;
}

function downloadReports(buttonType, type){
	var temp_currentResultSelection;
	if (type == 'initial_mapping') {
		temp_currentResultSelection = 'counts/' + currentResultSelection + '.counts.tsv';
	}else if (type == 'RSEM'){
		temp_currentResultSelection = currentResultSelection;
	}else if (type == 'DESEQ') {
		temp_currentResultSelection = currentResultSelection;
	}
	var URL = BASE_PATH + "/public/api/?source=" + API_PATH + '/public/pub/' + wkey + '/' + temp_currentResultSelection + '&format=' + buttonType;
	window.open(URL);
}

function downloadTSV(type){
	var temp_currentResultSelection;
	if (type == 'initial_mapping') {
		temp_currentResultSelection = 'counts/' + currentResultSelection + '.counts.tsv';
	}else{
		temp_currentResultSelection = currentResultSelection;
	}
	var URL = BASE_PATH + '/public/pub/' + wkey + '/' + temp_currentResultSelection
	window.open(URL, '_blank');
}

function getReportWKey(run_id){
	var wkey = "";
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/ngsquerydb.php",
			data: { p: 'getWKey', run_id: run_id },
			async: false,
			success : function(s)
			{
				wkey = s[0].wkey;
			}
	});
	return wkey;
}

function sendToPlots(){
	sendWKey(wkey);
	window.location.href = BASE_PATH+ '/plot';
}

function numberWithCommas(x) {
	return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

$(function() {
	"use strict";
	if (phpGrab.theSegment == 'report') {

	var hrefSplit = window.location.href.split("/");
	var runId = hrefSplit[hrefSplit.length - 2];
	wkey = getReportWKey(runId);
	var samples = hrefSplit[hrefSplit.length - 1].substring(0, hrefSplit[hrefSplit.length - 1].length - 1).split(",");
	
	var summary_files = [];
	var count_files = [];
	var RSEM_files = [];
	var DESEQ_files = [];
	
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/ngsquerydb.php",
			data: { p: 'getReportList', wkey: wkey },
			async: false,
			success : function(s)
			{
				for(var x = 0; x < s.length; x++){
					if(s[x].type == 'rsem'){
						RSEM_files.push(s[x]);
					}else if (s[x].type == 'deseq'){
						DESEQ_files.push(s[x]);
					}else if (s[x].type == 'summary') {
						summary_files.push(s[x]);
					}else if (s[x].type == 'counts'){
						count_files.push(s[x]);
					}
				}
			}
	});
	
	var summary_rna_type = [];
	for (var z = 0; z < summary_files.length; z++) {
		summary_rna_type.push(summary_files[z]['file'].split("/")[summary_files[z]['file'].split("/").length - 1].split(".")[0]);
	}
	for (var z = 0; z < summary_files.length; z++) {
		document.getElementById('tablerow').appendChild(createElement('th', ['id'], [summary_rna_type[z]]));
		document.getElementById(summary_rna_type[z]).innerHTML = summary_rna_type[z];
	}
	
	var samplenames = [];
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/ngsquerydb.php",
			data: { p: 'getSampleNames', samples: samples.toString() },
			async: false,
			success : function(s)
			{
				for(var x  = 0; x < s.length; x++){
					if (s[x].samplename == null) {
						libraries.push(s[x].name);
					}else{
						libraries.push(s[x].samplename);
					}
				}
				for(var x  = 0; x < s.length; x++){
					samplenames.push(s[x].samplename);
				}
			}
	});
	
	var read_counts = [];
	
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/initialmappingdb.php",
			data: { p: 'getCounts', samples: samples.toString() },
			async: false,
			success : function(s)
			{
				for(var x  = 0; x < s.length; x++){
					read_counts.push(s[x].total_reads);
				}
			}
	});
	
	console.log(summary_files);
	
	if (summary_files.length > 0) {
		document.getElementById('tablerow').appendChild(createElement('th', ['id'], ['unused']));
		document.getElementById('unused').innerHTML = 'Reads Left';
		document.getElementById('tablerow').appendChild(createElement('th', ['id'], ['selection']));
		document.getElementById('selection').innerHTML = 'Selected';
		for (var z = 0; z < summary_files.length; z++) {
			if (z == 0){
				if (summary_files.length == 1) {
					var table_array_raw = (parseMoreTSV(['File','Total Reads','Reads 1','Reads >1','Unmapped Reads'], summary_files[z]['file']));
					for(var x = 0; x < table_array_raw.length; x++){
						var table_array_push = [table_array_raw[x][0], table_array_raw[x][1], parseInt(table_array_raw[x][2].split(" ")[0]) + parseInt(table_array_raw[x][3].split(" ")[0]), table_array_raw[x][4].split(" ")[0]];
						table_array.push(table_array_push);
					}
				}else{
					var table_array_raw = (parseMoreTSV(['File','Total Reads','Reads 1','Reads >1'], summary_files[z]['file']));
					for(var x = 0; x < table_array_raw.length; x++){
						var table_array_push = [table_array_raw[x][0], table_array_raw[x][1], parseInt(table_array_raw[x][2].split(" ")[0]) + parseInt(table_array_raw[x][3].split(" ")[0])];
						table_array.push(table_array_push);
					}
				}
			}else if (z == summary_files.length - 1) {
				console.log(summary_files[z]['file']);
				var parsed_add = parseMoreTSV(['Reads 1','Reads >1','Unmapped Reads'], summary_files[z]['file']);
				console.log(parsed_add);
				for(var x = 0; x < table_array.length; x ++){
					var concat_array = table_array[x];
					table_array[x] = concat_array.concat([parseInt(parsed_add[x][0].split(" ")[0]) + parseInt(parsed_add[x][1].split(" ")[0]), parsed_add[x][2].split(" ")[0]]);
				}
			}else{
				var parsed_add = parseMoreTSV(['Reads 1','Reads >1'], summary_files[z]['file']);
				for(var x = 0; x < table_array.length; x ++){
					var concat_array = table_array[x];
					table_array[x] = concat_array.concat([ parseInt(parsed_add[x][0].split(" ")[0]) + parseInt(parsed_add[x][1].split(" ")[0]) ]);
				}
			}
		}
		
		var separator = 3;
		if (table_array.length == 1) {
			separator = 4;
		}
		//Initial Mapping Results
		var reports_table = $('#jsontable_initial_mapping').dataTable();
		reports_table.fnClearTable();
		for (var x = 0; x < (table_array.length); x++) {
			var row_array = table_array[x];
			var reads_total = row_array[1];
			row_array[1] = numberWithCommas(row_array[1]);
			for(var y = 2; y < row_array.length; y++){
				row_array[y] = numberWithCommas(row_array[y] + " (" + ((row_array[y]/reads_total)*100).toFixed(2) + " %)");
			}
			row_array.push("<input type=\"checkbox\" class=\"ngs_checkbox\" name=\"" + row_array[0] + "\" id=\"lib_checkbox_"+x+"\" onClick=\"storeLib(this.name)\">");
			reports_table.fnAddData(row_array);
		}
		createDropdown(summary_rna_type, 'initial_mapping');
	}else if (read_counts.length > 0) {
		var reports_table = $('#jsontable_initial_mapping').dataTable();
		reports_table.fnClearTable();
		for(var y = 0; y < read_counts.length; y++){
			if (samplenames[y] == '' || samplenames[y] == null || samplenames[y] == undefined) {
				reports_table.fnAddData([libraries[y], numberWithCommas(read_counts[y])]);
			}else{
				reports_table.fnAddData([samplenames[y], numberWithCommas(read_counts[y])]);
			}
		}
	}else{
		document.getElementById('send_to_plots').disabled = true;
		document.getElementById('initial_mapping_exp').remove();
	}
	
	//Create a check for FASTQC output????
	if (getFastQCBool(runId)) {
		createSummary(true);
		createDetails(libraries);
	}else{
		document.getElementById('summary_exp').remove();
		document.getElementById('details_exp').remove();
	}
	
	if (DESEQ_files.length > 0) {
		var deseq_file_paths = [];
		for (var z = 0; z < DESEQ_files.length; z++){
			deseq_file_paths.push(DESEQ_files[z].file);
		}
		createDropdown(deseq_file_paths, 'DESEQ');
	}else{
		document.getElementById('DESEQ_exp').remove();
	}
	
	if (RSEM_files.length > 0) {
		var rsem_file_paths = [];
		for (var z = 0; z < RSEM_files.length; z++){
			rsem_file_paths.push(RSEM_files[z].file);
		}
		createDropdown(rsem_file_paths, 'RSEM');
	}else{
		document.getElementById('RSEM_exp').remove();
	}
	//reports_table.fnAdjustColumnSizing(true);
	}
});
