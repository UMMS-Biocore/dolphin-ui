	
function backToStatus(basepath){
	window.location.href = basepath + '/stat/status';
}

function sendToPlot(id){
	var wkey = getWKey(id);
	sendWKey(wkey);
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/sessionrequests.php",
			data: { p: "setPlotToggle", type: 'normal', file: '' },
			async: false,
			success : function(s)
			{
			}
	});
	window.location.href = BASE_PATH+'/plot';
}

function sendToAdvancedStatus(run_id){
	window.location.href = BASE_PATH+'/stat/advstatus/' + run_id;
}

function getWKey(run_id){
	var wkey = "";
	$.ajax({ type: "GET",
			url: BASE_PATH+"/public/ajax/ngsquerydb.php",
			data: { p: 'getWKey', run_id: run_id },
			async: false,
			success : function(s)
			{
			   wkey = s[0].wkey;
			}
	});
	return wkey;
}

function selectService(id){
	service_id = id;
	var runparams = $('#jsontable_jobs').dataTable();
	$.ajax({ type: "GET",
			 url: BASE_PATH +"/ajax/datajobs.php?id=" + id,
			 async: false,
			 success : function(s)
			 {
				runparams.fnClearTable();
				var parsed = JSON.parse(s);
				for(var i = 0; i < parsed.length; i++) {
					runparams.fnAddData([
						parsed[i].title,
						parsed[i].duration,
						parsed[i].job_num,
						parsed[i].submit,
						parsed[i].start,
						parsed[i].finish,
						'<button id="'+parsed[i].num+'" class="btn btn-primary btn-xs pull-right" onclick="selectJob(this.id)">Select Job</button>'
					]);
				} // End For
				document.getElementById('service_jobs').style.display = 'inline';
			}
		});
	runparams.fnSort( [ [4,'asc'] ] );
	//runparams.fnAdjustColumnSizing(true);
}

function selectJob(id){
	$.ajax({ type: "GET",
			url: BASE_PATH + "/public/ajax/datajobout.php?id=" + id,
			async: false,
			success : function(s)
			{
				var parsed = JSON.parse(s);
				joboutDataModal(parsed[0].jobname, parsed[0].jobout);
			}
		});
}

function errorOutModal(run_id, wkey){
	var obtained_log;
	$.ajax({ type: "GET",
			url: BASE_PATH +"/public/ajax/dataerrorlogs.php",
			data: { p: 'getStdOut', run_id: run_id },
			async: false,
			success : function(s)
			{
				if (s.length > 20) {
					obtained_log = "...<br>"
					for(var i = s.length - 20; i < s.length; i++){
						obtained_log += s[i];
					}
				}else{
					for(var i = 0; i < s.length; i++){
						obtained_log += s[i];
					}
				}
			}
	});
	$('#logModal').modal({
      show: true
	});
	document.getElementById('logRunId').innerHTML = "run." + run_id + ".wrapper.std:";
	document.getElementById('logDetails').innerHTML = obtained_log;
	
	if (wkey == null || wkey == "null") {
		document.getElementById('modal_adv_status').style.display = "none";
	}else{
		var adv_stat_check = [];
		$.ajax({ type: "GET",
				url: BASE_PATH + "/public/ajax/dataservice.php?wkey=" + wkey,
				async: false,
				success : function(s)
				{
					adv_stat_check = s;
				}
		});
		
		if (adv_stat_check.length > 0) {
			document.getElementById('modal_adv_status').style.display = "show";
			document.getElementById('modal_adv_status').setAttribute("onclick", "sendToAdvancedStatus("+run_id+")");
		}else{
			document.getElementById('modal_adv_status').style.display = "none";
		}
	}
}

function queueCheck(run_id){
	var run_status = [];
	$.ajax({ type: "GET",
			url: BASE_PATH +"/public/ajax/dataerrorlogs.php",
			data: { p: 'checkQueued', run_id: run_id },
			async: false,
			success : function(s)
			{
				run_status = s;
			}
	});
	
	if (run_status.length == 3) {
		if (run_status[2] == null && run_status[0] == '0' && run_status[1] == '0') {
			console.log(run_status);
		}else{
			errorOutModal(run_id, run_status[2]);
		}
	}
	
}

function runningErrorCheck(run_id){
	var run_status;
	$.ajax({ type: "GET",
			url: BASE_PATH +"/public/ajax/dataerrorlogs.php",
			data: { p: 'errorCheck', run_id: run_id },
			async: false,
			success : function(s)
			{
				run_status = s;
			}
	});
	return run_status
}

function joboutDataModal(jobname, jobout) {
   $('#joboutData').modal({
      show: true
   });
   document.getElementById('job_modal_jobname').innerHTML = jobname;
   document.getElementById('job_modal_text').innerHTML = jobout;
}

function changeRunGroup(id, group){
	document.getElementById('myModalPerms').innerHTML = 'Change run group';
	document.getElementById('permsLabel').innerHTML = 'Which group should see this run?'
	document.getElementById('permsDiv').innerHTML = '<select id="permsIDSelect" class="form-control"></select>'
	document.getElementById('confirmPermsButton').setAttribute('style', 'display:show');
	document.getElementById('confirmPermsButton').setAttribute('onclick','confirmGroupChange('+id+')');
	document.getElementById('cancelPermsButton').innerHTML = 'Cancel';
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngsquerydb.php",
		data: { p: 'getGroups'},
		async: false,
		success : function(s)
		{
			console.log(s);
			for(var x = 0; x < s.length; x++){
				if (s[x].id == group) {
					document.getElementById('permsIDSelect').innerHTML += '<option value="' + s[x].id + '" selected="true">' + s[x].name + '</option>';
				}else{
					document.getElementById('permsIDSelect').innerHTML += '<option value="' + s[x].id + '">' + s[x].name + '</option>';
				}
			}
		}
	});
	$('#permsModal').modal({
		show: true
	});
}

function confirmGroupChange(id){
	var group_id = document.querySelector("select").selectedOptions[0].value;
	var group_changed;
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngsquerydb.php",
		data: { p: 'changeRunGroup', group_id: group_id, run_id: id},
		async: false,
		success : function(s)
		{
			group_changed = s;
		}	
	});
	if (group_changed == 'pass') {
		document.getElementById('permsLabel').innerHTML = 'Run group has been changed!'
		document.getElementById('permsDiv').innerHTML = '';
		document.getElementById(id).setAttribute('name', group_id);
	}else{
		document.getElementById('permsLabel').innerHTML = 'Error occured, run group was not changed.'
		document.getElementById('permsDiv').innerHTML = '';
	}
}

function changeRunPerms(id, group) {
	document.getElementById('myModalPerms').innerHTML = 'Change run permissions';
	document.getElementById('confirmPermsButton').setAttribute('onclick','confirmPermsChange()');
	$('#permsModal').modal({
		show: true
	});
}

function confirmPermsChange(){
	
}
