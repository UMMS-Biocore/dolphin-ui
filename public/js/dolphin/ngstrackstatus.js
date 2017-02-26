var run_id = '0';
var wkey = '';
var page_mark_runparams = 0;
var page_mark_services = 0;
var page_mark_jobs = 0;
var selected_service;
var service_id;
var clusteruser;

$(function() {
	"use strict";
	
	//The Calender
	$("#calendar").datepicker();
	
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
	
	$.ajax({ type: "GET",
		url: BASE_PATH+"/public/ajax/ngs_stat_funcs.php",
		data: { p: "getClusterUser" },
		async: false,
		success : function(s)
		{
			clusteruser = s;
		}
	});
	
	/*##### STATUS TABLE #####*/
	if (segment == 'status') {
		var run_type = getRunType();
		console.log(run_type);
		document.getElementById('run_types').setAttribute('onChange', 'changeRunType(this.value)');
		document.getElementById('run_types').options[run_type].setAttribute('selected', 'true');
		var runparams = $('#jsontable_runparams').dataTable( {
			stateSave: true,
			"columnDefs": [
							{ className: "directory-col", "targets": [ 2 ] }
						  ]
		});
		
		$.ajax({ type: "GET",
			 url: BASE_PATH+"/public/ajax/ngs_tables.php",
			 data: { p: "getStatus", q: qvar, r: rvar, seg: segment, search: theSearch, uid: uid, gids: gids },
			 async: false,
			 success : function(s)
			 {
				console.log(s);
				runparams.fnClearTable();
				for(var i = 0; i < s.length; i++) {
					var runstat = "";
					var disabled = '';
					
					if (s[i].run_status == 0 || s[i].run_status == 2) {
						s[i].run_status = runningErrorCheck(s[i].id);
					}
						
					if (s[i].run_status == 0) {
						runstat = '<button id="'+s[i].id+'" class="btn btn-xs disabled" onclick="queueCheck(this.id)"><i class="fa fa-refresh">\tQueued</i></button>';
						disabled = '<li><a href="#" id="'+s[i].id+'" name="'+s[i].run_group_id+'" onClick="killRun(this.id)">Cancel</a></li>';
					}else if (s[i].run_status == 1) {
						runstat = '<button id="'+s[i].id+'" class="btn btn-success btn-xs"  onclick="sendToAdvancedStatus(this.id)"><i class="fa fa-check">\tComplete!</i></button>';
						disabled = '<li><a href="#" id="'+s[i].id+'" name="'+s[i].run_group_id+'" onClick="sendToPlot(this.id)">Generate Plots</a></li>';
					}else if (s[i].run_status == 2){
						runstat = '<button id="'+s[i].id+'" class="btn btn-warning btn-xs" onclick="sendToAdvancedStatus(this.id)"><i class="fa fa-refresh">\tRunning...</i></button>';
						disabled = '<li><a href="#" id="'+s[i].id+'" name="'+s[i].run_group_id+'" onClick="killRun(this.id)">Stop</a></li>';
					}else if (s[i].run_status == 3){
						runstat = '<button id="'+s[i].id+'" class="btn btn-danger btn-xs" onclick="errorOutModal(this.id, \''+ s[i].wkey + '\')"><i class="fa fa-warning">\tError</i></button>';
					}else if (s[i].run_status == 4){
						runstat = '<button id="'+s[i].id+'" class="btn btn-danger btn-xs" onclick="sendToAdvancedStatus(this.id)"><i class="fa fa-warning">\tStopped</i></button>';
					}else if (s[i].run_status == 5) {
						runstat = '<button id="'+s[i].id+'" class="btn btn-xs disabled" onclick="queueCheck(this.id)"><i class="fa fa-refresh">\tWaiting</i></button>';
						disabled = '<li><a href="#" id="'+s[i].id+'" name="'+s[i].run_group_id+'" onClick="killRun(this.id)">Cancel</a></li>';
					}else if (s[i].run_status == 6) {
						runstat = '<button id="'+s[i].id+'" class="btn btn-xs disabled" ><i class="fa fa-exchange">\tReset</i></button>';
					}
					
					if (s[i].owner_id == uid) {
						disabled += '<li><a href="#" id="perms_'+s[i].id+'" name="'+s[i].group_id+'" onclick="changeRunPerms(this.id, this.name)">Change Permissions</a></li>' +
							'<li class="divider"></li>';
					}
					if (s[i].outdir.split("/")[s[i].outdir.split("/").length - 1] != 'initial_run' || s[i].run_status == 1) {
						disabled = disabled + '<li><a href="#" id="'+s[i].id+'" name="'+s[i].run_group_id+'" onclick="rerunSelected(this.id, this.name)">Rerun</a></li>';
					}
					
					if (runstat != "") {
						runparams.fnAddData([
						s[i].id,
						s[i].run_name,
						s[i].outdir,
						s[i].run_description,
						runstat,
						s[i].username,
						'<div class="btn-group pull-right">' +
						'<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Options <span class="fa fa-caret-down"></span></button>' +
						'</button>' +
						'<ul class="dropdown-menu" role="menu">' +
							'<li><a href="#" id="'+s[i].id+'" name="'+s[i].run_group_id+'" onClick="reportSelected(this.id, this.name)">Report Details</a></li>' +
							'<li class="divider"></li>' +
							disabled +
							'<li><a href="#" id="'+s[i].id+'" name="'+s[i].run_group_id+'" onclick="resumeSelected(this.id, this.name)">Resume</a></li>' +
							'<li><a href="#" id="'+s[i].id+'" name="'+s[i].run_group_id+'" onclick="resetSelected(this.id, this.name)">Reset</a></li>' +
							'<li class="divider"></li>' +
							'<li><a href="#" id="'+s[i].id+'" name="'+s[i].run_group_id+'" onClick="deleteRunparams(\''+s[i].id+'\')">Delete</a></li>' +
						'</ul>' +
						'</div>',
						]);
					}
				} // End For
			}
		});

	runparams.fnSort( [ [0,'des'] ] );
	//runparams.fnAdjustColumnSizing(true);
	
	}else if (segment == 'advstatus') {
		$.ajax({ type: "GET",
			url: BASE_PATH +"/ajax/sessionrequests.php",
			data: { p: 'getAdvStatusRunID' },
			async: false,
			success : function(s)
			{
				console.log(s);
				run_id = s;
			}
		});
		
		wkey = getWKey(run_id);
		document.getElementById('send_to_reports').name = run_id;
		var runparams = $('#jsontable_services').dataTable();
		console.log(wkey);	
		var progress_bars = progressBars();
		
		$.ajax({ type: "GET",
			 url: BASE_PATH + "/public/ajax/dataservice.php?wkey=" + wkey,
			 async: false,
			 success : function(s)
			 {
				console.log(s);
				runparams.fnClearTable();
				var parsed = s;
				for(var i = 0; i < parsed.length; i++) {
					if (parsed[i].result == 1) {
						var bartype = 'success';
						var colortype = 'green'
					}else{
						var bartype = 'danger';
						var colortype = 'red';
					}
					runparams.fnAddData([
						parsed[i].title,
						parsed[i].duration,
						progress_bars[0][i],
						progress_bars[1][i],
						parsed[i].start,
						parsed[i].finish,
						'<button id="'+parsed[i].num+'" class="btn btn-warning btn-xs pull-right" name="soft" title="Soft Reset" onclick="resetType('+run_id+', '+parsed[i].num+', \''+wkey+'\', \''+parsed[i].title+'\', \'services\', this)"><span class="fa fa-times"></span></button>' +
						'<button id="'+parsed[i].num+'_select" class="btn btn-primary btn-xs pull-right" title="Select" onclick="selectService(this.id, \''+parsed[i].title+'\', \''+wkey+'\')">&nbsp;<span class="fa fa-caret-down"></span>&nbsp;</button>'
					]);
				} // End For
			}
		});
		runparams.fnSort( [ [4,'asc'] ] );
		//runparams.fnAdjustColumnSizing(true);
	}else if (segment == "reroute") {
		window.location.href = BASE_PATH+'/stat/advstatus';
	}
	
	$('#jsontable_runparams').on( 'page.dt', function () {
		var table = $('#jsontable_runparams').DataTable();
		var info = table.page.info();
		page_mark_runparams = info.page;
	} );
	$('#jsontable_services').on( 'page.dt', function () {
		var table = $('#jsontable_services').DataTable();
		var info = table.page.info();
		page_mark_services = info.page;
	} );
	$('#jsontable_jobs').on( 'page.dt', function () {
		var table = $('#jsontable_jobs').DataTable();
		var info = table.page.info();
		page_mark_jobs = info.page;
	} );
	
	setInterval( function () {
		if (segment == 'status') {
			var runparams = $('#jsontable_runparams').dataTable();
			$.ajax({ type: "GET",
				 url: BASE_PATH+"/public/ajax/ngs_tables.php",
				 data: { p: "getStatus", q: qvar, r: rvar, seg: segment, search: theSearch, uid: uid, gids: gids },
				 async: false,
				 success : function(s)
				 {
					runparams.fnClearTable();
					for(var i = 0; i < s.length; i++) {
						var runstat = "";
						var disabled = '';
						
						if (s[i].run_status == 0 || s[i].run_status == 2) {
							s[i].run_status = runningErrorCheck(s[i].id);
						}
						
						if (s[i].run_status == 0) {
							runstat = '<button id="'+s[i].id+'" class="btn btn-xs disabled" onclick="queueCheck(this.id)"><i class="fa fa-refresh">\tQueued</i></button>';
							disabled = '<li><a href="#" id="'+s[i].id+'" name="'+s[i].run_group_id+'" onClick="killRun(this.id)">Cancel</a></li>';
						}else if (s[i].run_status == 1) {
							runstat = '<button id="'+s[i].id+'" class="btn btn-success btn-xs"  onclick="sendToAdvancedStatus(this.id)"><i class="fa fa-check">\tComplete!</i></button>';
							disabled = '<li><a href="#" id="'+s[i].id+'" name="'+s[i].run_group_id+'" onClick="sendToPlot(this.id)">Generate Plots</a></li>';
						}else if (s[i].run_status == 2){
							runstat = '<button id="'+s[i].id+'" class="btn btn-warning btn-xs" onclick="sendToAdvancedStatus(this.id)"><i class="fa fa-refresh">\tRunning...</i></button>';
							disabled = '<li><a href="#" id="'+s[i].id+'" name="'+s[i].run_group_id+'" onClick="killRun(this.id)">Stop</a></li>';
						}else if (s[i].run_status == 3){
							runstat = '<button id="'+s[i].id+'" class="btn btn-danger btn-xs" onclick="errorOutModal(this.id, \''+ s[i].wkey + '\')"><i class="fa fa-warning">\tError</i></button>';
						}else if (s[i].run_status == 4){
							runstat = '<button id="'+s[i].id+'" class="btn btn-danger btn-xs" onclick="sendToAdvancedStatus(this.id)"><i class="fa fa-warning">\tStopped</i></button>';
						}else if (s[i].run_status == 5) {
							runstat = '<button id="'+s[i].id+'" class="btn btn-xs disabled" onclick="queueCheck(this.id)"><i class="fa fa-refresh">\tWaiting</i></button>';
							disabled = '<li><a href="#" id="'+s[i].id+'" name="'+s[i].run_group_id+'" onClick="killRun(this.id)">Cancel</a></li>';
						}else if (s[i].run_status == 6) {
							runstat = '<button id="'+s[i].id+'" class="btn btn-xs disabled" ><i class="fa fa-exchange">\tReset</i></button>';
						}
						if (s[i].owner_id == uid) {
							disabled += '<li><a href="#" id="perms_'+s[i].id+'" name="'+s[i].group_id+'" onclick="changeRunPerms(this.id, this.name)">Change Permissions</a></li>' +
								'<li class="divider"></li>';
						}
						if (s[i].outdir.split("/")[s[i].outdir.split("/").length - 1] != 'initial_run' || s[i].run_status == 1) {
							disabled = disabled + '<li><a href="#" id="'+s[i].id+'" name="'+s[i].run_group_id+'" onclick="rerunSelected(this.id, this.name)">Rerun</a></li>';
						}
						
						if (runstat != "") {
							runparams.fnAddData([
							s[i].id,
							s[i].run_name,
							s[i].outdir,
							s[i].run_description,
							runstat,
							s[i].username,
							'<div class="btn-group pull-right">' +
							'<button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="true">Options <span class="fa fa-caret-down"></span></button>' +
							'</button>' +
							'<ul class="dropdown-menu" role="menu">' +
								'<li><a href="#" id="'+s[i].id+'" name="'+s[i].run_group_id+'" onClick="reportSelected(this.id, this.name)">Report Details</a></li>' +
								'<li class="divider"></li>' +
								disabled +
								'<li><a href="#" id="'+s[i].id+'" name="'+s[i].run_group_id+'" onclick="resumeSelected(this.id, this.name)">Resume</a></li>' +
								'<li><a href="#" id="'+s[i].id+'" name="'+s[i].run_group_id+'" onclick="resetSelected(this.id, this.name)">Reset</a></li>' +
								'<li class="divider"></li>' +
								'<li><a href="#" id="'+s[i].id+'" name="'+s[i].run_group_id+'" onClick="deleteRunparams(\''+s[i].id+'\')">Delete</a></li>' +
							'</ul>' +
							'</div>',
							]);
						}
					} // End For
				}
			});
			
			$('#jsontable_runparams').DataTable().page(page_mark_runparams).draw(false);
		}else if (segment == 'advstatus') {
			var runparams = $('#jsontable_services').dataTable();
			var progress_bars = progressBars();
			$.ajax({ type: "GET",
					 url: BASE_PATH + "/public/ajax/dataservice.php?wkey=" + wkey,
					 async: false,
					 success : function(s)
					 {
						runparams.fnClearTable();
						var parsed = s;
						for(var i = 0; i < parsed.length; i++) {
							if (parsed[i].result == 1) {
								var bartype = 'success';
								var colortype = 'green'
							}else{
								var bartype = 'danger';
								var colortype = 'red';
							}
							runparams.fnAddData([
								parsed[i].title,
								parsed[i].duration,
								progress_bars[0][i],
								progress_bars[1][i],
								parsed[i].start,
								parsed[i].finish,
								'<button id="'+parsed[i].num+'" class="btn btn-warning btn-xs pull-right" name="soft" title="Soft Reset" onclick="resetType('+run_id+', '+parsed[i].num+', \''+wkey+'\', \''+parsed[i].title+'\', \'services\', this)"><span class="fa fa-times"></span></button>' +
								'<button id="'+parsed[i].num+'_select" class="btn btn-primary btn-xs pull-right" title="Select" onclick="selectService(this.id, \''+parsed[i].title+'\', \''+wkey+'\')">&nbsp;<span class="fa fa-caret-down"></span>&nbsp;</button>'
							]);
						} // End For
					}
				});
			runparams.fnSort( [ [4,'asc'] ] );
			$('#jsontable_services').DataTable().page(page_mark_services).draw(false);
			
			if (service_id != undefined) {
				var runjob = $('#jsontable_jobs').dataTable();
				$.ajax({ type: "GET",
						 url: BASE_PATH +"/ajax/datajobs.php?id=" + service_id,
						 async: false,
						 success : function(s)
						 {
							runjob.fnClearTable();
							var parsed = s;
							var reset = "";
							for(var i = 0; i < parsed.length; i++) {
								if (selected_service != parsed[i].title) {
									reset = '<button id="'+parsed[i].num+'" class="btn btn-warning btn-xs pull-right" name="soft" title="Soft Reset"onclick="resetType('+run_id+', '+parsed[i].num+', \''+wkey+'\', \''+parsed[i].title+'\', \'jobs\', this)"><span class="fa fa-times"></span></button>'; 
								}else{
									reset = "";
								}
								runjob.fnAddData([
									parsed[i].title,
									parsed[i].duration,
									parsed[i].result,
									parsed[i].job_num,
									parsed[i].submit,
									parsed[i].start,
									parsed[i].finish,
									reset +
									'<button id="'+parsed[i].num+'_select" class="btn btn-primary btn-xs pull-right" title="Select" onclick="selectJob(this.id)">&nbsp;<span class="fa fa-caret-down"></span>&nbsp;</button>'
								]);
							} // End For
							document.getElementById('service_jobs').style.display = 'inline';
						}
					});
				runjob.fnSort( [ [4,'asc'] ] );
				$('#jsontable_jobs').DataTable().page(page_mark_jobs).draw(false);
			}
		}
	}, 60000 );
});