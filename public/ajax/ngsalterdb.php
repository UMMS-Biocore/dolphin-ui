<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");

$query = new dbfuncs();
//header

function runCmd($idKey, $query)
{
    $cmd = "cd ../../scripts && mkdir -p ../tmp/logs/run$idKey && python dolphin_wrapper.py -r $idKey -c ".CONFIG.">> ../tmp/logs/run$idKey/run.$idKey.wrapper.std 2>&1 & echo $! &";
    $PID_COMMAND = popen( $cmd, "r" );
    $PID =fread($PID_COMMAND, 2096);
    $data=$query->runSQL("
        UPDATE ngs_runparams
        SET wrapper_pid = $PID
        WHERE id = '$idKey'
        ");
    pclose($PID_COMMAND);
}

function killPid($run_id, $query)
{
	$pids = json_decode($query->queryTable("SELECT wrapper_pid, runworkflow_pid
							   FROM ngs_runparams
							   WHERE id = $run_id"));
	
	$workflow_pid = $pids[0]->runworkflow_pid;
	$wrapper_pid = $pids[0]->wrapper_pid;

	if($workflow_pid != NULL && $wrapper_pid != NULL){
        $grep_check_workflow = "ps -ef | grep '[".substr($workflow_pid, 0, 1)."]".substr($workflow_pid,1)."'";
        $grep_check_wrapper = "ps -ef | grep '[".substr($wrapper_pid, 0, 1)."]".substr($wrapper_pid,1)."'";
        
        $grep_find_workflow = pclose(popen( $grep_check_workflow, "r" ) );
        $grep_find_wrapper = pclose(popen( $grep_check_wrapper, "r" ) );
    }else{
        $grep_find_workflow = NULL;
        $grep_find_wrapper = NULL;
    }
    
	if($grep_find_workflow > 0 && $grep_find_workflow != NULL){
		pclose(popen( "kill -9 $workflow_pid", "r" ) );
	}
	if($grep_find_wrapper > 0 && $grep_find_wrapper != NULL){
		pclose(popen( "kill -9 $wrapper_pid", "r" ) );
	}
}

if (isset($_POST['p'])){$p = $_POST['p'];}
if (isset($_POST['start'])){$start = $_POST['start'];}
if (isset($_POST['end'])){$end = $_POST['end'];}

if ($p == "submitPipeline" )
{
	//Grab the inputs
	if (isset($_POST['json'])){$json = $_POST['json'];}
	if (isset($_POST['outdir'])){$outdir = $_POST['outdir'];}
	if (isset($_POST['name'])){$name = $_POST['name'];}
	if (isset($_POST['desc'])){$desc = $_POST['desc'];}
	if (isset($_POST['runGroupID'])){$runGroupID = $_POST['runGroupID'];}
    if (isset($_POST['barcode'])){$barcode = $_POST['barcode'];}
    if (isset($_POST['uid'])){$uid = $_POST['uid'];}
    if (isset($_POST['gids'])){$gids = $_POST['gids'];}
    
    $outdir_check = $query->queryAVal("SELECT outdir FROM ngs_runparams WHERE outdir = '$outdir'");
    
    if($outdir_check == $outdir){
        $idKey=$query->queryAVal("SELECT id FROM ngs_runparams WHERE outdir = '$outdir' limit 1");
        $wrapper_pid=$query->queryAVal("SELECT wrapper_pid FROM ngs_runparams WHERE outdir = '$outdir'");
        $workflow_pid=$query->queryAVal("SELECT runworkflow_pid FROM ngs_runparams WHERE outdir = '$outdir'");
        $wkey=$query->queryAVal("SELECT wkey FROM ngs_runparams WHERE outdir = '$outdir'");
        
        killPid($idKey, $query);
        $data=$query->runSQL("
        INSERT INTO ngs_wkeylist
        (run_id, wkey, wrapper_pid, workflow_pid, time_added)
        VALUES
        ($idKey, '$wkey', $wrapper_pid, $workflow_pid, now())
        ");
        
        $data=$query->runSQL("
        UPDATE ngs_runparams
        SET run_status = 0,
        wrapper_pid = NULL,
        runworkflow_pid = NULL,
        wkey = NULL,
        barcode = $barcode,
        json_parameters = '$json',
        run_name = '$name',
        run_description = '$desc',
        date_modified = now(),
        last_modified_user = $uid
        WHERE id = '$idKey'
        ");
        runCmd($idKey, $query);
        $data=$idKey;
    }else{
        //run_group_id set to -1 as a placeholder.Cannot grab primary key as it's being made, so a placeholder is needed.
        $data=$query->runSQL("
        INSERT INTO ngs_runparams (run_group_id, outdir, run_status, barcode, json_parameters, run_name, run_description,
        owner_id, group_id, perms, date_created, date_modified, last_modified_user)
        VALUES (-1, '$outdir', 0, $barcode, '$json', '$name', '$desc',
        $uid, NULL, 3, now(), now(), $uid)");
        //need to grab the id for runlist insertion
        $idKey=$query->queryAVal("SELECT id FROM ngs_runparams WHERE run_group_id = -1 and run_name = '$name' order by id desc limit 1");
        runCmd($idKey, $query);
        //update required to make run_group_id equal to it's primary key "id".Replace the arbitrary -1 with the id
        if (isset($_POST['runid'])){$runGroupID = $_POST['runid'];}
        if( $runGroupID == 'new'){
            $data=$query->runSQL("UPDATE ngs_runparams SET run_group_id = id WHERE run_group_id = -1");
        }else{
            $data=$query->runSQL("UPDATE ngs_runparams SET run_group_id = $runGroupID WHERE run_group_id = -1");
            $idKey= $idKey - $runGroupID;
        }
        $data=$idKey;
    }
}
else if ($p == 'insertRunlist')
{
	//Grab the inputs
	if (isset($_POST['sampID'])){$sampID = $_POST['sampID'];}
	if (isset($_POST['runID'])){$runID = $_POST['runID'];}
    if (isset($_POST['uid'])){$uid = $_POST['uid'];}
    if (isset($_POST['gids'])){$gids = $_POST['gids'];}
	$searchQuery = "INSERT INTO ngs_runlist
		(run_id, sample_id, owner_id, group_id, perms, date_created, date_modified, last_modified_user)
		VALUES ";
    if(is_string($sampID)){
        $searchQuery .= "($runID, $sampID, $uid, NULL, 3, NOW(), NOW(), $uid)";
    }else{
        foreach ($sampID as $s){
            $searchQuery .= "($runID, $s, $uid, NULL, 3, NOW(), NOW(), $uid)";
            if($s != end($sampID)){
                $searchQuery .= ",";
            }
        }
    }
	$data=$query->runSQL($searchQuery);
}
else if ($p == 'deleteRunparams')
{
    if (isset($_POST['run_id'])){$run_id = $_POST['run_id'];}
    
    killPid($run_id, $query);
    
	$query->runSQL("DELETE FROM ngs_runlist WHERE run_id = $run_id");
	$query->runSQL("DELETE FROM ngs_runparams WHERE id = $run_id");
}
else if ($p == 'noAddedParamsRerun')
{
    if (isset($_POST['run_id'])){$run_id = $_POST['run_id'];}
    
    killPid($run_id, $query);
    
    $data=$query->runSQL("
	UPDATE ngs_runparams
    SET run_status=0,
    wrapper_pid = NULL,
    runworkflow_pid = NULL
    WHERE id = $run_id
    ");
    
    runCmd($run_id, $query);  
}

//footer
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo $data;
exit;
?>
