<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$query = new dbfuncs();

$p = '';
$normalized = ['facility', 'source', 'organism', 'molecule', 'lab', 'organization', 'genotype', 'library_type',
				  'biosample_type', 'instrument_model', 'treatment_manufacturer'];

if (isset($_GET['p'])){$p = $_GET['p'];}

if($p == 'updateDatabase')
{

	if (isset($_GET['id'])){$id = $_GET['id'];}
	if (isset($_GET['type'])){$type = $_GET['type'];}
	if (isset($_GET['table'])){$table = $_GET['table'];}
	if (isset($_GET['value'])){$value = $_GET['value'];}
	
	if(in_array($type, $normalized)){
		$type_list = json_decode($query->queryTable("SELECT id FROM ngs_".$type." WHERE $type = '$value'"));
		if($type_list != array()){
			$data=$query->runSQL("UPDATE $table SET ".$type."_id = ".$type_list[0]->id." WHERE id = $id"); 	
		}else{
			$query->runSQL("INSERT INTO ngs_".$type." ($type) VALUES ('$value')");
			$insert_id= json_decode($query->queryTable("SELECT id FROM ngs_".$type." WHERE $type = '$value'"));
			$data=$query->runSQL("UPDATE $table SET ".$type."_id = '".$insert_id[0]->id."' WHERE id = $id");
		}	
	}else{
		$data=$query->runSQL("UPDATE $table SET ".$table.".".$type." = '$value' WHERE id = $id");
	}
}
else if($p == 'checkPerms')
{
	if (isset($_GET['id'])){$id = $_GET['id'];}
	if (isset($_GET['uid'])){$uid = $_GET['uid'];}
	if (isset($_GET['table'])){$table = $_GET['table'];}
	
	$owner_id = json_decode($query->queryTable("SELECT owner_id FROM $table WHERE id = $id"));
	if($owner_id[0]->owner_id == $uid || $_SESSION['uid'] == 1){
		$data = 1;
	}else{
		$data = 0;
	}
}
else if($p == 'getDropdownValues')
{
	if (isset($_GET['type'])){$type = $_GET['type'];}
	$data=$query->queryTable("SELECT $type FROM ngs_".$type);
}
else if ($p == 'getExperimentPermissions')
{
	if (isset($_GET['experiments'])){$experiments = $_GET['experiments'];}
	$data=$query->queryTable("SELECT id FROM ngs_experiment_series WHERE id IN ($experiments) AND (owner_id = ".$_SESSION['uid']." OR 1 = ".$_SESSION['uid'].")");
}
else if($p == 'getLanePermissions')
{
	if (isset($_GET['lanes'])){$lanes = $_GET['lanes'];}
	$data=$query->queryTable("SELECT id FROM ngs_lanes WHERE id IN ($lanes) AND (owner_id = ".$_SESSION['uid']." OR 1 = ".$_SESSION['uid'].")");
}
else if($p == 'getSamplePermissions')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->queryTable("SELECT id FROM ngs_samples WHERE id IN ($samples) AND (owner_id = ".$_SESSION['uid']." OR 1 = ".$_SESSION['uid'].")");
}
else if($p == 'deleteSelected')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	if (isset($_GET['lanes'])){$lanes = $_GET['lanes'];}
	if (isset($_GET['experiments'])){$experiments = $_GET['experiments'];}
	
	//	EXPERIMENT SERIES
	if ($experiments != ""){
		$query->runSQL("DELETE FROM ngs_experiment_series WHERE id IN ($experiments)");
	}
	
	//	LANES
	$query->runSQL("DELETE FROM ngs_temp_lane_files WHERE lane_id IN ($lanes)");
	$query->runSQL("DELETE FROM ngs_fastq_files WHERE lane_id IN ($lanes)");
	$query->runSQL("DELETE FROM ngs_temp_sample_files WHERE sample_id IN (SELECT id FROM ngs_samples WHERE lane_id IN ($lanes)");
	$query->runSQL("DELETE FROM ngs_sample_conds WHERE sample_id IN (SELECT id FROM ngs_samples WHERE lane_id IN ($lanes))");
	$query->runSQL("DELETE FROM ngs_samples WHERE lane_id IN ($lanes)");
	$query->runSQL("DELETE FROM ngs_lanes WHERE id IN ($lanes)");
	
	//	SAMPLES
	$query->runSQL("DELETE FROM ngs_temp_sample_files WHERE sample_id IN ($samples)");
	$query->runSQL("DELETE FROM ngs_sample_conds WHERE sample_id IN ($samples)");
	$query->runSQL("DELETE FROM ngs_fastq_files WHERE sample_id IN ($samples)");
	$query->runSQL("DELETE FROM ngs_samples WHERE id IN ($samples)");
	
	//	WKEY
	$sample_run_ids=json_decode($query->queryTable("SELECT DISTINCT run_id FROM ngs_runlist WHERE sample_id IN ($samples)"));
	$lane_run_ids=json_decode($query->queryTable("SELECT DISTINCT run_id FROM ngs_runlist WHERE sample_id IN (SELECT id from ngs_samples WHERE lane_id in ($lanes))"));
	
	$all_run_ids = array();
	foreach($sample_run_ids as $sri){
		if(!in_array($sri->run_id, $all_run_ids)){
			array_push($all_run_ids, $sri->run_id);
		}
	}
	foreach($lane_run_ids as $lri){
		if(!in_array($lri->run_id, $all_run_ids)){
			array_push($all_run_ids, $lri->run_id);
		}
	}
	//	OBTAIN WKEY INFORMATION FOR REPORT_LIST REMOVAL //
	
	$wkeys = array();
	$wkeys_json = json_decode($query->queryTable("SELECT wkey FROM ngs_runparams WHERE id IN (".implode(",", $all_run_ids).")"));
	foreach($wkeys_json as $wj){
		if(!in_array($wj->wkey, $wkeys) && $wj->wkey != NULL && $wj->wkey != ''){
			array_push($wkeys, $wj->wkey);
		}
	}
	
	//	USE WKEY FOR REPORT_LIST REMOVAL	//
	foreach($wkeys as $w){
		$query->runSQL("DELETE FROM report_list WHERE wkey = '$w'");
		$query->runSQL("DELETE FROM ngs_wkeylist WHERE wkey = '$w'");
	}
	
	//	OBTAIN PID IF RUNNING AND REMOVE	//
	//	Check to make sure this is nessicary	//
	
	$workflow_pids = json_decode($query->queryTable("SELECT runworkflow_pid FROM ngs_runparams WHERE run_id IN (".implode(",", $all_run_ids).")"));
	$wrapper_pids = json_decode($query->queryTable("SELECT wrapper_pid FROM ngs_runparams WHERE run_id IN (".implode(",", $all_run_ids).")"));
	
	foreach($workflow_pids as $wp){
		$cmd = "ps -ef | grep '[".substr($wp->runworkflow_pid, 0, 1)."]".substr($wp->runworkflow_pid, 1)."'";
		$pid_check = pclose(popen( $cmd, "r" ) );
		if($pid_check > 0 && $pid_check != NULL){
			pclose(popen( "kill -9 ".$wp->runworkflow_pid, "r" ) );
		}
	}
	foreach($wrapper_pids as $wp){
		$cmd = "ps -ef | grep '[".substr($wp->wrapper_pid, 0, 1)."]".substr($wp->wrapper_pid, 1)."'";
		$pid_check = pclose(popen( $cmd, "r" ) );
		if($pid_check > 0 && $pid_check != NULL){
			pclose(popen( "kill -9 ".$wp->wrapper_pid, "r" ) );
		}
	}
	
	//	RUNS
	//	$query->runSQL("UPDATE ngs_runparams SET run_status = 5 WHERE id IN (".implode(",", $all_run_ids).")");
	//	For now, status is set to 5 to hide runs that no longer have proper imports/samples
	
	//	If sample is deleted, delete all run information
	$query->runSQL("DELETE FROM ngs_runlist WHERE run_id IN (".implode(",", $all_run_ids).")");
	$query->runSQL("DELETE FROM ngs_runparams WHERE id IN (".implode(",", $all_run_ids).")");
	$data = '';
}
else if ($p == 'intialRunCheck')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->queryTable("SELECT sample_id FROM ngs_fastq_files WHERE sample_id IN ($samples) AND total_reads > 0");
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
