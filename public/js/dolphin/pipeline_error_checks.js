/*
 *	pipeline_error_checks.js
 *	
 *	Storage of pipeline error check functions.
 *
 *	Functions consist of:
 *		- generating pipeline creation checks
 *		- generation of submission checks
 *		- creation of error output
 */

//	START GLOBAL VARIABLES

//	END GLOBAL VARIABLES


//	START GENERIC FUNCTIONS

function displayErrorModal(modal, message){
	//	If modal string passed isn't jquery
	if (modal.substring(0,1) != '#') {
		modal = '#' + modal;
	}
	$(modal).modal({
		show: true
	});
	document.getElementById('errorLabel').innerHTML = message;
	document.getElementById('errorAreas').innerHTML = '';
}

function checkPipelineExists(num, type){
	var check = false;
	if (currentPipelineVal.indexOf(type) > -1){
		check = true;
	}
	return check;
}

function checkPipelineDoesNotExist(num, type) {
	var check = false;
	if (currentPipelineVal.indexOf(type) == -1){
		check = true;
	}
	return check;
}

function checkPipelineUpstream(num, type){
	var check = false;
	if (currentPipelineID[currentPipelineVal.indexOf(type)] < num){
		check = true;
	}
	return check;
}

function checkPipelineDownstream(num, type){
	var check = false;
	if (currentPipelineID[currentPipelineVal.indexOf(type)] > num){
		check = true;
	}
	return check;
}

function checkPipelineReplacing(num, type){
	var check = false
	if (currentPipelineID[currentPipelineVal.indexOf(type)] == num) {
		check = true;
	}
	return check;
}

function checkPipelineVariableDependency(num, type, id){
	var check = false;
	//	Checkbox Check
	if (id.indexOf('check') > -1) {
		if (document.getElementById(id).checked == false) {
			check = true;
		}
	}
	return check;
}

function checkFieldNucleotidesOnly(input){
	var check = false;
	if (document.getElementById(input).value.match(/[bd-fh-sv-zBD-FH-SV-Z0-9\!\@\#\$\%\^\&\*\(\)\_\+\-\=\\\|\[\]\{\}\;\'\:\"\,\.\/\<\>\?\`\~]+/g)) {
		check = true;
	}
	return check;
}

function checkFieldCheckboxChecked(input){
	var check = false;
	if (!document.getElementById(input).checked) {
		check = true;
	}
	return check;
}

function checkFieldSelection(input, value){
	var check = false;
	if (!document.getElementById(input).value == value) {
		check = true;
	}
	return check;
}

function checkFieldsEmpty(input){
	var check = false;
	if (document.getElementById(input).value == null || document.getElementById(input).value == undefined || document.getElementById(input).value.trim() == "") {
		check = true;
	}
	return check;
}

function checkFieldIsNotInt(input){
	var check = false;
	if (isNaN(document.getElementById(input).value) || document.getElementById(input).value.toString().indexOf('.') > -1) {
		check = true;
	}
	return check;
}

function checkFieldIsNotFloat(input){
	var check = false;
	if (isNaN(document.getElementById(input).value) || document.getElementById(input).value.toString().indexOf('.') == -1) {
		check = true;
	}
	return check;
}

function checkFieldAlphaNumeric(input){
	var check = false;
	var alpha_numeric = /^[0-9a-zA-Z]*$/i;
	if (!document.getElementById(input).value.match(alpha_numeric)) {
		check = true;
	}
	return check;
}
function checkFieldAlphaNumericAdditionalChars(input, additional){
	var check = false;
	var alpha_numeric = new RegExp('^[0-9a-zA-Z' + additional + ']*$', 'i');
	if (!document.getElementById(input).value.match(alpha_numeric)) {
		check = true;
	}
	return check;
}

function checkFieldMultiSelectEmpty(input1, input2){
	var check = false;
	if (document.getElementById(input1).value == "") {
		check = true;
	}else if(document.getElementById(input2).value == "" ){
		check = true;
	}
	return check;
}

function checkChipMultiSelectEmpty(input){
	var check = false;
	if (document.getElementById(input).value == "") {
		check = true;
	}
	return check;
}

function checkGenomeType(input, genome){
	var check = false;
	if (document.getElementById(input).value != genome) {
		check = true;
	}
	return check;
}

function checkEmptyDataTable(table_string){
	var check = false;
	var table = $(table_string).dataTable();
	var table_nodes = table.fnGetNodes();
	if (table_nodes.length == 0) {
		check = true;
	}
	return check;
}

function checkDataTableContentsIDEmpty(table_string, content_num){
	var check = false;
	var table = $(table_string).dataTable();
	var table_nodes = table.fnGetNodes();
	for (var x = 0; x < table_nodes.length; x++){
		if (table_nodes[x].children[0].children[content_num].id == "" || table_nodes[x].children[0].children[content_num].id == undefined) {
			check = true
		}
	}
	return check;
}

function checkDataTableContentsIDRegex(table_string, content_num, regex_string){
	var check = false;
	var table = $(table_string).dataTable();
	var table_nodes = table.fnGetNodes();
	var regex = new RegExp(regex_string);
	for (var x = 0; x < table_nodes.length; x++){
		if (!regex.test(table_nodes[x].children[0].children[content_num].id)) {
			check = true
		}
	}
	return check;
}

//	END GENERIC FUNCTIONS
//	************************************************************************
//	START GROUPED FUNCTIONS

function pipelineSelectCheck(num, type){
	//	RSEM
	if (type == 'RNASeqRSEM') {
		//	If a RSEM pipeline already exists
		if(checkPipelineExists(num, type)){
			displayErrorModal('#errorModal', 'You cannot select more than one RSEM pipeline');
			return true;
		}
	//	Tophat
	}else if (type == 'Tophat') {
		//	If a Tophat pipeline already exists
		if(checkPipelineExists(num, type)){
			displayErrorModal('#errorModal', 'You cannot select more than one Tophat pipeline');
			return true;
		}
	//	ChipSeq
	}else if (type == 'ChipSeq') {
		//	If a ChipSeq pipeline already exists
		if(checkPipelineExists(num, type)){
			displayErrorModal('#errorModal', 'You cannot select more than one ChipSeq pipeline');
			return true;
		}
	//	DESeq
	}else if (type == 'DESeq') {
		//	RSEM pipeline must be present
		if(checkPipelineDoesNotExist(num, 'RNASeqRSEM')){
			displayErrorModal('#errorModal', 'DESeq requires a RSEM pipeline to run');
			return true;
		//	RSEM must run before DESeq
		}else if (checkPipelineDownstream(num, 'RNASeqRSEM')) {
			displayErrorModal('#errorModal', 'You must run a RSEM pipeline before you can run DESeq');
			return true;
		//	If DESeq is replacing RSEM
		}else if (checkPipelineReplacing(num, 'RNASeqRSEM')) {
			displayErrorModal('#errorModal', 'DESeq requires a RNASeqRSEM pipeline to run');
			return true;
		}
	//	BisulphiteMapping
	}else if (type == 'BisulphiteMapping') {
		//	If a BisulphiteMapping pipeline already exists
		if(checkPipelineExists(num, type)){
			displayErrorModal('#errorModal', 'You cannot select more than one BisulphiteMapping pipeline');
			return true;
		}
	}else if (type == 'DiffMeth') {
		//	BisulphiteMapping pipeline must be present
		if(checkPipelineDoesNotExist(num,  'BisulphiteMapping')){
			displayErrorModal('#errorModal', 'DiffMeth requires a BisulphiteMapping pipeline to run');
			return true;
		//	BisulphiteMapping must run before BisulphiteMapping
		}else if (checkPipelineDownstream(num, 'BisulphiteMapping')) {
			displayErrorModal('#errorModal', 'You must run a BisulphiteMapping pipeline before you can run DiffMeth');
			return true;
		//	If DiffMeth is replacing BisulphiteMapping
		}else if (checkPipelineReplacing(num, 'BisulphiteMapping')) {
			displayErrorModal('#errorModal', 'DiffMeth requires a BisulphiteMapping pipeline to run');
			return true;
		}
	}else if (type == 'HaplotypeCaller') {
		//	If a HaplotypeCaller pipeline already exists
		if(checkPipelineExists(num, type)){
			displayErrorModal('#errorModal', 'You cannot select more than one HaplotypeCaller pipeline');
			return true;
		//	Tophat/ChipSeq must run before DESeq
		}else{
			var tophat_check = checkPipelineDoesNotExist(num, 'Tophat') || checkPipelineDownstream(num, 'Tophat') || checkPipelineReplacing(num, 'Tophat');
			var chipseq_check = checkPipelineDoesNotExist(num, 'ChipSeq') || checkPipelineDownstream(num, 'ChipSeq') || checkPipelineReplacing(num, 'ChipSeq');
			if (chipseq_check) {
				displayErrorModal('#errorModal', 'You must run a ChipSeq pipeline before you can run HaplotypeCaller');
				return true;
			}
		}
	}
	return false;
}

function pipelineSubmitCheck(non_pipeline, non_pipeline_values, pipeline, pipeline_index, chipseq, chipseq_values){
	//	Non-pipeline checks empty
	var non_pipeline_dictionary = ['adapters', 'quality', 'trim', 'rna', 'split'];
	//	Run name
	if (checkFieldsEmpty('run_name')) {
		displayErrorModal('#errorModal', 'Run Name field cannot be empty');
		return true;
	}
	//	Run Description empty
	if (checkFieldsEmpty('run_description')) {
		displayErrorModal('#errorModal', 'Run Description field cannot be empty');
		return true;
	}
	//	Run outdir empty
	if (checkFieldsEmpty('outdir')) {
		displayErrorModal('#errorModal', 'Output Directory field cannot be empty');
		return true;
	}
	//	Run outdir nonnumeric
	if (checkFieldAlphaNumericAdditionalChars('outdir', '\\_\\-\\/\\.')) {
		displayErrorModal('#errorModal', 'Output Directory must use proper directory syntax');
		return true;
	}
	
	//	Adapters
	if (non_pipeline[0] == 'yes' && (checkFieldNucleotidesOnly(non_pipeline_values[0][0]+"_val") || checkFieldsEmpty(non_pipeline_values[0][0]+"_val")) ) {
		displayErrorModal('#errorModal', 'Adapted must use the appropriate nucleotide syntax in Adapters');
		return true;
	}
	
	//	Quality
	//	window sizw
	if (non_pipeline[1] == "yes" && (checkFieldsEmpty(non_pipeline_values[1][0]+"_val") || checkFieldIsNotInt(non_pipeline_values[1][0]+"_val")) ) {
		displayErrorModal('#errorModal', 'Window Size must be of type int in Quality Filtering');
		return true;
	//	required quality
	}else if (non_pipeline[1] == "yes" && (checkFieldsEmpty(non_pipeline_values[1][1]+"_val") || checkFieldIsNotInt(non_pipeline_values[1][1]+"_val")) ) {
		displayErrorModal('#errorModal', 'Required Quality must be of type int in Quality Filtering');
		return true;
	//	leading
	}else if (non_pipeline[1] == "yes" && (checkFieldsEmpty(non_pipeline_values[1][2]+"_val") || checkFieldIsNotInt(non_pipeline_values[1][2]+"_val")) ) {
		displayErrorModal('#errorModal', 'Leading must be of type int in Quality Filtering');
		return true;
	//	trailing
	}else if (non_pipeline[1] == "yes" && (checkFieldsEmpty(non_pipeline_values[1][3]+"_val") || checkFieldIsNotInt(non_pipeline_values[1][3]+"_val")) ) {
		displayErrorModal('#errorModal', 'Trailing must be of type int in Quality Filtering');
		return true;
	//	minlen
	}else if (non_pipeline[1] == "yes" && (checkFieldsEmpty(non_pipeline_values[1][4]+"_val") || checkFieldIsNotInt(non_pipeline_values[1][4]+"_val")) ) {
		displayErrorModal('#errorModal', 'Minlen must be of type int in Quality Filtering');
		return true;
	}
	
	//	Trim
	//	5 length 1
	if (non_pipeline[2] == "yes" && (checkFieldsEmpty(non_pipeline_values[2][1]+"_val") || checkFieldIsNotInt(non_pipeline_values[2][1]+"_val")) ) {
		displayErrorModal('#errorModal', '5 Length 1 must be of type int in Trimming');
		return true;
	//	3 length 1
	}else if (non_pipeline[1] == "yes" && (checkFieldsEmpty(non_pipeline_values[2][2]+"_val") || checkFieldIsNotInt(non_pipeline_values[2][2]+"_val")) ) {
		displayErrorModal('#errorModal', '3 Length 1 must be of type int in Trimming');
		return true;
	}
	if (document.getElementById(non_pipeline_values[2][0]+'_val').value == 'paired-end') {
		//	5 length 2
		if (non_pipeline[2] == "yes" && (checkFieldsEmpty(non_pipeline_values[2][3]+"_val") || checkFieldIsNotInt(non_pipeline_values[2][3]+"_val")) ) {
			displayErrorModal('#errorModal', '5 Length 2 must be of type int in Trimming');
			return true;
		//	3 length 2
		}else if (non_pipeline[1] == "yes" && (checkFieldsEmpty(non_pipeline_values[2][4]+"_val") || checkFieldIsNotInt(non_pipeline_values[2][4]+"_val")) ) {
			displayErrorModal('#errorModal', '3 Length 2 must be of type int in Trimming');
			return true;
		}
	}
	
	//	RNA
	//	change parameters
	if (non_pipeline[3] == "yes" && document.getElementById(non_pipeline_values[3][8]+'_val') != null) {
		if (checkFieldsEmpty(non_pipeline_values[3][8]+'_val')) {
			displayErrorModal('#errorModal', 'Change Parameters must not be empty in Common RNAs');
			return true;
		}
	}
	
	//	Split
	if (non_pipeline[4] == "yes" && (checkFieldsEmpty(non_pipeline_values[4][0]+'_val') || checkFieldIsNotInt(non_pipeline_values[4][0]+'_val'))) {
		displayErrorModal('#errorModal', 'Number of Reads per File must be of type int in Splitting');
		return true;
	}
	
	//	Pipeline checks
	for(var x = 0; x < pipeline.length; x++){
		var name = pipeline[x]
		//	RSEM
		if (name == 'RNASeqRSEM') {
			//	extFactor is empty and selected
			if (!checkFieldSelection('select_1_'+pipeline_index[x], 'yes') && (checkFieldsEmpty('textarea_2_'+pipeline_index[x]) || checkFieldIsNotInt('textarea_2_'+pipeline_index[x]))) {
				displayErrorModal('#errorModal', 'extFactor field must be of type int within RNASeqRSEM');
				return true;
			//	single end dedup selected
			}else if (!checkFieldCheckboxChecked('checkbox_1_'+pipeline_index[x]) && (!checkFieldSelection('spaired', 'no'))){
				displayErrorModal('#errorModal', 'Deduplication can not be performed on single-end reads');
				return true;
			}
		//	Tophat
		}else if (name == 'Tophat') {
			//	extFactor is empty and selected
			if (!checkFieldSelection('select_1_'+pipeline_index[x], 'yes') && (checkFieldsEmpty('textarea_2_'+pipeline_index[x]) || checkFieldIsNotInt('textarea_2_'+pipeline_index[x]))) {
				displayErrorModal('#errorModal', 'extFactor field must be of type int within Tophat');
				return true;
			//	single end dedup selected
			}else if (!checkFieldCheckboxChecked('checkbox_4_'+pipeline_index[x]) && (!checkFieldSelection('spaired', 'no'))){
				displayErrorModal('#errorModal', 'Deduplication can not be performed on single-end reads');
				return true;
			}
		//	ChipSeq
		}else if (name == 'ChipSeq') {
			//	name
			if (checkDataTableContentsIDEmpty('#json_chiptable', 0)) {
				displayErrorModal('#errorModal', 'Chip Input is missing a name within ChipSeq');
				return true;
			//	correct characters
			}else if (checkDataTableContentsIDRegex('#json_chiptable', 0, '^[a-zA-Z0-9\\-\\_]+$')) {
				displayErrorModal('#errorModal', 'Name must contain alpha-numeric, dash, and underscore characters only within ChipSeq');
				return true;
			}else if (checkEmptyDataTable('#json_chiptable')) {
				displayErrorModal('#errorModal', 'You must select a Sample input for Chip before you run ChipSeq.');
				return true;
			//	single end dedup selected
			}else if (!checkFieldCheckboxChecked('checkbox_2_'+pipeline_index[x]) && (!checkFieldSelection('spaired', 'no'))){
				displayErrorModal('#errorModal', 'Deduplication can not be performed on single-end reads');
				return true;
			}
			
			//	extFactor is empty and selected
			if (!checkFieldSelection('select_3_'+pipeline_index[x], 'yes') && (checkFieldsEmpty('textarea_2_'+pipeline_index[x]) || checkFieldIsNotInt('textarea_2_'+pipeline_index[x]))) {
				displayErrorModal('#errorModal', 'extFactor field must be of type int within ChipSeq');
				return true;
			//	Multimapper is empty
			}else if (checkFieldIsNotInt('text_1_'+pipeline_index[x]) || checkFieldsEmpty('text_1_'+pipeline_index[x])) {
				displayErrorModal('#errorModal', 'Multimapper field must contain an integer value within ChipSeq');
				return true;
			//	Tag size(bp) for MACS is non-int
			}else if (checkFieldIsNotInt('text_2_'+pipeline_index[x]) || checkFieldsEmpty('text_2_'+pipeline_index[x])) {
				displayErrorModal('#errorModal', 'Tag size(bp) for MACS field must contain an integer value within ChipSeq');
				return true;
			//	Band width(bp) for MACS is non-int
			}else if (checkFieldIsNotInt('select_1_'+pipeline_index[x]) || checkFieldsEmpty('select_1_'+pipeline_index[x])) {
				displayErrorModal('#errorModal', 'Band width(bp) for MACS field must contain an integer value within ChipSeq');
				return true;
			//	Effective genome size(bp) is non-int
			}else if (checkFieldIsNotInt('select_2_'+pipeline_index[x]) || checkFieldsEmpty('select_2_'+pipeline_index[x])) {
				displayErrorModal('#errorModal', 'Effective genome size(bp) field must contain an integer value within ChipSeq');
				return true;
			}
		//	DESeq
		}else if (name == 'DESeq') {
			//	Name is empty
			if (checkFieldsEmpty('text_1_'+pipeline_index[x])) {
				displayErrorModal('#errorModal', 'Name field cannot be empty within DESeq');
				return true;
			//	Check naming
			}else if (checkFieldAlphaNumericAdditionalChars('text_1_'+pipeline_index[x], '\\_\\-')) {
				displayErrorModal('#errorModal', 'DESeq name field must be alpha-numeric characters, dashes, or underscores');
				return true;
			//	Check Multiple Selection
			}else if (checkFieldMultiSelectEmpty('multi_select_1_'+pipeline_index[x], 'multi_select_2_'+pipeline_index[x])) {
				displayErrorModal('#errorModal', 'Conditions 1 and 2 cannot be empty within DESeq');
				return true;
			//	padj cutoff is empty or non-float
			}else if (checkFieldIsNotFloat('text_2_'+pipeline_index[x]) || checkFieldsEmpty('text_2_'+pipeline_index[x])) {
				displayErrorModal('#errorModal', 'pAdj cutoff is not of type float within DESeq');
				return true;
			//	Fold Change cutoff is empty or non-int
			}else if (checkFieldIsNotInt('text_3_'+pipeline_index[x]) || checkFieldsEmpty('text_3_'+pipeline_index[x])) {
				displayErrorModal('#errorModal', 'Fold Change cutoff is not of type int within DESeq');
				return true;
			}
		//	BisulphiteMapping
		}else if (name == 'BisulphiteMapping') {
			//	Digestion Site is empty
			if (checkFieldsEmpty('text_1_'+pipeline_index[x])) {
				displayErrorModal('#errorModal', 'Digestion Site field cannot be empty within BisulphiteMapping');
				return true;
			//	extFactor is empty and selected
			}else if (!checkFieldSelection('select_1_'+pipeline_index[x], 'yes') && (checkFieldsEmpty('textarea_2_'+pipeline_index[x]) || checkFieldIsNotInt('textarea_2_'+pipeline_index[x]))) {
				displayErrorModal('#errorModal', 'extFactor field must be of type int within BisulphiteMapping');
				return true;
			//	MethylKit Tile Size
			}else if (!checkFieldCheckboxChecked('checkbox_5_'+pipeline_index[x]) && (checkFieldIsNotInt('text_2_'+pipeline_index[x]) || checkFieldsEmpty('text_2_'+pipeline_index[x])) ) {
				displayErrorModal('#errorModal', 'Tile Size field must be of type int for BisulphiteMapping');
				return true;
			//	MethylKit Step Size
			}else if (!checkFieldCheckboxChecked('checkbox_5_'+pipeline_index[x]) && (checkFieldIsNotInt('text_3_'+pipeline_index[x]) || checkFieldsEmpty('text_3_'+pipeline_index[x]))) {
				displayErrorModal('#errorModal', 'Step Size field must be of type int for BisulphiteMapping');
				return true;
			//	MethylKit Min Coverage
			}else if (!checkFieldCheckboxChecked('checkbox_5_'+pipeline_index[x]) && (checkFieldIsNotInt('text_4_'+pipeline_index[x]) || checkFieldsEmpty('text_4_'+pipeline_index[x])) ) {
				displayErrorModal('#errorModal', 'Min Coverage field must be of type int for BisulphiteMapping');
				return true;
			//	MethylKit Top N Regions
			}else if (!checkFieldCheckboxChecked('checkbox_5_'+pipeline_index[x]) && (checkFieldIsNotInt('text_5_'+pipeline_index[x]) || checkFieldsEmpty('text_5_'+pipeline_index[x])) ) {
				displayErrorModal('#errorModal', 'Top N Regions field must be of type int for BisulphiteMapping');
				return true;
			}
		//	DiffMeth
		}else if (name == 'DiffMeth') {
			//	Name is empty
			if (checkFieldsEmpty('text_1_'+pipeline_index[x])) {
				displayErrorModal('#errorModal', 'Name field cannot be empty within DiffMeth');
				return true;
			//	Name characters
			}else if (checkFieldAlphaNumericAdditionalChars('text_1_'+pipeline_index[x], '\\_\\-')) {
				displayErrorModal('#errorModal', 'DiffMeth name field must be alpha-numeric characters, dashes, or underscores');
				return true;
			//	Check Multiple Selection
			}else if (checkFieldMultiSelectEmpty('multi_select_1_'+pipeline_index[x], 'multi_select_2_'+pipeline_index[x])) {
				displayErrorModal('#errorModal', 'Conditions 1 and 2 cannot be empty within DiffMeth');
				return true;
			}
		//	HaplotypeCaller
		}else if (name == 'HaplotypeCaller') {
			//	Human only
			if (!checkFieldCheckboxChecked('checkbox_1_'+pipeline_index[x]) && checkGenomeType('genomebuild', 'human,hg19')) {
				displayErrorModal('#errorModal', 'Only Human genomes can use the Compare Common SNPs option');
				return true;
			}
			//	Human only
			if (!checkFieldCheckboxChecked('checkbox_2_'+pipeline_index[x]) && checkGenomeType('genomebuild', 'human,hg19')) {
				displayErrorModal('#errorModal', 'Only Human genomes can use the Compare Clinical SNPs option');
				return true;
			}
			//	Human only
			if (!checkFieldCheckboxChecked('checkbox_3_'+pipeline_index[x]) && checkGenomeType('genomebuild', 'human,hg19')) {
				displayErrorModal('#errorModal', 'Only Human genomes can use the Compare Enhancers option');
				return true;
			}
			//	Human only
			if (!checkFieldCheckboxChecked('checkbox_4_'+pipeline_index[x]) && checkGenomeType('genomebuild', 'human,hg19')) {
				displayErrorModal('#errorModal', 'Only Human genomes can use the Compare Promoters option');
				return true;
			}
			//	Human only
			if (!checkFieldCheckboxChecked('checkbox_5_'+pipeline_index[x]) && checkGenomeType('genomebuild', 'human,hg19')) {
				displayErrorModal('#errorModal', 'Only Human genomes can use the Compare Common SNPs option');
				return true;
			}
			//	Min Calling Threshold Confidence
			if (checkFieldIsNotInt('text_1_'+pipeline_index[x]) || checkFieldsEmpty('text_1_'+pipeline_index[x])) {
				displayErrorModal('#errorModal', 'Min Calling Threshold Confidence field must be of type int for HaplotypeCaller');
				return true;
			//	Min Emitting Threshold Confidence
			}else if (checkFieldIsNotInt('text_2_'+pipeline_index[x]) || checkFieldsEmpty('text_2_'+pipeline_index[x])) {
				displayErrorModal('#errorModal', 'Min Emitting Threshold Confidence field must be of type int for HaplotypeCaller');
				return true;
			//	Min Base Quality Score
			}else if (checkFieldIsNotInt('text_3_'+pipeline_index[x]) || checkFieldsEmpty('text_3_'+pipeline_index[x])) {
				displayErrorModal('#errorModal', 'Min Base Quality Score field must be of type int for HaplotypeCaller');
				return true;
			//	Min Reads Per Alignment Start
			}else if (checkFieldIsNotInt('text_4_'+pipeline_index[x]) || checkFieldsEmpty('text_4_'+pipeline_index[x])) {
				displayErrorModal('#errorModal', 'Min Reads Per Alignment Start field must be of type int for HaplotypeCaller');
				return true;
			//	Max Reads In Region Per Sample
			}else if (checkFieldIsNotInt('text_5_'+pipeline_index[x]) || checkFieldsEmpty('text_5_'+pipeline_index[x])) {
				displayErrorModal('#errorModal', 'Max Reads In Region Per Sample field must be of type int for HaplotypeCaller');
				return true;
			}
		}
	}
	return false;
}

//	END GROUPED FUNCTIONS
