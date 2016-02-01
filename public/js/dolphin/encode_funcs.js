var sample_info = [];
var lane_info = [];
var protocol_info = [];
var experiment_info = [];
var antibody_info = [];

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
	
var nucleic_acid_term_id = ['SO:0000356', 'SO:0000352'];
	//	RNA, DNA

function checkForEncodeSubmission(){
	var boolPass = true;
	var errorMsg = '';
	
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
			if (sample_info[x].biosample_type == undefined) {
				boolPass = false;
				boolBreak = false;
				
				errorMsg += '<b>Biosample type</b> for <b>sample id: ' + sample_info[x].id + '</b> is not defined.<br>';
			}
			if (sample_info[x].source == undefined) {
				boolPass = false;
				boolBreak = false;
				
				errorMsg += '<b>Source</b> for <b>sample id: ' + sample_info[x].id + '</b> is not defined.<br>';
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
			
			/*
			if (sample_info[x].notes == undefined) {
				boolPass = false;
				errorMsg += '<b>Notes</b> for sample id:' + sample_info[x].id + ' is not defined';
			}
			if (sample_info[x].biological_replica == undefined) {
				boolPass = false;
				errorMsg += '<b>Biological replica</b> for sample id:' + sample_info[x].id + ' is not defined';
			}
			if (sample_info[x].technical_replica == undefined) {
				boolPass = false;
				errorMsg += '<b>Techinical replica</b> for sample id:' + sample_info[x].id + ' is not defined';
			}
			*/
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
			if (lane_info[x].date_submitted == undefined) {
				boolPass = false;
				boolBreak = false;
				
				errorMsg += '<b>Date submitted</b> for <b>lane id: ' + lane_info[x].id + '</b> is not defined.<br>';
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
			if (protocol_info[x].library_strategy == undefined) {
				boolPass = false;
				boolBreak = false;
				
				errorMsg += '<b>Library strategy</b> for <b>protocol id: ' + protocol_info[x].id + '</b> is not defined.<br>';
			}
			
			if (!boolBreak) {
				errorMsg += '<br>';
			}
		}
		
		if (boolPass) {
			$('#deleteModal').modal({
				show: true
			});
			document.getElementById('myModalLabel').innerHTML = 'Encode Checks';
			document.getElementById('deleteLabel').innerHTML = 'Checking for proper ENCODE submission...';
			document.getElementById('deleteAreas').innerHTML = '<b>Database checks passed without error for ENCODE submission';
				
			document.getElementById('cancelDeleteButton').innerHTML = "Cancel";
			document.getElementById('confirmDeleteButton').innerHTML = "Submit";
			document.getElementById('confirmDeleteButton').setAttribute('onclick', 'encodeCheckForPatch()');
			document.getElementById('confirmDeleteButton').setAttribute('data-dismiss', '');
			document.getElementById('confirmDeleteButton').setAttribute('class', 'btn btn-success');
			document.getElementById('confirmDeleteButton').setAttribute('style', 'display:show');
			document.getElementById('confirmPatchButton').setAttribute('style', 'display:none');
			document.getElementById('confirmPatchButton').setAttribute('data-dismiss', '');
		}else{
			errorMsg += '<b>Database checks did not pass inspection, submission to ENCODE has been halted.  See errors above for more details.</b>'
			
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

function encodeCheckForPatch(){
	var boolPass = false;
	for ( var x = 0; x < sample_info.length; x++ ){
		if (sample_info[x].donor_acc != null || sample_info[x].donor_uuid != null) {
			boolPass = true;
		}else if (sample_info[x].treatment_uuid != null) {
			boolPass = true;
		}else if (sample_info[x].biosample_acc != null || sample_info[x].biosample_uuid != null) {
			boolPass = true;
		}else if (sample_info[x].library_acc != null || sample_info[x].library_uuid != null) {
			boolPass = true;
		}else if (sample_info[x].replicate_uuid != null) {
			boolPass = true;
		}
	}
	for ( var x = 0; x < lane_info.length; x++ ){
		if (lane_info[x].experiment_acc != null || lane_info[x].experiment_uuid != null) {
			boolPass = true;
		}
	}
	
	if (boolPass) {
		document.getElementById('myModalLabel').innerHTML = 'Encode Patch';
		document.getElementById('deleteLabel').innerHTML = 'Some encode information has already been entered.';
		document.getElementById('deleteAreas').innerHTML = 'Would you like to request to patch the current information?<br><br>'+
					'Submission/Patching may take a minute to fully submit to ENCODE';
			
		document.getElementById('cancelDeleteButton').innerHTML = "Cancel";
		
		document.getElementById('confirmDeleteButton').innerHTML = "No";
		document.getElementById('confirmDeleteButton').setAttribute('onclick', 'encodePost(\'post\')');
		document.getElementById('confirmDeleteButton').setAttribute('data-dismiss', '');
		document.getElementById('confirmDeleteButton').setAttribute('class', 'btn btn-success');
		document.getElementById('confirmDeleteButton').setAttribute('style', 'display:show');
		
		document.getElementById('confirmPatchButton').setAttribute('style', 'display:show');
		document.getElementById('confirmPatchButton').setAttribute('onclick', 'encodePost(\'patch\')');
	}else{
		encodePost('post');
	}
}

function getDataInfo(){
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
	
	//	Lane, Protocol, and Experiment ids
	var lane_ids = [];
	for (var x = 0; x < sample_info.length; x++) {
		if (lane_ids.indexOf(sample_info[x].lane_id) < 0 ){
			lane_ids.push(sample_info[x].lane_id);
		}
	}
	var protocol_ids = [];
	for (var x = 0; x < sample_info.length; x++) {
		if (protocol_ids.indexOf(sample_info[x].protocol_id) < 0 ){
			console.log('test');
			protocol_ids.push(sample_info[x].protocol_id);
		}
	}
	var experiment_id = sample_info[0].series_id;
	
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
	//	Lane Info
	$.ajax({ type: "GET",
		url: BASE_PATH + "/public/ajax/encode_data.php",
		data: { p: 'getProtocolDataInfo', protocols: protocol_ids.toString() },
		async: false,
		success : function(s)
		{
			console.log(s);
			protocol_info = s;
		}
	});
	
	//	Experiment Info
	$.ajax({ type: "GET",
		url: BASE_PATH + "/public/ajax/encode_data.php",
		data: { p: 'getSeriesDataInfo', series: experiment_id },
		async: false,
		success : function(s)
		{
			experiment_info = s;
		}
	});
	
	//	Antibody Info
	$.ajax({ type: "GET",
		url: BASE_PATH + "/public/ajax/encode_data.php",
		data: { p: 'getAntibodyDataInfo'},
		async: false,
		success : function(s)
		{
			antibody_info = s;
		}
	});
}

function getDonorJson(){
	var donors = [];
	var organisms = [];
	var acc_checks = [];
	for(var x = 0; x < sample_info.length; x++){
		if (donors.indexOf(sample_info[x].donor) < 0 ){
			donors.push(sample_info[x].donor);
			donor_ids.push(sample_info[x].did);
			organisms.push(sample_info[x].organism);
			//acc_checks.push(sample_info[x].donor_acc);
			//donor_accs.push(sample_info[x].donor_acc);
		}
	}
	var donor_json = '[';
	for(var y = 0; y < donors.length; y++){
		if (acc_checks[y] == null) {
			//	Alias
			if (experiment_info[0].lab != null && donors[y] != null) {
				donor_json += '{"aliases":["'+experiment_info[0].lab+':'+donors[y]+'"],';
			}
			//	Award
			if (experiment_info[0].grant != null) {
				donor_json += '"award":"'+experiment_info[0].grant+'",';
			}
			//	Lab
			if (experiment_info[0].lab != null) {
				donor_json += '"lab":"'+experiment_info[0].lab+'",';
			}
			//	Organism
			if (organisms[y] != null) {
				donor_json += '"organism":"'+organisms[y]+'",';
			}
			//	Life Stage
			donor_json += '"life_stage":"unknown",';
			//	Age
			donor_json += '"age":"unknown",';
			//	Sex
			donor_json += '"sex":"unknown"}';
			
			var comma_bool = false;
			for(var z = y + 1; z < donors.length; z++){
				if (acc_checks[z] == null) {
					comma_bool = true;
				}
			}
			if (comma_bool == true) {
				console.log(comma_bool);
				donor_json += ','
			}
		}
	}
	donor_json += ']';
	var donor_json_patch = '[';
	for(var y = 0; y < donors.length; y++){
		if (acc_checks[y] != null) {
			//	Alias
			if (experiment_info[0].lab != null && donors[y] != null) {
				donor_json_patch += '{"aliases":["'+experiment_info[0].lab+':'+donors[y]+'"],';
			}
			//	Award
			if (experiment_info[0].grant != null) {
				donor_json_patch += '"award":"'+experiment_info[0].grant+'",';
			}
			//	Lab
			if (experiment_info[0].lab != null) {
				donor_json_patch += '"lab":"'+experiment_info[0].lab+'",';
			}
			//	Organism
			if (organisms[y] != null) {
				donor_json_patch += '"organism":"'+organisms[y]+'",';
			}
			//	Life Stage
			donor_json_patch += '"life_stage":"unknown",';
			//	Age
			donor_json_patch += '"age":"unknown",';
			//	Sex
			donor_json_patch += '"sex":"unknown"}';
			
			var comma_bool = false;
			for(var z = y + 1; z < donors.length; z++){
				if (acc_checks[z] != null) {
					comma_bool = true;
				}
			}
			if (comma_bool == true) {
				donor_json_patch += ','
			}
		}
	}
	donor_json_patch += ']';
	
	return [donor_json, donor_json_patch];
}

function getSampleJson(){
	var sample_json = '[';	
	for(var x = 0; x < sample_info.length; x++){
		biosample_accs.push(sample_info[x].biosample_acc);
		if (sample_info[x].biosample_acc == null) {
			//	Alias
			if (experiment_info[0].lab != null && sample_info[x].samplename != null) {
				sample_json += '{"aliases":["'+experiment_info[0].lab +':'+sample_info[x].samplename+'"],';
			}
			//	Donor
			if (experiment_info[0].lab != null && sample_info[x].donor != null) {
				sample_json += '"donor":"'+experiment_info[0].lab +':'+sample_info[x].donor+'",';
			}
			//	Biosample Term Name
			sample_json += '"biosample_term_name":"dendritic cell",';
			//	Biosample Term ID
			sample_json += '"biosample_term_id":"CL:0000451",';
			//	Biosample Type
			if (sample_info[x].biosample_type != null) {
				sample_json += '"biosample_type":"'+sample_info[x].biosample_type+'",';
			}
			//	Source
			if (sample_info[x].source != null) {
				//sample_json += '"source":"'+sample_info[x].source+'",';
				sample_json += '"source":"unknown",';
			}
			//	Organism
			if (sample_info[x].organism != null) {
				sample_json += '"organism":"'+sample_info[x].organism+'",';
			}
			//	Award
			if (experiment_info[0].grant != null) {
				sample_json += '"award":"'+experiment_info[0].grant+'",';
			}
			//	Lab
			if (experiment_info[0].lab != null) {
				sample_json += '"lab":"'+experiment_info[0].lab+'",';
			}
			//	Treatments
			sample_json_patch += '"treatments":["manuel-garber:LPS_'+parseInt(sample_info[x].time)/60+'h"],'
			//	Date Obtained
			var lane_id_pos = -1;
			for(var y = 0; y < lane_info.length; y++){
				if (lane_info[y].id == sample_info[x].lane_id) {
					lane_id_pos = y;
				}
			}
			if (lane_id_pos == -1 || lane_info[lane_id_pos].date_received != undefined) {
				sample_json += '"date_obtained":"'+lane_info[lane_id_pos].date_received.split(' ')[0]+'"';
			}
			
			//	Derived From
			//sample_json += '"derived_from":""';
			
			sample_json += '}';
			var comma_bool = false;
			for(var z = x + 1; z < sample_info.length; z++){
				if (sample_info[z].biosample_acc == null) {
					comma_bool = true;
				}
			}
			if (comma_bool == true) {
				sample_json += ','
			}
			biosample_ids.push(sample_info[x].id);
		}
	}
	sample_json += ']';
	
	var sample_json_patch = '[';
	for(var x = 0; x < sample_info.length; x++){
		if (sample_info[x].biosample_acc != null) {
			//	Alias
			if (experiment_info[0].lab != null && sample_info[x].samplename != null) {
				sample_json_patch += '{"aliases":["'+experiment_info[0].lab +':'+sample_info[x].samplename+'"],';
			}
			//	Donor
			if (experiment_info[0].lab != null && sample_info[x].donor != null) {
				sample_json_patch += '"donor":"'+experiment_info[0].lab +':'+sample_info[x].donor+'",';
			}
			//	Biosample Term Name
			sample_json_patch += '"biosample_term_name":"dendritic cell",';
			//	Biosample Term ID
			sample_json_patch += '"biosample_term_id":"CL:0000451",';
			//	Biosample Type
			if (sample_info[x].biosample_type != null) {
				sample_json_patch += '"biosample_type":"'+sample_info[x].biosample_type+'",';
			}
			//	Source
			if (sample_info[x].source != null) {
				//sample_json_patch += '"source":"'+sample_info[x].source+'",';
				sample_json_patch += '"source":"unknown",';
			}
			//	Organism
			if (sample_info[x].organism != null) {
				sample_json_patch += '"organism":"'+sample_info[x].organism+'",';
			}
			//	Award
			if (experiment_info[0].grant != null) {
				sample_json_patch += '"award":"'+experiment_info[0].grant+'",';
			}
			//	Lab
			if (experiment_info[0].lab != null) {
				sample_json_patch += '"lab":"'+experiment_info[0].lab+'",';
			}
			//	Treatments
			sample_json_patch += '"treatments":["manuel-garber:LPS_'+parseInt(sample_info[x].time)/60+'h"],'
			//	Date Obtained
			var lane_id_pos = -1;
			for(var y = 0; y < lane_info.length; y++){
				if (lane_info[y].id == sample_info[x].lane_id) {
					lane_id_pos = y;
				}
			}
			if (lane_id_pos == -1 || lane_info[lane_id_pos].date_received != undefined) {
				sample_json_patch += '"date_obtained":"'+lane_info[lane_id_pos].date_received.split(' ')[0]+'"';
			}
			
			//	Derived From
			//sample_json += '"derived_from":""';
			
			sample_json_patch += '}';
			var comma_bool = false;
			for(var z = x + 1; z < sample_info.length; z++){
				if (sample_info[z].biosample_acc != null) {
					comma_bool = true;
				}
			}
			if (comma_bool == true) {
				sample_json_patch += ','
			}
			biosample_ids.push(sample_info[x].id);
		}
	}
	sample_json_patch += ']';
	
	return [sample_json, sample_json_patch];
}

function getLibraryJson(){
	var library = '[{"aliases":["manuel-garber:D64_6d_1h_R848_lib_rep1"],"biosample":"manuel-garber:D64_6d_1h_R848","nucleic_acid_term_name":"RNA","nucleic_acid_term_id":"SO:0000356","' +
	'depleted_in_term_name":["rRNA"],"depleted_in_term_id":["SO:0000252"],"extraction_method":"RNeasy Plus Mini Kit Qiagen cat#74134 plus additional on column Dnase treatment","award":"U01HG007910","lab":"manuel-garber"}]';
	
	var lib_json = '[';
	for(var x = 0; x < sample_info.length; x++){
		library_accs.push(sample_info[x].library_acc);
		if (sample_info[x].library_acc == null) {
			//	Alias
			if (experiment_info[0].lab != null && sample_info[x].samplename != null) {
				lib_json += '{"aliases":["'+experiment_info[0].lab +':'+sample_info[x].samplename+'_lib"],';
			}
			//	Biosample
			if (sample_info[0].samplename != null) {
				lib_json += '"biosample":"'+experiment_info[0].lab + ':' + sample_info[x].samplename+'",';
			}
			//	Nucleic_acid_term_name
			var proto_lib_type = -1;
			for(var y = 0; y < protocol_info.length; y++){
				if (protocol_info[y].id == sample_info[x].protocol_id) {
					proto_lib_type = y;
				}
			}
			if (sample_info[x].molecule != null) {
				if (sample_info[x].molecule.toLowerCase().indexOf('rna') > -1) {
					lib_json += '"nucleic_acid_term_name":"RNA",';
				}else if(sample_info[x].molecule.toLowerCase().indexOf('dna') > -1){
					lib_json += '"nucleic_acid_term_name":"DNA",';
				}
			}
			//	Nucleic_acid_term_id
			if (sample_info[x].molecule != null) {
				if (sample_info[x].molecule.toLowerCase().indexOf('rna') > -1) {
					lib_json += '"nucleic_acid_term_id":"SO:0000356",';
				}else if(sample_info[x].molecule.toLowerCase().indexOf('dna') > -1){
					lib_json += '"nucleic_acid_term_id":"SO:0000352",';
				}
			}
			
			//	Depleted_in_term_name
			if (sample_info[x].molecule.toLowerCase().indexOf('rrna') > -1) {
				lib_json += '"depleted_in_term_name":["rRNA"],';
				lib_json += '"depleted_in_term_id":"SO:0000252",';
			}
			
			//	Search for Molecule within ontology db
			
			//	Extraction_method
			if (sample_info[x].protocol_id != null) {
				for(var y = 0; y < protocol_info.length; y++){
					if (protocol_info[y].id == sample_info[x].protocol_id) {
						lib_json += '"extraction_method":"' + protocol_info[y].extraction + '",'
					}
				}
			}
			//	Award
			if (experiment_info[0].grant != null) {
				lib_json += '"award":"'+experiment_info[0].grant+'",';
			}
			//	Size range
			if (sample_info[x].read_length != null) {
				lib_json += '"size_range":"50-100",';
				//lib_json += '"size_range":"'+sample_info[x].read_length+'",';
			}
			//	Lab
			if (experiment_info[0].lab != null) {
				lib_json += '"lab":"'+experiment_info[0].lab+'"';
			}
			lib_json += '}';
			var comma_bool = false;
			for(var z = x + 1; z < sample_info.length; z++){
				if (sample_info[z].library_acc == null) {
					comma_bool = true;
				}
			}
			if (comma_bool == true) {
				lib_json += ','
			}
			library_ids.push(sample_info[x].id);
		}
	}
	lib_json += ']';
	
	var lib_json_patch = '[';
	for(var x = 0; x < sample_info.length; x++){
		if (sample_info[x].library_acc != null) {
			//	Alias
			if (experiment_info[0].lab != null && sample_info[x].samplename != null) {
				lib_json_patch += '{"aliases":["'+experiment_info[0].lab +':'+sample_info[x].samplename+'_lib"],';
			}
			//	Biosample
			if (sample_info[0].samplename != null) {
				lib_json_patch += '"biosample":"'+experiment_info[0].lab + ':' + sample_info[x].samplename+'",';
			}
			//	Nucleic_acid_term_name
			var proto_lib_type = -1;
			for(var y = 0; y < protocol_info.length; y++){
				if (protocol_info[y].id == sample_info[x].protocol_id) {
					proto_lib_type = y;
				}
			}
			console.log(proto_lib_type);
			if (sample_info[x].molecule != null) {
				if (sample_info[x].molecule.toLowerCase().indexOf('rna') > -1) {
					lib_json_patch += '"nucleic_acid_term_name":"RNA",';
				}else if(sample_info[x].molecule.toLowerCase().indexOf('dna') > -1){
					lib_json_patch += '"nucleic_acid_term_name":"DNA",';
				}
			}
			//	Nucleic_acid_term_id
			if (sample_info[x].molecule != null) {
				if (sample_info[x].molecule.toLowerCase().indexOf('rna') > -1) {
					lib_json_patch += '"nucleic_acid_term_id":"SO:0000356",';
				}else if(sample_info[x].molecule.toLowerCase().indexOf('dna') > -1){
					lib_json_patch += '"nucleic_acid_term_id":"SO:0000352",';
				}
			}
			
			//	Depleted_in_term_name
			/*
			if (sample_info[x].molecule != undefined) {
				lib_json_patch += '"depleted_in_term_name":"' + sample_info[x].molecule + '",';
			}
			*/
			//	Depleted_in_term_id
			
			//	Search for Molecule within ontology db
			
			//	Extraction_method
			if (sample_info[x].protocol_id != null) {
				for(var y = 0; y < protocol_info.length; y++){
					if (protocol_info[y].id == sample_info[x].protocol_id) {
						lib_json_patch += '"extraction_method":"' + protocol_info[y].extraction + '",'
					}
				}
			}
			//	Size range
			if (sample_info[x].read_length != null) {
				lib_json_patch += '"size_range":"50-100",';
				//lib_json_patch += '"size_range":"'+sample_info[x].read_length+'",';
			}
			//	Award
			if (experiment_info[0].grant != null) {
				lib_json_patch += '"award":"'+experiment_info[0].grant+'",';
			}
			//	Lab
			if (experiment_info[0].lab != null) {
				lib_json_patch += '"lab":"'+experiment_info[0].lab+'"';
			}
			lib_json_patch += '}';
			var comma_bool = false;
			for(var z = x + 1; z < sample_info.length; z++){
				if (sample_info[z].library_acc != null) {
					comma_bool = true;
				}
			}
			if (comma_bool == true) {
				lib_json_patch += ','
			}
			library_ids.push(sample_info[x].id);
		}
	}
	lib_json_patch += ']';
	
	return [lib_json, lib_json_patch];
}

function getReplicateJson() {
	var rep_json = '[';
	for(var x = 0; x < sample_info.length; x++){
		replicate_uuids.push(sample_info[x].replicate_uuid);
		if (sample_info[x].replicate_uuid == null) {
			//	Alias
			if (experiment_info[0].lab != null && sample_info[x].samplename != null) {
				rep_json += '{"aliases":["'+experiment_info[0].lab +':'+sample_info[x].samplename+'_replica"],';
			}
			//	Experiment
			var proto_lib_type = -1;
			for(var y = 0; y < protocol_info.length; y++){
				if (protocol_info[y].id == sample_info[x].protocol_id) {
					proto_lib_type = y;
				}
			}
			if (experiment_info[0].lab != null && sample_info[x].samplename != null) {
				if (protocol_info[proto_lib_type].library_strategy != null) {
					if (protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('rna') > -1) {
						rep_json += '"experiment":"'+experiment_info[0].lab +':'+sample_info[x].samplename+'_RNA-seq",';
					}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('chip') > -1){
						rep_json += '"experiment":"'+experiment_info[0].lab +':'+sample_info[x].samplename+'_ChIP-seq",';
					}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('atac') > -1){
						rep_json += '"experiment":"'+experiment_info[0].lab +':'+sample_info[x].samplename+'_ATAC-seq",';
					}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('rnase') > -1){
						rep_json += '"experiment":"'+experiment_info[0].lab +':'+sample_info[x].samplename+'_RNase-seq",';
					}
				}
			}
			//	Biological Replicate Number
			if (sample_info[x].biological_replica == null || sample_info[x].biological_replica == '' || sample_info[x].biological_replica < 1) {
				rep_json += '"biological_replicate_number":1,';
			}else{
				rep_json += '"biological_replicate_number":'+sample_info[x].biological_replica+',';
			}
			//	Technical Replicate Number
			if (sample_info[x].technical_replica == null || sample_info[x].technical_replica == '' || sample_info[x].technical_replica < 1) {
				rep_json += '"technical_replicate_number":1,';
			}else{
				rep_json += '"technical_replicate_number":'+sample_info[x].technical_replica+',';
			}
			//	Library
			if (experiment_info[0].lab != null && sample_info[x].samplename != null) {
				rep_json += '"library":"'+experiment_info[0].lab +':'+sample_info[x].samplename+'_lib"';
			}
			//rep_json += ',"antibody":"ENCAB969VGQ"'
			rep_json += '}';
			var comma_bool = false;
			for(var z = x + 1; z < sample_info.length; z++){
				if (sample_info[z].replicate_uuid == null) {
					comma_bool = true;
				}
			}
			if (comma_bool == true) {
				rep_json += ','
			}
			replicate_ids.push(sample_info[x].id);
		}
	}
	rep_json += ']';
	
	var rep_json_patch = '[';
	for(var x = 0; x < sample_info.length; x++){
		if (sample_info[x].replicate_uuid != null) {
			//	Alias
			if (experiment_info[0].lab != null && sample_info[x].samplename != null) {
				rep_json_patch += '{"aliases":["'+experiment_info[0].lab +':'+sample_info[x].samplename+'_replica"],';
			}
			//	Experiment
			var proto_lib_type = -1;
			for(var y = 0; y < protocol_info.length; y++){
				if (protocol_info[y].id == sample_info[x].protocol_id) {
					proto_lib_type = y;
				}
			}
			if (experiment_info[0].lab != null && sample_info[x].samplename != null) {
				if (protocol_info[proto_lib_type].library_strategy != null) {
					if (protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('rna') > -1) {
						rep_json_patch += '"experiment":"'+experiment_info[0].lab +':'+sample_info[x].samplename+'_RNA-seq",';
					}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('chip') > -1){
						rep_json_patch += '"experiment":"'+experiment_info[0].lab +':'+sample_info[x].samplename+'_ChIP-seq",';
					}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('dna') > -1){
						rep_json_patch += '"experiment":"'+experiment_info[0].lab +':'+sample_info[x].samplename+'_DNA-seq",';
					}
				}
			}
			//	Biological Replicate Number
			if (sample_info[x].biological_replica == null || sample_info[x].biological_replica == '') {
				rep_json_patch += '"biological_replicate_number":1,';
			}else{
				rep_json_patch += '"biological_replicate_number":'+sample_info[x].biological_replica+',';
			}
			//	Technical Replicate Number
			if (sample_info[x].technical_replica == null || sample_info[x].technical_replica == '') {
				rep_json_patch += '"technical_replicate_number":1,';
			}else{
				rep_json_patch += '"technical_replicate_number":'+sample_info[x].technical_replica+',';
			}
			//	Library
			if (experiment_info[0].lab != null && sample_info[x].samplename != null) {
				rep_json_patch += '"library":"'+experiment_info[0].lab +':'+sample_info[x].samplename+'_lib"';
			}
			//rep_json_patch += '",antibody":"ENCAB969VGQ"'
			rep_json_patch += '}';
			var comma_bool = false;
			for(var z = x + 1; z < sample_info.length; z++){
				if (sample_info[z].replicate_uuid != null) {
					comma_bool = true;
				}
			}
			if (comma_bool == true) {
				rep_json_patch += ','
			}
			replicate_ids.push(sample_info[x].id);
		}
	}
	rep_json_patch += ']';
	
	return [rep_json, rep_json_patch];
}

function getExperimentJson(){
	var biosample_term_ids = ['CL:0000451', 'CL:0000576'];
	//	dendritic cell, monocyte
	
	var exp_json = '[';
	for(var x = 0; x < sample_info.length; x++){
		experiment_accs.push(sample_info[x].experiment_acc);
		var proto_lib_type = -1;
		for(var y = 0; y < protocol_info.length; y++){
			if (protocol_info[y].id == sample_info[x].protocol_id) {
				proto_lib_type = y;
			}
		}
		console.log(proto_lib_type);
		if (sample_info[x].experiment_acc == null) {
			//	Alias
			if (experiment_info[0].lab != null && sample_info[x].samplename != null) {
				if (protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('rna') > -1) {
					exp_json += '{"aliases":["'+experiment_info[0].lab +':'+sample_info[x].samplename+'_RNA-seq"],';
				}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('chip') > -1){
					exp_json += '{"aliases":["'+experiment_info[0].lab +':'+sample_info[x].samplename+'_ChIP-seq"],';
				}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('atac') > -1){
					exp_json += '{"aliases":["'+experiment_info[0].lab +':'+sample_info[x].samplename+'_ATAC-seq"],';
				}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('mnase') > -1){
					exp_json += '{"aliases":["'+experiment_info[0].lab +':'+sample_info[x].samplename+'_MNase-seq"],';
				}
			}
			//	Assay_term_name
			if (protocol_info[proto_lib_type].library_strategy != null) {
				if (protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('rna') > -1) {
					exp_json += '"assay_term_name":"RNA-seq",';
				}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('chip') > -1){
					exp_json += '"assay_term_name":"ChIP-seq",';
				}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('atac') > -1){
					exp_json += '"assay_term_name":"ATAC-seq",';
				}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('mnase') > -1){
					exp_json += '"assay_term_name":"MNase-seq",';
				}
			}
			//	Assay_term_id
			if (protocol_info[proto_lib_type].library_strategy != null) {
				if (protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('rna') > -1) {
					exp_json += '"assay_term_id":"OBI:0001271",';
				}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('chip') > -1){
					exp_json += '"assay_term_id":"OBI:0000716",';
				}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('atac') > -1){
					exp_json += '"assay_term_id":"OBI:0002039",';
				}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('mnase') > -1){
					exp_json += '"assay_term_id":"OBI:0001924",';
				}
			}
			//	Biosample_term_id
			if (sample_info[x].source != null) {
				/*
				if (sample_info[x].source.toLowerCase().indexOf('monocyte') > -1) {
					exp_json += '"biosample_term_id":"' + biosample_term_ids[1] + '",';
				}else
				*/
				if(sample_info[x].source.toLowerCase().indexOf('dendritic') > -1){
					exp_json += '"biosample_term_id":"' + biosample_term_ids[0] + '",';
				}
			}
			//	Biosample_term_name
			if (sample_info[x].source != null) {
				/*
				if (sample_info[x].source.toLowerCase().indexOf('monocyte') > -1) {
					exp_json += '"biosample_term_name":"monocyte",';
				}else
				*/
				if(sample_info[x].source.toLowerCase().indexOf('dendritic') > -1){
					exp_json += '"biosample_term_name":"dendritic cell",';
				}
			}
			//	Biosample_type
			if (sample_info[x].biosample_type != null) {
				if (sample_info[x].biosample_type.toLowerCase().indexOf('in vitro') > -1) {
					exp_json += '"biosample_type":"in vitro differentiated cells",';
				}
			}
			//	Description
			if (sample_info[x].notes != null) {
				exp_json += '"description":"' + sample_info[x].notes + '",';
			}
			//	Lab
			if (experiment_info[0].lab != null) {
				exp_json += '"lab":"'+experiment_info[0].lab+'",';
			}
			//	Award
			if (experiment_info[0].grant != null) {
				exp_json += '"award":"'+experiment_info[0].grant+'"}';
			}
			var comma_bool = false;
			for(var z = x + 1; z < sample_info.length; z++){
				if (sample_info[z].experiment_acc == null) {
					comma_bool = true;
				}
			}
			if (comma_bool == true) {
				exp_json += ','
			}
			if (experiment_ids.indexOf(sample_info[x].id) < 0) {
				experiment_ids.push(sample_info[x].id);
			}
		}
	}
	exp_json += ']';
	
	var exp_json_patch = '[';
	for(var x = 0; x < sample_info.length; x++){
		var proto_lib_type = -1;
		for(var y = 0; y < protocol_info.length; y++){
			if (protocol_info[y].id == sample_info[x].protocol_id) {
				proto_lib_type = y;
			}
		}
		if (sample_info[x].experiment_acc != null) {
			//	Alias
			if (experiment_info[0].lab != null && sample_info[x].samplename != null) {
				if (protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('rna') > -1) {
					exp_json_patch += '{"aliases":["'+experiment_info[0].lab +':'+sample_info[x].samplename+'_RNA-seq"],';
				}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('chip') > -1){
					exp_json_patch += '{"aliases":["'+experiment_info[0].lab +':'+sample_info[x].samplename+'_ChIP-seq"],';
				}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('atac') > -1){
					exp_json_patch += '{"aliases":["'+experiment_info[0].lab +':'+sample_info[x].samplename+'_ATAC-seq"],';
				}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('mnase') > -1){
					exp_json_patch += '{"aliases":["'+experiment_info[0].lab +':'+sample_info[x].samplename+'_MNase-seq"],';
				}
			}
			//	Assay_term_name
			if (protocol_info[proto_lib_type].library_strategy != null) {
				if (protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('rna') > -1) {
					exp_json_patch += '"assay_term_name":"RNA-seq",';
				}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('chip') > -1){
					exp_json_patch += '"assay_term_name":"ChIP-seq",';
				}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('atac') > -1){
					exp_json_patch += '"assay_term_name":"ATAC-seq",';
				}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('mnase') > -1){
					exp_json_patch += '"assay_term_name":"MNase-seq",';
				}
			}
			//	Assay_term_id
			if (protocol_info[proto_lib_type].library_strategy != null) {
				if (protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('rna') > -1) {
					exp_json_patch += '"assay_term_id":"OBI:0001271",';
				}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('chip') > -1){
					exp_json_patch += '"assay_term_id":"OBI:0000716",';
				}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('atac') > -1){
					exp_json_patch += '"assay_term_id":"OBI:0002039",';
				}else if(protocol_info[proto_lib_type].library_strategy.toLowerCase().indexOf('mnase') > -1){
					exp_json_patch += '"assay_term_id":"OBI:0001924",';
				}
			}
			//	Biosample_term_id
			if (sample_info[x].source != null) {
				/*
				if (sample_info[x].source.toLowerCase().indexOf('monocyte') > -1) {
					exp_json_patch += '"biosample_term_id":"' + biosample_term_ids[1] + '",';
				}else
				*/
				if(sample_info[x].source.toLowerCase().indexOf('dendritic') > -1){
					exp_json_patch += '"biosample_term_id":"' + biosample_term_ids[0] + '",';
				}
			}
			//	Biosample_term_name
			if (sample_info[x].source != null) {
				/*
				if (sample_info[x].source.toLowerCase().indexOf('monocyte') > -1) {
					exp_json_patch += '"biosample_term_name":"monocyte",';
				}else
				*/
				if(sample_info[x].source.toLowerCase().indexOf('dendritic') > -1){
					exp_json_patch += '"biosample_term_name":"dendritic cell",';
				}
			}
			//	Biosample_type
			if (sample_info[x].biosample_type != null) {
				if (sample_info[x].biosample_type.toLowerCase().indexOf('in vitro') > -1) {
					exp_json_patch += '"biosample_type":"in vitro differentiated cells",';
				}
			}
			//	Description
			if (sample_info[x].notes != null) {
				exp_json_patch += '"description":"' + sample_info[x].notes + '",';
			}
			//	Lab
			if (experiment_info[0].lab != null) {
				exp_json_patch += '"lab":"'+experiment_info[0].lab+'",';
			}
			//	Award
			if (experiment_info[0].grant != null) {
				exp_json_patch += '"award":"'+experiment_info[0].grant+'"}';
			}
			var comma_bool = false;
			for(var z = x + 1; z < sample_info.length; z++){
				if (sample_info[z].experiment_acc != null) {
					comma_bool = true;
				}
			}
			if (comma_bool == true) {
				exp_json_patch += ','
			}
			if (experiment_ids.indexOf(sample_info[x].id) < 0) {
				experiment_ids.push(sample_info[x].id);
			}
		}
	}
	exp_json_patch += ']';
	
	return [exp_json, exp_json_patch];
}

//	Optional?
function getTreatmentJson(){
	var treat_json = '[';
	var treat_json_patch = '[';
	for(var x = 0; x < sample_info.length; x++){
		treatment_uuid.push(sample_info[0].treatment_uuid);
		console.log(treatment_ids.indexOf(sample_info[x].sid));
		if (sample_info[x].treatment_uuid == null && treatment_ids.indexOf(sample_info[x].sid) == -1) {
			treat_json += '{"aliases":["manuel-garber:LPS_'+parseInt(sample_info[x].time)/60+'h"],';
			treat_json += '"treatment_term_name":"Lipopolysaccharide",';
			treat_json += '"treatment_term_id":"CHEBI:16412",';
			treat_json += '"treatment_type":"infection",';
			if ((parseInt(sample_info[x].time)/60) == 0) {
				treat_json += '"concentration":0,';
				treat_json += '"concentration_units":"ng/mL",';
			}else{
				treat_json += '"concentration":100,';
				treat_json += '"concentration_units":"ng/mL",';
			}
			treat_json += '"duration":'+(parseInt(sample_info[x].time)/60)+',';
			treat_json += '"duration_units":"hour"},';
			
			treatment_ids.push(sample_info[0].sid);
		}
		
		if (sample_info[x].treatment_uuid != null && treatment_ids.indexOf(sample_info[x].sid) == -1) {
			treat_json_patch += '{"aliases":["manuel-garber:LPS_'+parseInt(sample_info[x].time)/60+'h"],';
			treat_json_patch += '"treatment_term_name":"Lipopolysaccharide",';
			treat_json_patch += '"treatment_term_id":"CHEBI:16412",';
			treat_json_patch += '"treatment_type":"infection",';
			if ((parseInt(sample_info[x].time)/60) == 0) {
				treat_json_patch += '"concentration":0,';
				treat_json_patch += '"concentration_units":"ng/mL",';
			}else{
				treat_json_patch += '"concentration":100,';
				treat_json_patch += '"concentration_units":"ng/mL",';
			}
			treat_json_patch += '"duration":'+(parseInt(sample_info[x].time)/60)+',';
			treat_json_patch += '"duration_units":"hour"},';
			
			treatment_ids.push(sample_info[0].sid);
		}
	}
	if (treat_json != '[') {
		treat_json = treat_json.substring(0,treat_json.length - 1);
	}
	treat_json += ']';
	if (treat_json_patch != '[') {
		treat_json_patch = treat_json_patch.substring(0,treat_json_patch.length - 1);
	}
	treat_json_patch += ']';
	console.log([treat_json, treat_json_patch]);
	return [treat_json, treat_json_patch];
}

//	Optional?
function getAntibodyJson(){
	var antibody_lot = "[]";
	var antibody_lot_patch = "[]";

	var abcam = '[{"aliases":["' + experiment_info[0].lab + ':' + antibody_info[0].product_id +'"],';
	abcam += '"source":"' + antibody_info[0].source + '",';
	abcam += '"product_id":"' + antibody_info[0].product_id +'",';
	abcam += '"lot_id":"' + antibody_info[0].lot_id + '",';
	abcam += '"host_organism":"' + antibody_info[0].host_organism + '",';
	abcam += '"lab":"' + experiment_info[0].lab + '",';
	abcam += '"award":"' + experiment_info[0].grant + '",';
	abcam += '"targets":["' + antibody_info[0].target + '"]}]';
	
	if (antibody_info[0].antibody_lot_acc == null) {
		antibody_lot = abcam
	}
	if (antibody_info[0].antibody_lot_acc != null) {
		var antibody_lot_patch = abcam;
	}
	if (antibody_ids.indexOf(antibody_info[0].id) < 0) {
			antibody_ids.push(antibody_info[0].id);
			antibody_accs.push(antibody_info[0].antibody_lot_acc);
	}
	return [antibody_lot, antibody_lot_patch];
}

function getEncodeAccession(json_name, accession){
	response = [];
	$.ajax({ type: "GET",
		url: BASE_PATH + "/public/ajax/encode_get.php",
		data: { json_name: json_name, accession: accession },
		async: false,
		success : function(s)
		{
			var string_array_json = "[" + s + "]";
			response = JSON.parse(string_array_json);
		}
	});
	return response;
}

function getEncodeUUID(json_name, uuid){
	response = [];
	$.ajax({ type: "GET",
		url: "https://www.encodeproject.org/search/?type=" + json_name + "&limit=all&format=json&frame=object",
		async: false,
		success : function(s)
		{
			for(var x = 0; x < s['@graph'].length; x++){
				if (s['@graph'][x].uuid == uuid) {
					console.log(s['@graph'][x]);
				}
			}
		}
	});
	return response;
}

function encodeFilePost(){
	console.log(biosample_ids);
	console.log(experiment_accs);
	console.log(replicate_uuids);
	for(var x = 0; x < biosample_ids.length; x++){
		$.ajax({ type: "GET",
			url: BASE_PATH + "/public/ajax/encode_files.php",
			data: { sample_id: biosample_ids[x], experiment: experiment_accs[x], replicate: replicate_uuids[x] },
			async: false,
			success : function(s)
			{
				var file_post_string = "[" + s + "]";
				console.log(file_post_string);
				var file_response = JSON.parse(file_post_string);
				console.log(file_response);
			}
		});
	}
}

function encodeSubmission(name, json, subType, type, table){
	var response = [];
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
	
	console.log(name);
	console.log(json);
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
		console.log(accs.toString());
		$.ajax({ type: "GET",
			url: BASE_PATH + "/public/ajax/encode_patch.php",
			data: { json_name: name, json_passed: json, accession: accs.toString() },
			async: false,
			success : function(s)
			{
				var string_array_json = "[" + s + "]";
				console.log(string_array_json);
				response = JSON.parse(string_array_json);
			}
		});
	}
	console.log(subType);
	
	output += '<b>' + name + ' ' + subType + ' Submission:</b></br>';
	
	for(var x = 0; x < response.length; x++){
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
					submitAccessionAndUuid(item[x], table, type, "treatment", response[x]['@graph'][0].uuid);
				}
			}else if (type == "replicate"){
				if (replicate_uuids[x] == null || replicate_uuids[x] == "" || replicate_uuids[x] == undefined) {
					replicate_uuids[x] = response[x]['@graph'][0].uuid;
				}
				if (response[x]['@graph'][0].uuid != undefined) {
					submitAccessionAndUuid(item[x], table, type, "replicate", response[x]['@graph'][0].uuid);
				}
			}else if (type == "experiment"){
				if (experiment_accs[x] == null || experiment_accs[x] == "" || experiment_accs[x] == undefined) {
					experiment_accs[x] = response[x]['@graph'][0].accession;
				}
				if (response[x]['@graph'][0].accession != undefined && response[x]['@graph'][0].uuid != undefined) {
					submitAccessionAndUuid(item[x], table, type, response[x]['@graph'][0].accession, response[x]['@graph'][0].uuid);
				}
			}else{
				if (response[x]['@graph'][0].accession != undefined && response[x]['@graph'][0].uuid != undefined) {
					submitAccessionAndUuid(item[x], table, type, response[x]['@graph'][0].accession, response[x]['@graph'][0].uuid);
				}
			}
		}else{
			//	ERROR
			if (response[x].status.toLowerCase() == 'error') {
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
	}
	return output;
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

function encodePost(subType){
	//getEncodeAccession('antibody_lot', 'ENCAB969VGQ');
	var responseOutput = '';
	
	//	Grab json information
	var donor_pre_json = getDonorJson();
	var experiment_pre_json = getExperimentJson();
	var treatment_pre_json = getTreatmentJson();
	var biosample_pre_json = getSampleJson();
	var library_pre_json = getLibraryJson();
	var antibody_lot_pre_json = getAntibodyJson();
	var replicate_pre_json = getReplicateJson();
	
	console.log(donor_pre_json);
	console.log(experiment_pre_json);
	console.log(treatment_pre_json);
	console.log(biosample_pre_json);
	console.log(library_pre_json);
	console.log(antibody_lot_pre_json);
	console.log(replicate_pre_json);
	
	//	ALREADY IN ENCODE //
	//	EXPERIMENTS
	/*
	$.ajax({ type: "GET",
		url: "https://www.encodeproject.org/search/?type=experiment&limit=all&format=json&frame=object",
		async: false,
		success : function(s)
		{
			for(var x = 0; x < s['@graph'].length; x++){
				if (s['@graph'][x].uuid == '"2eaa0ac2-c1d1-4fd1-8422-477675c4c6ae"') {
					console.log(s['@graph'][x]);
				}
			}
		}
	});
	
	//	BIOSAMPLE
	//	biosample_term_id, biosample_term_name, biosample_type
	$.ajax({ type: "GET",
		url: "https://www.encodeproject.org/search/?type=biosample&limit=all&format=json&frame=object",
		async: false,
		success : function(s)
		{
			for(var x = 0; x < s['@graph'].length; x++){
				if (s['@graph'][x].biosample_term_id == 'CL:0000451') {
					console.log(s['@graph'][x].biosample_term_name);
				}
			}
		}
	});
	//	ASSAY
	//	assay_term_id, assay_term_name
	$.ajax({ type: "GET",
		url: "https://www.encodeproject.org/search/?type=experiment&limit=all&format=json&frame=object",
		async: false,
		success : function(s)
		{
			for(var x = 0; x < s['@graph'].length; x++){
				if (s['@graph'][x].assay_term_name == 'ATAC-seq') {
					console.log(s['@graph'][x].assay_term_name);
				}
			}
		}
	});
	//	ORGANISMS
	$.ajax({ type: "GET",
		url: "https://www.encodeproject.org/search/?type=organism&format=json&frame=object&limit=all",
		async: false,
		success : function(s)
		{
			//console.log(s);
		}
	});
	
	//	SOURCE
	$.ajax({ type: "GET",
		url: "https://www.encodeproject.org/search/?type=source&format=json&frame=object&limit=all",
		async: false,
		success : function(s)
		{
			//console.log(s);
		}
	});
	
	//	ANTIBODY
	$.ajax({ type: "GET",
		url: "https://www.encodeproject.org/search/?type=antibody_lot&limit=all&format=json&frame=object",
		async: false,
		success : function(s)
		{
			for(var x = 0; x < s['@graph'].length; x++){
				if (s['@graph'][x].uuid == '"2eaa0ac2-c1d1-4fd1-8422-477675c4c6ae"') {
					console.log(s['@graph'][x]);
				}
			}
		}
	});
	*/
	
	//	DONOR SUBMISSION
	if (donor_pre_json[0] != "[]") {
		responseOutput += encodeSubmission('human_donor', donor_pre_json[0], "post", "donor", "ngs_donor");
	}
	if (subType == "patch" && donor_pre_json[1] != "[]") {
		responseOutput += encodeSubmission('human_donor', donor_pre_json[1], subType, "donor", "ngs_donor");
	}
	
	//	EXPERIMENT SUBMISSION
	if (experiment_pre_json[0] != "[]") {
		responseOutput += encodeSubmission('experiments', experiment_pre_json[0], "post", "experiment", "ngs_samples");
	}
	if (subType == "patch" && experiment_pre_json[1] != "[]") {
		responseOutput += encodeSubmission('experiments', experiment_pre_json[1], subType, "experiment", "ngs_samples");
	}
	//	TREATMENT SUBMISSION
	if (treatment_pre_json[0] != "[]") {
		//responseOutput +=
		encodeSubmission('treatments', treatment_pre_json[0], "post", "treatment", "ngs_samples");
	}
	/*
	if (subType == "patch" && treatment_pre_json[1] != "[]") {
		responseOutput += encodeSubmission('treatments', treatment_pre_json[1], subType, "treatment", "ngs_samples");
	}
	*/
	//	BIOSAMPLE SUBMISSION
	if (biosample_pre_json[0] != "[]") {
		responseOutput += encodeSubmission('biosamples', biosample_pre_json[0], "post", "biosample", "ngs_samples");
	}
	if (subType == "patch" && biosample_pre_json[1] != "[]") {
		responseOutput += encodeSubmission('biosamples', biosample_pre_json[1], subType, "biosample", "ngs_samples");
	}
	
	//	LIBRARY SUBMISSION
	if (library_pre_json[0] != "[]") {
		responseOutput += encodeSubmission('libraries', library_pre_json[0], "post", "library", "ngs_samples");
	}
	if (subType == "patch" && library_pre_json[1] != "[]") {
		responseOutput += encodeSubmission('libraries', library_pre_json[1], subType, "library", "ngs_samples");
	}
	/*
	//	ANTIBODY_LOT SUBMISSION
	if (antibody_lot_pre_json[0] != "[]") {
		responseOutput += encodeSubmission('antibodies', antibody_lot_pre_json[0], "post", "antibody_lot", "ngs_antibody_target");
	}
	if (subType == "patch" && antibody_lot_pre_json[1] != "[]") {
		responseOutput += encodeSubmission('antibodies', antibody_lot_pre_json[1], subType, "antibody_lot", "ngs_antibody_target");
	}
	*/
	//	REPLICATE SUBMISSION
	if (replicate_pre_json[0] != "[]") {
		responseOutput += encodeSubmission('replicate', replicate_pre_json[0], "post", "replicate", "ngs_samples");
	}
	if (subType == "patch" && replicate_pre_json[1] != "[]") {
		responseOutput += encodeSubmission('replicate', replicate_pre_json[1], subType, "replicate", "ngs_samples");
	}
	
	//	FILE SUBMISSION
	encodeFilePost();
	
	//	Report Errors/Successes to modal
	document.getElementById('myModalLabel').innerHTML = 'Encode Submission';
	document.getElementById('deleteLabel').innerHTML = 'Response from ENCODE servers:';
	document.getElementById('deleteAreas').innerHTML = responseOutput;
		
	document.getElementById('cancelDeleteButton').innerHTML = "OK";
	document.getElementById('confirmDeleteButton').setAttribute('style', 'display:none');
	document.getElementById('confirmPatchButton').setAttribute('style', 'display:none');
}