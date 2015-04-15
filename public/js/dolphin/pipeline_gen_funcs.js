/*
 *  Author: Nicholas Merowsky
 *  Date: 09 Apr 2015
 *  Ascription:
 */

//GLOBAL VARIABLES
var jsonTypeList = ['genomebuild', 'spaired', 'resume', 'barcodes', 'fastqc', 'adapter', 'quality', 'trim', 'commonind', 'split', 'pipeline', 'advparams', 'custom'];
var radioTypeCheckList = ['pipeline', 'trimpaired', 'advparams', 'custom'];
var currentChecked = "";
var checklist_samples = [];
var checklist_lanes = [];
var pipelineNum = 0;
var customSeqNum = 0;
var customSeqNumCheck = [];
var pipelineDict = ['RNASeqRSEM', 'Tophat', 'ChipSeq'];
var rnaList = ["ercc","rrna","mirna","trna","snrna","rmsk","genome","change_params"];
var qualityDict = ["window size","required quality","leading","trailing","minlen"];
var trimmingDict = ["single or paired-end", "5 length 1", "3 length 1", "5 length 2", "3 length 2"];
var currentPipelineID = [];
var currentPipelineVal =[];
var rsemSwitch = false;

/*##### FILL A RERUN PIPELINE WITH PREVIOUS SELECTIONS #####*/
function rerunLoad() {
    var hrefSplit = window.location.href.split("/");
    var rerunLoc = $.inArray('rerun', hrefSplit)
    var infoArray = [];
    var jsonObj;
    
    //make sure this is a rerun
    if (rerunLoc != -1) {
	infoArray = grabReload(hrefSplit[rerunLoc + 2]);
	jsonObj = infoArray[0];
	//repopulate page
	for (var x = 0; x < (jsonTypeList.length); x++) {
	    if (jsonObj.hasOwnProperty(jsonTypeList[x]) && jsonObj[jsonTypeList[x]] != 'none') {
		var element = document.getElementById(jsonTypeList[x]);
		if (element != null) {
		    if (element.id == "spaired") {
			if ( jsonObj[jsonTypeList[x]] == 'paired') {
			    element.value = 'yes'
			}else{
			    element.value = 'no';
			}
		    }else if (element.id == "resume"){
			    element.value = 'no';
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
			if (splt1.length == 1) {
			    if (jsonTypeList[x] == 'split') {
				document.getElementById('number of reads per file_val').value = jsonObj[jsonTypeList[x]];
			    }else{
				document.getElementById(jsonTypeList[x]+'_val').value = jsonObj[jsonTypeList[x]];
			    }
			}else{
			    for (var z = 0; z < splt1.length; z++) {
				var splt2 = splt1[z].split(",");
				if (jsonTypeList[x] == 'quality') {
				    document.getElementById( qualityDict[z]+'_val' ).value = splt2[0];
				}else if (jsonTypeList[x] == 'trim'){
				    if (z == 0 && jsonObj.hasOwnProperty('trimpaired')) {
					document.getElementById( trimmingDict[z]+'_val').value = 'paired-end';
				    }else if(z == 0 && !jsonObj.hasOwnProperty('trimpaired')){
					document.getElementById( trimmingDict[z]+'_val').value = 'single-end';
				    }
				    document.getElementById( trimmingDict[z+1]+'_val' ).value = splt2[0];
				}else if (jsonTypeList[x] == 'commonind'){
				    for(var y = 0; y < splt2.length; y++){
					$( '#'+splt2[y]+'_yes' ).iCheck('check');
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
				rsemSwitch = true;
			    }else if (splt2[0] == pipelineDict[1]) {
				//Tophat
				additionalPipes();
				document.getElementById('select_'+i).value = pipelineDict[1];
				pipelineSelect(i);
				document.getElementById('textarea_'+i).value = splt2[1];
				document.getElementById('select_1_'+i).value = splt2[2];
				document.getElementById('select_2_'+i).value = splt2[3];
			    }else if (splt2[0] == pipelineDict[2]){
				//Chipseq
				additionalPipes();
				document.getElementById('select_'+i).value = pipelineDict[2];
				pipelineSelect(i);
				document.getElementById('textarea_'+i).value = splt2[1];
				document.getElementById('text_1_'+i).value = splt2[2];
				document.getElementById('text_2_'+i).value = splt2[3];
				document.getElementById('select_1_'+i).value = splt2[4];
				document.getElementById('select_2_'+i).value = splt2[5];
				document.getElementById('select_3_'+i).value = splt2[6];
				document.getElementById('select_4_'+i).value = splt2[7];
			    }
			    
			}
			document.getElementById(jsonTypeList[x]+'_exp_body').setAttribute('style', 'display: block');
		    }
		}
	    }
	}
	document.getElementById('outdir').value = infoArray[1];
	document.getElementById('run_name').value = infoArray[2];
	document.getElementById('description').value = infoArray[3];
    }
}

/*##### SELECT/FILL PIPELINE #####*/
//used to generate divs within the html for the additional pipelines
function pipelineSelect(num){
    //Grab some useful variables
    var pipeType = document.getElementById('select_'+num).value;
    var divAdj = createElement('div', ['id', 'class'], ['select_child_'+num, 'input-group margin col-md-11']);
    //Check for only one RSEM
    if (pipeType == pipelineDict[0] && rsemSwitch)
    {
	alert("Warning: You cannot select more than one additional RSEM pipeline")
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
	    rsemSwitch = true;
	    
	}else if (pipeType == pipelineDict[1]) {
	    //Tophat Pipeline
	    divAdj.appendChild( createElement('label', ['class','TEXTNODE'], ['box-title', 'Tophat parameters:']));
	    divAdj.appendChild( createElement('textarea', ['id', 'class'], ['textarea_'+num, 'form-control']));
	    divAdj = mergeTidy(divAdj, 6,
				[ [createElement('label', ['class','TEXTNODE'], ['box-title', 'IGV/TDF Conversion:']), 
				createElement('select', ['id', 'class', 'OPTION', 'OPTION'], ['select_1_'+num, 'form-control', 'no', 'yes'])],
				[createElement('label', ['class','TEXTNODE'], ['box-title', 'BigWig Conversion:']),
				createElement('select', ['id', 'class', 'OPTION', 'OPTION'], ['select_2_'+num, 'form-control', 'no', 'yes'])] ]);
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
	}
	//replace div
	$('#select_child_'+num).replaceWith(divAdj);
	
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
    var description = document.getElementById("description").value;
    
    //Expanding
    var doBarcode = findRadioChecked("barcodes");
    var doAdapter = findRadioChecked("adapter");
    var doQuality = findRadioChecked("quality");
    var doTrimming = findRadioChecked("trim");
    var doRNA = findRadioChecked("commonind");
    var doSplit = findRadioChecked("split");
    
    var barcode = findAdditionalInfoValues(doBarcode, ["distance", "format"]);
    var adapter = findAdditionalInfoValues(doAdapter, ["adapter"]);
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
    if (freshrun != "yes") {
	json = json + ',"resume":"no"'
    }else{
	json = json + ',"resume":"resume"'
	previous = 'resume';
    }
    json = json + ',"fastqc":"' + fastqc + '"'
    
    //expanding
    //barcode
    if (doBarcode == "yes") {
	json = json + ',"barcodes":"distance,' + barcode[0] + ':format,' + barcode[1] + '"';
	previous = 'barcodes';
    }else{
	json = json + ',"barcodes":"none"';
    }
    //adapter
    if (doAdapter == "yes") {
	previous = 'adapter';
    }
    json = json + ',"adapter":"' + adapter[0] + '"';
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
		json = json + ':' + rna[i]
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
    
    //insert new values into ngs_runparams
    var runparamsInsert = postInsertRunparams(json, outputdir, run_name, description);
    //insert new values into ngs_runlist
    var submitted = postInsertRunlist(runparamsInsert[0], ids, runparamsInsert[1], runparamsInsert[2]);
    if (submitted) {
	window.location.href = "/dolphin/pipeline/status";
    }
}

/*##### CHECKBOX FUNCTIONS #####*/
function pass_data(name, id){
    currentChecked = name;
    if (id == "sample_checkbox") {
    //sample checkbox
	if ( checklist_samples.indexOf( name ) > -1 ){
	    //remove
	    checklist_samples.pop(name);
	}else{
	    //add
	    checklist_samples.push(name);
	}
    }
    else
    {
    //lane checkbox
    if ( checklist_lanes.indexOf( name ) > -1 ){
	    //remove
	    checklist_lanes.pop(name);
	}
	else
	{
	    //add
	    checklist_lanes.push(name);
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

/*##### SEND TO PIPELINE WITH SELECTION #####*/
function submitSelected(){
    window.location.href = "/dolphin/pipeline/selected/" + checklist_samples + "$" + checklist_lanes;
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
					['id', 'class', 'onchange', 'OPTION_DIS_SEL', 'OPTION', 'OPTION', 'OPTION'],
					['select_'+pipelineNum, 'form-control', 'pipelineSelect('+pipelineNum+')', '--- Select a Pipeline ---',
					pipelineDict[0], pipelineDict[1], pipelineDict[2] ]));
    innerDiv.appendChild( createElement('div', ['id'], ['select_child_'+pipelineNum]));
    outerDiv.appendChild( innerDiv );
    outerDiv.appendChild( createElement('input', ['id', 'type', 'class', 'style', 'value', 'onclick'],
					['removePipe_'+pipelineNum, 'button', 'btn btn-primary', 'display:""', 'Remove Pipeline',
					'removePipes('+pipelineNum+')']));
    //attach to master
    master.appendChild( outerDiv );
    pipelineNum = pipelineNum + 1;
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
	for (var x = 0; x < masterDiv.length; x++) {
	    var e = masterDiv[x]
	    if (e.type != undefined) {
		pipeJSON += ':' + e.value;
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
