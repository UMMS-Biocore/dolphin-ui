/*
 * Author: Nicholas Merowsky
 * Date: 26 Nov 2014
 * Ascription:
 **/

$(function() {
	if (typeof(initialSubmission) != "undefined" && window.location.href.split("/").indexOf('process') > -1) {
		console.log(initialSubmission);
		var initial_split = initialSubmission.split(",");
		var outdir;
		var runname;
		var rundesc;
		var sample_lane;
		var experiment_series;
		var group;
		var perms;
		var JSON_OBJECT = {};

		//	Create the Json object
		if (window.location.href.split("/").indexOf('fastlane') > -1) {
			runname = 'Fastlane Initial Run';
			rundesc = 'Fastlane Initial Run within import: ' + initial_split[5];
			outdir = initial_split[8] + '/initial_run';
			
			JSON_OBJECT['genomebuild'] = 'human,hg19';
			if (initial_split[3] == 'yes') {
				JSON_OBJECT['spaired'] = 'paired';
			}else{
				JSON_OBJECT['spaired'] = 'no';
			}
			JSON_OBJECT['resume'] = 'no';
			JSON_OBJECT['fastqc'] = 'no';
			if (initial_split[2] == 'no') {
				JSON_OBJECT['barcodes'] = 'none';
				JSON_OBJECT['adapters'] = 'none';
			}else{
				var barcodes = {};
				barcodes['distance'] = barcode_array.split(',')[0];
				barcodes['format'] = barcode_array.split(",")[1];
				barcodes['barcodes'] = initial_split[initial_split.length - 3].replace(/[\s\t\,\:]+/g, ",");
				JSON_OBJECT['barcodes'] = [barcodes];
			}
			JSON_OBJECT['adapters'] = 'none';
			JSON_OBJECT['quality'] = 'none';
			JSON_OBJECT['trim'] = 'none';
			JSON_OBJECT['split'] = 'none';
			JSON_OBJECT['commonind'] = 'none';
			JSON_OBJECT['submission'] = '0';
			
			var names_list = [];var names = initial_split[7].split(":");
			for(var y = 0; y < names.length; y++){
				if (names_list.indexOf(names[y].split(" ")[0]) < 0) {
					names_list.push(names[y].split(" ")[0]);
				}
			}
			
			sample_lane = "'" + initial_split[5] + "'";
			experiment_series = initial_split[4];
			group = initial_split[initial_split.length - 2];
			perms = initial_split[initial_split.length - 1];
			
		}else{
			runname = 'Import Initial Run';
			rundesc = 'Import Initial Run within series: ' + initial_split[0];
			outdir = initial_split[1] + '/initial_run';
			experiment_series = initial_split[0];
			
			JSON_OBJECT['genomebuild'] = 'human,hg19';
			if (initial_split[4] == 'paired') {
				JSON_OBJECT['spaired'] = 'paired';
			}else{
				JSON_OBJECT['spaired'] = 'no';
			}
			JSON_OBJECT['resume'] = 'no';
			JSON_OBJECT['fastqc'] = 'no';
			if (initial_split[3] == 'lane') {
				var barcodes = {};
				barcodes['distance'] = '1';
				barcodes['format'] = '5 end read 1';
				JSON_OBJECT['barcodes'] = [barcodes];
			}else{
				JSON_OBJECT['barcodes'] = 'none';
			}
			JSON_OBJECT['adapters'] = 'none';
			JSON_OBJECT['quality'] = 'none';
			JSON_OBJECT['trim'] = 'none';
			JSON_OBJECT['split'] = 'none';
			JSON_OBJECT['commonind'] = 'none';
			JSON_OBJECT['submission'] = '0';
			
			var names_list = initialNameList.split(",");
			for(var y = 5; y < initial_split.length - 2; y++ ){
				if (y == 5){
					sample_lane = "'" + initial_split[y] + "'";
				}else{
					sample_lane = sample_lane + ",'" + initial_split[y] + "'";
				}
			}
			group = initial_split[initial_split.length - 2];
			perms = initial_split[initial_split.length - 1];
		}
		console.log(initial_split);
		console.log(names_list);
		
		//	If json object, outdir, runname, and rundesc are not undefined
		if (JSON_OBJECT != undefined && outdir != undefined && runname != undefined && rundesc != undefined) {
			//	Check to see if runparams has already launched
			var returned_info = [];
			//	Gather up information about the run
			console.log(names_list.toString());
			console.log(sample_lane);
			console.log(experiment_series);
			$.ajax({
				type: 	'GET',
				url: 	BASE_PATH+'/public/ajax/initialmappingdb.php',
				data:  	{ p: 'checkRunList', names: names_list.toString(), lane: sample_lane, experiment: experiment_series },
				async:	false,
				success: function(s)
				{
					//	returned array of arrays
					//	previous run:
					// 	[
					//		[ [ ], [ ], added samples per initial run ]
					//		[ outdirs ]
					//		[ run ids ]
					//		[ sample ids]
					//	]
					//	else if new run:
					//	[ [], [], [], [sample ids]]
					
					returned_info = s;
					console.log(s);
					if (returned_info[1][0] != undefined) {
						for(var k = 0; k < returned_info[1].length; k++){
							//	get variables for previoius run
							var added_samples = returned_info[0][k];
							console.log(added_samples);
							var init_run_id = returned_info[2][k];
							console.log(init_run_id);
							
							//	insert into runlist
							var submitted = postInsertRunlist('insertRunlist', added_samples, init_run_id);
							console.log(submitted);
							
							//	Remove samples not in runlist
							removeRunlist(init_run_id, returned_info[3].toString());
							
							//	Insert into run params
							var runparamsInsert = postInsertRunparams(JSON_OBJECT, returned_info[1][k], runname, rundesc, perms, group, '');
							console.log(runparamsInsert)
						}
					}else{
						//insert new values into ngs_runparams
						var runparamsInsert = postInsertRunparams(JSON_OBJECT, outdir, runname, rundesc, perms, group, '');
						console.log(runparamsInsert);
						console.log(returned_info[3].toString());
						if (window.location.href.split("/").indexOf('fastlane') == -1) {
							removeRunlist(runparamsInsert[1], returned_info[3].toString());
						}
						//insert new values into ngs_runlist
						var submitted = postInsertRunlist(runparamsInsert[0], returned_info[3], runparamsInsert[1]);
						console.log(submitted);
					}
				}
			});
		}
	}
});

function removeRunlist(run_id, sample_ids) {
	$.ajax({
		type: 	'GET',
		url: 	BASE_PATH+'/public/ajax/initialmappingdb.php',
		data:  	{ p: 'removeRunlistSamples', run_id: run_id, sample_ids: sample_ids},
		async:	false,
		success: function(s)
		{
		}
	});
}