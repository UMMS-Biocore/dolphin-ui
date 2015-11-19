/*
 *Author: Nicholas Merowsky
 *Date: 09 Apr 2015
 *Ascription:
 */

//GLOBAL VARIABLES
var jsonTypeList = ['genomebuild', 'spaired', 'resume', 'barcodes', 'fastqc', 'adapter', 'quality', 'trim', 'commonind', 'split', 'pipeline', 'advparams', 'custom'];
var radioTypeCheckList = ['pipeline', 'trimpaired', 'advparams', 'custom'];
var currentChecked = "";
var checklist_samples = [];
var checklist_lanes = [];
var checklist_experiment_series = [];
var pipelineNum = 0;
var customSeqNum = 0;
var customSeqNumCheck = [];
var pipelineDict = ['RNASeqRSEM', 'Tophat', 'ChipSeq', 'DESeq', 'BisulphiteMapping'];
var rnaList = ["ercc","rRNA","miRNA","tRNA","piRNA","snRNA","rmsk","genome","change_params"];
var qualityDict = ["window size","required quality","leading","trailing","minlen"];
var trimmingDict = ["single or paired-end", "5 length 1", "3 length 1", "5 length 2", "3 length 2"];
var currentPipelineID = [];
var currentPipelineVal =[];
var rsemSwitch = false;
var deseqList = ['RSEM'];
var valid_samples;

/*##### FILL A RERUN PIPELINE WITH PREVIOUS SELECTIONS #####*/
function rerunLoad() {
	var hrefSplit = window.location.href.split("/");
	if (hrefSplit[4] == 'search') {
		document.getElementById('dolphin_basket').parentNode.setAttribute('style','overflow:scroll');
		var basket = $('#dolphin_basket').dataTable( {
			//"bFilter":	false,
			//"ordering": false,
			//"info":     false,
			"lengthChange": false,
			"pageLength": 20,
			"columnDefs": [ { "targets": 2, "orderable": false } ]
		} );
	}
	var rerunLoc = $.inArray('rerun', hrefSplit);
	var infoArray = [];
	var json_primer = '';
	var jsonObj;

	//make sure this is a rerun
	if (rerunLoc != -1) {
	infoArray = grabReload(hrefSplit[rerunLoc + 1]);
	json_primer = infoArray[0];
	jsonObj = JSON.parse(json_primer);
	//repopulate page
	for (var x = 0; x < (jsonTypeList.length); x++) {
		if (jsonObj.hasOwnProperty(jsonTypeList[x]) && jsonObj[jsonTypeList[x]] != 'none' && jsonObj[jsonTypeList[x]] != 'NONE' && jsonObj[jsonTypeList[x]] != 'NO' && jsonTypeList[x] != 'barcodes') {
			var element = document.getElementById(jsonTypeList[x]);
			if (element != null) {
				if (element.id == "spaired") {
					if ( jsonObj[jsonTypeList[x]] == 'paired') {
						element.value = 'yes'
					}else{
						element.value = 'no';
					}
				}else if (element.id == "resume"){
					element.value = 'Resume';
				}else{
					element.value = jsonObj[jsonTypeList[x]];
				}
			}else{
				//try radio
				if (radioTypeCheckList.indexOf(jsonTypeList[x]) == -1) {
					//expand the altered fields
					$( '#'+jsonTypeList[x]+'_yes' ).iCheck('check');
					document.getElementById(jsonTypeList[x]+'_exp').setAttribute('class', 'box box-default');
					document.getElementById(jsonTypeList[x]+'_exp_btn').setAttribute('class', 'fa fa-minus');
					document.getElementById(jsonTypeList[x]+'_exp_body').setAttribute('style', 'display: block');
					
					//fill the fields that have been expanded
					var splt1 = jsonObj[jsonTypeList[x]].split(":");
					if (splt1.length == 1 && jsonTypeList[x] != 'commonind') {
						if (jsonTypeList[x] == 'split') {
							document.getElementById('number of reads per file_val').value = jsonObj[jsonTypeList[x]];
						}else{
							document.getElementById(jsonTypeList[x]+'_val').value = jsonObj[jsonTypeList[x]].replace(/__cr____cn__/g, "\n");;
						}
					}else{
						for (var z = 0; z < splt1.length; z++) {
							var splt2 = splt1[z].split(",");
							if (jsonTypeList[x] == 'quality') {
								document.getElementById( qualityDict[z]+'_val' ).value = splt2[0];
							}else if (jsonTypeList[x] == 'trim'){
								if (z == 0 && jsonObj.hasOwnProperty('trimpaired')) {
								document.getElementById( trimmingDict[z]+'_val').value = 'paired-end';
								selectTrimming('single or paired-end_val', 0, 0);
								}else if(z == 0 && !jsonObj.hasOwnProperty('trimpaired')){
								document.getElementById( trimmingDict[z]+'_val').value = 'single-end';
								}
								document.getElementById( trimmingDict[z+1]+'_val' ).value = splt2[0];
							}else if (jsonTypeList[x] == 'commonind'){
								for(var y = 0; y < splt2.length; y++){
									$( '#'+splt2[y]+'_yes' ).iCheck('check');
									deseqList.push(splt2[y]);
								}
							}else{
								document.getElementById( splt2[0]+'_val' ).value = splt2[1];
							}
						}
					}
				}else if (jsonTypeList[x] == 'advparams') {
					changeRNAParamsBtn();
					document.getElementById('change_params_val').value = jsonObj[jsonTypeList[x]];
				}else if (jsonTypeList[x] == 'custom') {
					document.getElementById(jsonTypeList[x]+'_exp').setAttribute('class', 'box box-default');
					document.getElementById(jsonTypeList[x]+'_exp_btn').setAttribute('class', 'fa fa-minus');
					for(var y = 0; y < jsonObj[jsonTypeList[x]].length; y++){
						sequenceSetsBtn();
						fillCustomSequenceSet(y, jsonObj[jsonTypeList[x]][y].split(":"));
					}
					document.getElementById(jsonTypeList[x]+'_exp_body').setAttribute('style', 'display: block');
				}else{
					//pipeline
					document.getElementById(jsonTypeList[x]+'_exp').setAttribute('class', 'box box-default');
					document.getElementById(jsonTypeList[x]+'_exp_btn').setAttribute('class', 'fa fa-minus');
	
					var splt1 = jsonObj[jsonTypeList[x]];
					for (var i = 0; i < splt1.length; i++){
						var splt2 = splt1[i].split(":");
						if (splt2[0] == pipelineDict[0]) {
							//RSEM
							additionalPipes();
							document.getElementById('select_'+i).value = pipelineDict[0];
							pipelineSelect(i);
							document.getElementById('textarea_'+i).value = splt2[1];
							document.getElementById('select_1_'+i).value = splt2[2];
							document.getElementById('select_2_'+i).value = splt2[3];
							if (splt2[4] == '1') {
								document.getElementById('checkbox_1_'+i).checked = true;
							}
							rsemSwitch = true;
						}else if (splt2[0] == pipelineDict[1]) {
							//Tophat
							additionalPipes();
							document.getElementById('select_'+i).value = pipelineDict[1];
							pipelineSelect(i);
							document.getElementById('textarea_'+i).value = splt2[1];
							document.getElementById('select_1_'+i).value = splt2[2];
							document.getElementById('select_2_'+i).value = splt2[3];
							if (splt2[4] == '1') {
								document.getElementById('checkbox_1_'+i).checked = true;
							}
							if (splt2[5] == '1') {
								document.getElementById('checkbox_2_'+i).checked = true;
							}
							if (splt2[6] == '1') {
								document.getElementById('checkbox_3_'+i).checked = true;
							}
							if (splt2[7] == '1') {
								document.getElementById('checkbox_4_'+i).checked = true;
							}
						}else if (splt2[0] == pipelineDict[2]){
							//Chipseq
							additionalPipes();
							document.getElementById('select_'+i).value = pipelineDict[2];
							pipelineSelect(i);
							document.getElementById('textarea_'+i).value = splt2[1].replace(/__cr____cn__/g, "\n");
							document.getElementById('text_1_'+i).value = splt2[2];
							document.getElementById('text_2_'+i).value = splt2[3];
							document.getElementById('select_1_'+i).value = splt2[4];
							document.getElementById('select_2_'+i).value = splt2[5];
							document.getElementById('select_3_'+i).value = splt2[6];
							document.getElementById('select_4_'+i).value = splt2[7];
						}else if (splt2[0] == pipelineDict[3]) {
							//DESEQ
							additionalPipes();
							document.getElementById('select_'+i).value = pipelineDict[3];
							pipelineSelect(i);
							
							//handle for multiple selections
							var select_values = splt2[1].split(",");
							var select_locations = splt2[2].split(",");
							var select1_values = [];
							var select2_values = [];
							for(var f = 0; f < select_locations.length; f++){
								if (select_locations[f] == 'Cond1') {
									select1_values.push(select_values[f]);
								}else{
									select2_values.push(select_values[f]);
								}
							}
							
							var select1 = document.getElementById('multi_select_1_'+i);
							for(var h = 0; h < select1.options.length; h++){
								if (select1_values.indexOf(select1.options[h].value) != -1) {
									select1.options[h].selected = true;
								}
							}
							var select2 = document.getElementById('multi_select_2_'+i);
							for(var h = 0; h < select1.options.length; h++){
								if (select2_values.indexOf(select2.options[h].value) != -1) {
									select2.options[h].selected = true;
								}
							}
							document.getElementById('select_3_'+i).value = splt2[3];
							document.getElementById('select_4_'+i).value = splt2[4];
							document.getElementById('text_1_'+i).value = splt2[5];
							document.getElementById('text_2_'+i).value = splt2[6];
							document.getElementById('select_5_'+i).value = splt2[7];
						}else if (splt2[0] == pipelineDict[4]) {
							//MMap
							additionalPipes();
							document.getElementById('select_'+i).value = pipelineDict[4];
							pipelineSelect(i);
							
							document.getElementById('text_1_'+i).value = splt2[2];
							document.getElementById('textarea_1_'+i).value = splt2[3];
							
							//MCall
							//handle for multiple selections
							if (splt2[4] == 1) {
								document.getElementById('checkbox_2_'+i).checked = true;
							}
							var select_values = splt2[5].split(",");
							var select_locations = splt2[6].split(",");
							var select1_values = [];
							var select2_values = [];
							for(var f = 0; f < select_locations.length; f++){
								if (select_locations[f] == 'Cond1') {
									select1_values.push(select_values[f]);
								}else{
									select2_values.push(select_values[f]);
								}
							}
							
							var select1 = document.getElementById('multi_select_1_'+i);
							for(var h = 0; h < select1.options.length; h++){
								if (select1_values.indexOf(select1.options[h].value) != -1) {
									select1.options[h].selected = true;
								}
							}
							var select2 = document.getElementById('multi_select_2_'+i);
							for(var h = 0; h < select1.options.length; h++){
								if (select2_values.indexOf(select2.options[h].value) != -1) {
									select2.options[h].selected = true;
								}
							}
							document.getElementById('textarea_2_'+i).value = splt2[7];
							
							//MComp
							if (splt2[8] == '1') {
								document.getElementById('checkbox_3_'+i).checked = true;
							}
							document.getElementById('textarea_3_'+i).value = splt2[9];
						}
					}
					document.getElementById(jsonTypeList[x]+'_exp_body').setAttribute('style', 'display: block');
				}
			}
		}
	}
	document.getElementById('outdir').value = infoArray[1];
	document.getElementById('run_name').value = infoArray[2];
	document.getElementById('run_description').value = infoArray[3];
	}
}

/*##### SELECT/FILL PIPELINE #####*/
//used to generate divs within the html for the additional pipelines
function pipelineSelect(num){
	//Grab some useful variables
	var pipeType = document.getElementById('select_'+num).value;
	var divAdj = createElement('div', ['id', 'class', 'style'], ['select_child_'+num, 'input-group margin col-md-11', 'float:left']);
	//Check for only one RSEM/DESeq dependencies
	if (pipeType == pipelineDict[0] && rsemSwitch)
	{
		$('#errorModal').modal({
			show: true
		});
		document.getElementById('errorLabel').innerHTML ='You cannot select more than one additional RSEM pipeline';
		document.getElementById('errorAreas').innerHTML = '';
		document.getElementById('select_'+num).value = currentPipelineVal[currentPipelineID.indexOf(num)];
	}
	else if (pipeType == pipelineDict[3] && !rsemSwitch) {
		$('#errorModal').modal({
			show: true
		});
		document.getElementById('errorLabel').innerHTML ='You must first add a RSEM pipeline before running DESeq';
		document.getElementById('errorAreas').innerHTML = '';
		document.getElementById('select_'+num).value = currentPipelineVal[currentPipelineID.indexOf(num)];
	}
	else
	{
	//pipelineDict: global variable containing selections
	if (pipeType == pipelineDict[0]) {
		//RNASeq RSEM
		divAdj.appendChild( createElement('label', ['class','TEXTNODE'], ['box-title', 'RSEM parameters:']));
		var testText = createElement('textarea', ['id', 'class'], ['textarea_'+num,'form-control'])
		testText.value = '--bowtie-e 70 --bowtie-chunkmbs 100';
		divAdj.appendChild( testText );
		divAdj = mergeTidy(divAdj, 6,
				[ [createElement('label', ['class','TEXTNODE'], ['box-title', 'IGV/TDF Conversion:']),
				createElement('select', ['id','class', 'OPTION', 'OPTION'], ['select_1_'+num, 'form-control', 'no', 'yes'])],

				[createElement('label', ['class','TEXTNODE'], ['box-title', 'BigWig Conversion:']),
				createElement('select', ['id', 'class', 'OPTION', 'OPTION'], ['select_2_'+num, 'form-control', 'no', 'yes'])] ]);
		divAdj = mergeTidy(divAdj, 12,
				[ [createElement('label', ['class','TEXTNODE'], ['box-title margin', 'RNA-Seq QC:']),
				   createElement('input', ['id', 'type', 'class'], ['checkbox_1_'+num, 'checkbox', 'margin'])] ]);
		rsemSwitch = true;
	}else if (pipeType == pipelineDict[1]) {
		//Tophat Pipeline
		divAdj.appendChild( createElement('label', ['class','TEXTNODE'], ['box-title', 'Tophat parameters:']));
		divAdj.appendChild( createElement('textarea', ['id', 'class'], ['textarea_'+num, 'form-control']));
		divAdj = mergeTidy(divAdj, 6,
				[ [createElement('label', ['class','TEXTNODE'], ['box-title margin', 'IGV/TDF Conversion:']),
				createElement('select', ['id', 'class', 'OPTION', 'OPTION'], ['select_1_'+num, 'form-control margin', 'no', 'yes'])],
				[createElement('label', ['class','TEXTNODE'], ['box-title margin', 'BigWig Conversion:']),
				createElement('select', ['id', 'class', 'OPTION', 'OPTION'], ['select_2_'+num, 'form-control margin', 'no', 'yes'])] ]);
		divAdj = mergeTidy(divAdj, 12,
				[ [createElement('label', ['class','TEXTNODE'], ['box-title margin', 'RNA-Seq QC:']),
				   createElement('input', ['id', 'type', 'class'], ['checkbox_1_'+num, 'checkbox', 'margin'])] ]);
		divAdj = mergeTidy(divAdj, 12,
				[ [createElement('label', ['class','TEXTNODE'], ['box-title', 'Picard Metrics:'])] ]);
		divAdj = mergeTidy(divAdj, 12,
				[ [createElement('label', ['class','TEXTNODE'], ['margin', 'Collect RNA Metrics']),
				createElement('input', ['id', 'type', 'class'], ['checkbox_2_'+num, 'checkbox', 'margin'])],
				[createElement('label', ['class','TEXTNODE'], ['margin', 'Collect Other Metrics']),
				createElement('input', ['id', 'type', 'class'], ['checkbox_3_'+num, 'checkbox', 'margin'])],
				[createElement('label', ['class','TEXTNODE'], ['margin', 'Mark Duplicates']),
				createElement('input', ['id', 'type', 'class'], ['checkbox_4_'+num, 'checkbox', 'margin'])] ]);
	}else if (pipeType == pipelineDict[2]) {
		//ChipSeq Pipeline
		divAdj.appendChild( createElement('label', ['class','TEXTNODE'], ['box-title', 'Chip Input Definitions:']));
		divAdj.appendChild( createElement('textarea', ['id', 'class'], ['textarea_'+num, 'form-control']));
		divAdj = mergeTidy(divAdj, 6,
				[ [createElement('label', ['class','TEXTNODE'], ['box-title', 'Multimapper:']),
				createElement('input', ['id', 'class', 'type', 'value'], ['text_1_'+num, 'form-control', 'text', '1'])],
				[createElement('label', ['class','TEXTNODE'], ['box-title', 'Tag size(bp) for MACS:']),
				createElement('input', ['id', 'class', 'type', 'value'], ['text_2_'+num, 'form-control', 'text', '75'])] ]);
		divAdj = mergeTidy(divAdj, 6,
				[ [createElement('label', ['class','TEXTNODE'], ['box-title', 'Band width(bp) for MACS:']),
				createElement('input', ['id', 'class', 'type', 'value'], ['select_1_'+num, 'form-control', 'text', '230'])],
				[createElement('label', ['class','TEXTNODE'], ['box-title', 'Effective genome size(bp):']),
				createElement('input', ['id', 'class', 'type', 'value'], ['select_2_'+num, 'form-control', 'text', '2700000000'])] ]);
		divAdj = mergeTidy(divAdj, 6,
				[ [createElement('label', ['class','TEXTNODE'], ['box-title', 'IGV/TDF Conversion:']),
				createElement('select', ['id', 'class', 'OPTION', 'OPTION'], ['select_3_'+num, 'form-control', 'no', 'yes'])],
				[createElement('label', ['class','TEXTNODE'], ['box-title', 'BigWig Conversion:']),
				createElement('select', ['id', 'class', 'OPTION', 'OPTION'], ['select_4_'+num, 'form-control', 'no', 'yes'])] ]);
	}else if (pipeType == pipelineDict[3]) {
		//DESEQ
		divAdj = mergeTidy(divAdj, 6,
				[ [createElement('label', ['class','TEXTNODE'], ['box-title', 'Condition 1']),
				createElement('select',['id', 'class', 'multiple', 'size', 'onchange'],['multi_select_1_'+num, 'form-control', 'multiple', '8', 'deselectCondition(1, '+num+')'])],
				[createElement('label', ['class','TEXTNODE'], ['box-title', 'Condition 2']),
				createElement('select',['id', 'class', 'multiple', 'size', 'onchange'],['multi_select_2_'+num, 'form-control', 'multiple', '8', 'deselectCondition(2, '+num+')'])] ]);
		divAdj = mergeTidy(divAdj, 6,
				[ [createElement('label', ['class','TEXTNODE'], ['box-title', 'Fit Type:']),
				createElement('select', ['id', 'class', 'OPTION', 'OPTION', 'OPTION'], ['select_3_'+num, 'form-control', 'parametric', 'local', 'mean'])],
				[createElement('label', ['class','TEXTNODE'], ['box-title', 'Heatmap:']),
				createElement('select', ['id', 'class', 'OPTION', 'OPTION'], ['select_4_'+num, 'form-control', 'Yes', 'No'])] ]);
		divAdj = mergeTidy(divAdj, 6,
				[ [createElement('label', ['class','TEXTNODE'], ['box-title', 'pAdj cutoff']),
				createElement('input', ['id', 'class', 'type', 'value'], ['text_1_'+num, 'form-control', 'text', '0.01'])],
				[createElement('label', ['class','TEXTNODE'], ['box-title', 'Fold Change cutoff']),
				createElement('input', ['id', 'class', 'type', 'value'], ['text_2_'+num, 'form-control', 'text', '2'])] ]);
		divAdj = mergeTidy(divAdj, 12,
				[ [createElement('label', ['class','TEXTNODE'], ['box-title', 'Select Sequence']),
				createElement('select', ['id', 'class'], ['select_5_'+num, 'form-control'])] ]);
	}else if (pipeType == pipelineDict[4]) {
		//MMap
		var labelDiv = createElement('div', ['class'], ['col-md-12 text-center']);
		labelDiv.appendChild( createElement('label', ['class','TEXTNODE'], ['box-title margin', 'Run BSMap:']));
		labelDiv.appendChild( createElement('input', ['id', 'type', 'class', 'checked', 'disabled'], ['checkbox_1_'+num, 'checkbox', 'margin']));
		divAdj.appendChild(labelDiv);
		var innerDiv = createTidyDiv(12);
		divAdj = mergeTidy(divAdj, 12,
				[[createLabeledDiv(12, 'RRBS&nbsp;&nbsp;', '&nbsp;&nbsp;WGBS', createElement('input', ['id', 'name', 'type', 'value', 'onClick', 'checked'], [num+'_RRBS', num, 'radio', 'RRBS', 'bisulphiteSelect(this.id, '+num+')']),
					   createElement('input', ['id', 'name', 'type', 'value', 'onClick',], [num+'_WGBS', num, 'radio', 'WGBS', 'bisulphiteSelect(this.id, '+num+')']))
					],
				[createElement('label', ['class','TEXTNODE'], ['box-title', 'Digestion Site:']),
				createElement('input', ['id', 'class', 'type', 'value'], ['text_1_'+num, 'form-control', 'text', 'C-CGG'])]
				]);
		labelDiv = createElement('div', ['class'], ['col-md-12']);
		labelDiv.appendChild( createElement('label', ['class','TEXTNODE'], ['box-title', 'Additional BSMap Parameters:']));
		labelDiv.appendChild( createElement('textarea', ['id', 'class'], ['textarea_1_'+num, 'form-control']));
		divAdj.appendChild(labelDiv);
		
		//MCALL
		labelDiv = createElement('div', ['class'], ['col-md-12 text-center']);
		labelDiv.appendChild( createElement('label', ['class','TEXTNODE'], ['box-title margin', 'Run MCall:']));
		labelDiv.appendChild( createElement('input', ['id', 'type', 'class'], ['checkbox_2_'+num, 'checkbox', 'margin']));
		divAdj.appendChild(labelDiv);
		divAdj = mergeTidy(divAdj, 6,
				[ [createElement('label', ['class','TEXTNODE'], ['box-title', 'MCall Condition 1']),
				createElement('select',['id', 'class', 'multiple', 'size', 'onchange'],['multi_select_1_'+num, 'form-control', 'multiple', '8', 'deselectCondition(1, '+num+')'])],
				[createElement('label', ['class','TEXTNODE'], ['box-title', 'MCall Condition 2']),
				createElement('select',['id', 'class', 'multiple', 'size', 'onchange'],['multi_select_2_'+num, 'form-control', 'multiple', '8', 'deselectCondition(2, '+num+')'])] ]);
		labelDiv = createElement('div', ['class'], ['col-md-12']);
		labelDiv.appendChild( createElement('label', ['class','TEXTNODE'], ['box-title', 'Additional MCall Parameters:']));
		labelDiv.appendChild( createElement('textarea', ['id', 'class'], ['textarea_2_'+num, 'form-control']));
		divAdj.appendChild(labelDiv);
		
		//MComp
		labelDiv = createElement('div', ['class'], ['col-md-12 text-center']);
		labelDiv.appendChild( createElement('label', ['class','TEXTNODE'], ['box-title margin', 'Run MComp:']));
		labelDiv.appendChild( createElement('input', ['id', 'type', 'class'], ['checkbox_3_'+num, 'checkbox', 'margin']));
		divAdj.appendChild(labelDiv);
		labelDiv = createElement('div', ['class'], ['col-md-12']);
		labelDiv.appendChild( createElement('label', ['class','TEXTNODE'], ['box-title', 'Additional MComp Parameters:']));
		labelDiv.appendChild( createElement('textarea', ['id', 'class'], ['textarea_3_'+num, 'form-control']));
		divAdj.appendChild(labelDiv);
	}
	//replace div
	$('#select_child_'+num).replaceWith(divAdj);
	if (pipeType == pipelineDict[3]) {
		for (var x = 0; x < deseqList.length; x++) {
			var opt = createElement('option', ['id', 'value'], [deseqList[x], deseqList[x]]);
			opt.innerHTML = deseqList[x];
			document.getElementById('select_5_'+num).appendChild(opt);
		}
	}
	
	//MULTI-SELECT
	if (document.getElementById('multi_select_1_'+num) != null) {
		var sample_names = getSampleNames(window.location.href.split('/')[window.location.href.split('/').length - 1].replace('$', ''));
		console.log(sample_names);
		for(var x = 0; x < sample_names.length; x++){
				document.getElementById('multi_select_1_'+num).appendChild(createElement('option', ['id', 'value'], [num+'_1_'+sample_names[x], sample_names[x]]));
				document.getElementById(num+'_1_'+sample_names[x]).innerHTML = sample_names[x]
				document.getElementById('multi_select_2_'+num).appendChild(createElement('option', ['id', 'value'], [num+'_2_'+sample_names[x], sample_names[x]]));
				document.getElementById(num+'_2_'+sample_names[x]).innerHTML = sample_names[x]
		}
	}
	
	//adjust global pipeline counter
	if (currentPipelineID.indexOf(num) == -1) {
		currentPipelineID.push(num);
		currentPipelineVal.push(pipeType);
	}
	else if (currentPipelineID.indexOf(num) != -1 && currentPipelineVal.indexOf(currentPipelineID.indexOf(num)) != pipeType)
	{
		if (currentPipelineVal[currentPipelineID.indexOf(num)] == pipelineDict[0]) {
		rsemSwitch = false;
		}
		currentPipelineVal[currentPipelineID.indexOf(num)] = pipeType;
	}
	}
}

/*##### SUBMIT PIPELINE RUN #####*/
function submitPipeline(type) {
	//Static
	var genome = document.getElementById("genomebuild").value;
	var matepair = document.getElementById("spaired").value;
	var freshrun = document.getElementById("resume").value;
	var outputdir = document.getElementById("outdir").value;
	var fastqc = document.getElementById("fastqc").value;
	var run_name = document.getElementById("run_name").value;
	var description = document.getElementById("run_description").value;
	var perms = document.getElementById("perms").value;
	var group = document.getElementById("groups").value;
	
	var empty_values = []
	if (run_name == "") {
		empty_values.push('Run Name');
	}
	if (description == "") {
		empty_values.push("Run Description");
	}
	if (outputdir == "") {
		empty_values.push('Output Directory');
	}
	
	if (empty_values.length > 0) {
		$('#errorModal').modal({
			show: true
		});
		document.getElementById('errorLabel').innerHTML ='The following fields may not be empty for submission:';
		document.getElementById('errorAreas').innerHTML = empty_values.join(", ");
	}else{
		//Expanding
		var doAdapter = findRadioChecked("adapter");
		var doQuality = findRadioChecked("quality");
		var doTrimming = findRadioChecked("trim");
		var doRNA = findRNAChecked(rnaList);
		var doSplit = findRadioChecked("split");
	
		var adapter = findAdditionalInfoValues(doAdapter, ["adapter"]);

		var adapterCheck = false;
		if (adapter[0].match(/[bd-fh-sv-zBD-FH-SV-Z0-9\!\@\#\$\%\^\&\*\(\)\_\+\-\=\\\|\[\]\{\}\;\'\:\"\,\.\/\<\>\?\`\~]+/g)) {
			adapterCheck = true;
		}
			
		var quality = findAdditionalInfoValues(doQuality, ["window size", "required quality", "leading", "trailing", "minlen"]);
		var trimming = findAdditionalInfoValues(doTrimming, ["single or paired-end", "5 length 1", "3 length 1", "5 length 2", "3 length 2"]);
		var rna = findAdditionalInfoValues(doRNA, rnaList);
		var split = findAdditionalInfoValues(doSplit, ["number of reads per file"]);
	
		//Pipeline
		var pipelines = findPipelineValues();
	
		//Grab sample ids
		var ids = getSampleIDs(phpGrab.theSearch);
		var previous = 'none';
		//start json construction
		//static
		var json = '{"genomebuild":"' + genome + '"'
		if (matepair == "yes") {
			json = json + ',"spaired":"paired"'
			previous = 'spaired';
		}else{
			json = json + ',"spaired":"no"';
		}
		if (freshrun == "Fresh") {
			json = json + ',"resume":"no"'
		}else{
			json = json + ',"resume":"resume"'
			previous = 'resume';
		}
		json = json + ',"fastqc":"' + fastqc + '"'
	
		//expanding
		//barcode
		/*
		if (doBarcode == "yes") {
			json = json + ',"barcodes":"distance,' + barcode[0] + ':format,' + barcode[1] + '"';
			previous = 'barcodes';
		}else{
		*/
		json = json + ',"barcodes":"none"';

		//adapter
		if (doAdapter == "yes") {
			previous = 'adapter';
			json = json + ',"adapter":"' + adapter[0].toUpperCase().replace(/\r\n|\r|\n/g, "__cr____cn__").replace(/U/g, 'T') + '"';
		}else{
			json = json + ',"adapter":"none"';	
		}
		//quality
		if (doQuality == "yes") {
			json = json + ',"quality":"' + quality[0] + ':' + quality[1] + ':' + quality[2] + ':' + quality[3] + ':' + quality[4] + '"';
			previous = 'quality';
		}else{
			json = json + ',"quality":"none"'
		}
		//trim
		if (doTrimming == "yes") {
			json = json + ',"trim":"' + trimming[1] + ':' + trimming[2];
			previous = 'trim';
		}else{
			json = json + ',"trim":"none"';
		}
		if (trimming[0] == 'paired-end' && doTrimming == 'yes') {
			json = json + ':' + trimming[3] + ':' + trimming[4] + '","trimpaired":"paired'
		}
		if (doTrimming == "yes") {
			json = json + '"';
		}
		//split
		if (doSplit == "yes") {
			previous = 'split';
		}
		json = json + ',"split":"' + split[0] + '"';
	
		//expanding multiple queries
		if (doRNA == "yes"){
			json = json + ',"commonind":"'
			var rnacheck = true;
			for (var i = 0; i < rna.length; i++) {
				if (rnacheck) {
					json = json + rna[i];
					previous = rna[i];
					rnacheck = false;
				}else if (rna[i] != undefined && rnaList.indexOf(rna[i]) == -1){
					json = json + '","advparams":"' + rna[i];
				}else if (rna[i] != undefined) {
					json = json + ',' + rna[i]
					previous = rna[i];
				}
			}
			json = json + '"'
		}else{
			json = json + ',"commonind":"none"'
		}
		var customSeqSet = findCustomSequenceSets(previous);
		json = json + customSeqSet;
		json = json + pipelines + '}'
		//end json construction
	
		if (adapterCheck && doAdapter == 'yes') {
			$('#errorModal').modal({
				show: true
			});
			document.getElementById('errorLabel').innerHTML ='Please use A,T,C,G only in the adapter';
			document.getElementById('errorAreas').innerHTML = '';
		}else{
			//insert new values into ngs_runparams
			var runparamsInsert = postInsertRunparams(json, outputdir, run_name, description, perms, group);
			//insert new values into ngs_runlist
			console.log(runparamsInsert);
			console.log(ids);
			var submitted = postInsertRunlist(runparamsInsert[0], ids, runparamsInsert[1]);
			if (submitted) {
				window.location.href = BASE_PATH+"/stat/status";
			}
		}
	}
}

function commonRNACheck(id){
	console.log(id);
}

function sendToFastlane(){
	window.location.href = BASE_PATH+"/fastlane";
}

function sendToStatus(){
	window.location.href = BASE_PATH+"/stat/status/";
}

function generateTableLink(){
	window.location.href = BASE_PATH+"/tablecreator";
}

function backFromDetails(back_type){
	var changeHTML = '';
	var hrefSplit = window.location.href.split("/");
	var search = hrefSplit[hrefSplit.length - 1];
	var id = hrefSplit[hrefSplit.length - 2];
	var type = hrefSplit[hrefSplit.length - 3];

	if (back_type == 'Sample') {
	hrefSplit[hrefSplit.length - 2] = getLaneIdFromSample(hrefSplit[hrefSplit.length - 2]);
	hrefSplit[hrefSplit.length - 3] = 'experiments'
	}else if (back_type == 'Experiment') {
	hrefSplit[hrefSplit.length - 2] = getSeriesIdFromLane(hrefSplit[hrefSplit.length - 2]);
	hrefSplit[hrefSplit.length - 3] = 'experiment_series'
	}else{
		searchSplit = hrefSplit[hrefSplit.length - 1].split('$');
		lastSearch = searchSplit[searchSplit.length - 1].split('=');

		if (lastSearch[1] != undefined) {
		hrefSplit[hrefSplit.length - 2] = lastSearch[1];
		hrefSplit[hrefSplit.length - 3] = lastSearch[0];
		hrefSplit[hrefSplit.length - 4] = 'browse';
		}else{
		hrefSplit.pop();
		hrefSplit.pop();
		hrefSplit.pop();
		hrefSplit[hrefSplit.length -1] = 'index';
		}
	}

	for (var x = 0; x < hrefSplit.length; x++) {
	if (x == (hrefSplit.length - 1)) {
		changeHTML += hrefSplit[x];
	}else{
		changeHTML += hrefSplit[x] + '/';
	}
	}
	window.location.href = changeHTML;
}

/*##### CHECKBOX FUNCTIONS #####*/
function manageChecklists(name, type){
	if (type == 'sample_checkbox') {
		//sample checkbox
		if ( checklist_samples.indexOf( name ) > -1 ){
			//remove
			checklist_samples.splice(checklist_samples.indexOf(name), 1);
			removeFromDolphinBasket(name);
	
			var lane_check = getLaneIdFromSample(name);
			var experiment_check = getExperimentIdFromSample(name);
			if (checklist_lanes.indexOf(lane_check) > -1) {
				if (document.getElementById('lane_checkbox_' + lane_check) != undefined) {
					var check = document.getElementById('lane_checkbox_' + lane_check);
					check.checked = !check.checked;
				}
				checklist_lanes.splice(checklist_lanes.indexOf(lane_check), 1);
			}
			console.log(name);
			console.log(experiment_check);
			if (checklist_experiment_series.indexOf(experiment_check) > -1) {
				if (document.getElementById('experiment_checkbox_' + experiment_check) != undefined) {
					var check = document.getElementById('experiment_checkbox_' + experiment_check);
					check.checked = !check.checked;
				}
				checklist_experiment_series.splice(checklist_experiment_series.indexOf(experiment_check), 1);
			}
			
			if (document.getElementById('sample_checkbox_' + name) != undefined) {
				if (document.getElementById('sample_checkbox_' + name).checked != false) {
					var check = document.getElementById('sample_checkbox_' + name);
					check.checked = !check.checked;
				}
			}
			if (checklist_samples.length == 0) {
				document.getElementById('clear_basket').disabled = 'true';
			}
		}else{
			//add
			checklist_samples.push(name);
			addToDolphinBasket(name);
			sendBasketInfo(name);
	
			var lane_check = getLaneIdFromSample(name);
			var lane_samples = getLanesToSamples(lane_check);
			var lanes_bool = true;
			var experiment_check = getExperimentIdFromSample(name);
			var experiment_samples = getSamplesFromExperimentSeries(experiment_check);
			var experiment_bool = true;
			
			if (document.getElementById('clear_basket').disabled) {
				document.getElementById('clear_basket').disabled = false;
			}
			if (document.getElementById('sample_checkbox_' + name) != undefined) {
				if (document.getElementById('sample_checkbox_' + name).checked != true) {
					var check = document.getElementById('sample_checkbox_' + name);
					check.checked = !check.checked;
				}
			}
			if (checklist_lanes.indexOf(lane_check) == -1) {
				for(var x = 0; x < lane_samples.length; x++){
					if (lane_samples[x] == undefined) {
						lanes_bool = false;
					}else{
						if (checklist_samples.indexOf(lane_samples[x]) == -1 && lanes_bool) {
							lanes_bool = false;
						}
					}
				}
				if (lanes_bool) {
					if (document.getElementById('lane_checkbox_' + lane_check) != undefined) {
						var check = document.getElementById('lane_checkbox_' + lane_check);
						check.checked = !check.checked;
					}
					checklist_lanes.push(lane_check);
				}
			}
			
			if (checklist_experiment_series.indexOf(experiment_check) == -1) {
				for(var x = 0; x < experiment_samples.length; x++){
					if (experiment_samples[x] == undefined) {
						experiment_bool = false;
					}else{
						if (checklist_samples.indexOf(experiment_samples[x]) == -1 && experiment_bool) {
							experiment_bool = false;
						}
					}
				}
				if (experiment_bool) {
					if (document.getElementById('experiment_checkbox_' + experiment_check) != undefined) {
						var check = document.getElementById('experiment_checkbox_' + experiment_check);
						check.checked = !check.checked;
					}
					checklist_experiment_series.push(experiment_check);
				}
			}
		}
	}else if (type == "experiment_checkbox") {
		//	experiment series checkbox
		if (checklist_experiment_series.indexOf(name) > -1) {
			//remove
			checklist_experiment_series.splice(checklist_experiment_series.indexOf(name), 1);
			var experiment_samples = getSamplesFromExperimentSeries(name);
			console.log(experiment_samples);
			for (var y = 0; y < experiment_samples.length; y++){
				if ( checklist_samples.indexOf( experiment_samples[y] ) > -1 ){
					manageChecklists(experiment_samples[y], 'sample_checkbox');
				}
			}
		}else{
			//add
			checklist_experiment_series.push(name);
			var experiment_samples = getSamplesFromExperimentSeries(name);
			console.log(experiment_samples);
			for (var y = 0; y < experiment_samples.length; y++){
				if ( checklist_samples.indexOf( experiment_samples[y] ) < 0 ){
					manageChecklists(experiment_samples[y], 'sample_checkbox');
				}
			}
		}
	}else{
		//lane checkbox
		if ( checklist_lanes.indexOf( name ) > -1 ){
			//remove
			checklist_lanes.splice(checklist_lanes.indexOf(name), 1);
			var lane_samples = getLanesToSamples(name);
			
			for (var x = 0; x < lane_samples.length; x++) {
				if ( checklist_samples.indexOf( lane_samples[x] ) > -1 ){
					manageChecklists(lane_samples[x], 'sample_checkbox');
				}
			}
		}
		else
		{
			//add
			checklist_lanes.push(name);
			var lane_samples = getLanesToSamples(name);
			
			for (var x = 0; x < lane_samples.length; x++) {
				if (checklist_samples.indexOf(lane_samples[x]) < 0) {
					manageChecklists(lane_samples[x], 'sample_checkbox');
				}
			}
		}
	}
}

function reloadBasket(){
	var lastBasket = getBasketInfo();
	if (lastBasket != undefined) {
		var basketArray = lastBasket.split(",");
		console.log(basketArray);
		for (var x = 0; x < basketArray.length; x++) {
			if (basketArray != '0') {
				manageChecklists(basketArray[x], 'sample_checkbox');
			}
		}
	}
}

function addToDolphinBasket(sampleID){
	var sample_info = getSingleSample(sampleID);
	var sample_name = '';
	var table = $('#dolphin_basket').dataTable();

	if (sample_info[1] != '' && sample_info[1] != 'null' && sample_info[1] != null) {
		sample_name = sample_info[1];
	}else{
		sample_name = sample_info[2];
	}
	
	if (table != null) {
		table.fnAddData([
			sampleID,
			sample_name,
			'<button id="remove_basket_'+sampleID+'" class="btn btn-danger btn-xs pull-right" onclick="manageChecklists(\''+sampleID+'\', \'sample_checkbox\')"><i class="fa fa-times"></i></button>'
		])
	}
}

function removeFromDolphinBasket(sampleID){
	var table = $('#dolphin_basket').dataTable();
	var data = table.fnGetData();
	for (var samp_data in data) {
		if (data[samp_data][0] == sampleID) {
			data.splice(samp_data, 1);
		}
	}
	table.fnClearTable();
	if (data.toString() != "") {
		table.fnAddData(data);
	}else{
		table.fnClearTable();
	}
	removeBasketInfo(name);
	/*
	var tblrow = document.getElementById(sampleID);
	tblrow.parentNode.removeChild(tblrow);
	*/
}

function clearBasket(){
	for(var x = (checklist_samples.length - 1); x >= 0; x = x - 1){
		manageChecklists(checklist_samples[x], 'sample_checkbox');
	}
	flushBasketInfo();
}

/*
 *	Used for datatables, discontinued
 */
function checkOffAllSamples(){
	var hrefSplit = window.location.href.split("/");
	var searchLoc = $.inArray('search', hrefSplit);

	if (searchLoc != -1) {
		var pagination = document.getElementById('st_pagination_samples');
		var pagination_ul = pagination.childNodes;
		var pagination_li = pagination_ul[0].childNodes;
		for(var y = 0; y < pagination_li.length; y++){
			pagination_li[y].setAttribute('onClick', 'checkCheckedList()');
		}
	}
}

/*
 *	Used for datatables, discontinued
 */
function checkOffAllLanes(){
	var hrefSplit = window.location.href.split("/");
	var searchLoc = $.inArray('search', hrefSplit);

	if (searchLoc != -1) {
		var pagination = document.getElementById('st_pagination_lanes');
		var pagination_ul = pagination.childNodes;
		pagination_ul[0].setAttribute('onClick', 'checkCheckedLanes()');
		var pagination_li = pagination_ul[0].childNodes;
		for(var y = 0; y < pagination_li.length; y++){
			pagination_li[y].setAttribute('onClick', 'checkCheckedLanes()');
		}
	}
}

function getValidSamples(lane_samples){
	var valid_samples;
	$.ajax({ type: "GET",
				url: BASE_PATH+"/public/ajax/initialmappingdb.php",
				data: { p: 'laneToSampleChecking', sample_ids: lane_samples.toString()},
				async: false,
				success : function(r)
				{
					valid_samples = r;
				}
			});
	return valid_samples;
}

function checkCheckedList(){
	var allSamples = getAllSampleIds();
	for (var x = 0; x < allSamples.length; x++){
		if ( document.getElementById('sample_checkbox_' + allSamples[x]) != null) {
			if (checklist_samples.indexOf(allSamples[x]) > -1) {
				document.getElementById('sample_checkbox_' + allSamples[x]).setAttribute('checked', 'true');
			}else{
				document.getElementById('sample_checkbox_' + allSamples[x]).removeAttribute('checked');
			}
		}
	}
	
}

function checkCheckedLanes(){
	var allLanes = getAllLaneIds();
	for (var x = 0; x < allLanes.length; x++){
		if ( document.getElementById('lane_checkbox_' + allLanes[x]) != null) {
			if (checklist_lanes.indexOf(allLanes[x]) > -1) {
				document.getElementById('lane_checkbox_' + allLanes[x]).setAttribute('checked', 'true');
			}else{
				document.getElementById('lane_checkbox_' + allLanes[x]).removeAttribute('checked');
			}
		}
	}
}

function passIDData(run_group_id, id){
	currentChecked = name;
	//lane checkbox
	if ( checklist_lanes.indexOf( id ) > -1 ){
	//remove
	checklist_lanes.pop(id);
	checklist_samples.pop(run_group_id);
	}else{
	//add
	checklist_lanes.push(id);
	checklist_samples.push(run_group_id);
	}
}

function returnToIndex(){
	window.location.href = BASE_PATH+"/search/index/";
}

function changeDataGroup(command){
	if (checklist_experiment_series.length == 1) {
		var experiment_series_group;
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/ngsquerydb.php",
			data: { p: 'getExperimentSeriesGroup', experiment: checklist_experiment_series.toString() },
			async: false,
			success : function(s)
			{
				experiment_series_group = s;
			}
		});
		if (command == 'change_owner') {
			//	Change Experiment Series Owner
			document.getElementById('permsLabel').innerHTML = 'Which user should own the selected Experiment Series?';
			document.getElementById('permsDiv').innerHTML = '<select id="permsIDSelect" class="form-control"></select>';
			$.ajax({ type: "GET",
				url: BASE_PATH+"/public/ajax/ngsquerydb.php",
				data: { p: 'getAllUsers', experiment: checklist_experiment_series.toString() },
				async: false,
				success : function(s)
				{
					console.log(s);
					for(var x = 0; x < s.length; x++){
						if (s[x].id == experiment_series_group) {
							document.getElementById('permsIDSelect').innerHTML += '<option value="' + s[x].id + '" selected="true">' + s[x].username + '</option>';
						}else{
							document.getElementById('permsIDSelect').innerHTML += '<option value="' + s[x].id + '">' + s[x].username + '</option>';
						}
					}
				}
			});
			if (document.getElementById('permsIDSelect').innerHTML != '') {
				document.getElementById('confirmPermsButton').setAttribute('style', 'display:show');
				document.getElementById('cancelPermsButton').innerHTML = 'Cancel';
				document.getElementById('confirmPermsButton').setAttribute('onclick', 'confirmPermsPressed()');
			}else{
				document.getElementById('permsLabel').innerHTML = 'You do not have permissions to change this Experiment Series Group.';
				document.getElementById('permsDiv').innerHTML = '';
				document.getElementById('confirmPermsButton').setAttribute('style', 'display:none');
				document.getElementById('cancelPermsButton').innerHTML = 'OK';
			}
		}else if (command == 'change_group') {
			//	Change Experiment Series group
			document.getElementById('permsLabel').innerHTML = 'Which group should own the selected Experiment Series?';
			document.getElementById('permsDiv').innerHTML = '<select id="permsIDSelect" class="form-control"></select>';
			$.ajax({ type: "GET",
					url: BASE_PATH+"/public/ajax/ngsquerydb.php",
					data: { p: 'changeDataGroupNames', experiment: checklist_experiment_series.toString() },
					async: false,
					success : function(s)
					{
						console.log(s);
						for(var x = 0; x < s.length; x++){
							if (s[x].id == experiment_series_group) {
								document.getElementById('permsIDSelect').innerHTML += '<option value="' + s[x].id + '" selected="true">' + s[x].name + '</option>';
							}else{
								document.getElementById('permsIDSelect').innerHTML += '<option value="' + s[x].id + '">' + s[x].name + '</option>';
							}
						}
					}
				});
			if (document.getElementById('permsIDSelect').innerHTML != '') {
				document.getElementById('confirmPermsButton').setAttribute('style', 'display:show');
				document.getElementById('cancelPermsButton').innerHTML = 'Cancel';
				document.getElementById('confirmPermsButton').setAttribute('onclick', 'confirmPermsPressed()');
			}else{
				document.getElementById('permsLabel').innerHTML = 'You do not have permissions to change this Experiment Series Group.';
				document.getElementById('permsDiv').innerHTML = '';
				document.getElementById('confirmPermsButton').setAttribute('style', 'display:none');
				document.getElementById('cancelPermsButton').innerHTML = 'OK';
			}
		}
	}else if (checklist_experiment_series.length == 0){
		document.getElementById('permsLabel').innerHTML = 'You must select a Experiment Series to change it\'s group.';
		document.getElementById('permsDiv').innerHTML = '';
		document.getElementById('confirmPermsButton').setAttribute('style', 'display:none');
		document.getElementById('cancelPermsButton').innerHTML = 'OK';
	}else{
		document.getElementById('permsLabel').innerHTML = 'You may only select one Experiment Series at a time.';
		document.getElementById('permsDiv').innerHTML = '';
		document.getElementById('confirmPermsButton').setAttribute('style', 'display:none');
		document.getElementById('cancelPermsButton').innerHTML = 'OK';
	}
	$('#permsModal').modal({
		show: true
	});
}

function confirmPermsPressed(){
	console.log(document.querySelector("select").selectedOptions[0].value);
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngsquerydb.php",
		data: { p: 'changeDataGroup', group_id: document.querySelector("select").selectedOptions[0].value, experiment: checklist_experiment_series.toString() },
		async: false,
		success : function(s)
		{
			console.log(s);
		}
	});
	document.getElementById('permsLabel').innerHTML = 'Selected data\'s group has been changed!'
	document.getElementById('permsDiv').innerHTML = '';
	document.getElementById('confirmPermsButton').setAttribute('style', 'display:none');
	document.getElementById('cancelPermsButton').innerHTML = 'OK';
}

function changeDataOwner(){
	
}

/*##### SEND TO PIPELINE WITH SELECTION #####*/
function submitSelected(){
	if (selectionTest()) {
		if (initialRunTest()) {
			window.location.href = BASE_PATH+"/pipeline/selected/" + checklist_samples + "$";
		}
	}
}

function selectionTest(){
	if (checklist_samples.length > 0) {
		return true;
	}else{
		$('#deleteModal').modal({
			show: true
		});
		document.getElementById('myModalLabel').innerHTML = 'Selection error';
		document.getElementById('deleteLabel').innerHTML ='No samples/imports selected.  Please select samples/imports in order to send them to the pipeline';
		document.getElementById('deleteAreas').innerHTML = '';
			
		document.getElementById('cancelDeleteButton').innerHTML = "OK";
		document.getElementById('confirmDeleteButton').setAttribute('style', 'display:none');
		return false;
	}
}

function initialRunTest(){
	var valid_samples = [];
	$.ajax({ type: "GET",
				url: BASE_PATH+"/public/ajax/browse_edit.php",
				data: { p: 'intialRunCheck', samples: checklist_samples.toString()},
				async: false,
				success : function(r)
				{
					console.log(r);
					for (var x = 0; x < r.length; x++) {
						valid_samples.push(r[x].sample_id);
					}
				}
			});
	if (valid_samples.length == checklist_samples.length) {
		return true;
	}else{
		$('#deleteModal').modal({
			show: true
		});
		
		var spliced_samples = checklist_samples;
		for(var x = 0; x < valid_samples.length; x++){
			var loc = spliced_samples.indexOf(valid_samples[x]);
			spliced_samples.splice(loc, 1);
		}
		
		document.getElementById('myModalLabel').innerHTML = 'Selection error';
		document.getElementById('deleteLabel').innerHTML ='Some samples/imports selected have not finished their initial processing.';
		document.getElementById('deleteAreas').innerHTML = 'You cannot use these sample(s) within the pipeline until they finish their initial processing:' +
			'<br><br>Sample id(s): ' + spliced_samples.join(", ");
			
		document.getElementById('cancelDeleteButton').innerHTML = "OK";
		document.getElementById('confirmDeleteButton').setAttribute('style', 'display:none');
		return false;
	}
}

/*##### CHECK RADIO SELECTED FUNCTION #####*/
function findRadioChecked(title){
	var value = ""
	if (document.getElementById(title+"_yes").checked) {
		value = document.getElementById(title+"_yes").value;
	}else{
		value = document.getElementById(title+"_no").value;
	}
	return value;
}

function findRNAChecked(titles){
	var value = ""
	for(var x = 0; x < titles.length - 1; x++){
		if (document.getElementById(titles[x]+"_yes").checked) {
			value = document.getElementById(titles[x]+"_yes").value;
		}
	}
	if (value == "") {
		value = "no"
	}
	return value;
}

/*##### REMOVE PIPELINES #####*/
function removePipes(num){
	var div = document.getElementById('TESTBOXAREA_'+ num);
	var index = currentPipelineID.indexOf(num);
	div.parentNode.removeChild(div);
	if (currentPipelineVal[index] == pipelineDict[0]) {
	rsemSwitch = false;
	}
	currentPipelineVal.splice(index,1);
	currentPipelineID.splice(index,1);
}

/*##### ADD PIPELINES #####*/
function additionalPipes(){
	//find parent div
	var master = document.getElementById('masterPipeline');
	//create children divs/elements
	var outerDiv = createElement('div', ['id', 'class', 'style'], ['TESTBOXAREA_'+pipelineNum, 'callout callout-info margin', 'display:""']);
	var innerDiv = document.createElement( 'div' );
	//attach children to parent
	innerDiv.appendChild( createElement('select',
					['id', 'class', 'onchange', 'OPTION_DIS_SEL', 'OPTION', 'OPTION', 'OPTION', 'OPTION', 'OPTION'],
					['select_'+pipelineNum, 'form-control', 'pipelineSelect('+pipelineNum+')', '--- Select a Pipeline ---',
					pipelineDict[0], pipelineDict[1], pipelineDict[2], pipelineDict[3], pipelineDict[4]]));
	innerDiv.appendChild( createElement('div', ['id'], ['select_child_'+pipelineNum]));
	outerDiv.appendChild( innerDiv );
	outerDiv.appendChild( createElement('input', ['id', 'type', 'class', 'style', 'value', 'onclick'],
					['removePipe_'+pipelineNum, 'button', 'btn btn-primary', 'display:""', 'Remove Pipeline',
					'removePipes('+pipelineNum+')']));
	//attach to master
	master.appendChild( outerDiv );
	pipelineNum = pipelineNum + 1;
}

function deselectCondition(condition, pipeNum){
	var selection = document.getElementById('multi_select_'+condition+'_'+pipeNum);
	if (condition == 1) {
		var opposite = 2;
	}else if(condition == 2){
		var opposite = 1;
	}else if (condition == 3) {
		var opposite = 4;
	}else if (condition == 4) {
		var opposite = 3;
	}
	var opposite_selection = document.getElementById('multi_select_'+opposite+'_'+pipeNum);
	for (var x = 0; x < selection.options.length; x++) {
		var opt = selection.options[x];
		var opposite_opt = opposite_selection.options[x];
		if (opt.selected) {
			opposite_opt.disabled = true;
			opposite_opt.style.opacity = 0.4;
		}else{
			opposite_opt.disabled = false;
			opposite_opt.style.opacity = 1;
		}
	}
}

function getMultipleSelectionBox(selection) {
	var selected = [];
	for (var x = 0; x < selection.options.length; x++) {
		var opt = selection.options[x];
		if (opt.selected) {
			selected.push(opt.value);
		}
	}
	return selected;
}

 /*##### PUSH IF SELECTED FUNCTION #####*/
function findAdditionalInfoValues(goWord, additionalArray){
	var values = [];
	if (goWord == "yes") {
	for (var i = 0, len = additionalArray.length; i < len; i++) {
		if (document.getElementById(additionalArray[i]+'_val') != null) {
		values.push(document.getElementById(additionalArray[i]+'_val').value);
		}else{
		if(document.getElementById(additionalArray[i]+'_yes') != null){
			if(document.getElementById(additionalArray[i]+'_yes').checked){
			values.push(additionalArray[i]);
			}
		}
		}
	}
	}else{
	for (var i = 0, len = additionalArray.length; i < len; i++) {
		values.push('none');
	}
	}
	return values;
}
/*##### GENERATE ADDITIONAL PIPELINE STR FOR JSON #####*/
function findPipelineValues(){
	var pipeJSON = "";
	if (currentPipelineID.length > 0) {
		pipeJSON = ',"pipeline":["'
	}
	for (var y = 0; y < currentPipelineID.length; y++) {
		pipeJSON += currentPipelineVal[y];
		var masterDiv = document.getElementById('select_child_'+currentPipelineID[y]).getElementsByTagName('*');
		
		var conditions_array = [];
		var conditions_type_array = [];
		var multireset = false;
		for (var x = 0; x < masterDiv.length; x++) {
			if (multireset == true) {
				conditions_array = [];
				conditions_type_array = [];
				multireset = false;
			}
			var e = masterDiv[x];
			if (e.type != undefined) {
				if (e.type == "select-multiple") {
					if (conditions_array.length == 0) {
						var current_cond = getMultipleSelectionBox(e);
						for(z in current_cond){
							conditions_array.push(current_cond[z]);
							conditions_type_array.push('Cond1');
						}
					}else{
						var current_cond2 = getMultipleSelectionBox(e);
						for(z in current_cond2){
							conditions_array.push(current_cond2[z]);
							conditions_type_array.push('Cond2');
						}
						pipeJSON += ':' + conditions_array.toString() + ':' + conditions_type_array.toString();
						multireset = true;
					}
				}else{
					if (e.type == 'checkbox') {
						if (e.checked) {
							pipeJSON += ':1';
						}else{
							pipeJSON += ':0';
						}
					}else if(e.type != 'radio'){
						pipeJSON += ':' + e.value.replace(/\r\n|\r|\n/g, "__cr____cn__");
					}
				}
			}
		}
		if (currentPipelineID[y] == currentPipelineID[currentPipelineID.length - 1]) {
			pipeJSON += '"]';
		}else{
			pipeJSON += '","';
		}
	}
	return pipeJSON;
}

/*##### GENERATE ADDITIONAL CUSTOM SEQUENCE SET FOR JSON #####*/
function findCustomSequenceSets(previous){
	var pipeJSON = '';
	var placeholdName = '';
	if (customSeqNumCheck.length > 0) {
	//start json str
	pipeJSON = ',"custom":["';
	}
	for (var y = 0; y < customSeqNumCheck.length; y++){
	var masterDiv = document.getElementById('custom_seq_inner_'+customSeqNumCheck[y]).getElementsByTagName('*');
	for (var x = 0; x < masterDiv.length - 1; x++){
		var e = masterDiv[x];
		if (e.id == 'custom_5_'+customSeqNumCheck[y]) {
		if (e.value == 'yes') {
			pipeJSON+= ':1';
		}else{
			pipeJSON+= ':0';
		}
		}else if (e.type != undefined) {
		if (x == 2) {
			pipeJSON+= e.value;
		}else{
			pipeJSON+= ':' + e.value;
		}
		if (x == 5) {
			placeholdName = e.value;
		}
		}
	}
	if (customSeqNumCheck[y] == customSeqNumCheck[customSeqNumCheck.length - 1]) {
		pipeJSON += ':' + previous + '"]';
	}else{
		pipeJSON += ':' + previous + '","';
		previous = placeholdName;
	}
	}
	return pipeJSON;
}

function sequenceSetsBtn(){
	var outerDiv = document.getElementById('custom_seq_outer');
	var innerDiv = createElement('div', ['id', 'class'], ['custom_seq_inner_'+customSeqNum, 'callout callout-info margin']);

	var babyDiv1 = createElement('div', [], []);
	var babyDiv2 = createElement('div', [], []);
	var babyDiv3 = createElement('div', [], []);
	var babyDiv4 = createElement('div', [], []);
	var babyDiv5 = createElement('div', [], []);

	babyDiv1.appendChild(createElement('label', ['class','TEXTNODE'], ['box-title', 'Custom sequence index file (full path)']));
	babyDiv1.appendChild(createElement('input', ['id', 'class', 'type', 'value'], ['custom_1_'+customSeqNum, 'form-control', 'text', '']));
	innerDiv.appendChild(babyDiv1);

	babyDiv2.appendChild(createElement('label', ['class','TEXTNODE'], ['box-title', 'Name of the index']));
	babyDiv2.appendChild(createElement('input', ['id', 'class', 'type', 'value'], ['custom_2_'+customSeqNum, 'form-control', 'text', '']));
	innerDiv.appendChild(babyDiv2);

	babyDiv3.appendChild(createElement('label', ['class','TEXTNODE'], ['box-title', 'Bowtie parameters']));
	babyDiv3.appendChild(createElement('input', ['id', 'class', 'type', 'value'], ['custom_3_'+customSeqNum, 'form-control', 'text', '']));
	innerDiv.appendChild(babyDiv3);

	babyDiv4.appendChild(createElement('label', ['class','TEXTNODE'], ['box-title', 'Description']));
	babyDiv4.appendChild(createElement('input', ['id', 'class', 'type', 'value'], ['custom_4_'+customSeqNum, 'form-control', 'text', '']));
	innerDiv.appendChild(babyDiv4);

	babyDiv5.appendChild(createElement('label', ['class','TEXTNODE'], ['box-title', 'Filter Out']));
	babyDiv5.appendChild(createElement('select', ['id', 'class', 'value', 'OPTION', 'OPTION'], ['custom_5_'+customSeqNum, 'form-control', '', 'yes', 'no']));
	innerDiv.appendChild(babyDiv5);

	innerDiv.appendChild(createElement('input', ['id', 'class', 'type', 'value', 'onclick'],['remove_custom_'+customSeqNum, 'btn btn-primary', 'button', 'Remove Custom Sequence Set', 'removeSequenceSetsBtn('+customSeqNum+')']));
	outerDiv.appendChild(innerDiv);

	customSeqNumCheck.push(customSeqNum);
	customSeqNum++;
}

function fillCustomSequenceSet(num, dataArray){
	document.getElementById('custom_1_'+num).value = dataArray[0];
	document.getElementById('custom_2_'+num).value = dataArray[1];
	document.getElementById('custom_3_'+num).value = dataArray[2];
	document.getElementById('custom_4_'+num).value = dataArray[3];
	if (dataArray[4] == 0) {
	document.getElementById('custom_5_'+num).value = 'no';
	}else{
	document.getElementById('custom_5_'+num).value = 'yes';
	}
}

function removeSequenceSetsBtn(num){
	var removing = document.getElementById('custom_seq_inner_'+num);
	removing.parentNode.removeChild(removing);
	customSeqNumCheck.splice(customSeqNumCheck.indexOf(num), 1);
}

function changeRNAParamsBtn(){
	if(document.getElementById('change_params_inner') != undefined){
	document.getElementById('change_params_outer').removeChild(document.getElementById('change_params_inner'));
	}else{
	var outerDiv = document.getElementById('change_params_outer');

	var innerDiv = createElement('div', ['id', 'style'], ['change_params_inner', 'display:"none"']);
	var label = createElement('label', ['class','TEXTNODE'], ['box-title', 'Bowtie Parameters']);
	var textBox = createElement('input', ['id', 'class', 'type', 'value'], ['change_params_val', 'form-control', 'text', '-N 1']);

	innerDiv.appendChild(label);
	innerDiv.appendChild(textBox);
	outerDiv.appendChild(innerDiv);
	}
}

function selectTrimming(select_id, five_num, three_num) {
	var trim_select = document.getElementById(select_id);
	var trim_parent_parent = trim_select.parentNode.parentNode
	var single_option = trim_select.childNodes[0];
	var paired_option = trim_select.childNodes[1];

	if (trim_select.value == paired_option.value) {
	var five_len = createElement('div', ['class'], ['col-md-6']);
	var three_len = createElement('div', ['class'], ['col-md-6']);

	five_len.appendChild(createElement('label', ['class','TEXTNODE'], ['box-title', '5 length 2']));
	five_len.appendChild(createElement('input', ['id', 'class', 'type', 'value'], ['5 length 2_val', 'form-control', 'text', five_num]));
	trim_parent_parent.appendChild(five_len);

	three_len.appendChild(createElement('label', ['class','TEXTNODE'], ['box-title', '3 length 2']));
	three_len.appendChild(createElement('input', ['id', 'class', 'type', 'value'], ['3 length 2_val', 'form-control', 'text', three_num]));
	trim_parent_parent.appendChild(three_len);
	}else{
	trim_parent_parent.removeChild(trim_parent_parent.childNodes[5]);
	trim_parent_parent.removeChild(trim_parent_parent.childNodes[4]);
	}
}

function bisulphiteSelect(id, num){
	if (id == num+'_RRBS') {
		var dig_site = document.getElementById('text_1_'+num);
		dig_site.disabled = false;
		dig_site.value = 'C-CGG';
	}else{
		var dig_site = document.getElementById('text_1_'+num);
		dig_site.disabled = true;
		dig_site.value = '';
	}
}
