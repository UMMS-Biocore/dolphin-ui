<?php
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");

if (isset($_GET['p'])){$p = $_GET['p'];}
if (isset($_GET['run_id'])){$id = $_GET['run_id'];}

$query = new dbfuncs();

if($p == 'getStdOut'){
	$data = json_encode(array_slice(str_replace("\n", "<br>", file('../../tmp/logs/run'.$id.'/run.'.$id.'.wrapper.std')), -21));
}else if($p == 'checkQueued'){
	$data = [];
	$pids = json_decode($query->queryTable("
	SELECT wrapper_pid, runworkflow_pid, wkey
	FROM ngs_runparams
	WHERE id = $id
	"));
	
	$workflow_pid = $pids[0]->runworkflow_pid;
	$wrapper_pid = $pids[0]->wrapper_pid;
	
	if($workflow_pid != null){
		$grep_find_workflow = popen( "ps -ef | grep '[".substr($workflow_pid, 0, 1)."]".substr($workflow_pid,1)."'", "r" );
		$workflow = fread($grep_find_workflow, 2096);
		pclose($grep_find_workflow);
	}else{
		$workflow = null;
	}
	if($wrapper_pid != null){
		$grep_find_wrapper = popen( "ps -ef | grep '[".substr($wrapper_pid, 0, 1)."]".substr($wrapper_pid,1)."'", "r" );
		$wrapper = fread($grep_find_wrapper, 2096);
		pclose($grep_find_wrapper);
	}else{
		$wrapper = null;
	}
	
	if($workflow != ""){
		array_push($data, '1');
	}else{
		array_push($data, '0');
	}
	
	if($wrapper != ""){
		array_push($data, '1');
	}else{
		array_push($data, '0');
	}

	array_push($data, $pids[0]->wkey);
	$data = json_encode($data);
}else if ($p == 'errorCheck'){
	$pids = json_decode($query->queryTable("
	SELECT wrapper_pid, runworkflow_pid, wkey, run_status
	FROM ngs_runparams
	WHERE id = $id
	"));
	
	if($pids[0]->run_status != '1'){
		$workflow_pid = $pids[0]->runworkflow_pid;
		$wrapper_pid = $pids[0]->wrapper_pid;
		
		if($workflow_pid != null){
			$grep_find_workflow = popen( "ps -ef | grep '[".substr($workflow_pid, 0, 1)."]".substr($workflow_pid,1)."'", "r" );
			$workflow = fread($grep_find_workflow, 2096);
			pclose($grep_find_workflow);
		}else{
			$workflow = "null";
		}
		if($wrapper_pid != null){
			$grep_find_wrapper = popen( "ps -ef | grep '[".substr($wrapper_pid, 0, 1)."]".substr($wrapper_pid,1)."'", "r" );
			$wrapper = fread($grep_find_wrapper, 2096);
			pclose($grep_find_wrapper);
		}else{
			$wrapper = "null";
		}
	}
	
	if(($workflow == "") || ($wrapper == "")){
		$query->runSQL("
		update ngs_runparams
		set run_status = 3
		where id = $id
		");
		$data = '3';
	}else{
		$data = $pids[0]->run_status;
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