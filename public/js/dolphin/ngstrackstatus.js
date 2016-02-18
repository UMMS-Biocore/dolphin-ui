
var page_mark_runparams = 0;
var page_mark_services = 0;
var page_mark_jobs = 0;
var service_id;

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
	
	/*##### STATUS TABLE #####*/
	if (segment == 'status') {
		var run_type = getRunType();
		console.log(run_type);
		document.getElementById('run_types').setAttribute('onChange', 'changeRunType(this.value)');
		document.getElementById('run_types').options[run_type].setAttribute('selected', 'true');
		var runparams = $('#jsontable_runparams').dataTable( {
			stateSave: true
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
	var run_id = '0';
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
	var wkey = getWKey(run_id);
	var runparams = $('#jsontable_services').dataTable();
	console.log(wkey);
	
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
						'<span class="pull-right badge bg-'+colortype+'">'+parsed[i].percentComplete.split(".")[0]+'%</span>',
						'<div class="progress progress-xs"><div class="progress-bar progress-bar-'+bartype+'" style="width: '+parsed[i].percentComplete+'%"></div></div>',
						parsed[i].start,
						parsed[i].finish,
						'<button id="'+parsed[i].num+'" class="btn btn-primary btn-xs pull-right" onclick="selectService(this.id)">Select Service</button>'
					]);
				} // End For
			}
		});
		runparams.fnSort( [ [4,'asc'] ] );
		//runparams.fnAdjustColumnSizing(true);
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
								'<span class="pull-right badge bg-'+colortype+'">'+parsed[i].percentComplete.split(".")[0]+'%</span>',
								'<div class="progress progress-xs"><div class="progress-bar progress-bar-'+bartype+'" style="width: '+parsed[i].percentComplete+'%"></div></div>',
								parsed[i].start,
								parsed[i].finish,
								'<button id="'+parsed[i].num+'" class="btn btn-primary btn-xs pull-right" onclick="selectService(this.id)">Select Service</button>'
							]);
						} // End For
					}
				});
			runparams.fnSort( [ [4,'asc'] ] );
			$('#jsontable_services').DataTable().page(page_mark_services).draw(false);
			
			if ($('jsontable_jobs') != undefined) {
				var runjob = $('#jsontable_jobs').dataTable();
				$.ajax({ type: "GET",
						 url: BASE_PATH +"/ajax/datajobs.php?id=" + service_id,
						 async: false,
						 success : function(s)
						 {
							runjob.fnClearTable();
							var parsed = s;
							for(var i = 0; i < parsed.length; i++) {
								runjob.fnAddData([
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
				runjob.fnSort( [ [4,'asc'] ] );
				$('#jsontable_jobs').DataTable().page(page_mark_jobs).draw(false);
			}
		}
	}, 60000 );
});