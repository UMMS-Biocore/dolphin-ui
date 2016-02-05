/*
 * Author: Nicholas Merowsky
 * Date: 26 Nov 2014
 * Ascription:
 **/

$(function() {
	if (typeof(initialSubmission) != undefined && window.location.href.split("/").indexOf('process') > -1) {
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
				JSON_OBJECT['barcodes'] = [barcodes];
				JSON_OBJECT['adapters'] = initial_split[initial_split.length - 3].split(" ")[1].replace(/\:/g, "__cr____cn__");
			}
			JSON_OBJECT['quality'] = 'none';
			JSON_OBJECT['trim'] = 'none';
			JSON_OBJECT['split'] = 'none';
			JSON_OBJECT['commonind'] = 'none';
			JSON_OBJECT['submission'] = '0';
			
			var names_list = [];
			if (initial_split[2] == 'yes') {
				var names = initial_split[initial_split.length - 3].split(":");
				for(var y = 0; y < names.length; y++){
					if (names_list.indexOf(names[y].split(" ")[0]) == -1) {
						names_list.push(names[y].split(" ")[0]);
					}
				}
			}else{
				var names = initial_split[7].split(":");
				for(var y = 0; y < names.length; y++){
					if (names_list.indexOf(names[y].split(" ")[0]) < 0) {
						names_list.push(names[y].split(" ")[0]);
					}
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
			console.log(initial_split);
			for(var y = 5; y < initial_split.length; y++ ){
				if (y == 5){
					sample_lane = "'" + initial_split[y] + "'";
				}else{
					sample_lane = sample_lane + ",'" + initial_split[y] + "'";
				}
			}
			group = initial_split[initial_split.length - 2];
			perms = initial_split[initial_split.length - 1];
		}
		
		if (JSON_OBJECT != undefined && outdir != undefined && runname != undefined && rundesc != undefined) {
			//	Check to see if runparams has already launched
			var run_ids = [];
			var initial_run_ids = [];
			var names_to_ids = [];
			console.log(names_list);
			console.log(sample_lane);
			console.log(experiment_series);
			$.ajax({
				type: 	'GET',
				url: 	BASE_PATH+'/public/ajax/ngsquerydb.php',
				data:  	{ p: 'getSamplesFromName', names: names_list.toString(), lane: sample_lane, experiment: experiment_series },
				async:	false,
				success: function(s)
				{
					for(var x = 0; x < s.length; x++){
						names_to_ids.push(s[x].id);
					}
				}
			});
			console.log(names_to_ids.toString())
			$.ajax({
				type: 	'GET',
				url: 	BASE_PATH+'/public/ajax/initialmappingdb.php',
				data:  	{ p: 'checkRunList', sample_ids: names_to_ids.toString()},
				async:	false,
				success: function(s)
				{
					for(var x = 0; x < s.length; x++){
						run_ids.push(s[x].run_id);
					}
				}
			});
			console.log(run_ids);
			if (run_ids.length > 0) {
				$.ajax({
					type: 	'GET',
					url: 	BASE_PATH+'/public/ajax/initialmappingdb.php',
					data:  	{ p: 'checkRunParams', run_ids: run_ids.toString()},
					async:	false,
					success: function(s)
					{
						for(var x = 0; x < s.length; x++){
							initial_run_ids.push(s[x].id);
						}
					}
				});
				console.log(initial_run_ids);
				if (initial_run_ids.length > 0){
					for(var x = 0; x < initial_run_ids.length; x++){
						var added_samples = [];
						var samples_returned = [];
						$.ajax({
							type: 	'GET',
							url: 	BASE_PATH+'/public/ajax/initialmappingdb.php',
							data:  	{ p: 'checkRunToSamples', run_id: initial_run_ids[x]},
							async:	false,
							success: function(s)
							{
								for(var z = 0; z < s.length; z++){
									samples_returned.push(s[z].sample_id);
								}
								for(var z = 0; z < names_to_ids.length; z++){
									if (samples_returned.indexOf(names_to_ids[z]) < 0) {
										added_samples.push(names_to_ids[z]);
									}
								}
							}
						});
						console.log(added_samples);
						if (added_samples.length > 0) {
							var submitted = postInsertRunlist('insertRunlist', added_samples, initial_run_ids[x]);
							console.log(submitted);
							$.ajax({
								type: 	'GET',
								url: 	BASE_PATH+'/public/ajax/initialmappingdb.php',
								data:  	{ p: 'removeRunlistSamples', run_id: initial_run_ids[x], sample_ids: names_to_ids.toString()},
								async:	false,
								success: function(s)
								{
								}
							});
						}
							var runparamsInsert = postInsertRunparams(JSON_OBJECT, outdir, runname, rundesc, perms, group);
							console.log(runparamsInsert);
					}
				}
			}else{
				//insert new values into ngs_runparams
				var runparamsInsert = postInsertRunparams(JSON_OBJECT, outdir, runname, rundesc, perms, group);
				console.log(runparamsInsert);
				console.log(names_to_ids);
				if (window.location.href.split("/").indexOf('fastlane') == -1) {
					$.ajax({
						type: 	'GET',
						url: 	BASE_PATH+'/public/ajax/initialmappingdb.php',
						data:  	{ p: 'removeRunlistSamples', run_id: runparamsInsert[1], sample_ids: names_to_ids.toString()},
						async:	false,
						success: function(s)
						{
						}
					});
				}
				//insert new values into ngs_runlist
				var submitted = postInsertRunlist(runparamsInsert[0], names_to_ids, runparamsInsert[1]);
				console.log(submitted);
			}
		}
	}
	
});