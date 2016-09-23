<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");

$query = new dbfuncs();

if(!function_exists('waitRun')){
	function waitRun($ids, $idKey, $query){
		if($ids != ''){
			$initial_check=$query->queryTable("
				SELECT sample_id
				FROM ngs_fastq_files
				WHERE sample_id in (".$ids.")
				AND total_reads > 0
				");
			$wait_check_error = false;
			if($initial_check == '[]'){
				$wait_check_error = true;
			}else{
				foreach(json_decode($initial_check) as $ic){
					if (!in_array($ic->sample_id, explode(",",$ids))){
						$wait_check_error = true;
					}
				}
			}
			if($wait_check_error){
				$query->runSQL("
				UPDATE ngs_runparams
				SET run_status = 5
				WHERE id = '$idKey'
				");
				return false;
			}
		}
		return true;
	}
}

if(!function_exists('runCmd')){
	function runCmd($idKey, $query, $wkey)
	{
		$wkeystr="";
		if ($wkey!="")
		{
			$wkeystr = "-w $wkey";
		}
		$cmd = "cd ../../scripts && mkdir -p ../tmp/logs/run$idKey && python dolphin_wrapper.py $wkeystr -r $idKey -c ".CONFIG.">> ../tmp/logs/run$idKey/run.$idKey.wrapper.std 2>&1 & echo $! &";
		$PID_COMMAND = popen( $cmd, "r" );
		$PID =fread($PID_COMMAND, 2096);
		$data=$query->runSQL("
			UPDATE ngs_runparams
			SET wrapper_pid = $PID
			WHERE id = '$idKey'
			");
		pclose($PID_COMMAND);
	}
}

		 
if(!function_exists('killPid')){
	function killPid($run_id, $query)
	{
		$pids = json_decode($query->queryTable("SELECT wkey, wrapper_pid, runworkflow_pid
								   FROM ngs_runparams
								   WHERE id = $run_id"));
		
		$workflow_pid = $pids[0]->runworkflow_pid;
		$wrapper_pid = $pids[0]->wrapper_pid;
		$wkey = $pids[0]->wkey;
	
		if($workflow_pid != NULL && $wrapper_pid != NULL && $workflow_pid != 0 && $wrapper_pid != 0){
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
		return $wkey;
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
    if (isset($_POST['group'])){$group = $_POST['group'];}
	if (isset($_POST['perms'])){$perms = $_POST['perms'];}
    if (isset($_POST['ids'])){$ids = $_POST['ids'];}
	
    $outdir_check = $query->queryAVal("SELECT outdir FROM ngs_runparams WHERE outdir = '$outdir'");
    
    if($outdir_check == $outdir){
		$table=json_decode($query->queryTable("SELECT id,wrapper_pid,runworkflow_pid,wkey FROM ngs_runparams WHERE outdir = '$outdir' limit 1"));
        $idKey=$table[0]->id;
        $wrapper_pid=$table[0]->wrapper_pid;
        $workflow_pid=$table[0]->runworkflow_pid;
        $wkey=$table[0]->wkey;
        
        killPid($idKey, $query);
		if($wkey != ''){
        $data=$query->runSQL("
			INSERT INTO ngs_wkeylist
			(run_id, wkey, wrapper_pid, workflow_pid, time_added)
			VALUES
			($idKey, '$wkey', $wrapper_pid, $workflow_pid, now())
			");
		}
        
		if($name == 'Import Initial Run' || $name == 'Fastlane Initial Run'){
			$wkey = '';
		}
		
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
		group_id = $group,
		perms = $perms,
        date_modified = now(),
        last_modified_user = $uid
        WHERE id = '$idKey'
        ");
		$checkWait = waitRun($ids, $idKey, $query);
        if($checkWait){
			runCmd($idKey, $query, $wkey);
		}
        $data=$idKey;
    }else{
        //run_group_id set to -1 as a placeholder.Cannot grab primary key as it's being made, so a placeholder is needed.
        $data=$query->runSQL("
        INSERT INTO ngs_runparams (run_group_id, outdir, run_status, barcode, json_parameters, run_name, run_description,
        owner_id, group_id, perms, date_created, date_modified, last_modified_user)
        VALUES (-1, '$outdir', 0, $barcode, '$json', '$name', '$desc',
        $uid, $group, $perms, now(), now(), $uid)");
        //need to grab the id for runlist insertion
        $idKey=$query->queryAVal("SELECT id FROM ngs_runparams WHERE run_group_id = -1 and run_name = '$name' order by id desc limit 1");
        $wkey="";
		$checkWait = waitRun($ids, $idKey, $query);
        if($checkWait){
			runCmd($idKey, $query, $wkey);
		}
        //update required to make run_group_id equal to it's primary key "id".Replace the arbitrary -1 with the id
        if( $runGroupID == 'new'){
            $data=$query->runSQL("UPDATE ngs_runparams SET run_group_id = id WHERE run_group_id = -1");
        }else{
            $data=$query->runSQL("UPDATE ngs_runparams SET run_group_id = $runGroupID WHERE run_group_id = -1");
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
	$query->runSQL($searchQuery);
	$data=json_encode($sampID);
}
else if ($p == 'deleteRunparams')
{
    if (isset($_POST['run_id'])){$run_id = $_POST['run_id'];}
    
    killPid($run_id, $query);
	
	$insert_query = "
	INSERT INTO ngs_deleted_runs
	(run_id, outdir, run_status, json_parameters,
	run_name, run_description, owner_id, group_id, perms,
	last_modified_user)
	SELECT id, outdir, run_status, json_parameters,
	run_name, run_description, owner_id, group_id, perms,
	last_modified_user
	FROM ngs_runparams WHERE id = $run_id";
	$query->runSQL($insert_query);
	
	$query->runSQL("DELETE FROM ngs_runlist WHERE run_id = $run_id");
	$query->runSQL("DELETE FROM ngs_runparams WHERE id = $run_id");
	$data = '';
}
else if ($p == 'noAddedParamsRerun')
{
    if (isset($_POST['run_id'])){$run_id = $_POST['run_id'];}
    
    $wkey=killPid($run_id, $query);
    
    $data=$query->runSQL("
	UPDATE ngs_runparams
    SET run_status=0,
    wrapper_pid = NULL,
    runworkflow_pid = NULL
    WHERE id = $run_id
    ");
    
    runCmd($run_id, $query, $wkey);  
}
else if($p == 'updateProfile')
{
    if (isset($_POST['img'])){$img = $_POST['img'];}
    $data=$query->runSQL("
	UPDATE users
    SET photo_loc = '".$img."'
    WHERE username = '".$_SESSION['user']."'
    ");
}
else if ($p == 'alterAccessKey')
{
    if (isset($_POST['id'])){$id = $_POST['id'];}
    if (isset($_POST['a_key'])){$a_key = $_POST['a_key'];}
    $data=$query->runSQL("
	UPDATE amazon_credentials
    SET aws_access_key_id = '".$a_key."'
    WHERE id = $id
    ");
}
else if ($p == 'alterSecretKey')
{
    if (isset($_POST['id'])){$id = $_POST['id'];}
    if (isset($_POST['s_key'])){$s_key = $_POST['s_key'];}
    $data=$query->runSQL("
	UPDATE amazon_credentials
    SET aws_secret_access_key = '".$s_key."'
    WHERE id = $id
    ");
}
else if ($p == 'resetWKey'){
	if (isset($_POST['id'])){$id = $_POST['id'];}
	$table=json_decode($query->queryTable("SELECT id,wrapper_pid,runworkflow_pid,wkey FROM ngs_runparams WHERE id = $id limit 1"));
	$idKey=$table[0]->id;
	$wrapper_pid=$table[0]->wrapper_pid;
	$workflow_pid=$table[0]->runworkflow_pid;
	$wkey=$table[0]->wkey;
	killPid($idKey, $query);
	$data=$query->runSQL("
	INSERT INTO ngs_wkeylist
	(run_id, wkey, wrapper_pid, workflow_pid, time_added)
	VALUES
	($idKey, '$wkey', $wrapper_pid, $workflow_pid, now())
	");
	$data = $query->runSQL("
	UPDATE ngs_runparams
	SET run_status = 6,
	wkey = ''
	WHERE id = $id
	");
}

if (!headers_sent()) {
   header('Cache-Control: no-cache, must-revalidate');
   header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
   header('Content-type: application/json');
   echo $data;
   exit;
}else{
   echo $data;
}
?>
