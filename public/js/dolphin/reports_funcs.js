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
var table_data = {};
var type_dictionary = ['rRNA', 'miRNA', 'piRNA', 'tRNA', 'snRNA', 'rmsk', 'ercc'];
var initial_mapping_table = [];

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
	console.log(BASE_PATH + "/public/api/?source=" + API_PATH + "/public/pub/" + wkey + "/" + url_path);
	var parsedArray = [];
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

function parseFlagstat(url_path) {
	var mapped = '';
	$.ajax({ type: "GET",
			url: BASE_PATH + "/public/pub/" + wkey + "/" + url_path,
			async: false,
			success : function(s)
			{
				mapped = s.split("\n")[4].split(" ")[0];
			}
	});
	return mapped;
}

function parseDedup(url_path) {
	var dedup = {};
	$.ajax({ type: "GET",
			url: BASE_PATH + "/public/pub/" + wkey + "/" + url_path,
			async: false,
			success : function(s)
			{
				console.log(s);
				var array = s.split("\n");
				for (var x = 0; x < array.length; x++) {
					var name = array[x].split("/")[array[x].split("/").length - 1].split("PCR_duplicates")[0];
                    name = name.slice(0,-1);
					dedup[name] = array[x].split(" ")[1]
				}
			}
	});
	return dedup;
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
	var run_id = '0';
	$.ajax({ type: "GET",
		url: BASE_PATH +"/ajax/sessionrequests.php",
		data: { p: 'getReportsRunID' },
		async: false,
		success : function(s)
		{
			var returnedSamples = s.split(',');
			console.log(returnedSamples);
			for(var x = 0; x < s.length; x++){
				if (x == 0) {
					run_id = returnedSamples[x];
				}
			}
		}
	});
	var wkey = getReportWKey(run_id);
	var pairCheck = findIfMatePaired(run_id);
	
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
		if (type_dictionary.indexOf(mapping_list[x]) == -1) {
			type_dictionary.push(mapping_list[x]);
		}
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
		console.log(BASE_PATH + "/public/api/?source=" + API_PATH + '/public/pub/' + wkey + '/' + temp_currentResultSelection);
	}else{
		temp_currentResultSelection = currentResultSelection;
	}
	
	$.ajax({ type: "GET",
			url: BASE_PATH + "/public/api/?source=" + API_PATH + '/public/pub/' + wkey + '/' + temp_currentResultSelection,
			async: false,
			success : function(s)
			{
				objList = s;
			}
	});
	for(var d = 0; d < objList.length; d++){
		var keys = obtainObjectKeys(objList[d]);
		var newObj = {};
		for(var c = 0; c < keys.length; c++){
			if(!isNaN(parseFloat(keys[c][0])) && isFinite(keys[c][0])){
				keys[c] = "_" + keys[c];
			}
			newObj[keys[c].replace(/\./g, "_")] = objList[d][keys[c]];
		}
		objList[d] = newObj
	}
	var keys = obtainObjectKeys(objList[0]);
	console.log(keys)
	
	if(currentResultSelection.split(".")[currentResultSelection.split(".").length - 1] == "tsv" || type_dictionary.indexOf(currentResultSelection) > -1){
		var masterDiv = document.getElementById(type+'_exp_body');
		var tableDiv = createElement('div', ['id', 'class', 'style'], [type+'_table_div', 'panel panel-default margin', 'overflow-x:scroll']);
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
			
			tableDiv = createElement('div', ['id', 'class', 'style'], [type+'_table_div', 'panel panel-default margin', 'overflow-x:scroll']);
			var table = generateSelectionTable(keys, type);
			tableDiv.appendChild(table)
			masterDiv.appendChild(tableDiv);
		}
		
		createStreamScript(keys, type)
		var data = objList, html = $.trim($("#template_"+type).html()), template = Mustache.compile(html);
		console.log(keys);
		var view = function(record, index){
			var mergeRecords = '<tr>';
			for(var x = 0; x < keys.length; x++){
				mergeRecords += '<td>';
				mergeRecords += record[keys[x]];
				mergeRecords += '</td>';
			}
			mergeRecords += '</tr>';
			return mergeRecords;
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
		
		if (type == 'rseqc' && /counts.tsv/.test(currentResultSelection)) {
			var name_gather_bool = true;
			var rseqc_categories = [];
			var rseqc_series = [];
			for (var cat in objList) {
				var series_object = {};
				var data_array = [];
				for (ser in objList[cat]) {
					if (ser == 'region') {
						series_object['name'] = objList[cat][ser];
					}else{
						if (name_gather_bool) {
							rseqc_categories.push(ser);
						}
						data_array.push(parseInt(objList[cat][ser]));
					}
				}
				name_gather_bool = false;
				series_object['data'] = data_array;
				rseqc_series.push(series_object);
			}
			
			document.getElementById('rseqc_table_div').appendChild(createElement('button', ['id', 'class', 'onclick'], ['switch_plot', 'btn btn-primary margin', 'switchStacking("rseqc_table_div", "rseqc_plot")']))
			document.getElementById('switch_plot').innerHTML = 'Switch Plot Type';
			createHighchart(rseqc_categories, rseqc_series, 'RSeQC Count Results', 'Comparitive Sample Percentages', 'rseqc_table_div', 'rseqc_plot', 'percent');
			showHighchart('rseqc_table_div');
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
		if(currentResultSelection.split("/")[0] == 'rsem' || currentResultSelection == 'mRNA' || currentResultSelection == 'tRNA'){
			var li = createElement('li', [], []);
			var a = createElement('a', ['onclick', 'style'], ['downloadReports("debrowser", "'+type+'")', 'cursor:pointer']);
			a.innerHTML = 'Send to DEBrowser';
			li.appendChild(a);
			ul.appendChild(li);
			ul.appendChild(createElement('li', ['class'], ['divider']));
		}
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
		temp_currentResultSelection = 'counts/' + document.getElementById('select_'+type+'_report').value + '.counts.tsv';
	}else{
		temp_currentResultSelection = document.getElementById('select_'+type+'_report').value;
	}
	var URL = API_PATH + '/public/api/?source=' + API_PATH + '/public/pub/' + wkey + '/' + temp_currentResultSelection + '&format=' + buttonType;
	if (buttonType == 'debrowser') {
        URL = API_PATH + '/public/api/?source=' + API_PATH + '/public/pub/' + wkey + '/' + temp_currentResultSelection + '&format=JSON';
		$.ajax({ type: "GET",
                        url: BASE_PATH+"/public/ajax/sessionrequests.php",
                        data: { p: "sendToDebrowser", table: URL },
                        async: false,
                        success : function(s)
                        {
                        }
        });
        window.location.href = BASE_PATH + '/debrowser';
    }else{
		window.open(URL);
	}
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
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/sessionrequests.php",
			data: { p: "setPlotToggle", type: 'normal', file: '' },
			async: false,
			success : function(s)
			{
			}
	});
	window.location.href = BASE_PATH+ '/plot';
}

function numberWithCommas(x) {
	return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function populateTable(summary_files, samplenames, libraries, read_counts) {
	var RNA_types = [];
	for (var z = 0; z < summary_files.length; z++) {
		if (!/pcrdups/.test(summary_files[z]['file']) && !/flagstat/.test(summary_files[z]['file'])) {
			console.log(summary_files[z]['file'])
			RNA_types.push(summary_files[z]['file'].split("/")[1].split(".")[0]);
		}
	}
	if (summary_files.length > 0) {
		for (var z = 0; z < samplenames.length; z++) {
			table_data[samplenames[z]] = {};
			table_data[samplenames[z]]['total_reads'] = read_counts[z];
		}
		
		for (var z = 0; z < summary_files.length; z++) {
			if (!/pcrdups/.test(summary_files[z]['file']) && !/flagstat/.test(summary_files[z]['file'])) {
				var RNA_name = '';
				for (RNA in RNA_types) {
					if (RNA_types[RNA] == summary_files[z]['file'].split("/")[1].split(".")[0]) {
						RNA_name = RNA_types[RNA];
					}
				}
				console.log(RNA_types)
				var table_array_raw = (parseMoreTSV(['File','Total Reads','Reads 1','Reads >1','Unmapped Reads'], summary_files[z]['file']));
				for(var x = 0; x < table_array_raw.length; x++){
					table_data[table_array_raw[x][0]][RNA_name] = parseInt(table_array_raw[x][2].split(" ")[0]) + parseInt(table_array_raw[x][3].split(" ")[0]);
					table_data[table_array_raw[x][0]]['unmapped'] = parseInt(table_array_raw[x][4].split(" ")[0]);
				}
			}else if (/pcrdups/.test(summary_files[z]['file'])){
				dedup = parseDedup(summary_files[z]['file']);
				for (var k = 0; k < samplenames.length; k++) {
					if (/rsem/.test(summary_files[z]['file'])){
						table_data[samplenames[k]]['rsem_dedup'] = Math.floor(dedup[samplenames[k]] * table_data[samplenames[k]]['total_reads']);
					}else if (/tophat/.test(summary_files[z]['file'])){
						table_data[samplenames[k]]['tophat_dedup'] = Math.floor(dedup[samplenames[k]] * table_data[samplenames[k]]['total_reads']);
					}else if (/chip/.test(summary_files[z]['file'])){
						table_data[samplenames[k]]['chip_dedup'] = Math.floor(dedup[samplenames[k]] * table_data[samplenames[k]]['total_reads']);
					}
				}
			}else if (/flagstat/.test(summary_files[z]['file'])){
				if (/rsem/.test(summary_files[z]['file'])){
					table_data[summary_files[z]['file'].split("/")[1].split(".")[0]]['rsem'] = parseFlagstat(summary_files[z]['file']);
				}else if (/tophat/.test(summary_files[z]['file'])){
					table_data[summary_files[z]['file'].split("/")[1].split(".")[0]]['tophat'] = parseFlagstat(summary_files[z]['file']);
				}else if (/chip/.test(summary_files[z]['file'])){
					table_data[summary_files[z]['file'].split("/")[1].split(".")[0]]['chip'] = parseFlagstat(summary_files[z]['file']);
				}
			}
		}
		
		//Initial Mapping Results
		var reports_table = $('#jsontable_initial_mapping').dataTable();
		reports_table.fnClearTable();
		document.getElementById('jsontable_initial_mapping').setAttribute('style','overflow-x:scroll');
		for (key in table_data) {
			sample_data = table_data[key];
			row_array = [key];
			if (sample_data['total_reads'] != undefined) { row_array.push(sample_data['total_reads']) }
			for (RNA in RNA_types) {
				if (sample_data[RNA_types[RNA]] != undefined) { row_array.push(sample_data[RNA_types[RNA]]) }
			}
			if (sample_data['unmapped'] != undefined) { row_array.push(sample_data['unmapped']) }
			row_array = checkTableOutput(sample_data['rsem'], 'Transcriptomic Reads Aligned (RSEM)', row_array);
			row_array = checkTableOutput(sample_data['rsem_dedup'], 'Deduplicated Reads (RSEM)', row_array);
			row_array = checkTableOutput(sample_data['tophat'], 'Genomic Reads Aligned (Tophat)', row_array);
			row_array = checkTableOutput(sample_data['tophat_dedup'], 'Deduplicated Reads (Tophat)', row_array);
			row_array = checkTableOutput(sample_data['chip'], 'Genomic Reads Aligned (Chip)', row_array);
			row_array = checkTableOutput(sample_data['chip_dedup'], 'Deduplicated Reads (Chip)', row_array);
			
			var reads_total = row_array[1];
			row_array[1] = numberWithCommas(row_array[1]);
			for(var y = 2; y < row_array.length; y++){
				row_array[y] = numberWithCommas(row_array[y] + " (" + ((row_array[y]/reads_total)*100).toFixed(2) + " %)");
			}
			console.log(row_array)
			reports_table.fnAddData(row_array);
			initial_mapping_table.push(row_array);
		}
	}else if (read_counts.length > 0) {
		var reports_table = $('#jsontable_initial_mapping').dataTable();
		reports_table.fnClearTable();
		for(var y = 0; y < read_counts.length; y++){
			if (samplenames[y] == '' || samplenames[y] == null || samplenames[y] == undefined) {
				reports_table.fnAddData([libraries[y], numberWithCommas(read_counts[y])]);
				initial_mapping_table.push([libraries[y], numberWithCommas(read_counts[y])]);
			}else{
				reports_table.fnAddData([samplenames[y], numberWithCommas(read_counts[y])]);
				initial_mapping_table.push([samplenames[y], numberWithCommas(read_counts[y])])
			}
		}
	}else{
		document.getElementById('empty_div').innerHTML = '<h3 class="text-center">Your results have not been generated yet.  If your run has errored out, please contact your Dolphin Admin.' +
				'  If your run is currently running or queued, please be patient as the data is being generated.</h3>';
		document.getElementById('send_to_plots').disabled = true;
		document.getElementById('initial_mapping_exp').remove();
	}
}

function checkTableOutput(sample_data, ui_id, row_array) {
	if (sample_data != undefined) {
		row_array.push(sample_data)
	}else if (document.getElementById(ui_id) != undefined) {
		row_array.push("");
	}
	return row_array
}

function downloadInitialMapping() {
	var textString = "";
	for (var object in $('#jsontable_initial_mapping').dataTable().dataTableSettings[0].aoColumns) {
		if (object == $('#jsontable_initial_mapping').dataTable().dataTableSettings[0].aoColumns.length - 1) {
			textString += $('#jsontable_initial_mapping').dataTable().dataTableSettings[0].aoColumns[object].sTitle + "\n";
		}else{
			textString += $('#jsontable_initial_mapping').dataTable().dataTableSettings[0].aoColumns[object].sTitle + "\t";
		}
	}
	var textFile = null;
	for (var sample_array in initial_mapping_table) {
		textString += initial_mapping_table[sample_array].join("\t") + "\n";
	}
    var data = new Blob([textString], {type: 'text/tsv'});
	if (textFile !== null) {
		window.URL.revokeObjectURL(textFile);
    }
	textFile = window.URL.createObjectURL(data);
	var link = document.getElementById('download_link');
	link.href = textFile;
    link.click()
}

$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
	showHighchart('plots');
});

$(function() {
	"use strict";
	
	if (phpGrab.theSegment == 'report') {
		var run_id = '0';
		var samples = [];
		$.ajax({ type: "GET",
			url: BASE_PATH +"/ajax/sessionrequests.php",
			data: { p: 'getReportsRunID' },
			async: false,
			success : function(s)
			{
				var returnedSamples = s.split(',');
				for(var x = 0; x < returnedSamples.length; x++){
					if (x == 0) {
						run_id = returnedSamples[x];
					}else{
						samples.push(returnedSamples[x]);
					}
				}
			}
		});
		wkey = getReportWKey(run_id);
		var summary_files = [];
		var count_files = [];
		var RSEM_files = [];
		var DESEQ_files = [];
		var picard_files = [];
		var rseqc_files = [];
		
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
						}else if (s[x].type.split('_')[0] == 'picard') {
							picard_files.push(s[x]);
						}else if (s[x].type.split('_')[0] == 'RSeQC') {
							rseqc_files.push(s[x]);
						}
					}
				}
		});
		
		var summary_rna_type = [];
		var non_rna_object = {}
		for (var z = 0; z < summary_files.length; z++) {
			if (!/flagstat/.test(summary_files[z]['file']) && !/pcrdups/.test(summary_files[z]['file'])) {
				var RNA = summary_files[z]['file'].split("/")[summary_files[z]['file'].split("/").length - 1].split(".")[0];
				summary_rna_type.push(RNA);
				document.getElementById('tablerow').appendChild(createElement('th', ['id'], [RNA]));
				document.getElementById(RNA).innerHTML = RNA;
				if (document.getElementById('unused') != undefined) {
					document.getElementById('unused').remove();
					document.getElementById('tablerow').appendChild(createElement('th', ['id'], ['unused']));
					document.getElementById('unused').innerHTML = 'Reads Left';
				}else{
					document.getElementById('tablerow').appendChild(createElement('th', ['id'], ['unused']));
					document.getElementById('unused').innerHTML = 'Reads Left';
				}
			}else if (/flagstat/.test(summary_files[z]['file'])){
				if (/rsem/.test(summary_files[z]['file'])){
					non_rna_object['rsem'] = true;
				}else if (/tophat/.test(summary_files[z]['file'])){
					non_rna_object['tophat'] = true;
				}else if (/chip/.test(summary_files[z]['file'])){
					non_rna_object['chip'] = true;
				}
			}else if (/pcrdups/.test(summary_files[z]['file'])){
				if (/rsem/.test(summary_files[z]['file'])){
					non_rna_object['rsem_dedup'] = true;
				}else if (/tophat/.test(summary_files[z]['file'])){
					non_rna_object['tophat_dedup'] = true;
				}else if (/chip/.test(summary_files[z]['file'])){
					non_rna_object['chip_dedup'] = true;
				}
			}
		}
		if (non_rna_object['rsem']){
			document.getElementById('tablerow').appendChild(createElement('th', ['id'], ['Transcriptomic Reads Aligned (RSEM)']));
			document.getElementById('Transcriptomic Reads Aligned (RSEM)').innerHTML = 'Transcriptomic Reads Aligned (RSEM)';
		}
		if (non_rna_object['rsem_dedup']){
			document.getElementById('tablerow').appendChild(createElement('th', ['id'], ['Deduplicated Reads (RSEM)']));
			document.getElementById('Deduplicated Reads (RSEM)').innerHTML = 'Deduplicated Reads (RSEM)';
		}
		if (non_rna_object['tophat']){
			document.getElementById('tablerow').appendChild(createElement('th', ['id'], ['Genomic Reads Aligned (Tophat)']));
			document.getElementById('Genomic Reads Aligned (Tophat)').innerHTML = 'Genomic Reads Aligned (Tophat)';
		}
		if (non_rna_object['tophat_dedup']){
			document.getElementById('tablerow').appendChild(createElement('th', ['id'], ['Deduplicated Reads (Tophat)']));
			document.getElementById('Deduplicated Reads (Tophat)').innerHTML = 'Deduplicated Reads (Tophat)';
		}
		if (non_rna_object['chip']){
			document.getElementById('tablerow').appendChild(createElement('th', ['id'], ['Genomic Reads Aligned (Chip)']));
			document.getElementById('Genomic Reads Aligned (Chip)').innerHTML = 'Genomic Reads Aligned (Chip)';
		}
		if (non_rna_object['chip_dedup']){
			document.getElementById('tablerow').appendChild(createElement('th', ['id'], ['Deduplicated Reads (Chip)']));
			document.getElementById('Deduplicated Reads (Chip)').innerHTML = 'Deduplicated Reads (Chip)';
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
		
		populateTable(summary_files, samplenames, libraries, read_counts);
		var table_div = document.getElementById('jsontable_initial_mapping_wrapper').children[0]
		table_div.appendChild(createElement('button', ['id', 'class', 'onclick'], ['initial_download_button', 'btn btn-primary margin pull-right', 'downloadInitialMapping()']), table_div.children[0])
		document.getElementById('initial_download_button').innerHTML = 'Download Table';
		document.getElementById('jsontable_initial_mapping_wrapper').appendChild(createElement('a', ['id', 'download', 'style'], ['download_link', 'initial_mapping.tsv', 'display:none']))
		createDropdown(summary_rna_type, 'initial_mapping');
		
		for (var sample_obj in table_data) {
			if (table_data[sample_obj]['rsem'] != undefined) {
				rsem_toggle = true;
				rsem_categories.push(sample_obj);
				for (var data in table_data[sample_obj]) {
					if (!/chip/.test(data) && !/tophat/.test(data) && !/total_reads/.test(data) && !/unmapped/.test(data)) {
						if (rsem_series[data] == undefined) {
							var name = data;
							if (data == 'rsem') {
								name = 'reads mapped'
							}else if (data == 'rsem_dedup') {
								name = 'dedup reads'
							}
							rsem_series[data] = {name: name, data: [parseInt(table_data[sample_obj][data])]}
						}else{
							rsem_series[data]['data'].push(parseInt(table_data[sample_obj][data]))
						}
					}
				}
			}
			
			if (table_data[sample_obj]['tophat_dedup'] != undefined) {
				tophat_toggle = true;
				tophat_categories.push(sample_obj);
				for (var data in table_data[sample_obj]) {
					if (!/chip/.test(data) && !/rsem/.test(data) && !/total_reads/.test(data) && !/unmapped/.test(data)) {
						if (tophat_series[data] == undefined) {
							var name = data;
							if (data == 'tophat') {
								name = 'reads mapped'
							}else if (data == 'tophat_dedup') {
								name = 'dedup reads'
							}
							tophat_series[data] = {name: name, data: [parseInt(table_data[sample_obj][data])]}
						}else{
							tophat_series[data]['data'].push(parseInt(table_data[sample_obj][data]))
						}
					}
				}
			}
			
			if (table_data[sample_obj]['chip_dedup'] != undefined) {
				chip_toggle = true;
				chip_categories.push(sample_obj);
				for (var data in table_data[sample_obj]) {
					if (!/rsem/.test(data) && !/tophat/.test(data) && !/total_reads/.test(data) && !/unmapped/.test(data)) {
						if (chip_series[data] == undefined) {
							var name = data;
							if (data == 'chip') {
								name = 'reads mapped'
							}else if (data == 'chip_dedup') {
								name = 'dedup reads'
							}
							chip_series[data] = {name: name, data: [parseInt(table_data[sample_obj][data])]}
						}else{
							chip_series[data]['data'].push(parseInt(table_data[sample_obj][data]))
						}
					}
				}
			}
			
			if (table_data[sample_obj]['chip_dedup'] == undefined && table_data[sample_obj]['tophat_dedup'] == undefined && table_data[sample_obj]['rsem_dedup'] == undefined) {
				base_categories.push(sample_obj);
				for (var data in table_data[sample_obj]) {
					if (!/rsem/.test(data) && !/tophat/.test(data) && !/chip/.test(data) && !/total_reads/.test(data)) {
						console.log(data)
						if (base_series[data] == undefined) {
							var name = data;
							base_series[data] = {name: name, data: [parseInt(table_data[sample_obj][data])]}
						}else{
							base_series[data]['data'].push(parseInt(table_data[sample_obj][data]))
						}
					}
				}
            }
		}
		console.log(rsem_series)
		console.log(tophat_series)
		console.log(chip_series)
		console.log(base_series)
		
		if (rsem_toggle) {
			var rsem_final_series = [];
			for (var series in rsem_series) {
				rsem_final_series.push(rsem_series[series]);
			}
			document.getElementById('plots').appendChild(createElement('button', ['id', 'class', 'onclick', 'style'], ['rsem_switch', 'btn btn-primary margin', 'switchStacking("plots", "rsem_plot")', 'display:none']))
			document.getElementById('rsem_switch').innerHTML = 'Switch RSEM Plot Type';
			createHighchart(rsem_categories, rsem_final_series, 'Distribution of RSEM Reads', 'Percentage of Reads', 'plots', 'rsem_plot', 'percent');
		}
		
		if (tophat_toggle) {
			var tophat_final_series = [];
			for (var series in tophat_series) {
				tophat_final_series.push(tophat_series[series]);
			}
			console.log(tophat_final_series)
			document.getElementById('plots').appendChild(createElement('button', ['id', 'class', 'onclick', 'style'], ['tophat_switch', 'btn btn-primary margin', 'switchStacking("plots", "tophat_plot")', 'display:none']))
			document.getElementById('tophat_switch').innerHTML = 'Switch Tophat Plot Type';
			createHighchart(tophat_categories, tophat_final_series, 'Distribution of Tophat Reads', 'Percentage of Reads', 'plots', 'tophat_plot', 'percent');
		}
		
		if (chip_toggle) {
			var chip_final_series = [];
			for (var series in chip_series) {
				chip_final_series.push(chip_series[series]);
			}
			document.getElementById('plots').appendChild(createElement('button', ['id', 'class', 'onclick', 'style'], ['chip_switch', 'btn btn-primary margin', 'switchStacking("plots", "chip_plot")', 'display:none']))
			document.getElementById('chip_switch').innerHTML = 'Switch Chip Plot Type';
			createHighchart(chip_categories, chip_final_series, 'Distribution of Chip Reads', 'Percentage of Reads', 'plots', 'chip_plot', 'percent');
		}
		
		if (!chip_toggle && !tophat_toggle && !rsem_toggle) {
            var base_final_series = [];
			for (var series in base_series) {
				base_final_series.push(base_series[series]);
			}
			document.getElementById('plots').appendChild(createElement('button', ['id', 'class', 'onclick', 'style'], ['base_switch', 'btn btn-primary margin', 'switchStacking("plots", "base_plot")', 'display:none']))
			document.getElementById('base_switch').innerHTML = 'Switch Plot Type';
			createHighchart(base_categories, base_final_series, 'Distribution of Reads', 'Percentage of Reads', 'plots', 'base_plot', 'percent');
        }
		
		//Create a check for FASTQC output????
		if (getFastQCBool(run_id)) {
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
		
		if (picard_files.length > 0) {
			var picard_file_paths = [];
			for (var z = 0; z < picard_files.length; z++){
				picard_file_paths.push(picard_files[z].file);
			}
			createDropdown(picard_file_paths, 'picard');
		}else{
			document.getElementById('picard_exp').remove();
		}
		
		if (rseqc_files.length > 0) {
			var rseqc_file_paths = [];
			for (var z = 0; z < rseqc_files.length; z++){
				rseqc_file_paths.push(rseqc_files[z].file);
			}
			createDropdown(rseqc_file_paths, 'rseqc');
		}else{
			document.getElementById('rseqc_exp').remove();
		}
	}
});
