/*
 * Author: Nicholas Merowsky
 * Date: 26 Nov 2014
 * Ascription:
 **/

function organismSelect(org){
	if (org == 'human') {
		org = org + ',hg19';
	}else if (org == 'hamster') {
		org = org + ',cho-k1';
	}else if (org == 'rat') {
		org = org + ',rn5';
	}else if (org == 'zebrafish') {
		org = org + ',danRer7';
	}else if (org == 'mouse') {
		org = org + ',mm10';
	}else if (org == 'mousetest') {
		org = org + ',mm10';
	}else if (org == 's_cerevisiae') {
		org = org + ',sacCer3';
	}else if (org == 'c_elegans') {
		org = org + ',ce10';
	}else if (org == 'cow') {
		org = org + ',bosTau7';
	}else if (org == 'd_melanogaster') {
		org = org + ',dm3';
	}
	return org;
}

$(function() {
	if (typeof(initialSubmission) != undefined && window.location.href.split("/")[window.location.href.split("/").length -1] == 'process') {
		var initial_split = initialSubmission.split(",");
		var json;
		var outdir;
		var runname;
		var rundesc;
		var sample_lane;
		var experiment_series;
		
		//	Create the Json object
		if (window.location.href.split("/")[window.location.href.split("/").length - 2] == 'fastlane') {
			runname = 'Fastlane Initial Run';
			rundesc = 'Fastlane Initial Run within import: ' + initial_split[5];
			outdir = initial_split[8] + '/initial_run';
			
			json = '{"genomebuild":"' + initial_split[0] + ',' + initial_split[1] + '"';
			if (initial_split[3] == 'yes') {
				json = json + ',"spaired":"paired"';
			}else{
				json = json + ',"spaired":"no"';
			}
			json = json + ',"resume":"no","fastqc":"no"';
			if (initial_split[2] == 'no') {
				json = json + ',"barcodes":"none","adapters":"none"';
			}else{
				json = json + ',"barcodes":"distance,' + barcode_array.split(',')[0] + ':format,'+ barcode_array.split(",")[1];
				json = json + '","adapters":"' + initial_split[initial_split.length - 1].split(" ")[1].replace(/\:/g, "__cr____cn__") + '"';
			}
			json = json + ',"quality":"none","quality":"none",';
			json = json + '"trim":"none","split":"none","commonind":"none"}'
			
			var names_list = [];
			if (initial_split[2] == 'yes') {
				var names = initial_split[initial_split.length - 1].split(":");
				for(var y = 0; y < names.length; y++){
					names_list.push(names[y].split(" ")[0]);
				}
			}else{
				var names = initial_split[7].split(":");
				for(var y = 0; y < names.length; y++){
					names_list.push(names[y].split(" ")[0]);
				}
			}
			
			sample_lane = initial_split[5];
			experiment_series = initial_split[4];
			
		}else{
			runname = 'Import Initial Run';
			rundesc = 'Import Initial Run within series: ' + initial_split[0];
			outdir = initial_split[1] + '/initial_run';
			experiment_series = initial_split[0];
			
			json = '{"genomebuild":"' + organismSelect(initial_split[2]) + '"';
			if (initial_split[4] == 'paired') {
				json = json + ',"spaired":"paired"';
			}else{
				json = json + ',"spaired":"no"';
			}
			json = json + ',"resume":"no","fastqc":"no",';
			if (initial_split[3] == 'lane') {
				json = json + '"barcodes":"distance,1:format,5 end read 1",';
			}else{
				json = json + '"barcodes":"none",';
			}
			json = json + '"adapters":"none"';
			json = json + ',"quality":"none","quality":"none",';
			json = json + '"trim":"none","split":"none","commonind":"none"}'
			
			var names_list = initialNameList.split(",");
			console.log(initial_split);
			for(var y = 5; y < initial_split.length; y++ ){
				if (y == 5){
					sample_lane = "'" + initial_split[y] + "'";
				}else{
					sample_lane = sample_lane + ",'" + initial_split[y] + "'";
				}
			}
		}
		
		if (json != undefined & outdir != undefined && runname != undefined && rundesc != undefined) {
			//	Check to see if runparams has already launched
			var run_ids = [];
			var initial_run_ids = [];
			var names_to_ids = [];
			var lanes_to_ids = [];
			
			console.log(names_list);
			$.ajax({
				type: 	'GET',
				url: 	BASE_PATH+'/public/ajax/ngsquerydb.php',
				data:  	{ p: 'getLanesFromName', lane: sample_lane, experiment: experiment_series },
				async:	false,
				success: function(s)
				{
					for(var x = 0; x < s.length; x++){
						lanes_to_ids.push(s[x].id);
					}
				}
			});
			console.log(lanes_to_ids);
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
			console.log(names_to_ids)
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
				if (initial_run_ids.length > 0){
					for(var x = 0; x < initial_run_ids.length; x++){
						var added_samples = [];
						$.ajax({
							type: 	'GET',
							url: 	BASE_PATH+'/public/ajax/initialmappingdb.php',
							data:  	{ p: 'checkRunToSamples', run_id: initial_run_ids[x], sample_ids: names_to_ids.toString()},
							async:	false,
							success: function(s)
							{
								for(var x = 0; x < s.length; x++){
									if (names_to_ids.indexOf(s[x].id) > -1) {
										added_samples.push(s[x].id);
									}
								}
							}
						});
						if (added_samples.length > 0) {
							var submitted = postInsertRunlist('insertRunList', added_samples, initial_run_ids[x]);
						}
					}
				}
			}else{
				//insert new values into ngs_runparams
				var runparamsInsert = postInsertRunparams(json, outdir, runname, rundesc);
				console.log(runparamsInsert);
				//insert new values into ngs_runlist
				var submitted = postInsertRunlist(runparamsInsert[0], names_to_ids, runparamsInsert[1]);
				console.log(submitted);
			}
			
		}
	}
	
});