/*
 *Author: Nicholas Merowsky
 *Date: 09 Apr 2015
 *Ascription:
 */

//	Variables
var allCurrentSamples = [];
var allCurrentLanes = [];
var allCurrentSeries = [];

function rerunSelected(runID, groupID){
	var sample_ids = [];
	if (runOwnerCheck(runID) != 'Permission Denied') {
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/ngsquerydb.php",
			data: { p: "getRunSamples", runID: runID },
			async: false,
			success : function(s)
			{
				console.log(s);
				for(var i = 0; i < s.length; i++) {
					sample_ids.push(s[i].sample_id);
				}
			}
		});
		window.location.href = BASE_PATH+"/pipeline/rerun/" + runID + "/" + sample_ids + "$";
	}else{
		$('#runModal').modal({
			show: true
		});
	}
}

function reportSelected(runID, groupID){
	var ids = [];
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngsquerydb.php",
		data: { p: "getRunSamples", runID: runID },
		async: false,
		success : function(s)
		{
			for(var i = 0; i < s.length; i++) {
			ids.push(s[i].sample_id);
			}
		}
	});
	$.ajax({ type: "GET",
		url: BASE_PATH +"/ajax/sessionrequests.php",
		data: { p: 'setReportsRunID', reports_id: runID, reports_selection: ids.toString() },
		async: false,
		success : function(s)
		{
			window.location.href = BASE_PATH+"/pipeline/report";
		}
	});
}

function getSampleIDs(search, gids, uid){
	var ids= [];
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngs_tables.php",
		data: { p: "getSelectedSamples", search: search, gids: gids, uid: uid},
		async: false,
		success : function(s)
		{
			console.log(s);
			for(var i = 0; i < s.length; i++) {
				ids.push(s[i].id);
			}
		}
	});
	return ids;
}

function getSampleNames(samples_string){
    var sample_name_array = [];
    $.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngsquerydb.php",
		data: { p: "getSampleNames", samples: samples_string },
		async: false,
		success : function(s)
		{
			for(var i = 0; i < s.length; i++) {
                sample_name_array.push(s[i].name);
            }
		}
	});
    return sample_name_array
}

function getTrueSampleNames(samples_string) {
    var sample_name_array = [];
    $.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngsquerydb.php",
		data: { p: "getSampleNames", samples: samples_string },
		async: false,
		success : function(s)
		{
			for(var i = 0; i < s.length; i++) {
                sample_name_array.push(s[i].samplename);
            }
		}
	});
    return sample_name_array
}

function grabReload(groupID){
	jsonArray = [];
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngsquerydb.php",
		data: { p: "grabReload", groupID: groupID },
		async: false,
		success : function(s)
		{
            jsonArray.push(s[0].json_parameters);
			jsonArray.push(s[0].outdir);
			jsonArray.push(s[0].run_name);
			jsonArray.push(s[0].run_description);
			jsonArray.push(s[0].group_id);
			jsonArray.push(s[0].perms);
		}
	});
	return jsonArray
}

function getSummaryInfo(runid, sampleids){
	var nameArray = [];
	var dirArray = [];
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngsquerydb.php",
		data: { p: "getReportNames", runid: runid, samp: sampleids.toString() },
		async: false,
		success : function(s)
		{
			for(var x = 0; x < s.length; x++){
				nameArray.push(s[x].file_name);
				dirArray.push(s[x].outdir);
			}
		}
	});
	return [nameArray, dirArray];
}

function getLanesToSamples(lane_id){
	samples_returned = [];
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngsquerydb.php",
		data: { p: "lanesToSamples", lane: lane_id},
		async: false,
		success : function(s)
		{
			for(var x = 0; x < s.length; x++){
				samples_returned.push(s[x].id);
			}
		}
	});
	return samples_returned;
}

function getAllSampleIds(){
	if (allCurrentSamples.length == 0) {
		var samples_returned = [];
		$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngsquerydb.php",
		data: { p: "getAllSampleIds" },
		async: false,
		success : function(s)
		{
			console.log(s);
			for(var x = 0; x < s.length; x++){
				samples_returned.push(s[x].id);
			}
			allCurrentSamples = samples_returned;
			
		}
		});
	}
	return allCurrentSamples;
}

function getAllLaneIds(){
	if (allCurrentLanes.length == 0) {
		lanes_returned = [];
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/ngsquerydb.php",
			data: { p: "getAllLaneIds" },
			async: false,
			success : function(s)
			{
				for(var x = 0; x < s.length; x++){
					lanes_returned.push(s[x].id);
				}
				allCurrentLanes = lanes_returned;
			}
		});
	}
	return allCurrentLanes;
}

function getAllExperimentIds(){
	if (allCurrentSeries.length == 0) {
		experiments_returned = [];
		$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/ngsquerydb.php",
			data: { p: "getAllExperimentIds" },
			async: false,
			success : function(s)
			{
				for(var x = 0; x < s.length; x++){
					experiments_returned.push(s[x].id);
				}
				allCurrentSeries = experiments_returned;
			}
		});
	}
	return allCurrentSeries;
}

function getSeriesIdFromLane(lane){
	series_id_returned = -1;
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngsquerydb.php",
		data: { p: "getSeriesIdFromLane", lane: lane },
		async: false,
		success : function(s)
		{
			series_id_returned = s[0].series_id;
		}
	});
	return series_id_returned;
}

function getLaneIdFromSample(sample){
	lane_id_returned = -1;
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngsquerydb.php",
		data: { p: "getLaneIdFromSample", sample: sample },
		async: false,
		success : function(s)
		{
			if (s[0] != undefined) {
				lane_id_returned = s[0].id;
			}
		}
	});
	return lane_id_returned;
}

function getExperimentIdFromSample(sample){
    experiment_id_returned = -1;
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngsquerydb.php",
		data: { p: "getExperimentIdFromSample", sample: sample },
		async: false,
		success : function(s)
		{
			if (s[0] != undefined) {
				experiment_id_returned = s[0].id;
			}
		}
	});
	return experiment_id_returned;
}

function getSamplesFromExperimentSeries(experiment){
	sample_ids_returned = [];
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngsquerydb.php",
		data: { p: "getSamplesfromExperimentSeries", experiment: experiment },
		async: false,
		success : function(s)
		{
			for(var x = 0; x < s.length; x++){
                sample_ids_returned.push(s[x].id);
            }
		}
	});
	return sample_ids_returned;
}

function getFastQCBool(id){
    var bool = false;
    $.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngsquerydb.php",
		data: { p: "getFastQCBool", id: id },
		async: false,
		success : function(s)
		{
			var json = s[0].json_parameters;
            var jsonObj = JSON.parse(json);
            if (jsonObj.fastqc == 'yes') {
                bool = true;
            }else{
                bool = false;
            }
		}
	});
    return bool;
}

function getSingleSample(sampleID){
	var sample_info = [];
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngsquerydb.php",
		data: { p: "getSingleSample", sample: sampleID },
		async: false,
		success : function(s)
		{
			if (s[0] != undefined) {
				sample_info.push(s[0].id);
                sample_info.push(s[0].samplename)
				sample_info.push(s[0].name);
			}
		}
	});
	return sample_info;
}

function findIfMatePaired(runID){
	var checkMP = false;
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngsquerydb.php",
		data: { p: "checkMatePaired", runid: runID },
		async: false,
		success : function(s)
		{
			var jsonGrab = s.map(JSON.stringify);
			newJsonArray = jsonGrab[0].split('\\"');
			if (newJsonArray[7] == 'paired') {
				checkMP = true;
			}
		}
	});
	return checkMP;
}

function runOwnerCheck(run_id){
	var response = '';
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngsquerydb.php",
		data: { p: "runOwnerCheck", run_id: run_id },
		async: false,
		success : function(s)
		{
			response = s;
			console.log(s)
		}
	});
	return response;
}
