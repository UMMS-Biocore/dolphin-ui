var addModalType = '';
var sampleRuns = {};
var runParams = {};
var active_runs = [];

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
		data: { p: "getBatchSubmissions" },
		async: false,
		success : function(s)
		{
			console.log(s);
			var subbatchtable = $('#jsontable_submissions_batch_table').dataTable();
			subbatchtable.fnClearTable();
			for(var x = 0; x < s.length; x++){
				var sub_status;
				if (s[x].sub_status == 1) {
					sub_status = "<button class=\"btn btn-success\"></button>";
				}else if (s[x].sub_status == 2) {
					sub_status = "<button class=\"btn btn-danger\"></button>";
				}
				subbatchtable.fnAddData([
					s[x].id,
					s[x].samples,
					sub_status,
					s[x].output_file,
					"<button class=\"btn btn-primary pull-right\" onclick=\"resubmitEncode('"+s[x].samples+"')\">Resubmit</button>",
				]);
			}
		}
	});
	
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
		loadFiles();
	}
}

function loadSamples(){
	var treatmentSelect = document.getElementById('addSampleTreatment')
	var antibodySelect = document.getElementById('addSampleAntibody')
	var biosampleSelect = document.getElementById('selectBiosample')
	var experimentSelect = document.getElementById('selectExperiment')
	
	var linkBiosample = document.getElementById('linkBiosample')
	var linkExperiment = document.getElementById('linkExperiment')
	
	var ret_biosample_accs = [];
	var ret_experiment_accs = [];
	
	treatmentSelect.innerHTML = ''
	antibodySelect.innerHTML = ''
	biosampleSelect.innerHTML = ''
	experimentSelect.innerHTML = ''
	
	linkBiosample.innerHTML = '<option value="none">* New Accession *</option>';
	linkExperiment.innerHTML = '<option value="none">* New Accession *</option>';
	
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/encode_tables.php",
		data: { p: "getSamples", samples:basket_info },
		async: false,
		success : function(s)
		{
			console.log(s);
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
					"<p onclick=\"editBox("+1+", '"+s[x].organism_id+"', 'organism', 'ngs_organism', this, 'ngs_samples', '"+s[x].sample_id+"', 'organism_id')\">"+s[x].organism+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].molecule_id+"', 'molecule', 'ngs_molecule', this, 'ngs_samples', '"+s[x].sample_id+"', 'molecule_id')\">"+s[x].molecule+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].lab_id+"', 'lab', 'ngs_lab', this, 'ngs_experiment_series', '"+s[x].e_id+"', 'lab_id')\">"+s[x].lab+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].e_id+"', 'grant', 'ngs_experiment_series', this, '', '', '')\">"+s[x].grant+"</p>",
					"<button id=\"sample_removal_"+s[x].sample_id+"\" class=\"btn btn-danger btn-xs pull-right\" onclick=\"manageChecklists('"+s[x].sample_id+"', 'sample_checkbox')\"><i class=\"fa fa-times\"></i></button>",
					"<input type=\"checkbox\" class=\"pull-right\" onclick=\"allCheckboxCheck("+s[x].sample_id+", 'sample')\">"
				]);
				
				//	Modal
				treatmentSelect.innerHTML += '<option id="treatment_'+s[x].samplename+'" value="'+s[x].sample_id+'">'+s[x].samplename+'</option>'
				antibodySelect.innerHTML += '<option id="antibody_'+s[x].samplename+'" value="'+s[x].sample_id+'">'+s[x].samplename+'</option>'
				biosampleSelect.innerHTML += '<option id="antibody_'+s[x].samplename+'" value="'+s[x].sample_id+'">'+s[x].samplename+'</option>'
				experimentSelect.innerHTML += '<option id="antibody_'+s[x].samplename+'" value="'+s[x].sample_id+'">'+s[x].samplename+'</option>'
				
				if (ret_biosample_accs.indexOf(s[x].biosample_acc) == -1) {
					ret_biosample_accs.push(s[x].biosample_acc)
				}
				if (ret_experiment_accs.indexOf(s[x].experiment_acc) == -1) {
					ret_experiment_accs.push(s[x].experiment_acc)
				}
			}
			
		}
	});
	for(var x in ret_biosample_accs){
		linkBiosample.innerHTML += '<option value="'+ret_biosample_accs[x]+'">'+ret_biosample_accs[x]+'</option>';
	}
	for(var x in ret_experiment_accs){
		linkExperiment.innerHTML += '<option value="'+ret_experiment_accs[x]+'">'+ret_experiment_accs[x]+'</option>';
	}
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
					"<p onclick=\"editBox("+1+", '"+s[x].donor_id+"', 'donor_acc', 'ngs_donor', this, '', '', '')\">"+s[x].donor_acc+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].donor_id+"', 'donor_uuid', 'ngs_donor', this, '', '', '')\">"+s[x].donor_uuid+"</p>",
					"<input type=\"checkbox\" class=\"pull-right\" onclick=\"allCheckboxCheck("+s[x].donor_id+", 'donor')\">"
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
					"<p onclick=\"editBox("+1+", '"+s[x].assay_id+"', 'assay_term_name', 'ngs_assay_term', this, 'ngs_protocols', '"+s[x].protocol_id+"', 'assay_term_id')\">"+s[x].assay_term_name+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].assay_id+"', 'assay_term_id', 'ngs_assay_term', this, 'ngs_protocols', '"+s[x].protocol_id+"', 'assay_term_id')\">"+s[x].assay_term_id+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].sample_id+"', 'description', 'ngs_samples', this, '', '', '')\">"+s[x].description+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].sample_id+"', 'experiment_acc', 'ngs_samples', this, '', '', '')\">"+s[x].experiment_acc+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].sample_id+"', 'experiment_uuid', 'ngs_samples', this, '', '', '')\">"+s[x].experiment_uuid+"</p>",
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
					"<input type=\"checkbox\" class=\"pull-right\" onclick=\"allCheckboxCheck("+s[x].id+", 'treatment')\">"
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
					"<p onclick=\"editBox("+1+", '"+s[x].sample_id+"', 'biosample_derived_from', 'ngs_samples', this, '', '', '')\">"+s[x].biosample_derived_from+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].treatment_id+"', 'name', 'ngs_treatment', this, 'ngs_samples', '"+s[x].sample_id+"', 'treatment_id')\">"+s[x].name+"</p>",
					"<p onclick=\"editEncodeBox("+1+", '"+s[x].biosample_id+"', 'biosample_term_name', 'ngs_biosample_term', this, 'ngs_samples', '"+s[x].sample_id+"', 'biosample_id', 'biosample')\">"+s[x].biosample_term_name+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].biosample_id+"', 'biosample_term_id', 'ngs_biosample_term', this, '', '', '')\">"+s[x].biosample_term_id+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].biosample_id+"', 'biosample_type', 'ngs_biosample_term', this, '', '', '')\">"+s[x].biosample_type+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].starting_amount_id+"', 'starting_amount', 'ngs_starting_amount', this, 'ngs_protocols', '"+s[x].protocol_id+"', 'starting_amount_id')\">"+s[x].starting_amount+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].starting_amount_id+"', 'starting_amount_units', 'ngs_starting_amount', this, 'ngs_protocols', '"+s[x].protocol_id+"', 'starting_amount_id')\">"+s[x].starting_amount_units+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].lane_id+"', 'date_submitted', 'ngs_lanes', this, '', '', '')\">"+s[x].date_submitted+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].lane_id+"', 'date_received', 'ngs_lanes', this, '', '', '')\">"+s[x].date_received+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].sample_id+"', 'biosample_acc', 'ngs_samples', this, '', '', '')\">"+s[x].biosample_acc+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].sample_id+"', 'biosample_uuid', 'ngs_samples', this, '', '', '')\">"+s[x].biosample_uuid+"</p>",
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
					"<p onclick=\"editBox("+1+", '"+s[x].nucleic_acid_id+"', 'nucleic_acid_term_name', 'ngs_nucleic_acid_term', this, 'ngs_protocols', '"+s[x].protocol_id+"', 'nucleic_acid_term_id')\">"+s[x].nucleic_acid_term_name+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].nucleic_acid_id+"', 'nucleic_acid_term_id', 'ngs_nucleic_acid_term', this, 'ngs_protocols', '"+s[x].protocol_id+"', 'nucleic_acid_term_id')\">"+s[x].nucleic_acid_term_id+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].protocol_id+"', 'crosslinking_method', 'ngs_protocols', this, '', '', '')\">"+s[x].crosslinking_method+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].sample_id+"', 'spike_ins', 'ngs_samples', this, '', '', '')\">"+s[x].spike_ins+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].protocol_id+"', 'extraction', 'ngs_protocols', this, '', '', '')\">"+s[x].extraction+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].protocol_id+"', 'fragmentation_method', 'ngs_protocols', this, '', '', '')\">"+s[x].fragmentation_method+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].sample_id+"', 'avg_insert_size', 'ngs_samples', this, '', '', '')\">"+s[x].avg_insert_size+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].sample_id+"', 'read_length', 'ngs_samples', this, '', '', '')\">"+s[x].read_length+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].imid+"', 'instrument_model', 'ngs_instrument_model', this, 'ngs_samples', '"+s[x].sample_id+"', 'instrument_model_id')\">"+s[x].instrument_model+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].flowcell_id+"', 'machine_name', 'ngs_flowcell', this, 'ngs_samples', '"+s[x].sample_id+"', 'flowcell_id', 'machine_name')\">"+s[x].machine_name+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].flowcell_id+"', 'flowcell', 'ngs_flowcell', this, 'ngs_samples', '"+s[x].sample_id+"', 'flowcell_id', 'flowcell')\">"+s[x].flowcell+"</p>",
					"<p onclick=\"editBox("+1+", '"+s[x].flowcell_id+"', 'lane', 'ngs_flowcell', this, 'ngs_samples', '"+s[x].sample_id+"', 'flowcell_id', 'lane')\">"+s[x].lane+"</p>",
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
					"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'uuid', 'ngs_antibody_target', this, '', '', '')\">"+s[x].antibody_lot_uuid+"</p>",
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

function loadFiles() {
	sampleRuns = {};
	runParams = {};
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/encode_tables.php",
		data: { p: "getFiles", samples:basket_info },
		async: false,
		success : function(s)
		{
			console.log(s);
			for(var x = 0; x < s.length; x++){
				if (sampleRuns[s[x].samplename] == undefined) {
					sampleRuns[s[x].samplename] = {};
					sampleRuns[s[x].samplename]['sid'] = s[x].sample_id
					sampleRuns[s[x].samplename]['rid'] = s[x].id
					sampleRuns[s[x].samplename]['did'] = s[x].dir_id
					sampleRuns[s[x].samplename][s[x].id] = 1
				}
				if (runParams[s[x].id] == undefined) {
					runParams[s[x].id] = JSON.parse(s[x].json_parameters)
					runParams[s[x].id].run_name = s[x].run_name
					runParams[s[x].id].run_description = s[x].run_description
					runParams[s[x].id].outdir = s[x].outdir
				}
				if (sampleRuns[s[x].samplename][s[x].id] == undefined) {
					sampleRuns[s[x].samplename][s[x].id] = 0
				}
			}
		}
	});
	
	var runtable = $('#jsontable_encode_runs').dataTable();
	var keys = Object.keys(sampleRuns)
	runtable.fnClearTable();
	var ordertable = $('#jsontable_encode_file_order').dataTable();
	ordertable.fnClearTable();
	for(var x = 0; x < keys.length; x++){
		var runs = Object.keys(sampleRuns[keys[x]])
		runs.splice(runs.indexOf('sid'), 1)
		runs.splice(runs.indexOf('rid'), 1)
		runs.splice(runs.indexOf('did'), 1)
		var sample_options = "";
		var directories = "";
		for(var y = 0; y < runs.length; y++){
			sample_options += "<option value=\""+runs[y]+"___"+runParams[runs[y]].run_name+"\">"+runParams[runs[y]].run_name+"</option>"
			directories += "<option value=\""+runParams[runs[y]].outdir+"\" style=\"display:none\"></option>"
		}
		runtable.fnAddData([
			keys[x],
			"<select id=\""+keys[x]+"_select\" class=\"form-control\" onChange=\"runSelectionEncode(this)\">" + sample_options + "</select>",
			"<select id=\""+keys[x]+"_dirselect\" style=\"display:none\">"+directories+"</select><p id=\""+keys[x]+"_dirvalue\">"+runParams[runs[0]].outdir+"</p>",
		]);
	}
	
	if (keys.length > 0) {
		runSelectionEncode(document.getElementById(keys[0]+'_select'))
	}
	
	var selectLength = keys.length
	console.log(selectLength)
	if (selectLength == 0) {
		document.getElementById("addSampleFiles").size = Math.floor((189)/18);
	}else if (selectLength == 1) {
		document.getElementById("addSampleFiles").size = Math.floor((203)/18);
	}else if (selectLength <= 10 ) {
		document.getElementById("addSampleFiles").size = Math.floor((((selectLength - 1)  * 51) + 203)/18);
	}else{
		document.getElementById("addSampleFiles").size = ((509)/18);
	}
	
}

function loadPreviousFiles(){
	var previoustable = $('#jsontable_previous_submissions').dataTable();
	previoustable.fnClearTable();
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/encode_tables.php",
		data: { p: "getSubmittedFiles", samples:basket_info },
		async: false,
		success : function(s)
		{
			console.log(s)
			for(var x = 0; x < s.length; x++){
				if (s[x].parent_file == 0) {
					previoustable.fnAddData([
						s[x].samplename,
						s[x].run_name,
						"Initial Fastq",
						s[x].parent_file,
						s[x].file_acc,
						s[x].file_uuid
					])
				}else{
					previoustable.fnAddData([
						s[x].samplename,
						s[x].run_name,
						s[x].outdir + s[x].file_name,
						s[x].parent_file,
						s[x].file_acc,
						s[x].file_uuid
					])
				}
			}
		}
	});
}

function runSelectionEncode(select){
	var id = select.id
	var id_name = id.split("_select")[0]
	var option = select.options[select.selectedIndex].value
	var option_split = option.split("___")
	var runs = Object.keys(sampleRuns[id_name])
	runs.splice(runs.indexOf('sid'), 1)
	runs.splice(runs.indexOf('rid'), 1)
	runs.splice(runs.indexOf('did'), 1)
	for (var x = 0; x < runs.length; x++) {
		if (runs[x] == option_split[0]) {
			sampleRuns[id_name][runs[x]] = 1
		}else{
			sampleRuns[id_name][runs[x]] = 0
		}
	}
	active_runs = gatherFileSelection()
	var options = createRunOptions(active_runs)
	var file_select = document.getElementById('addSampleFiles').innerHTML = options
	document.getElementById(id_name+'_dirvalue').innerHTML = document.getElementById(id_name+'_dirselect').options[select.selectedIndex].value
}

function gatherFileSelection() {
	var active_runs = []
	var samples = Object.keys(sampleRuns)
	console.log(samples)
	console.log(samples.length)
	for (var x = 0; x < samples.length; x++){
		console.log(samples[x])
		var runs = Object.keys(sampleRuns[samples[x]])
		runs.splice(runs.indexOf('sid'), 1)
		runs.splice(runs.indexOf('rid'), 1)
		runs.splice(runs.indexOf('did'), 1)
		for (var y = 0; y < runs.length; y++){
			console.log(sampleRuns[samples[x]][runs[y]])
			console.log(active_runs.indexOf(runs[y]))
			console.log("@@@@")
			if (sampleRuns[samples[x]][runs[y]] == 1 && active_runs.indexOf(runs[y]) == -1) {
				active_runs.push(runs[y])
			}
		}
	}
	return active_runs
}

function createRunOptions(active_runs) {
	console.log(active_runs)
	var options_parse = {};
	var options_select = "";
	
	for(var x = 0; x < active_runs.length; x++){
		options_parse = JSONOptionParse(active_runs[x], options_parse)
	}
	console.log(options_parse)
	var runs = Object.keys(options_parse)
	for (var z = 0; z < runs.length; z++){
		if (options_parse[runs[z]].length != active_runs.length) {
			console.log(options_parse)
			console.log(runs[z])
			console.log(active_runs)
			options_select += "<option disabled value=\""+runs[z]+"\">"+runs[z]+"</option>"
		}else{
			options_select += "<option value=\""+runs[z]+"\">"+runs[z]+"</option>"
		}
	}
	return options_select
}

function JSONOptionParse(run_id, options_parse){
	var pipeline = runParams[run_id].pipeline
	var commonind = runParams[run_id].commonind
	if (pipeline != undefined || pipeline != []) {
		if (runParams[run_id].commonind != "" && runParams[run_id].commonind != "no" && runParams[run_id].commonind != "none") {
			options_parse = mergeDedupChecks(runParams[run_id], run_id, merged, 'seqmapping', options_parse, commonind)
		}
		for(var y = 0; y < pipeline.length; y++){
			var merged = false;
			if (runParams[run_id].split != 'none') {
				merged = true;
			}
			if (pipeline[y].Type == 'RNASeqRSEM') {
				options_parse = mergeDedupChecks(pipeline[y], run_id, merged, 'rsem', options_parse, commonind)
			}else if (pipeline[y].Type == 'Tophat') {
				options_parse = mergeDedupChecks(pipeline[y], run_id, merged, 'tophat', options_parse, commonind)
			}else if (pipeline[y].MacsType.toLowerCase() == 'chip') {
				options_parse = mergeDedupChecks(pipeline[y], run_id, merged, 'chip', options_parse, commonind)
			}else if (pipeline[y].MacsType.toLowerCase() == 'atac') {
				options_parse = mergeDedupChecks(pipeline[y], run_id, merged, 'atac', options_parse, commonind)
			}else if (pipeline[y].Type == 'STAR') {
				//TBA
			}else if (pipeline[y].Type == 'Hisat2') {
				//TBA
			}
		}
	}
	return options_parse
}

function mergeDedupChecks(pipeline, run_id, merged, type, options_parse, commonind){
	var dedup = false;
	if (pipeline.MarkDuplicates == "yes") {
		dedup = true;
	}
	var tdf = false;
	if (pipeline.IGVTDF == "yes") {
		tdf = true;
	}
	var bigwig = false;
	if (pipeline.BAM2BW == "yes") {
		bigwig = true;
	}
	if (type == "seqmapping") {
		var split_common = commonind.split(",")
		for ( var x = 0; x < split_common.length; x++) {
			options_parse = optionsCheck(options_parse, '/seqmapping/'+split_common[x].toLowerCase()+'/', run_id, "fastq")
		}
	}
	
	if (dedup && merged) {
		if (type == 'rsem') {
			options_parse = optionsCheck(options_parse, '/dedupmergersem_ref.transcipts/', run_id, "bam")
			options_parse = optionsCheck(options_parse, '/rsem/genes', run_id, "tdf")
			options_parse = optionsCheck(options_parse, '/rsem/isoforms', run_id, "tdf")
			options_parse = optionsCheck(options_parse, '/ucsc_dedupmergersem_ref.transcipts/', run_id, "bigWig")
		}else{
			options_parse = optionsCheck(options_parse, '/dedupmerge'+type+'/', run_id, "bam")
			if (type == 'chip' || type == 'atac') {
				options_parse = optionsCheck(options_parse, '/macs/', run_id, "peaks-bed")
			}
			options_parse = optionsCheck(options_parse, '/ucsc_dedupmerge'+type+'/', run_id, "bigWig")
		}
	}else if (merged) {
		if (type == 'rsem') {
			options_parse = optionsCheck(options_parse, '/rsem/', run_id, "bam")
			options_parse = optionsCheck(options_parse, '/rsem/genes', run_id, "tdf")
			options_parse = optionsCheck(options_parse, '/rsem/isoforms', run_id, "tdf")
			options_parse = optionsCheck(options_parse, '/ucsc_rsem/', run_id, "bigWig")
		}else{
			options_parse = optionsCheck(options_parse, '/merge'+type+'/', run_id, "bam")
			if (type == 'chip' || type == 'atac') {
				options_parse = optionsCheck(options_parse, '/macs/', run_id, "peaks-bed")
			}
			options_parse = optionsCheck(options_parse, '/ucsc_dedupmerge'+type+'/', run_id, "bigWig")
		}
	}else if (dedup) {
		if (type == 'rsem') {
			options_parse = optionsCheck(options_parse, '/deduprsem_ref.transcipts/', run_id, "bam")
			options_parse = optionsCheck(options_parse, '/rsem/genes', run_id, "tdf")
			options_parse = optionsCheck(options_parse, '/rsem/isoforms', run_id, "tdf")
			options_parse = optionsCheck(options_parse, '/ucsc_deduprsem_ref.transcipts/', run_id, "bigWig")
		}else{
			options_parse = optionsCheck(options_parse, '/dedup'+type+'/', run_id, "bam")
			if (type == 'chip' || type == 'atac') {
				options_parse = optionsCheck(options_parse, '/macs/', run_id, "peaks-bed")
			}
			options_parse = optionsCheck(options_parse, '/ucsc_dedupmerge'+type+'/', run_id, "bigWig")
		}
	}else{
		if (type == 'rsem') {
			options_parse = optionsCheck(options_parse, '/rsem/', run_id, "bam")
			options_parse = optionsCheck(options_parse, '/rsem/genes', run_id, "tdf")
			options_parse = optionsCheck(options_parse, '/rsem/isoforms', run_id, "tdf")
			options_parse = optionsCheck(options_parse, '/ucsc_rsem/', run_id, "bigWig")
		}else if (type == 'chip' || type == 'atac'){
			options_parse = optionsCheck(options_parse, '/seqmapping/'+type+'/', run_id, "bam")
			if (type == 'chip' || type == 'atac') {
				options_parse = optionsCheck(options_parse, '/macs/', run_id, "peaks-bed")
			}
			options_parse = optionsCheck(options_parse, '/ucsc_'+type+'/', run_id, "bigWig")
		}else if (type != "seqmapping"){
			options_parse = optionsCheck(options_parse, '/'+type+'/', run_id, "bam")
			options_parse = optionsCheck(options_parse, '/ucsc_'+type+'/', run_id, "bigWig")
		}
	}
	return options_parse
}

function optionsCheck(options_parse, title, run_id, file){
	if (options_parse[title+ " " + file] == undefined) {
		options_parse[title+ " " + file] = []
		options_parse[title+ " " + file].push(run_id)
	}else{
		options_parse[title+ " " + file].push(run_id)
	}
	return options_parse
}

function submissionSelection(){
	var select = document.getElementById('addSampleFiles')
	var options = select.selectedOptions
	var ordertable = $('#jsontable_encode_file_order').dataTable();
	ordertable.fnClearTable();
	var options_html = "<option value=\"1\">1</option>";
	for (var x = 0; x < options.length; x++) {
		options_html += "<option value=\""+(x+2)+"\">"+(x+2)+"</option>"
	}
	ordertable.fnAddData([
			1,
			"Initial Fastq",
			"/input/",
			"fastq",
			"<textarea type=\"text\" class=\"form-control\" rows=\"1\" style=\"width:100%\"></textarea>",
			"<textarea type=\"text\" class=\"form-control\" rows=\"1\" style=\"width:100%\"></textarea>"
		])
	for (var x = 0; x < options.length; x++) {
		ordertable.fnAddData([
			x+2,
			"<select id=\""+(x+2)+"_orderselect\">"+options_html+"</select>",
			options[x].value.split(" ")[0],
			options[x].value.split(" ")[1],
			"<textarea type=\"text\" class=\"form-control\" rows=\"1\" style=\"width:100%\"></textarea>",
			"<textarea type=\"text\" class=\"form-control\" rows=\"1\" style=\"width:100%\"></textarea>"
		])
	}
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
	if (table == "sample" || table == "SampleSelection") {
		loadSamples();
	}else if (table == "donor" || table == "Donors") {
		loadDonors();
	}else if (table == "experiment" || table == "Experiments") {
		loadExperiments();
	}else if (table == "treatment" || table == "Treatments") {
		loadTreatments();
	}else if (table == "biosample" || table == "Biosamples") {
		loadBiosamples();
	}else if (table == "library" || table == "Libraries") {
		loadLibraries();
	}else if (table == "antibody" || table == "Antibodies") {
		loadAntibodies();
	}else if (table == "replicate" || table == "Replicates") {
		loadReplicates();
	}
}

function editEncodeBox(uid, id, type, table, element, parent_table, parent_table_id, parent_child, encode_table){
	singlecheck_table = encode_table;
	editBox(uid, id, type, table, element, parent_table, parent_table_id, parent_child);
}

function linkBiosample() {
	$('#linkBiosampleModal').modal({
		show: true
	});
	addModalType = 'biosample'
}

function linkExperiment() {
	$('#linkExperimentModal').modal({
		show: true
	});
	addModalType = 'experiment'
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

function getSampleFileRuns(){
	var selected = document.getElementById('addFileSelectSample')
	var samples = []
	for (var i = 0; i < selected.length; i++) {
		if (selected.options[i].selected){
			samples.push(selected.options[i].value);
		}
	}
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/encode_tables.php",
		data: { p: "getLikeSampleRuns", samples:samples.toString() },
		async: false,
		success : function(s)
		{
			console.log(s)
		}
	});
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

function createLink(type) {
	var selected = document.getElementById('select'+type)
	var samples = []
	for (var i = 0; i < selected.length; i++) {
		if (selected.options[i].selected){
			samples.push(selected.options[i].value);
		}
	}
	var acc = document.getElementById('link'+type).value
	if (acc == undefined || acc == '') {
		acc == 'none';
	}
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/encode_tables.php",
		data: { p: "linkSamples", type:type, samples:samples.toString(), acc:acc },
		async: false,
		success : function(s)
		{
			
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

function resubmitEncode(string_ids){
	flushBasketInfo();
	sendBasketInfoBulk(string_ids);
	toSubmitEncode();
}

$( function(){
	if (phpGrab.theSegment == "encode_submissions") {
		loadInEncodeSubmissions();
	}else{
		loadInEncodeTables();
		loadPreviousFiles();
	}
});