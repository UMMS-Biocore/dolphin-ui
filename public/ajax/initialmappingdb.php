<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$query = new dbfuncs();
$data = "";

if (isset($_GET['p'])){$p = $_GET['p'];}

if ($p == 'sampleChecking')
{
	if (isset($_SESSION['uid'])){$uid = $_SESSION['uid'];}
	if (isset($_GET['gids'])){$gids = $_GET['gids'];}
	if($uid != "" && $gids != ""){
	    $andPerms = "AND (((group_id in ($gids)) AND (perms >= 15)) OR (owner_id = $uid))";
	}
	$data=$query->queryTable("
	SELECT id
	FROM ngs_fastq_files
	WHERE total_reads > 0 $andPerms
	");
}
else if ($p == 'laneChecking')
{
	if (isset($_GET['uid'])){$uid = $_GET['uid'];}
	if (isset($_GET['gids'])){$gids = $_GET['gids'];}
	if($uid != "" && $gids != ""){
	    $andPerms = "AND (((group_id in ($gids)) AND (perms >= 15)) OR (owner_id = $uid))";
	}
	$data=$query->queryTable("
	SELECT 	DISTINCT lane_id
	FROM ngs_fastq_files
	WHERE total_reads > 0 $andPerms
	");
}
else if ($p == 'laneToSampleChecking')
{
	if (isset($_GET['sample_ids'])){$sample_ids = $_GET['sample_ids'];}
	$data=$query->queryTable("
	SELECT sample_id
	FROM ngs_fastq_files
	WHERE total_reads > 0
	AND sample_id in ($sample_ids)
	");
}
else if ($p == 'getCounts')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->queryTable("
	SELECT total_reads
	FROM ngs_fastq_files
	WHERE sample_id in ( $samples )
	");
}
else if ($p == 'checkRunList')
{
	if (isset($_GET['sample_ids'])){$sample_ids = $_GET['sample_ids'];}
	if (isset($_GET['run_id'])){$run_id = $_GET['run_id'];}
	$data=$query->queryTable("
	SELECT DISTINCT run_id
	FROM ngs_runlist
	WHERE sample_id in ( $sample_ids )
	");
}
else if($p == 'checkRunParams')
{
	if (isset($_GET['run_ids'])){$run_ids = $_GET['run_ids'];}
	$data=$query->queryTable("
	SELECT id
	FROM ngs_runparams
	WHERE id IN ($run_ids)
	AND (run_name = 'Fastlane Initial Run' OR run_name = 'Import Initial Run')
	");
}
else if ($p == 'checkRunToSamples')
{
	if (isset($_GET['run_id'])){$run_id = $_GET['run_id'];}
	$data=$query->queryTable("
	SELECT distinct sample_id
	FROM ngs_runlist
	WHERE run_id = '$run_id'
	");
}
else if ($p == 'checkFileToSamples')
{
	if (isset($_GET['run_id'])){$run_id = $_GET['run_id'];}
	if (isset($_GET['file_name'])){$file_name = $_GET['file_name'];}
	$data=$query->queryTable("
	SELECT distinct file_name
	FROM ngs_fastq_files
	WHERE file_name = '$file_name'
	");
}
else if ($p == 'removeRunlistSamples')
{
	if (isset($_GET['run_id'])){$run_id = $_GET['run_id'];}
	if (isset($_GET['sample_ids'])){$sample_ids = $_GET['sample_ids'];}
	$sample_ids_array = explode(",",$sample_ids);
	$ids=json_decode($query->queryTable("
	SELECT sample_id
	FROM ngs_runlist
	WHERE run_id = $run_id
	"));
	foreach($ids as $i){
		if(!in_array(strval($i->sample_id), $sample_ids_array)){
			$query->runSQL("
			DELETE FROM ngs_runlist
			WHERE run_id = $run_id
			AND sample_id = ".$i->sample_id
			);
			$query->runSQL("
			DELETE FROM ngs_samples
			WHERE id = ".$i->sample_id
			);
			$query->runSQL("
			DELETE FROM ngs_fastq_files
			WHERE sample_id = ".$i->sample_id
			);
		}
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
