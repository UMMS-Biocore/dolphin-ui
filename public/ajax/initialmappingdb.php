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
	//	Submission variables
	$added_samples_final = array();
	$outdirs_final = array();
	$run_ids_final = array();
	//	Gather information about samples
	if (isset($_GET['names'])){$names = $_GET['names'];}
    if (isset($_GET['lane'])){$lane = $_GET['lane'];}
    if (isset($_GET['experiment'])){$experiment = $_GET['experiment'];}
    //	Merge sample names for sql query
	$names = explode(",", $names);
    $sqlnames = "";
    foreach($names as $n){
        if($n != end($names)){
            $sqlnames.= "'".$n."',";
        }else{
            $sqlnames.= "'".$n."'";    
        }
    }
	//	Query for sample ids
    $sample_id_json=json_decode($query->queryTable("
    SELECT DISTINCT ns.id
    FROM ngs_samples ns, ngs_lanes nl, ngs_experiment_series ne 
    WHERE ns.name in ($sqlnames)
    AND ns.lane_id IN (SELECT id from ngs_lanes where name in ($lane))
    AND ns.series_id IN (SELECT id from ngs_experiment_series where experiment_name = '$experiment');
    "));
	//	Create in array form
	$sample_ids = array();
	foreach($sample_id_json as $sij){
		array_push($sample_ids, $sij->id);
	}
	//	Obtain initial runs
	$initial_runs=json_decode($query->queryTable("
	SELECT id, outdir
	FROM ngs_runparams
	WHERE id IN
		(SELECT DISTINCT run_id
		FROM ngs_runlist
		WHERE sample_id in ( ".implode(",",$sample_ids)." ))
	AND (run_name = 'Fastlane Initial Run' OR run_name = 'Import Initial Run')
	"));
	//	If initial run found
	if(count($initial_runs) > 0){
		foreach($initial_runs as $ir){
			array_push($outdirs_final, $ir->outdir);
			array_push($run_ids_final, $ir->id);
			//	get samples from runlist
			$samples_initial=json_decode($query->queryTable("
			SELECT distinct sample_id
			FROM ngs_runlist
			WHERE run_id = ".$ir->id
			));
			//	Array formatting
			$old_samples = array();
			foreach($samples_initial as $si){
				array_push($old_samples, $si->sample_id);
			}
			//	Find added samples if any.
			$new_samples = array();
			foreach($sample_ids as $si){
				if(array_search($si, $old_samples) === FALSE){
					array_push($new_samples, $si);
				}
			}
			array_push($added_samples_final, $new_samples);
		}
		$data = json_encode(array($added_samples_final, $outdirs_final, $run_ids_final, $sample_ids));
	}else{
		$data = json_encode(array(array(), array(), array(), $sample_ids));
	}
	//test
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
