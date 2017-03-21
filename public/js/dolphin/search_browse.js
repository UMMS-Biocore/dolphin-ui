$('.toggle').toggles({
  text:{on:'All',off:'Filtered'},
  width: 100,
  height: 20,
  on: true
});

$('.toggle').on('toggle', function(e, active) {
  if (active) {
    $('#imports_filtered_by_selection').hide();
    $('#samples_filtered_by_selection').hide();
    $('#browse_import_data_table').show();
    $('#browse_sample_data_table').show();
  } else {
    $('#imports_filtered_by_selection').show();
    $('#samples_filtered_by_selection').show();
    $('#browse_import_data_table').hide();
    $('#browse_sample_data_table').hide();

  }
});

function fillSampleTable(){
  if($('#table_div_samples').length == 0){
		$.ajax({ type: "GET",
    url: BASE_PATH+"/public/ajax/search_browse.php",
		data: { p: 'getSearchSamples' },
    async: false,
			success : function(s)
			{
        console.log(s);
          $('#browse_sample_data_table').html(s);
          ngsTrackCopy();

			}
		});
  }
}

function createFilteredSample($experiment_or_import, $id){
  $.ajax({ type: "GET",
    url: BASE_PATH+"/public/ajax/browse_edit.php",
    data: { p: 'getFilteredSampleData', experiment_or_import: $experiment_or_import, id: $id },
    async: false,
    success : function(s)
    {
      for(var i = 0; i < s.length; i++ ){
        s[i].options = '<input type="checkbox" class="ngs_checkbox" name="' +
        s[i].id + '" id="sample_checkbox_' + s[i].id + '" onclick="manageChecklists(this.name, \'sample_checkbox\')">';
      }
      console.log("+++-+++-+++-+++-+++-+++-+++-+++-+++-+++-+++-+++-+++-+++-+++-");
      console.log(s);
      var save = $('#table_div_samples_filtered table').detach();
      $('#table_div_samples_filtered').empty().append(save);
      groupsStreamTable = createStreamTable('samples_filtered', s, "", true, [10,20,50,100], 20, true, true);
    }
  });
}


function createFilteredImport($experiment_id){
  $.ajax({ type: "GET",
    url: BASE_PATH+"/public/ajax/browse_edit.php",
    data: { p: 'getFilteredImportData', 'experiment_id': $experiment_id },
    async: false,
    success : function(s)
    {
      for(var i = 0; i < s.length; i++ ){
        s[i].options = '<input type="checkbox" class="ngs_checkbox" name="' +
        s[i].id + '" id="lane_checkbox_' + s[i].id + '" onclick="manageChecklists(this.name, \'lane_checkbox\')">';
      }
      console.log("+++-+++-+++-+++-+++-+++-+++-+++-+++-+++-+++-+++-+++-+++-+++-");
      console.log(s);
      var save = $('#table_div_lanes_filtered table').detach();
      $('#table_div_lanes_filtered').empty().append(save);
      groupsStreamTable = createStreamTable('lanes_filtered', s, "", true, [10,20,50,100], 20, true, true);
    }
  });
}

function clearAllDetails(){
  $('#e_details').html('');
  $('#i_details').html('');
  $('#s_details').html('');
}

function hideFilteredTables(){
      $('#imports_filtered_by_selection').hide();
      $('#samples_filtered_by_selection').hide();
}
hideFilteredTables();

function displayExperimentDetails($experiment_id, $div_id, $called_from_import = false){
  clearAllDetails();
  var $html_to_return = "";
  var $fields = ["summary", "design", "name", "perms_name"];
  var $titles = ["Summary", "Overall Design", "Groups", "Permission"];
        $.ajax({ type: "GET",
            url: BASE_PATH+"/public/ajax/browse_edit.php",
            data: { p: 'getExperimentDetailsSearch', experiment_id: '' + $experiment_id},
            async: false,
            complete : function(s)
            {
              console.log(s);
              var $json_object = jQuery.parseJSON(s.responseText)[0];
              $html_to_return += getDetailsHTML($json_object, 'Experiment Series', 'experiment_name', $fields, $titles);
            }
        });
      $('#' + $div_id).html($html_to_return);
      if(!$called_from_import){
        createFilteredImport($experiment_id);
        createFilteredSample('experiment', $experiment_id);
      }
      $('#imports_filtered_by_selection').show();
      $('#samples_filtered_by_selection > h3').css({'color':'blue'});
      $('#samples_filtered_by_selection').show();
  }

function displayImportDetails($import_id, $div_id, $called_from_sample = false){
  clearAllDetails();
  var $html_to_return = "";
  var $fields = ["experiment_name", "facility", "date_submitted",
    "date_received", "total_samples", "resequenced", "group_name",
    "perms_name", "lane_id"];
  var $titles = ["Series Name", "Sequencing Facility", "Date Submitted",
    "Date Received", "# of Samples", "Resequenced", "Groups", "Permission",
    "Lane ID"];
  $.ajax({ type: "GET",
      url: BASE_PATH+"/public/ajax/browse_edit.php",
      data: { p: 'getImportDetailsSearch', import_id: '' + $import_id},
      async: false,
      complete : function(s)
      {
        console.log(s);
        var $json_object = jQuery.parseJSON(s.responseText)[0];
        // Also show Experiment Details
        displayExperimentDetails($json_object['series_id'], 'e_details', true);
        $html_to_return += getDetailsHTML($json_object, 'Import', 'import_name', $fields, $titles);
      }
  });
  $('#' + $div_id).html($html_to_return);
  if(!$called_from_sample){
    console.log("I'm here.")
    createFilteredSample('import', $import_id);
  }
  $('#imports_filtered_by_selection').hide();
  $('#samples_filtered_by_selection > h3').css({'color':'blue'});
  $('#samples_filtered_by_selection').show();
  }

function displaySampleDetails($sample_id, $div_id){
  clearAllDetails();
  var $html_to_return = "";
  var $fields1 = ["experiment_name", "import_name", "protocol_name", 
    "samplename", "barcode", "title", "source", "organism", "molecule",
    "instrument_model", "avg_insert_size", "read_length", "genotype",
    "library_type", "notes", "group_name", "perms_name", "donor", "time",
    "biological_replica", "technical_replica"];
  var $titles1 = ["Series Name", "Lane Name", "Protocol Name", "Sample Name", 
    "Barcode", "Title", "Source", "Organism", "Molecule", "Instrument Model",
    "Avg. Insert Size", "Read Length", "Genotype", "Library Type", "Notes",
    "Groups", "Permission", "Donor", "Time", "Biological Rep", "Technical Rep"];
  var $fields2 = ["fastq_dir", "file_names", "backup_dir", "file_name", 
    "amazon_bucket"];
  var $titles2 = ["Input File(s) Directory", "Input File(s)", 
    "Processed File(s) Dir", "Processed File(s)", "Amazon Backup"];

  var $fields_lists = [$fields1, $fields2, [], []];
  var $titles_lists = [$titles1, $titles2, [], []];
  $.ajax({ type: "GET",
      url: BASE_PATH+"/public/ajax/browse_edit.php",
      data: { p: 'getSampleDetailsSearch', sample_id: '' + $sample_id},
      async: false,
      complete : function(s)
      {
        console.log(s);
        var $json_object = jQuery.parseJSON(s.responseText)[0];
        // Also show Import Details (which in turn shows Experiment's)
        displayImportDetails($json_object['lane_id'], 'i_details', true);
        $html_to_return += getDetailsHTMLforSample($json_object, 
          ['Data', 'Directory', 'Runs', 'Tables'], 
          ['data_of_sample', 'directory_of_sample', 'runs_of_sample', 
            'tables_of_sample'], 
          $fields_lists, $titles_lists);

        console.log($html_to_return);
      }
  });
  $('#' + $div_id).html($html_to_return);
  $( "#data_of_sample" ).addClass('active');
  
  $('#imports_filtered_by_selection').hide();
  $('#samples_filtered_by_selection').hide();
}



  function getDetailsHTML($json_object, $top_title, $second_title, $fields, $titles){
    var $html_to_return = "<hr><h3>" + $top_title + "</h3><br/>"
    $html_to_return += "<h4>" + $json_object[$second_title] + "</h4>" + "<br/>";
    var $current_val;
    for(var i = 0; i < $fields.length; i++){
      $current_val = $json_object[$fields[i]];
      if($current_val){
        $html_to_return += "<label>" + $titles[i] + "</label>" + ": " + $current_val + "<br/>";
      }
    }
    return $html_to_return;
  }

  function getDetailsHTMLforSample($json_object, $top_title_list, $div_id_list, $fields_lists, $titles_lists){
    var $html_to_return = '<hr><h3>Sample</h3><div class="nav-tabs-custom"><ul id="tabList" class="nav nav-tabs">';
    for(var k = 0; k < $top_title_list.length; k++){
      $html_to_return += '<li class><a href="#' + $div_id_list[k] + '" id="' + $div_id_list[k] + '_select" data-toggle="tab" aria-expanded="true">' + $top_title_list[k] + '</a></li>';
    }
    $html_to_return += '</ul></div>';
    $html_to_return += '<div class="tab-content">';
    for(var i = 0; i < $top_title_list.length; i++){
      $html_to_return += '<div class="box-body tab-pane" id="' + $div_id_list[i] + '"><div style="overflow-y:scroll"><dl class="dl-horizontal">';
      var $current_val;
      for(var j = 0; j < $fields_lists[i].length; j++){
        $current_val = $json_object[$fields_lists[i][j]];
        if($current_val){
          $html_to_return += "<dt>" + $titles_lists[i][j] + "</dt><dd>" + $current_val + "</dd>";
        }
      }
      $html_to_return += '</dl></div></div>';
    }
    $html_to_return += '</div>';
    return $html_to_return;
  }

function ngsTrackCopy(){
  $(function() {
  	"use strict";

  	//The Calender
  	$("#calendar").datepicker();
  	$('input').on('ifChanged', function(event){
  		if (event.target.name.substring(0,6) == "common") {
  			var array = event.target.id.split("_");

  			if (array[1] == 'yes' && deseqList.indexOf(array[0]) == -1 && (array[0] == 'miRNA' || array[0] == 'tRNA')) {
  				var selects = document.getElementsByTagName("select");
  				for(var i = 0; i < selects.length; i++) {
  					if(selects[i].id.indexOf('select_5_') == 0) {
  						var opt = createElement('option', ['id', 'value'], [array[0], array[0]]);
  						opt.innerHTML = array[0];
  						selects[i].appendChild(opt);
  					}
  				}
  				deseqList.push(array[0]);
  			}else if (array[1] == 'no' && deseqList.indexOf(array[0]) > -1 ){
  				deseqList.splice(deseqList.indexOf(array[0]), 1);
  				var selects = document.getElementsByTagName("select");
  				for(var i = 0; i < selects.length; i++) {
  					if(selects[i].id.indexOf('select_5_') == 0) {
  						var children = selects[i].childNodes;
  						for (var y = 0; y < children.length; y++) {
  							if (children[y].id.split("_")[0] == array[0]) {
  								children[y].remove();
  							}
  						}
  					}
  				}
  			}
  		}
  	});

  	/*##### PAGE DETERMINER #####*/

  	var qvar = "";
  	var rvar = "";
  	var segment = "";
  	var theSearch = "";
  	var uid = "";
  	var gids = "";

  	if (phpGrab) {
  		var segment = phpGrab.theSegment;
  		var theSearch = phpGrab.theSearch;
  		uid = phpGrab.uid;
  		gids = phpGrab.gids;
  	}

  	//gids
  	if (gids == '') {
  		gids = -1;
  	}

  	//Details values
  	if (segment == "details") {
  		if (phpGrab.theField == "experiment_series") {
  			qvar = phpGrab.theValue;
  		}
  		else if (phpGrab.theField == "experiments") {
  			rvar = phpGrab.theValue;
  		}
  	}

  	//Browse values
  	else if (segment == "browse") {
  		qvar = phpGrab.theField;//field
  		rvar = unescape(phpGrab.theValue);//value
  	}

  	if (phpGrab.theField == "samples") {
  		reloadBasket();
  	}
  	//	If table generated page
  	if (phpGrab.theSegment == 'generated') {
  		//	Obtain table information (JSON format)
  		$.ajax({ type: "GET",
  			url: BASE_PATH +"/public/ajax/tablegenerator.php?",
  			data: { p: "getGeneratedTable"},
  			async: false,
  			success : function(s)
  			{
  				console.log(s);
  				table_params = s;
  			}
  		});
  		var json_obj;
  		//	If file does exist
  		if (table_params.file != '') {
  			//	Load JSON file
  			json_obj = undefined;
  			//	Obtain JSON data
  			$.ajax({ type: "GET",
  				url: BASE_PATH +"/public/ajax/tablegenerator.php",
  				data: { p: "getJsonFromFile", file: table_params.file},
  				async: false,
  				success : function(s)
  				{
  					json_obj = s;
  				}
  			});
  			//	If no information can be obtained
  			if (json_obj == undefined || json_obj == []) {
  				//	Generate JSON data manually
  				$.ajax({ type: "GET",
  					url: BASE_PATH +"/public/api/getsamplevals.php?" + table_params.parameters,
  					async: false,
  					success : function(s)
  					{
  						json_obj = JSON.parse(s);
  						generateStreamTable('generated', json_obj, phpGrab.theSegment, qvar, rvar, segment, theSearch, uid, gids);
  					}
  				});
  				//	Update table with new file information
  				//	Create both JSON and JSON2 files
  				$.ajax({ type: "GET",
  					url: BASE_PATH +"/public/ajax/tablegenerator.php",
  					data: { p: "updateTableFile", url: BASE_PATH +"/public/api/getsamplevals.php?" + table_params.parameters, id: table_params.id},
  					async: false,
  					success : function(s)
  					{
  						console.log(s);
  					}
  				});
  			}else{
  				//	Generate table with obtained JSON information from file
  				generateStreamTable('generated', json_obj, phpGrab.theSegment, qvar, rvar, segment, theSearch, uid, gids);
  			}
  		//	If file doesn't exist
  		}else{
  			//	Generate JSON data manually
  			$.ajax({ type: "GET",
  				url: BASE_PATH +"/public/api/getsamplevals.php?" + table_params.parameters,
  				async: false,
  				success : function(s)
  				{
  					json_obj = JSON.parse(s);
  					generateStreamTable('generated', json_obj, phpGrab.theSegment, qvar, rvar, segment, theSearch, uid, gids);
  				}
  			});
  			//	Update table with new file information
  				//	Create both JSON and JSON2 files
  			$.ajax({ type: "GET",
  				url: BASE_PATH +"/public/ajax/tablegenerator.php",
  				data: { p: "updateTableFile", url: BASE_PATH +"/public/api/getsamplevals.php?" + table_params.parameters, id: table_params.id},
  				async: false,
  				success : function(s)
  				{
  					console.log(s);
  				}
  			});
  		}

  		//	Plot variables contained within highchart_funcs.js
  		//	Dictionaries contained within report_funcs.js
  		var table_array = json_obj;
  		for( var j = 0; j < table_array.length; j++){
  			for( var i = 0; i < summary_dictionary.length; i++){
  				if (table_array[j][summary_dictionary[i]] != undefined) {
  					if (table_data[table_array[j]['Sample']] == undefined) {
  						table_data[table_array[j]['Sample']] = {};
  						if (summary_dictionary[i] != 'Sample') {
  							table_data[table_array[j]['Sample']][html_summary_dictionary[i]] = table_array[j][summary_dictionary[i]];
  						}
  					}else{
  						if (summary_dictionary[i] != 'Sample') {
  							table_data[table_array[j]['Sample']][html_summary_dictionary[i]] = table_array[j][summary_dictionary[i]];
  						}
  					}
  				}
  			}
  		}

  		//	Function within report_fincs.js
  		console.log("@@@@@@@  Highchart Logging Start @@@@@@@")
  		console.log(table_params);
  		console.log(table_array)

  		if (Object.keys(table_data).length > 0) {
  			summaryPlotSetup(table_data);
  			console.log(table_data);
  			createSummaryHighchart();
  			showHighchart('plots');
  		}

  		if (/RSeQC/.test(table_params.parameters) && /counts.tsv/.test(table_params.parameters)) {
  			rseqcPlotGen('rseqc', json_obj, 'generated_table')
  		}

  		//	Log data path
  		console.log(BASE_PATH +"/public/api/getsamplevals.php?" + table_params.parameters);

  	}else if (phpGrab.theSegment != 'report' && phpGrab.theSegment != 'table_viewer' && phpGrab.theSegment != "encode_submissions") {
  		var experiment_series_data = [];
  		var lane_data = [];
  		var sample_data = [];

  		/*##### SAMPLES TABLE #####*/
  		//var samplesTable = $('#jsontable_samples').dataTable();

  		var samplesType = "";
  		if (segment == 'selected') {
  			samplesType = "getSelectedSamples";
  			if (window.location.href.indexOf("/rerun/") == -1) {
  				theSearch = basket_info;
  			}
  			if (window.location.href.split("/").indexOf('tablecreator') > -1) {
  				qvar = "getTableSamples";
  			}
  		}else{
  			samplesType = "getSamples";
  		}
  		console.log(basket_info);
  		$.ajax({ type: "GET",
  			url: BASE_PATH+"/public/ajax/ngs_tables.php",
  			data: { p: samplesType, q: qvar, r: rvar, seg: segment, search: theSearch, uid: uid, gids: gids },
  			async: false,
  			success : function(s)
  			{
  				console.log(s);
  				sample_data = s;
  				var changeHTML = '';
  				var hrefSplit = window.location.href.split("/");
  				var typeLocSelected = $.inArray('selected', hrefSplit);
  				var typeLocRerun = $.inArray('rerun', hrefSplit);
  				var queryType = 'getSamples';
  				if (typeLocSelected > 0 || typeLocRerun > 0) {
  					theSearch = '';
  				}
  				var type = 'samples';
  				if (samplesType == 'getSamples' && segment == 'table_create') {
  					var samples_with_runs = [];
  					var objects_with_runs = [];
  					$.ajax({ type: "GET",
  						url: BASE_PATH+"/public/ajax/tablegenerator.php",
  						data: { p: "samplesWithRuns" },
  						async: false,
  						success : function(k)
  						{
  							for(var x = 0; x < k.length; x++){
  								samples_with_runs.push(k[x].sample_id);
  							}
  						}
  					});
  					console.log(samples_with_runs);
  					for(var z = 0; z < s.length; z++){
  						if (samples_with_runs.indexOf(s[z].id) > -1) {
  							objects_with_runs.push(s[z]);
  						}
  					}
  					console.log(objects_with_runs);
  					s = objects_with_runs;
  					queryType = 'table_create';
  				}
  				if (segment == 'selected') {
  					var runparams = $('#jsontable_samples_selected').dataTable();
  					runparams.fnClearTable();
  					for(var i = 0; i < s.length; i++){
  						var selection_bool = false;
  						if (window.location.href.indexOf("/rerun/") > -1 || window.location.href.indexOf("/selected/") > -1) {
  							selection_bool = true;
  						}
  						if (selection_bool) {
  							runparams.fnAddData([
  								s[i].id,
  								s[i].samplename,
  								s[i].organism,
  								s[i].molecule,
  								s[i].backup,
  								'<button id="sample_removal_'+s[i].id+'" class="btn btn-danger btn-xs pull-right" onclick="removeSampleSelection(\''+s[i].id+'\', this)"><i class=\"fa fa-times\"></i></button>'
  							]);
  							selected_samples.push(s[i].id);
  						}
  					}
  					samplesType = "getSamples";
  					$.ajax({ type: "GET",
  						url: BASE_PATH+"/public/ajax/ngs_tables.php",
  						data: { p: samplesType, q: qvar, r: rvar, seg: segment, search: theSearch, uid: uid, gids: gids },
  						async: false,
  						success : function(k)
  						{
  							generateStreamTable(type, k, queryType, qvar, rvar, segment, theSearch, uid, gids);
  							manageChecklistsBulk(selected_samples)
  						}
  					});
  				}else if (segment == "encode") {
  					generateStreamTable(type, s, queryType, qvar, rvar, segment, theSearch, uid, gids);
  					var basket = basket_info.split(",");
  					basket = basket.filter(function(e){return e});
  					manageChecklistsBulk(basket);
  				}else{
  					generateStreamTable(type, s, queryType, qvar, rvar, segment, theSearch, uid, gids);
  				}
  			}
  		});

  		// /*##### LANES TABLE #####*/
      //
  		// $.ajax({ type: "GET",
  		// 	url: BASE_PATH+"/public/ajax/ngs_tables.php",
  		// 	data: { p: "getLanes", q: qvar, r: rvar, seg: segment, search: theSearch, uid: uid, gids: gids },
  		// 	async: false,
  		// 	success : function(s)
  		// 	{
  		// 		lane_data = s;
  		// 		var type = 'lanes';
  		// 		var queryType = "getLanes";
  		// 		if (window.location.href.split("/").indexOf('search') > -1) {
  		// 			generateStreamTable(type, s, queryType, qvar, rvar, segment, theSearch, uid, gids);
  		// 		}
  		// 	}
  		// });
      //
  		// /*##### SERIES TABLE #####*/
  		// //var experiment_seriesTable = $('#jsontable_experiment_series').dataTable({responsive: true});
      //
  		// $.ajax({ type: "GET",
  		// 	url: BASE_PATH+"/public/ajax/ngs_tables.php",
  		// 	data: { p: "getExperimentSeries", q: qvar, r: rvar, seg: segment, search: theSearch, uid: uid, gids: gids },
  		// 	async: false,
  		// 	success : function(s)
  		// 	{
  		// 		experiment_series_data = s;
  		// 		var type = 'experiments';
  		// 		var queryType = "getExperimentSeries";
  		// 		if (window.location.href.split("/").indexOf('search') > -1) {
  		// 			generateStreamTable(type, s, queryType, qvar, rvar, segment, theSearch, uid, gids);
  		// 		}
  		// 	}
  		// });

  		if (segment == 'index' || segment == 'browse' || segment == 'details') {
  			console.log(experiment_series_data);
  			console.log(lane_data);
  			console.log(sample_data);
  			generateIDDictionary(experiment_series_data, lane_data, sample_data);
  			reloadBasket();
  		}
  	}
  	//Rerun Check
  	if (window.location.href.split("/").indexOf('selected') > -1 || window.location.href.split("/").indexOf('rerun') > -1) {
  		rerunLoad();
  	}
  });

}
