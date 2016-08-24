function responseCheck(data) {
	for(var x = 0; x < Object.keys(data).length; x++){
		if (data[Object.keys(data)[x]] == null) {
			data[Object.keys(data)[x]] = '<br>';
		}
	}
	return data;
}

function insertIntoDatabase(table, linking_table, value){
	
}

function loadInEncodeTables(){
	basket_info = getBasketInfo();
	if (basket_info != '') {
		console.log(basket_info);
		//Donors
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/encode_tables.php",
			data: { p: "getDonors", samples:basket_info },
			async: false,
			success : function(s)
			{
				console.log(s);
				var donortable = $('#jsontable_donors').dataTable();
				donortable.fnClearTable();
				for(var x = 0; x < s.length; x++){
					s[x] = responseCheck(s[x]);
					donortable.fnAddData([
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'donor', 'ngs_donor', this, '', '', '')\">"+s[x].donor+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'life_stage', 'ngs_donor', this, '', '', '')\">"+s[x].life_stage+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'age', 'ngs_donor', this, '', '', '')\">"+s[x].age+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'sex', 'ngs_donor', this, '', '', '')\">"+s[x].sex+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'donor_acc', 'ngs_donor', this, '', '', '')\">"+s[x].donor_acc+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'donor_uuid', 'ngs_donor', this, '', '', '')\">"+s[x].donor_uuid+"</p>"
					]);
				}
			}
		});
		//Experiments
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/encode_tables.php",
			data: { p: "getExperiments", samples:basket_info },
			async: false,
			success : function(s)
			{
				console.log(s);
				var experimenttable = $('#jsontable_experiments').dataTable();
				experimenttable.fnClearTable();
				for(var x = 0; x < s.length; x++){
					s[x] = responseCheck(s[x]);
					experimenttable.fnAddData([
						s[x].samplename,
						"<p onclick=\"editBox("+1+", '"+s[x].lab_id+"', 'lab', 'ngs_lab', this, 'experiment_series', '"+s[x].experiment_series_id+"', 'lab_id')\">"+s[x].lab+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].experiment_series_id+"', 'grant', 'ngs_experiment_series', this, '', '', '')\">"+s[x].grant+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].organism_id+"', 'organism', 'ngs_organism', this, 'ngs_samples', '"+s[x].sample_id+"', 'organism_id')\">"+s[x].organism+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].library_strategy_id+"', 'library_strategy', 'ngs_library_strategy', this, 'ngs_protocols', '"+s[x].protocol_id+"', 'library_strategy_id')\">"+s[x].library_strategy+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].sample_id+"', 'description', 'ngs_samples', this, '', '', '')\">"+s[x].description+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].sample_id+"', 'experiment_acc', 'ngs_samples', this, '', '', '')\">"+s[x].experiment_acc+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].sample_id+"', 'experiment_uuid', 'ngs_samples', this, '', '', '')\">"+s[x].experiment_uuid+"</p>"
					]);
				}
			}
		});
		//Treatments
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/encode_tables.php",
			data: { p: "getTreatments", samples:basket_info },
			async: false,
			success : function(s)
			{
				console.log(s);
				var treatmenttable = $('#jsontable_treatments').dataTable();
				treatmenttable.fnClearTable();
				for(var x = 0; x < s.length; x++){
					s[x] = responseCheck(s[x]);
					treatmenttable.fnAddData([
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'treatment_term_name', 'ngs_treatment', this, '', '', '')\">"+s[x].treatment_term_name+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'treatment_term_id', 'ngs_treatment', this, '', '', '')\">"+s[x].treatment_term_id+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'treatment_type', 'ngs_treatment', this, '', '', '')\">"+s[x].treatment_type+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'concentration', 'ngs_treatment', this, '', '', '')\">"+s[x].concentration+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'concentration_units', 'ngs_treatment', this, '', '', '')\">"+s[x].concentration_units+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'duration', 'ngs_treatment', this, '', '', '')\">"+s[x].duration+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'duration_units', 'ngs_treatment', this, '', '', '')\">"+s[x].duration_units+"</p>"
					]);
				}
			}
		});
		//Biosamples
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/encode_tables.php",
			data: { p: "getBiosamples", samples:basket_info },
			async: false,
			success : function(s)
			{
				console.log(s);
				var biosampletable = $('#jsontable_biosamples').dataTable();
				biosampletable.fnClearTable();
				for(var x = 0; x < s.length; x++){
					s[x] = responseCheck(s[x]);
					biosampletable.fnAddData([
						s[x].samplename,
						"<p onclick=\"editBox("+1+", '"+s[x].donor_id+"', 'donor', 'ngs_donor', this, '', '', '')\">"+s[x].donor+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].biosample_id+"', 'biosample_term_name', 'ngs_biosample_term', this, 'ngs_samples', '"+s[x].sample_id+"', 'biosample_id')\">"+s[x].biosample_term_name+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].biosample_id+"', 'biosample_term_id', 'ngs_biosample_term', this, 'ngs_samples', '"+s[x].sample_id+"', 'biosample_id')\">"+s[x].biosample_term_id+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].biosample_id+"', 'biosample_type', 'ngs_biosample_term', this, 'ngs_samples', '"+s[x].sample_id+"', 'biosample_id')\">"+s[x].biosample_type+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].lane_id+"', 'date_submitted', 'ngs_lanes', this, '', '', '')\">"+s[x].date_submitted+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].lane_id+"', 'date_received', 'ngs_lanes', this, '', '', '')\">"+s[x].date_received+"</p>"
					]);
				}
			}
		});
		//Libraries
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/encode_tables.php",
			data: { p: "getLibraries", samples:basket_info },
			async: false,
			success : function(s)
			{
				console.log(s);
				var librarytable = $('#jsontable_libraries').dataTable();
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
						"<p onclick=\"editBox("+1+", '"+s[x].imid+"', 'instrument_model', 'ngs_instrument_model', this, '', '', '')\">"+s[x].instrument_model+"</p>"
					]);
				}
			}
		});
		//Antibodies
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/encode_tables.php",
			data: { p: "getAntibodies", samples:basket_info },
			async: false,
			success : function(s)
			{
				console.log(s);
				var antybodytable = $('#jsontable_antibodies').dataTable();
				antybodytable.fnClearTable();
				for(var x = 0; x < s.length; x++){
					s[x] = responseCheck(s[x]);
					antybodytable.fnAddData([
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'target', 'ngs_antibody_target', this, '', '', '')\">"+s[x].target+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'source', 'ngs_antibody_target', this, '', '', '')\">"+s[x].source+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'product_id', 'ngs_antibody_target', this, '', '', '')\">"+s[x].product_id+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'lot_id', 'ngs_antibody_target', this, '', '', '')\">"+s[x].lot_id+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'host_organism', 'ngs_antibody_target', this, '', '', '')\">"+s[x].host_organism+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'clonality', 'ngs_antibody_target', this, '', '', '')\">"+s[x].clonality+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'isotype', 'ngs_antibody_target', this, '', '', '')\">"+s[x].isotype+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'purifications', 'ngs_antibody_target', this, '', '', '')\">"+s[x].purifications+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'url', 'ngs_antibody_target', this, '', '', '')\">"+s[x].url+"</p>"
					]);
				}
			}
		});
		//Replicates
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/encode_tables.php",
			data: { p: "getReplicates", samples:basket_info },
			async: false,
			success : function(s)
			{
				console.log(s);
				var replicatetable = $('#jsontable_replicates').dataTable();
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
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'biological_replica', 'ngs_samples', this, '', '', '')\">"+s[x].biological_replica+"</p>",
						"<p onclick=\"editBox("+1+", '"+s[x].id+"', 'technical_replica', 'ngs_samples', this, '', '', '')\">"+s[x].technical_replica+"</p>"
					]);
				}
			}
		});
	}
}

$( function(){
	loadInEncodeTables();
});