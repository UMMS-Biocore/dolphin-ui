/*
 *Author: Nicholas Merowsky
 *Date: 09 Apr 2015
 *Ascription:
 */

function postInsertRunparams(JSON_OBJECT, outputdir, name, description, perms, group, ids){

	 console.log(outputdir);
	 var successCheck = false;
	 var runlistCheck = "";
	 var runID = "";
	 var barcode = 1;
  
	 var uid = phpGrab.uid;
	 
	 //find the run group ID
	 var hrefSplit = window.location.href.split("/");
	 var rerunLoc = $.inArray('rerun', hrefSplit);
	 var outdir_check = 0;
	 var runGroupID;
	 $.ajax({
		  type: 'GET',
		  url: BASE_PATH+'/public/ajax/ngsquerydb.php',
		  data: { p: "checkOutputDir", outdir: outputdir},
		  async: false,
		  success: function(r)
		  {
			   console.log(r);
			   outdir_check = r;
		  },
		  error: function(request, error)
		  {
			   console.log(request);
			   console.log(error);
		  }
	 });
	 console.log(outdir_check);
	 if(window.location.href.split("/").indexOf('fastlane') > -1){
			   //if from fastlane
			  runGroupID = 'new';
	 }else if (outdir_check != undefined && outdir_check != 0) {
		  $.ajax({
			   type: 'GET',
			   url: BASE_PATH+'/public/ajax/ngsquerydb.php',
			   data: { p: "checkRunID", outdir: outputdir},
			   async: false,
			   success: function(r)
			   {
				   runGroupID = r;
			   }
		  });
	 }else{
		 //if not a rerun
		 runGroupID = 'new';
	 }
  
	 if (JSON_OBJECT.barcodes == 'none') {
		barcode = 0;
	 }
	 json = JSON.stringify(JSON_OBJECT);
	 console.log(runGroupID);
	  $.ajax({
		  type: 	'POST',
		  url: 	BASE_PATH+'/public/ajax/ngsalterdb.php',
		  data:  	{ p: "submitPipeline", json: json, outdir: outputdir, name: name, desc: description, runGroupID: runGroupID, barcode: barcode, uid: uid, group: group, perms: perms, ids: ids},
		  async:	false,
		  success: function(r)
		  {
			   console.log(r);
               successCheck = true;
               if (runGroupID == 'new') {
					runlistCheck = 'insertRunlist';
					runID = r;
               }else if (window.location.href.indexOf("/rerun/") > -1){
					runlistCheck = 'old';
					runID = r;
			   }else{
					runlistCheck = 'old';
			   }
          }
     });
	 if (successCheck) {
		 return [ runlistCheck, runID ];
	 }else{
		 return undefined;
	 }
}

function postInsertRunlist(runlistCheck, sample_ids, runID){
   var uid = phpGrab.uid;
   var gids = phpGrab.gids;
   var successCheck = false;
	 if (runlistCheck == 'insertRunlist') {
		  $.ajax({
			   type: 	'POST',
			   url: 	BASE_PATH+'/public/ajax/ngsalterdb.php',
			   data:  	{ p: runlistCheck, sampID: sample_ids, runID: runID, uid: uid, gids: gids},
			   async:	false,
			   success: function(r)
			   {
					successCheck = true;
					console.log('inserted');
			   }
		  });
	 }else if (runlistCheck == 'old') {
		  if (window.location.href.indexOf("/rerun/") != -1) {
			   runlistCheck = 'insertRunlist'
			   $.ajax({
					type: 'GET',
					url: BASE_PATH+'/public/ajax/ngsquerydb.php',
					data: { p: "clearPreviousSamples", run_id: runID},
					async: false,
					success: function(r)
					{
						 console.log(runID)
						 $.ajax({
							  type: 	'POST',
							  url: 	BASE_PATH+'/public/ajax/ngsalterdb.php',
							  data:  	{ p: runlistCheck, sampID: sample_ids, runID: runID, uid: uid, gids: gids},
							  async:	false,
							  success: function(p)
							  {
								   successCheck = true;
								   console.log(p);
							  }
						 }); 
					}
			   });
		  }
	 }
	 return successCheck;
}

function deleteRunparams(run_id) {
   $('#delModal').modal({
      show: true
   });
   document.getElementById('delRunId').value = run_id;
   document.getElementById('delRunId').innerHTML = run_id;
   document.getElementById('confirm_del_btn').value = run_id;
}

function resumeSelected(run_id, groupID){
	 if (runOwnerCheck(run_id) != 'Permission Denied') {
		  $.ajax({ type: "POST",
			   url: BASE_PATH+"/public/ajax/ngsalterdb.php",
			   data: { p: "noAddedParamsRerun", run_id: run_id },
			   async: false,
			   success : function(s)
			   {
					console.log(s)
			   }
		  });
		  
		  //   UPDATE THE PAGE
		  location.reload();
	 }else{
		  $('#runModal').modal({
			  show: true
		  });
	 }
}

function resetSelected(run_id, groupID){
	 if (runOwnerCheck(run_id) != 'Permission Denied') {
		  $.ajax({ type: "POST",
			   url: BASE_PATH+"/public/ajax/ngsalterdb.php",
			   data: { p: "resetWKey", id: run_id },
			   async: false,
			   success : function(s)
			   {
					console.log(s)
			   }
		  });
		 
		  //   UPDATE THE PAGE
		  location.reload();
	 }else{
		  $('#runModal').modal({
			  show: true
		  });
	 }
	 
}

function confirmDeleteRunparams(run_id){
   $.ajax({
            type: 	'POST',
            url: 	BASE_PATH+'/public/ajax/ngsalterdb.php',
            data:  	{ p: 'deleteRunparams', run_id: run_id },
            async:	false,
            success: function(r)
            {
            }
   });
   location.reload();
}

function killRun(run_id){
   $.ajax({
            type: 	'GET',
            url: 	BASE_PATH+'/public/ajax/kill_pid.php',
            data:  	{ p: 'killRun', run_id: run_id },
            async:	false,
            success: function(r)
            {
               console.log(r);
               location.reload();
            }
   });
}