//	Info Tables
sample_info = [];
lane_info = [];
protocol_info = [];
experiment_info = [];
treatment_info = [];
antibody_info = [];

/*	JSON Tables
	For each term, sort objects based on which table to parse
	The next object will display the JSON field on the left and the database variable on the right
	
	IE:
	
	<JSON TYPE> =
		<INFO ARRAY TO GRAB DATA FROM>:
				<JSON KEY> : <INFO ARRAY VALUE>,
				<JSON KEY> : <INFO ARRAY VALUE>,
				<JSON KEY> : <INFO ARRAY VALUE>
		,
		<INFO ARRAY TO GRAB DATA FROM>:
				<JSON KEY> : <INFO ARRAY VALUE>,
				<JSON KEY> : <INFO ARRAY VALUE>,
				<JSON KEY> : <INFO ARRAY VALUE>
*/
var donor_terms = {
		'experiment_info':{
				"award":'grant',
				"lab":'lab'
		},
		'sample_info':{
				"organism":'organism',
				"life_stage":'life_stage',
				"age":'age',
				"sex":'sex'
		}
};
var experiment_terms = {
		'experiment_info':{
				"award":'grant',
				"lab":'lab'
		},
		'protocol_info':{
				"assay_term_name":'assay_term_name',
				"assay_term_id":'assay_term_id'
		},
		'sample_info':{
				"biosample_term_name":'biosample_term_name',
				"biosample_term_id":'biosample_term_id',
				"biosample_type":'biosample_type',
				"description":'description'
		}
}
var treatment_terms = {
		'treatment_info':{
				"treatment_term_name":'treatment_term_name',
				"treatment_term_id":'treatment_term_id',
				"treatment_type":'treatment_type',
				"amount":'concentration',
				"amount_units":'concentration_units',
				"duration":'duration',
				"duration_units":'duration_units'
		}
}
var biosample_terms = {
		'experiment_info':{
				"award":'grant',
				"lab":'lab'
		},
		'protocol_info':{
				"starting_amount":'starting_amount',
				"starting_amount_units":'starting_amount_units'
		},
		'sample_info':{
				"biosample_term_name":'biosample_term_name',
				"biosample_term_id":'biosample_term_id',
				"biosample_type":'biosample_type',
				"organism":'organism',
				"derived_from":'biosample_derived_from',
				"source":'source'
		},
		'lane_info':{
				"date_obtained":'date_received'
		}
}
var library_terms = {
		'experiment_info':{
				"award":'grant',
				"lab":'lab'
		},
		'sample_info':{
				"spike-ins":'spike_ins',
				"size_range":'avg_insert_size'
		},
		'protocol_info':{
				"nucleic_acid_term_name":'nucleic_acid_term_name',
				"nucleic_acid_term_id":'nucleic_acid_term_id',
				"extraction_method":'extraction_method',
				"crosslinking_method":'crosslinking_method',
				"fragmentation_method":'fragmentation_method'
		}
}
var antibody_terms = {
		'experiment_info':{
				"award":'grant',
				"lab":'lab'
		},
		'antibody_info':{
				"source":'source',
				"product_id":'product_id',
				"lot_id":'lot_id',
				"host_organism":'host_organism',
				"targets":'targets',
				"clonality":'clonality',
				"isotype":'isotype',
				"purifications":'purifications',
				"url":'url'
		}
}
var replicate_terms = {
		'sample_info':{
				"biological_replicate_number":'biological_replica',
				"technical_replicate_number":'technical_replica'
		}
}

//	IDs/accessions/uuid arrays
var donor_ids = [];
var donor_accs = [];
var experiment_ids = [];
var experiment_accs = [];
var treatment_ids = [];
var treatment_uuid = [];
var biosample_ids = [];
var biosample_accs = [];
var library_ids = [];
var library_accs = [];
var antibody_ids = [];
var antibody_accs = [];
var replicate_ids = [];
var replicate_uuids = [];
var submission = true;
var id_hash = {
		donor: 0,
		experiment: 0,
		treatment: 0,
		biosample: 0,
		library: 0,
		antibody_lot: 0,
		replicate: 0
	}
//	RNA, DNA
var nucleic_acid_term_id = ['SO:0000356', 'SO:0000352'];
var log_str = '';

function resetGlobals(){
	sample_info = [];
	lane_info = [];
	protocol_info = [];
	experiment_info = [];
	treatment_info = [];
	antibody_info = [];
	donor_ids = [];
	donor_accs = [];
	experiment_ids = [];
	experiment_accs = [];
	treatment_ids = [];
	treatment_uuid = [];
	biosample_ids = [];
	biosample_accs = [];
	library_ids = [];
	library_accs = [];
	antibody_ids = [];
	antibody_accs = [];
	replicate_ids = [];
	replicate_uuids = [];
	id_hash = {
		donor: 0,
		experiment: 0,
		treatment: 0,
		biosample: 0,
		library: 0,
		antibody_lot: 0,
		replicate: 0
	};
	log_str = '';
}


/*
 *	Check the current sample selection with the index search page for proper submission requirements
 *
 *	Check reponses are output into a modal
 */

function checkForEncodeSubmission(type){
	var boolPass = true;
	var errorMsg = '';
	
	//	If no samples are selected, then output an error modal
	if (checklist_samples.length == 0) {
		$('#deleteModal').modal({
			show: true
		});
		document.getElementById('myModalLabel').innerHTML = 'Encode Checks';
		document.getElementById('deleteLabel').innerHTML = 'Checking for proper ENCODE submission...';
		document.getElementById('deleteAreas').innerHTML = '<b>No samples selected, please select samples before submission to ENCODE</b>';
			
		document.getElementById('cancelDeleteButton').innerHTML = "OK";
		document.getElementById('confirmDeleteButton').setAttribute('style', 'display:none');
		document.getElementById('confirmPatchButton').setAttribute('style', 'display:none');
	}else{
		//	Obtain key sample information
		getDataInfo();
		//	Experiment Series checks
		if(experiment_info.length != 1){
			boolPass = false;
			errorMsg += 'More than one experiement series selected.<br>'
		}
		if (experiment_info[0].lab == undefined) {
			boolPass = false;
			errorMsg += '<b>Lab</b> information for <b>experiment series id: ' + experiment_info[0].id + '</b> is not defined.<br>';
		}
		if (experiment_info[0].grant == undefined) {
			boolPass = false;
			errorMsg += '<b>Grant</b> information for <b>experiment series id: ' + experiment_info[0].id + '</b> is not defined.<br>';
		}
		if (!boolPass) {
				errorMsg += '<br>';
		}
		
		//	Sample Checks
		for(var x = 0; x < sample_info.length; x ++){
			if (sample_info[x].donor == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Donor</b> for <b>sample id: ' + sample_info[x].id + '</b> is not defined.<br>';
			}
			if (sample_info[x].biosample_id == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Biosample id</b> for <b>sample id: ' + sample_info[x].id + '</b> is not defined.<br>';
			}
			if (sample_info[x].organism == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Organism</b> for <b>sample id: ' + sample_info[x].id + '</b> is not defined.<br>';
			}
			if (sample_info[x].samplename == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Samplename</b> for <b>sample id: ' + sample_info[x].id + '</b> is not defined.<br>';
			}
			if (sample_info[x].molecule == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Molecule</b> for <b>sample id: ' + sample_info[x].id + '</b> is not defined.<br>';
			}
			if (sample_info[x].description == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Description</b> for <b>sample id: ' + sample_info[x].id + '</b> is not defined.<br>';
			}
			if (sample_info[x].read_length == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Read Length</b> for <b>sample id: ' + sample_info[x].id + '</b> is not defined.<br>';
			}
			if (sample_info[x].biosample_term_name == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Biosample Term Name</b> for <b>sample id: ' + sample_info[x].id + '</b> is not defined.<br>';
			}
			if (sample_info[x].biosample_term_id == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Biosample Term ID</b> for <b>sample id: ' + sample_info[x].id + '</b> is not defined.<br>';
			}
			if (sample_info[x].biosample_type == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Biosample Type</b> for <b>sample id: ' + sample_info[x].id + '</b> is not defined.<br>';
			}
			
			if (!boolBreak) {
				errorMsg += '<br>';
			}
		}
		
		//	Lane Checks
		for (var x = 0; x < lane_info.length; x++) {
			var boolBreak = true;
			if (lane_info[x].name == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Name</b> for <b>lane id: ' + lane_info[x].id + '</b> is not defined.<br>';
			}
			if (lane_info[x].date_received == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Date received</b> for <b>lane id: ' + lane_info[x].id + '</b> is not defined.<br>';
			}
			if (!boolBreak) {
				errorMsg += '<br>';
			}
		}
		
		//	Protocol Checks
		for (var x = 0; x < protocol_info.length; x++){
			var boolBreak = true;
			if (protocol_info[x].extraction == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Extraction</b> for <b>protocol id: ' + protocol_info[x].id + '</b> is not defined.<br>';
			}
			if (!boolBreak) {
				errorMsg += '<br>';
			}
		}
		
		//	Treatment Checks
		for (var x = 0; x < treatment_info.length; x++){
			var boolBreak = true;
			if (treatment_info[x].treatment_term_name == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Treatment term name</b> for <b>treatment id: ' + treatment_info[x].id + '</b> is not defined.<br>';
			}
			if (treatment_info[x].treatment_term_id == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Treatment term id</b> for <b>treatment id: ' + treatment_info[x].id + '</b> is not defined.<br>';
			}
			if (treatment_info[x].treatment_type == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Treatment type</b> for <b>treatment id: ' + treatment_info[x].id + '</b> is not defined.<br>';
			}
			if (treatment_info[x].concentration == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Concentration</b> for <b>treatment id: ' + treatment_info[x].id + '</b> is not defined.<br>';
			}
			if (treatment_info[x].concentration_units == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Concentration units</b> for <b>treatment id: ' + treatment_info[x].id + '</b> is not defined.<br>';
			}
			if (treatment_info[x].duration == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Duration</b> for <b>treatment id: ' + treatment_info[x].id + '</b> is not defined.<br>';
			}
			if (treatment_info[x].duration_units == undefined) {
				boolPass = false;
				boolBreak = false;
				errorMsg += '<b>Duration units</b> for <b>treatment id: ' + treatment_info[x].id + '</b> is not defined.<br>';
			}
			if (!boolBreak) {
				errorMsg += '<br>';
			}
		}
		
		//	if boolPass passed
		if (boolPass) {
			$('#deleteModal').modal({
				show: true
			});
			document.getElementById('myModalLabel').innerHTML = 'Encode Checks';
			document.getElementById('deleteLabel').innerHTML = 'Checking for proper ENCODE submission...';
			document.getElementById('deleteAreas').innerHTML = '<b>Database checks passed without error for ENCODE submission'+
				'<br><br>Submission might take a few minutes to process after hitting submit, please be patient</b>';
				
			document.getElementById('cancelDeleteButton').innerHTML = "Cancel";
			document.getElementById('confirmDeleteButton').innerHTML = "Submit";
			document.getElementById('confirmDeleteButton').setAttribute('onclick', 'encodeCheckForPatch("'+type+'")');
			document.getElementById('confirmDeleteButton').setAttribute('data-dismiss', '');
			document.getElementById('confirmDeleteButton').setAttribute('class', 'btn btn-success');
			document.getElementById('confirmDeleteButton').setAttribute('style', 'display:show');
			document.getElementById('confirmPatchButton').setAttribute('style', 'display:none');
			document.getElementById('confirmPatchButton').setAttribute('data-dismiss', '');
		}else{
			errorMsg += '<b>Database checks did not pass inspection, submission to ENCODE has been halted.  The entries listed above must have values in order to submit to ENCODE.</b>'
			$('#deleteModal').modal({
				show: true
			});
			document.getElementById('myModalLabel').innerHTML = 'Encode Checks';
			document.getElementById('deleteLabel').innerHTML = 'Checking for proper ENCODE submission...';
			document.getElementById('deleteAreas').innerHTML = errorMsg;
				
			document.getElementById('cancelDeleteButton').innerHTML = "OK";
			document.getElementById('confirmDeleteButton').setAttribute('style', 'display:none');
			document.getElementById('confirmPatchButton').setAttribute('style', 'display:none');
		}
	}
}

/*
 *	Checks is there are uuid's/accession numbers already given to the samples being submitted
 *	The user has the option to write over the current submission or only add metadata that hasn't been
 *	added already.
 */

function encodeCheckForPatch(type){
	var boolPass = true;
	for ( var x = 0; x < sample_info.length; x++ ){
		if (sample_info[x].donor_acc == null || sample_info[x].donor_uuid == null) {
			boolPass = false;
		}else if (sample_info[x].treatment_uuid == null) {
			boolPass = false;
		}else if (sample_info[x].biosample_acc == null || sample_info[x].biosample_uuid == null) {
			boolPass = false;
		}else if (sample_info[x].library_acc == null || sample_info[x].library_uuid == null) {
			boolPass = false;
		}else if (sample_info[x].replicate_uuid == null) {
			boolPass = false;
		}
	}
	for ( var x = 0; x < lane_info.length; x++ ){
		if (lane_info[x].experiment_acc == null || lane_info[x].experiment_uuid == null) {
			boolPass = false;
		}
	}
	
	if (boolPass) {
		if (type == 'metadata') {
			encodePost()
		}else if (type == 'files') {
			encodePostFiles()
		}else{
			encodePost()
			encodePostFiles()
		}
	}else{
		if (type == 'metadata') {
			encodePost()
		}else if (type == 'files') {
			encodePostFiles()
		}else{
			encodePost()
			encodePostFiles()
		}
	}
	console.log(log_str);
	logResponse();
}

/*
 *	Queries the database for all tables that are requried for encode submission and stores them as local variables
 */

function getDataInfo(){
	resetGlobals();
	
	//	Sample Info
	$.ajax({ type: "GET",
		url: BASE_PATH + "/public/ajax/encode_data.php",
		data: { p: 'getSampleDataInfo', samples: checklist_samples.toString() },
		async: false,
		success : function(s)
		{
			console.log(s);
			sample_info = s;
		}
	});
	
	//	Lane, Protocol, Experiment, Treatment, and Antibody ids
	var lane_ids = [];
	var protocol_ids = [];
	var treatment_ids = [];
	var antibody_lot_ids = [];
	for (var x = 0; x < sample_info.length; x++) {
		if (lane_ids.indexOf(sample_info[x].lane_id) < 0 ){
			lane_ids.push(sample_info[x].lane_id);
		}
		if (protocol_ids.indexOf(sample_info[x].protocol_id) < 0 ){
			protocol_ids.push(sample_info[x].protocol_id);
		}
		if (treatment_ids.indexOf(sample_info[x].treatment_id && sample_info[x].treatment_id != null) < 0 ){
			treatment_ids.push(sample_info[x].treatment_id);
		}
		if (antibody_lot_ids.indexOf(sample_info[x].antibody_lot_id) < 0 && sample_info[x].antibody_lot_id != null){
			antibody_lot_ids.push(sample_info[x].antibody_lot_id);
		}
	}
	
	//	Lane Info
	$.ajax({ type: "GET",
		url: BASE_PATH + "/public/ajax/encode_data.php",
		data: { p: 'getLaneDataInfo', lanes: lane_ids.toString() },
		async: false,
		success : function(s)
		{
			lane_info = s;
		}
	});
	//	Protocol Info
	$.ajax({ type: "GET",
		url: BASE_PATH + "/public/ajax/encode_data.php",
		data: { p: 'getProtocolDataInfo', protocols: protocol_ids.toString() },
		async: false,
		success : function(s)
		{
			protocol_info = s;
		}
	});
	//	Experiment Info
	$.ajax({ type: "GET",
		url: BASE_PATH + "/public/ajax/encode_data.php",
		data: { p: 'getSeriesDataInfo', series: sample_info[0].series_id },
		async: false,
		success : function(s)
		{
			experiment_info = s;
		}
	});
	//	Treatment Info
	$.ajax({ type: "GET",
		url: BASE_PATH + "/public/ajax/encode_data.php",
		data: { p: 'getTreatmentDataInfo', treatments: treatment_ids.toString()},
		async: false,
		success : function(s)
		{
			treatment_info = s;
		}
	});
	//	Antibody Info
	$.ajax({ type: "GET",
		url: BASE_PATH + "/public/ajax/encode_data.php",
		data: { p: 'getAntibodyDataInfo', antibodies: antibody_lot_ids.toString()},
		async: false,
		success : function(s)
		{
			antibody_info = s;
		}
	});
}

/*
 *	parses the database tables and creates an appropriate json object to send the metadata through.
 *
 *	Requires a json_type in order to know what type of metadata submission is being set up
 *
 *	creates two arrays of json objets based on if the samples are being patched or posted
 *
 *	returns a tuple of arrays of json objects.
 */

function createEncodeJson(json_type){
	//	Initiate needed variables
	var terms = [];
	var donors = [];
	var organism = [];
	var array_info;
	//	Obtains pre json information and sets up array_info
	if (json_type == 'donor'){
		for(var x = 0; x < sample_info.length; x++){
			if (donors.indexOf(sample_info[x].donor) < 0 ){
				donors.push(sample_info[x].donor);
				organism.push(sample_info[x].organism);
				donor_ids.push(sample_info[x].did);
				donor_accs.push(sample_info[x].donor_acc);
			}
		}
		array_info = donors;
	}else if (json_type == 'treatment') {
		array_info = treatment_info;
	}else if (json_type == 'antibody') {
		array_info = antibody_info;
	}else{
		array_info = sample_info;
	}
	
	var post = [];
	var patch = [];
	//	Using array_info, set up library calls
	for(var x = 0; x < array_info.length; x++){
		var json = {};
		var post_bool = true;
		//	Grab correct ids
		var exp_index = null;
		for(var y = 0; y < experiment_info.length; y++){
			if (experiment_info[y].id == sample_info[x].series_id) {
				exp_index = y;
			}
		}
		var lane_index = null;
		for(var y = 0; y < lane_info.length; y++){
			if (lane_info[y].id == sample_info[x].lane_id) {
				lane_index = y;
			}
		}
		var proto_index = null;
		for(var y = 0; y < protocol_info.length; y++){
			if (protocol_info[y].id == sample_info[x].protocol_id) {
				proto_index = y;
			}
		}
		var treatment_index = null;
		for(var y = 0; y < treatment_info.length; y++){
			if (treatment_info[y].id == sample_info[x].treatment_id) {
				treatment_index = y;
			}
		}
		var antibody_index = null;
		for(var y = 0; y < antibody_info.length; y++){
			if (antibody_info[y].id == sample_info[x].antibody_lot_id) {
				antibody_index = y;
			}
		}
		if (donors.indexOf(sample_info[x].donor) < 0 ){
			donors.push(sample_info[x].donor);
		}
		
		//	link correct terms, gather aliases, determine patch or post
		if (json_type == 'donor') {
			terms = donor_terms;
			json['aliases'] = [experiment_info[0].lab+':'+donors[x]];
			if (donor_accs[x] != null) {
				post_bool = false;
			}
			json = createJSON(json, x, donor_terms, exp_index, lane_index, proto_index, treatment_index, antibody_index)
		}else if (json_type == 'experiment') {
			terms = experiment_terms;
			if (experiment_info[0].lab != null && sample_info[x].samplename != null) {
				json['aliases'] = [experiment_info[0].lab +':'+sample_info[x].samplename+'_'+protocol_info[proto_index].assay_term_name];
			}
			experiment_ids.push(sample_info[x].id);
			experiment_accs.push(sample_info[x].experiment_acc);
			if (sample_info[x].experiment_acc != null) {
				post_bool = false;
			}
			json = createJSON(json, x, experiment_terms, exp_index, lane_index, proto_index, treatment_index, antibody_index)
		}else if (json_type == 'treatment') {
			terms = treatment_terms;
			json['aliases'] = [experiment_info[0].lab+':'+treatment_info[treatment_index].name+'_'+treatment_info[treatment_index].duration + treatment_info[treatment_index].duration_units.substring(0,1)];
			treatment_ids.push(treatment_info[x].id);
			treatment_uuid.push(treatment_info[x].uuid);
			if (treatment_info[x].uuid != undefined) {
				post_bool = false;
			}
			json = createJSON(json, x, treatment_terms, exp_index, lane_index, proto_index, treatment_index, antibody_index)
		}else if (json_type == 'biosample') {
			terms = biosample_terms;
			json['aliases'] = [experiment_info[0].lab +':'+sample_info[x].samplename];
			json['donor'] = experiment_info[0].lab +':'+sample_info[x].donor;
			json['treatments'] = [experiment_info[0].lab+':'+treatment_info[treatment_index].name+'_'+treatment_info[treatment_index].duration + treatment_info[treatment_index].duration_units.substring(0,1)];
			biosample_ids.push(sample_info[x].id);
			biosample_accs.push(sample_info[x].biosample_acc);
			if (sample_info[x].biosample_acc != null) {
				post_bool = false;
			}
			json = createJSON(json, x, biosample_terms, exp_index, lane_index, proto_index, treatment_index, antibody_index)
		}else if (json_type == 'library') {
			terms = library_terms;
			json['aliases'] = [experiment_info[0].lab +':'+sample_info[x].samplename+'_lib'];
			json['biosample'] = experiment_info[0].lab + ':' + sample_info[x].samplename;
			library_ids.push(sample_info[x].id);
			library_accs.push(sample_info[x].library_acc);
			if (sample_info[x].library_acc != null) {
				post_bool = false;
			}
			json = createJSON(json, x, library_terms, exp_index, lane_index, proto_index, treatment_index, antibody_index)
		}else if (json_type == 'antibody') {
			terms = antibody_terms;
			json['aliases'] = [experiment_info[0].lab + ':' + antibody_info[antibody_index].product_id];
			antibody_ids.push(antibody_info[x].id);
			antibody_accs.push(antibody_info[x].antibody_lot_uuid);
			if (antibody_info[x].antibody_lot_uuid != null) {
				post_bool = false;
			}
			json = createJSON(json, x, antibody_terms, exp_index, lane_index, proto_index, treatment_index, antibody_index)
		}else if (json_type == 'replicate') {
			terms = replicate_terms;
			json['aliases'] = [experiment_info[0].lab +':'+sample_info[x].samplename+'_replica'];
			json['experiment'] = experiment_info[0].lab +':'+sample_info[x].samplename+'_'+protocol_info[proto_index].assay_term_name;
			json['library'] = experiment_info[0].lab +':'+sample_info[x].samplename+'_lib';
			if (sample_info[x].antibody_lot_id != null) {
				json['antibody'] = antibody_info[antibody_index].antibody_lot_acc;
			}
			replicate_ids.push(sample_info[x].id);
			replicate_uuids.push(sample_info[x].replicate_uuid);
			if (sample_info[x].replicate_uuid != null) {
				post_bool = false;
			}
			json = createJSON(json, x, replicate_terms, exp_index, lane_index, proto_index, treatment_index, antibody_index)
		}
		
		if (post_bool) {
			post.push(json);
		}else{
		    patch.push(json);
		}
	}
	return [post, patch];
}

function createJSON(json, sample, terms, exp, lane, proto, treatment, antibody){
		for (var k in terms) {
				for(var p in terms[k]){
						if (k == 'experiment_info') {
								if (experiment_info[exp][terms[k][p]] != undefined) {
										json[p] = experiment_info[exp][terms[k][p]]
								}
						}else if (k == 'sample_info') {
								if (sample_info[sample][terms[k][p]] != undefined) {
										json[p] = sample_info[sample][terms[k][p]]
								}
						}else if (k == 'protocol_info') {
								if (protocol_info[proto][terms[k][p]] != undefined) {
										json[p] = protocol_info[proto][terms[k][p]]
								}
						}else if (k == 'treatment_info') {
								if (treatment_info[treatment][terms[k][p]] != undefined) {
										json[p] = treatment_info[treatment][terms[k][p]]
								}
						}else if (k == 'antibody_info') {
								if (antibody_info[antibody][terms[k][p]] != undefined) {
										json[p] = antibody_info[antibody][terms[k][p]]
								}
						}else if (k == 'lane_info') {
								if (lane_info[lane][terms[k][p]] != undefined) {
										json[p] = lane_info[lane][terms[k][p]]
								}
						}
						
				}
		}
		console.log(json)
		return json
}

/*
 *	Calls the file post php script to post file data to encode
 */

function encodeFilePost(){
	console.log(biosample_ids);
	console.log(experiment_accs);
	console.log(replicate_uuids);
	var response = '';
	for(var x = 0; x < biosample_ids.length; x++){
		$.ajax({ type: "GET",
			url: BASE_PATH + "/public/ajax/encode_files.php",
			data: { sample_id: biosample_ids[x], experiment: experiment_accs[x], replicate: replicate_uuids[x] },
			async: false,
			success : function(s)
			{
				console.log(s)
				//	Print out server response
				var file_post_string = "[" + s + "]";;
				var file_response = JSON.parse(file_post_string);
				console.log(file_response);
				for(var x = 0; x < file_response.length; x=x+2){
					response += "<b>File submission reponse:</b><br>" + JSON.stringify(file_response[x]) + "<br><br>";
				}
			}
		});
	}
	
	return response
}

/*
 *	Using the json created, submit the json using the proper type of submission protocol.
 *
 *	Reports the php scripts output for each submission
 */

function encodeSubmission(name, json, subType, type, table){
	var response = [];
	var string_array_json = '';
	var output = '';
	var item;
	var accs;
	
	if (type == "donor") {
		item = donor_ids;
		accs = donor_accs;
	}else if (type == "experiment") {
		item = experiment_ids;
		accs = experiment_accs;
	}else if (type == "treatment") {
		item = treatment_ids;
		accs = treatment_uuid;
	}else if (type == "biosample") {
		item = biosample_ids;
		accs = biosample_accs;
	}else if (type == "library") {
		item = library_ids;
		accs = library_accs;
	}else if (type == "antibody_lot") {
		item = antibody_ids;
		accs = antibody_accs;
	}else if (type == "replicate") {
		item = replicate_ids;
		accs = replicate_uuids;
	}
	if (subType == "post") {
		$.ajax({ type: "GET",
			url: BASE_PATH + "/public/ajax/encode_post.php",
			data: { json_name: name, json_passed: json },
			async: false,
			success : function(s)
			{
				var string_array_json = "[" + s + "]";
				console.log(string_array_json);
				response = JSON.parse(string_array_json);
			}
		});
	}else{
		$.ajax({ type: "GET",
			url: BASE_PATH + "/public/ajax/encode_patch.php",
			data: { json_name: name, json_passed: json, accession: accs.toString() },
			async: false,
			success : function(s)
			{
				string_array_json = "[" + s + "]";
				log_str += s + "\n";
				console.log(string_array_json);
				response = JSON.parse(string_array_json);
			}
		});
	}
	console.log(subType);
	output += '<b>' + name + ' ' + subType + ' Submission:</b></br>';
	
	for(var x = 0; x < response.length; x++){
		console.log(response)
		if (response[x].status.toLowerCase() == 'success') {
			//	SUCCESS
			output += 'Success<br>';
			if (response[x]['@graph'][0].accession != undefined) {
				output += 'accession: ' + response[x]['@graph'][0].accession + '<br>';
			}
			if (response[x]['@graph'][0].uuid != undefined) {
				output += 'uuid: ' + response[x]['@graph'][0].uuid + '<br><br>';
			}
			if (type == "treatment") {
				if (response[x]['@graph'][0].uuid != undefined) {
					console.log("inside treatment")
					submitAccessionAndUuid(item[id_hash[type]], table, type, "treatment", response[x]['@graph'][0].uuid);
				}
			}else if (type == "replicate"){
				if (replicate_uuids[x] == null || replicate_uuids[x] == "" || replicate_uuids[x] == undefined) {
					replicate_uuids[x] = response[x]['@graph'][0].uuid;
				}
				if (response[x]['@graph'][0].uuid != undefined) {
					submitAccessionAndUuid(item[id_hash[type]], table, type, "replicate", response[x]['@graph'][0].uuid);
				}
			}else if (type == "experiment"){
				if (experiment_accs[x] == null || experiment_accs[x] == "" || experiment_accs[x] == undefined) {
					experiment_accs[x] = response[x]['@graph'][0].accession;
				}
				if (response[x]['@graph'][0].accession != undefined && response[x]['@graph'][0].uuid != undefined) {
					submitAccessionAndUuid(item[id_hash[type]], table, type, response[x]['@graph'][0].accession, response[x]['@graph'][0].uuid);
				}
			}else{
				console.log(item[id_hash[type]])
				console.log(table)
				console.log(type)
				console.log(response[x]['@graph'][0].accession)
				console.log(response[x]['@graph'][0].uuid)
				if (response[x]['@graph'][0].accession != undefined && response[x]['@graph'][0].uuid != undefined) {
					submitAccessionAndUuid(item[id_hash[type]], table, type, response[x]['@graph'][0].accession, response[x]['@graph'][0].uuid);
				}
			}
		}else{
			//	ERROR
			if (response[x].status.toLowerCase() == 'error' || response[x].status.toLowerCase() == 'errors') {
				output += 'Error<br>';
			}
			if (response[x].code == 403) {
				output += 'Patch error: cannot edit this submission<br>';
				output += 'Permissions denied<br><br>';
			}else if (response[x].code == 404) {
				//404
				output += 'Submission error: cannot find submission location<br>';
				output += response[x].description + '<br>';
				output += response[x].detail + '<br><br>';
			}else if (response[x].code == 409) {
				//409
				output += 'Submission already exists<br>';
				output += response[x].detail + '<br><br>';
			}else if (response[x].code == 422){
				//422
				output += '';
				for(var y = 0; y < response[x]['errors'].length; y++){
					output += response[x]['errors'][y].description + '<br>';
				}
				output += response[x].description + '<br><br>';
			}
		}
		id_hash[type]++;
	}
	
	return output;
}

function logResponse(){
	$.ajax({ type: "GET",
		url: BASE_PATH + "/public/ajax/encode_data.php",
		data: { p: 'endLog', sample_ids: biosample_ids},
		async: false,
		success : function(s)
		{
			console.log(s);
		}
	});
}

function submitAccessionAndUuid(item, table, type, accession, uuid){
	$.ajax({ type: "GET",
		url: BASE_PATH + "/public/ajax/encode_data.php",
		data: { p: 'submitAccessionAndUuid', item: item, table: table, type: type, accession: accession, uuid: uuid},
		async: false,
		success : function(s)
		{
			console.log(s);
		}
	});
}

/*
 *	The heart of the encode submission
 *
 *	If checks are passed, this function is called to create each specific json set [post jsons, patch jsons]
 *
 *	Once the jsons have been created, they will be sent to either the post php script or patch php script.
 *
 *	Upon metadata submission, files linked the samples via the ngs_file_submissions table in the database will
 *	then be submitted to encode.
 *
 *	TO DO:
 *	This process takes a while, so either a loading modal is needed or a way to figure out how to submit in the background.
 *	If submitting in the background, response files need to be created for the user to check the output.
 */

function encodePost(){
	//	output response string initiation
	var responseOutput = '';
	
	//	Grab json information
	var donor_json = createEncodeJson("donor");
	var experiment_json = createEncodeJson("experiment");
	var treatment_json = createEncodeJson("treatment");
	var biosample_json = createEncodeJson("biosample");
	var library_json = createEncodeJson("library");
	var antibody_lot_json = createEncodeJson("antibody");
	var replicate_json = createEncodeJson("replicate");
	
	console.log(donor_json)
	console.log(experiment_json)
	console.log(treatment_json)
	console.log(biosample_json)
	console.log(antibody_lot_json)
	console.log(replicate_json)
	
	//	DONOR SUBMISSION
	console.log('donor')
	if (donor_json[0].toString() != "") {
		responseOutput += encodeSubmission('human_donor', donor_json[0], "post", "donor", "ngs_donor");
	}
	if (donor_json[1].toString() != "") {
		responseOutput += encodeSubmission('human_donor', donor_json[1], "patch", "donor", "ngs_donor");
	}
	//	EXPERIMENT SUBMISSION
	console.log('experiment')
	if (experiment_json[0].toString() != "") {
		responseOutput += encodeSubmission('experiments', experiment_json[0], "post", "experiment", "ngs_samples");
	}
	if (experiment_json[1].toString() != "") {
		responseOutput += encodeSubmission('experiments', experiment_json[1], "patch", "experiment", "ngs_samples");
	}
	//	TREATMENT SUBMISSION
	console.log('treatment')
	if (treatment_json[0].toString() != "") {
		//responseOutput +=
		responseOutput += encodeSubmission('treatment', treatment_json[0], "post", "treatment", "ngs_treatment");
	}
	if (treatment_json[1].toString() != "") {
		//responseOutput +=
		responseOutput += encodeSubmission('treatment', treatment_json[1], "patch", "treatment", "ngs_treatment");
	}
	//	BIOSAMPLE SUBMISSION
	console.log('biosample')
	if (biosample_json[0].toString() != "") {
		responseOutput += encodeSubmission('biosamples', biosample_json[0], "post", "biosample", "ngs_samples");
	}
	if (biosample_json[1].toString() != "") {
		responseOutput += encodeSubmission('biosamples', biosample_json[1], "patch", "biosample", "ngs_samples");
	}
	//	LIBRARY SUBMISSION
	console.log('library')
	if (library_json[0].toString() != "") {
		responseOutput += encodeSubmission('libraries', library_json[0], "post", "library", "ngs_samples");
	}
	if (library_json[1].toString() != "") {
		responseOutput += encodeSubmission('libraries', library_json[1], "patch", "library", "ngs_samples");
	}
	//	ANTIBODY_LOT SUBMISSION
	console.log('antibody_lot')
	if (antibody_lot_json[0].toString() != "") {
		//responseOutput +=
		responseOutput += encodeSubmission('antibody_lot', antibody_lot_json[0], "post", "antibody_lot", "ngs_antibody_target");
	}
	if (antibody_lot_json[1].toString() != "") {
		//responseOutput +=
		responseOutput += encodeSubmission('antibody_lot', antibody_lot_json[1], "patch", "antibody_lot", "ngs_antibody_target");
	}
	//	REPLICATE SUBMISSION
	console.log('replicate')
	if (replicate_json[0].toString() != "") {
		responseOutput += encodeSubmission('replicate', replicate_json[0], "post", "replicate", "ngs_samples");
	}
	if (replicate_json[1].toString() != "") {
		responseOutput += encodeSubmission('replicate', replicate_json[1], "patch", "replicate", "ngs_samples");
	}

	//	Report Errors/Successes to modal
	document.getElementById('myModalLabel').innerHTML = 'Encode Submission';
	document.getElementById('deleteLabel').innerHTML = 'Response from ENCODE servers:';
	document.getElementById('deleteAreas').innerHTML = responseOutput;
		
	document.getElementById('cancelDeleteButton').innerHTML = "OK";
	document.getElementById('confirmDeleteButton').setAttribute('style', 'display:none');
	document.getElementById('confirmPatchButton').setAttribute('style', 'display:none');
}

function gatherOrderTableInfo(){
	var ordertable = $('#jsontable_encode_file_order').dataTable();
	var ordertable_dict = {}
	var ordertable_nodes = ordertable.fnGetNodes()
	for(var x = 0; x < ordertable_nodes.length; x++){
		var id = ordertable_nodes[x].children[0].innerHTML
		ordertable_dict[id] = {}
		if (x == 0) {
			ordertable_dict[id]["p"] = "0"
		}else{
			ordertable_dict[id]["p"] = ordertable_nodes[x].children[1].children[0].value
		}
		ordertable_dict[id]["l"] = ordertable_nodes[x].children[2].innerHTML
		ordertable_dict[id]["t"] = ordertable_nodes[x].children[3].innerHTML
		ordertable_dict[id]["r"] = ordertable_nodes[x].children[4].children[0].value
		ordertable_dict[id]["d"] = ordertable_nodes[x].children[5].children[0].value
 	}
	return ordertable_dict
}

function gatherSampleRunInfo(){
	var sample_run_dict = {};
	var samples = Object.keys(sampleRuns)
	for(var x = 0; x < samples.length; x++){
		var selected_run = 0;
		var runs = Object.keys(sampleRuns[samples[x]])
		runs.splice(runs.indexOf('sid'), 1)
		runs.splice(runs.indexOf('rid'), 1)
		runs.splice(runs.indexOf('did'), 1)
		for(var y = 0; y < runs.length; y++){
			if (sampleRuns[samples[x]][runs[y]] == 1) {
				selected_run = runs[y]
			}
		}
		var sample_obj = {}
		sample_obj['rid'] = selected_run
		sample_obj['did'] = sampleRuns[samples[x]].did
		sample_run_dict[sampleRuns[samples[x]].sid]= sample_obj
	}
	return sample_run_dict
}

function recordFileSubmissions(ordertable_dict, sample_run_dict){
	console.log(ordertable_dict)
	console.log(sample_run_dict)
	$.ajax({ type: "GET",
		url: BASE_PATH + "/public/ajax/encode_tables.php",
		data: { p: 'enterFileSubmission', ordertable: ordertable_dict, samples: sample_run_dict},
		async: false,
		success : function(s)
		{
			console.log(s);
		}
	});
}

function encodePostFiles(){
	//	FILE DATABASE SUBMISSION
	console.log('file data submission')
	//	order table
	var ordertable_dict = gatherOrderTableInfo()
	//	run table
	var sample_run_dict = gatherSampleRunInfo()
	//	database insertion
	recordFileSubmissions(ordertable_dict, sample_run_dict)
	
	
	var file_response = encodeFilePost();
	
	//	Report Errors/Successes to modal
	document.getElementById('myModalLabel').innerHTML = 'Encode Submission';
	document.getElementById('deleteLabel').innerHTML = 'Response from ENCODE servers:';
	document.getElementById('deleteAreas').innerHTML += "<br><br><b>File Submission reponse:</b><br>" + file_response;
		
	document.getElementById('cancelDeleteButton').innerHTML = "OK";
	document.getElementById('confirmDeleteButton').setAttribute('style', 'display:none');
	document.getElementById('confirmPatchButton').setAttribute('style', 'display:none');
}
