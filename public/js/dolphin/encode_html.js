var addModalType = '';

function responseCheck(data) {
	for(var x = 0; x < Object.keys(data).length; x++){
		if (data[Object.keys(data)[x]] == null) {
			data[Object.keys(data)[x]] = '<br>';
		}
	}
	return data;
}

function loadInEncodeSubmissions(){
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/encode_tables.php",
			data: { p: "getSubmissions" },
			async: false,
			success : function(s)
			{
				console.log(s);
				var subtable = $('#jsontable_submissions_table').dataTable();
				subtable.fnClearTable();
				for(var x = 0; x < s.length; x++){
					var sub_status;
					if (s[x].sub_status == 1) {
						sub_status = "<button class=\"btn btn-success\"></button>";
					}else if (s[x].sub_status == 2) {
						sub_status = "<button class=\"btn btn-danger\"></button>";
					}
					subtable.fnAddData([
						s[x].id,
						s[x].samplename,
						sub_status,
						s[x].output_file
					]);
				}
			}
		});
}

function loadInEncodeTables(){
	basket_info = getBasketInfo();
	if (basket_info != '') {
		loadSamples();
		loadDonors();
		loadExperiments();
		loadTreatments();
		loadBiosamples();
		loadLibraries();
		loadAntibodies();
		loadReplicates();
	}
}

function loadSamples(){
	var treatmentSelect = document.getElementById('addSampleTreatment')
	var antibodySelect = document.getElementById('addSampleAntibody')
	
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/encode_tables.php",
		data: { p: "getSamples", samples:basket_info },
		async: false,
		success : function(s)
		{
			treatmentSelect.innerHTML = '';
			antibodySelect.innerHTML = '';
			var sampletable = $('#jsontable_encode_selected_samples').dataTable();
			sampletable.fnClearTable();
			for(var x = 0; x < s.length; x++){
				//	Datatable
				s[x] = responseCheck(s[x]);
				sampletable.fnAddData([
					s[x].sample_id,
					s[x].samplename,
					"<p onclick=\"editEncodeBox("+1+", '"+s[x].donor_id+"', 'donor', 'ngs_donor', this, 'ngs_samples', '"+s[x].sample_id+"', 'donor_id', 'donor')\">"+s[x].donor+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].source_id+"', 'source', 'ngs_source', this, 'ngs_samples', "+s[x].sample_id+", 'source_id')\">"+s[x].source+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].organism_id+"', 'organism', 'ngs_organism', this, 'ngs_samples', '"+s[x].sample_id+"', 'organism_id')\">"+s[x].organism+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].molecule_id+"', 'molecule', 'ngs_molecule', this, 'ngs_samples', '"+s[x].sample_id+"', 'molecule_id')\">"+s[x].molecule+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].lab_id+"', 'lab', 'ngs_lab', this, 'ngs_experiment_series', '"+s[x].experiment_series_id+"', 'lab_id')\">"+s[x].lab+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].experiment_series_id+"', 'grant', 'ngs_experiment_series', this, '', '', '')\">"+s[x].grant+"</p>",
					"<button id=\"sample_removal_"+s[x].id+"\" class=\"btn btn-danger btn-xs pull-right\" onclick=\"manageChecklists('"+s[x].sample_id+"', 'sample_checkbox')\"><i class=\"fa fa-times\"></i></button>",
					"<input type=\"checkbox\" class=\"pull-right\" onclick=\"allCheckboxCheck("+s[x].sample_id+", 'sample')\">"
				]);
				
				//	Modal
				treatmentSelect.innerHTML += '<option id="treatment_'+s[x].samplename+'" value="'+s[x].id+'">'+s[x].samplename+'</option>'
				antibodySelect.innerHTML += '<option id="treatment_'+s[x].samplename+'" value="'+s[x].id+'">'+s[x].samplename+'</option>'
			}
			
		}
	});
}

function loadDonors(){
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/encode_tables.php",
		data: { p: "getDonors", samples:basket_info },
		async: false,
		success : function(s)
		{
			console.log(s);
			var donortable = $('#jsontable_encode_donors').dataTable();
			donortable.fnClearTable();
			for(var x = 0; x < s.length; x++){
				s[x] = responseCheck(s[x]);
				donortable.fnAddData([
					"<p onclick=\"editEncodeBox("+1+", '"+s[x].donor_id+"', 'donor', 'ngs_donor', this, '', '', '', 'donor')\">"+s[x].donor+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].donor_id+"', 'life_stage', 'ngs_donor', this, '', '', '')\">"+s[x].life_stage+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].donor_id+"', 'age', 'ngs_donor', this, '', '', '')\">"+s[x].age+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].donor_id+"', 'sex', 'ngs_donor', this, '', '', '')\">"+s[x].sex+"</p>",
					s[x].donor_acc,
					s[x].donor_uuid,
					"<input type=\"checkbox\" class=\"pull-right\" onclick=\"allCheckboxCheck("+s[x].sample_id+", 'donor')\">"
				]);
			}
		}
	});
}

function loadExperiments(){
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/encode_tables.php",
		data: { p: "getExperiments", samples:basket_info },
		async: false,
		success : function(s)
		{
			console.log(s);
			var experimenttable = $('#jsontable_encode_experiments').dataTable();
			experimenttable.fnClearTable();
			for(var x = 0; x < s.length; x++){
				s[x] = responseCheck(s[x]);
				experimenttable.fnAddData([
					s[x].samplename,
					"<p onclick=\"editBox("+1+", '"+s[x].library_strategy_id+"', 'library_strategy', 'ngs_library_strategy', this, 'ngs_protocols', '"+s[x].protocol_id+"', 'library_strategy_id')\">"+s[x].library_strategy+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].source_id+"', 'source', 'ngs_source', this, 'ngs_samples', '"+s[x].sample_id+"', 'source_id')\">"+s[x].source+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].sample_id+"', 'description', 'ngs_samples', this, '', '', '')\">"+s[x].description+"</p>",
					s[x].experiment_acc,
					s[x].experiment_uuid,
					"<input type=\"checkbox\" class=\"pull-right\" onclick=\"allCheckboxCheck("+s[x].sample_id+", 'experiment')\">"
				]);
			}
		}
	});
}

function loadTreatments() {
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/encode_tables.php",
		data: { p: "getTreatments", samples:basket_info },
		async: false,
		success : function(s)
		{
			console.log(s);
			var treatmenttable = $('#jsontable_encode_treatments').dataTable();
			treatmenttable.fnClearTable();
			for(var x = 0; x < s.length; x++){
				s[x] = responseCheck(s[x]);
				treatmenttable.fnAddData([
					"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'name', 'ngs_treatment', this, '', '', '')\">"+s[x].name+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'treatment_term_name', 'ngs_treatment', this, '', '', '')\">"+s[x].treatment_term_name+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'treatment_term_id', 'ngs_treatment', this, '', '', '')\">"+s[x].treatment_term_id+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'treatment_type', 'ngs_treatment', this, '', '', '')\">"+s[x].treatment_type+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'concentration', 'ngs_treatment', this, '', '', '')\">"+s[x].concentration+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'concentration_units', 'ngs_treatment', this, '', '', '')\">"+s[x].concentration_units+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'duration', 'ngs_treatment', this, '', '', '')\">"+s[x].duration+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'duration_units', 'ngs_treatment', this, '', '', '')\">"+s[x].duration_units+"</p>",
					"<input type=\"checkbox\" class=\"pull-right\" onclick=\"allCheckboxCheck("+s[x].sample_id+", 'treatment')\">"
				]);
			}
		}
	});
}

function loadBiosamples() {
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/encode_tables.php",
		data: { p: "getBiosamples", samples:basket_info },
		async: false,
		success : function(s)
		{
			console.log(s);
			var biosampletable = $('#jsontable_encode_biosamples').dataTable();
			biosampletable.fnClearTable();
			for(var x = 0; x < s.length; x++){
				s[x] = responseCheck(s[x]);
				biosampletable.fnAddData([
					s[x].samplename,
					"<p onclick=\"editBox("+1+", '"+s[x].treatment_id+"', 'name', 'ngs_treatment', this, 'ngs_samples', '"+s[x].sample_id+"', 'treatment_id')\">"+s[x].name+"</p>",
					"<p onclick=\"editEncodeBox("+1+", '"+s[x].biosample_id+"', 'biosample_term_name', 'ngs_biosample_term', this, 'ngs_samples', '"+s[x].sample_id+"', 'biosample_id', 'biosample')\">"+s[x].biosample_term_name+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].biosample_id+"', 'biosample_term_id', 'ngs_biosample_term', this, '', '', '')\">"+s[x].biosample_term_id+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].biosample_id+"', 'biosample_type', 'ngs_biosample_term', this, '', '', '')\">"+s[x].biosample_type+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].lane_id+"', 'date_submitted', 'ngs_lanes', this, '', '', '')\">"+s[x].date_submitted+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].lane_id+"', 'date_received', 'ngs_lanes', this, '', '', '')\">"+s[x].date_received+"</p>",
					s[x].biosample_acc,
					s[x].biosample_uuid,
					"<input type=\"checkbox\" class=\"pull-right\" onclick=\"allCheckboxCheck("+s[x].sample_id+", 'biosample')\">"
				]);
			}
		}
	});
}

function loadLibraries() {
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/encode_tables.php",
		data: { p: "getLibraries", samples:basket_info },
		async: false,
		success : function(s)
		{
			console.log(s);
			var librarytable = $('#jsontable_encode_libraries').dataTable();
			librarytable.fnClearTable();
			for(var x = 0; x < s.length; x++){
				s[x] = responseCheck(s[x]);
				librarytable.fnAddData([
					s[x].samplename,
					"<p onclick=\"editBox("+1+", '"+s[x].molecule_id+"', 'molecule', 'ngs_molecule', this, 'ngs_samples', '"+s[x].sample_id+"', 'molecule_id')\">"+s[x].molecule+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].protocol_id+"', 'crosslinking_method', 'ngs_protocols', this, '', '', '')\">"+s[x].crosslinking_method+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].sample_id+"', 'spike_ins', 'ngs_samples', this, '', '', '')\">"+s[x].spike_ins+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].protocol_id+"', 'extraction', 'ngs_protocols', this, '', '', '')\">"+s[x].extraction+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].protocol_id+"', 'fragmentation_method', 'ngs_protocols', this, '', '', '')\">"+s[x].fragmentation_method+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].sample_id+"', 'read_length', 'ngs_samples', this, '', '', '')\">"+s[x].read_length+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].imid+"', 'instrument_model', 'ngs_instrument_model', this, 'ngs_samples', '"+s[x].sample_id+"', 'instrument_model_id')\">"+s[x].instrument_model+"</p>",
					s[x].library_acc,
					s[x].library_uuid,
					"<input type=\"checkbox\" class=\"pull-right\" onclick=\"allCheckboxCheck("+s[x].sample_id+", 'library')\">"
				]);
			}
		}
	});
}

function loadAntibodies() {
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/encode_tables.php",
		data: { p: "getAntibodies", samples:basket_info },
		async: false,
		success : function(s)
		{
			console.log(s);
			var antibodytable = $('#jsontable_encode_antibodies').dataTable();
			antibodytable.fnClearTable();
			for(var x = 0; x < s.length; x++){
				s[x] = responseCheck(s[x]);
				antibodytable.fnAddData([
					"<p onclick=\"editEncodeBox("+1+", '"+s[x].id+"', 'target', 'ngs_antibody_target', this, '', '', '', 'antibody')\">"+s[x].target+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'source', 'ngs_antibody_target', this, '', '', '')\">"+s[x].source+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'product_id', 'ngs_antibody_target', this, '', '', '')\">"+s[x].product_id+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'lot_id', 'ngs_antibody_target', this, '', '', '')\">"+s[x].lot_id+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'host_organism', 'ngs_antibody_target', this, '', '', '')\">"+s[x].host_organism+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'clonality', 'ngs_antibody_target', this, '', '', '')\">"+s[x].clonality+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'isotype', 'ngs_antibody_target', this, '', '', '')\">"+s[x].isotype+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'purifications', 'ngs_antibody_target', this, '', '', '')\">"+s[x].purifications+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'url', 'ngs_antibody_target', this, '', '', '')\">"+s[x].url+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'uuid', 'ngs_antibody_target', this, '', '', '')\">"+s[x].uuid+"</p>",
					"<input type=\"checkbox\" class=\"pull-right\" onclick=\"allCheckboxCheck("+s[x].sample_id+", 'antibody')\">"
				]);
			}
		}
	});
}

function loadReplicates(){
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/encode_tables.php",
		data: { p: "getReplicates", samples:basket_info },
		async: false,
		success : function(s)
		{
			console.log(s);
			var replicatetable = $('#jsontable_encode_replicates').dataTable();
			replicatetable.fnClearTable();
			for(var x = 0; x < s.length; x++){
				if (s[x].biological_replica == null) {
					s[x].biological_replica = 1;
				}
				if (s[x].technical_replica == null) {
					s[x].technical_replica = 1;
				}
				s[x] = responseCheck(s[x]);
				replicatetable.fnAddData([
					s[x].samplename,
					"<p onclick=\"editBox("+1+", '"+s[x].antibody_id+"', 'target', 'ngs_antibody_target', this, 'ngs_samples', '"+s[x].sample_id+"', 'antibody_lot_id')\">"+s[x].target+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].sample_id+"', 'biological_replica', 'ngs_samples', this, '', '', '')\">"+s[x].biological_replica+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].sample_id+"', 'technical_replica', 'ngs_samples', this, '', '', '')\">"+s[x].technical_replica+"</p>",
					s[x].replicate_uuid,
					"<input type=\"checkbox\" class=\"pull-right\" onclick=\"allCheckboxCheck("+s[x].sample_id+", 'replicate')\">"
				]);
			}
		}
	});
}

function changeValuesEncode(type, table, ele, event = event){
	//	submitChanges function located within browse_edit.js
	//	Global variables used within browse_edit.js
	allcheck_table = table;
	if (type == "all") {
		allcheck_bool = true;
		selected_ids = checklist_samples;
	}else{
		allcheck_bool = false;
		selected_ids = allcheck[allcheck_table];
	}
	submitChanges(ele, event);
	updateSingleTable(table);
}

function updateSingleTable(table){
	if (table == "sample") {
		loadSamples();
	}else if (table == "donor") {
		loadDonors();
	}else if (table == "experiment") {
		loadExperiments();
	}else if (table == "treatment") {
		loadTreatments();
	}else if (table == "biosample") {
		loadBiosamples();
	}else if (table == "library") {
		loadLibraries();
	}else if (table == "antibody") {
		loadAntibodies();
	}else if (table == "replicate") {
		loadReplicates();
	}
}

function editEncodeBox(uid, id, type, table, element, parent_table, parent_table_id, parent_child, encode_table){
	singlecheck_table = encode_table;
	editBox(uid, id, type, table, element, parent_table, parent_table_id, parent_child);
}

function addTreatment(){
	$('#addTreatmentModal').modal({
		show: true
	});
	addModalType = 'treatment'
}

function addAntibody(){
	$('#addAntibodyModal').modal({
		show: true
	});
	addModalType = 'antibody'
}

function createNewData(type){
	var selected = document.getElementById('addSample'+type)
	var samples = []
	for (var i = 0; i < selected.length; i++) {
		if (selected.options[i].selected){
			samples.push(selected.options[i].value);
		}
	}
	var name = document.getElementById('addName'+type).value
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/encode_tables.php",
		data: { p: "createEncodeRow", type:type, samples:samples.toString(), name:name },
		async: false,
		success : function(s)
		{
			console.log(type)
			console.log(samples.toString())
			console.log(name)
			console.log(s)
			if (type == 'Treatment') {
				loadTreatments();
				loadBiosamples();
			}else if (type == 'Antibody') {
				loadAntibodies();
				loadReplicates();
			}
		}
	});
}

function toSubmitEncode(){
	window.location.href = BASE_PATH+"/public/encode"
}

function toEncodeSubmissions() {
	window.location.href = BASE_PATH+"/public/encode/submissions"
}

function backToBrowser(){
	window.location.href = BASE_PATH+"/public/search"
}

$( function(){
	if (phpGrab.theSegment == "encode_submissions") {
		loadInEncodeSubmissions();
	}else{
		loadInEncodeTables();
	}
});