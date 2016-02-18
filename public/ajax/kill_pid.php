<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$query = new dbfuncs();

if (isset($_GET['p'])){$p = $_GET['p'];}

if($p == 'killRun')
{
	$setStatus = '';
	if (isset($_GET['run_id'])){$run_id = $_GET['run_id'];}
	$pids = json_decode($query->queryTable("SELECT wrapper_pid, runworkflow_pid
							   FROM ngs_runparams
							   WHERE id = $run_id"));
	if($pids[0]->runworkflow_pid != NULL){
		$workflow_pid = $pids[0]->runworkflow_pid;
		$grep_check_workflow = "ps -ef | grep '[".substr($workflow_pid, 0, 1)."]".substr($workflow_pid,1)."'";
		$grep_find_workflow = pclose(popen( $grep_check_workflow, "r" ) );
		if($grep_find_workflow > 0 && $grep_find_workflow != NULL){
			pclose(popen( "kill -9 $workflow_pid", "r" ) );
			$setStatus = "killed";
		}
	}
	
	if($pids[0]->wrapper_pid != NULL){
		$wrapper_pid = $pids[0]->wrapper_pid;
		$grep_check_wrapper = "ps -ef | grep '[".substr($wrapper_pid, 0, 1)."]".substr($wrapper_pid,1)."'";
		$grep_find_wrapper = pclose(popen( $grep_check_wrapper, "r" ) );
		if($grep_find_wrapper > 0 && $grep_find_wrapper != NULL){
			pclose(popen( "kill -9 $wrapper_pid", "r" ) );
			$setStatus = "killed";
		}
	}
	
	if($setStatus == "killed"){
		$data = $query->runSQL("UPDATE ngs_runparams
							   SET run_status = 4
							   WHERE id = $run_id");
	}
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