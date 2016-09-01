<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");
require_once("../api/funcs.php");
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$query = new dbfuncs();
$funcs = new funcs();

$p = '';
$normalized = ['facility', 'organism', 'molecule', 'lab', 'organization', 'genotype', 'library_type',
				'instrument_model', 'treatment_manufacturer', 'library_strategy', 'donor', 'biosample_term_name'];

if (isset($_GET['p'])){$p = $_GET['p'];}

if($p == 'insertDatabase')
{

	if (isset($_GET['type'])){$type = $_GET['type'];}
	if (isset($_GET['table'])){$table = $_GET['table'];}
	if (isset($_GET['value'])){$value = $_GET['value'];}
	if (isset($_GET['parent'])){$parent = $_GET['parent'];}
	if (isset($_GET['parent_id'])){$parent_id = $_GET['parent_id'];}
	if (isset($_GET['parent_child'])){$parent_child = $_GET['parent_child'];}
	
	$query->runSQL("INSERT INTO ".$table." ($type) VALUES ('$value')");
	$insert_id= json_decode($query->queryTable("SELECT id FROM ".$table." WHERE $type = '$value'"));
	$data=$query->runSQL("UPDATE $parent SET ".$parent_child." = '".$insert_id[0]->id."' WHERE id = '$parent_id'");
}
if($p == 'updateDatabase')
{

	if (isset($_GET['id'])){$id = $_GET['id'];}
	if (isset($_GET['type'])){$type = $_GET['type'];}
	if (isset($_GET['table'])){$table = $_GET['table'];}
	if (isset($_GET['value'])){$value = $_GET['value'];}
	if (isset($_GET['parent'])){$parent = $_GET['parent'];}
	if (isset($_GET['parent_id'])){$parent_id = $_GET['parent_id'];}
	if (isset($_GET['parent_child'])){$parent_child = $_GET['parent_child'];}
	
	if(in_array($type, $normalized)){
		$type_list = json_decode($query->queryTable("SELECT id FROM ".$table." WHERE $type = '$value'"));
		if($type_list != array()){
			$data=$query->runSQL("UPDATE $table SET ".$type."_id = ".$type_list[0]->id." WHERE id = '$id'"); 	
		}else{
			$query->runSQL("INSERT INTO ngs_".$type." ($type) VALUES ('$value')");
			$insert_id= json_decode($query->queryTable("SELECT id FROM ngs_".$type." WHERE $type = '$value'"));
			$data=$query->runSQL("UPDATE $table SET ".$type."_id = '".$insert_id[0]->id."' WHERE id = '$id'");
		}	
	}else{
		$data=$query->runSQL("UPDATE $table SET $table.$type = '$value' WHERE id = '$id'");
	}
}
if($p == 'updateDatabaseEncode')
{

	if (isset($_GET['id'])){$id = $_GET['id'];}
	if (isset($_GET['type'])){$type = $_GET['type'];}
	if (isset($_GET['table'])){$table = $_GET['table'];}
	if (isset($_GET['value'])){$value = $_GET['value'];}
	if (isset($_GET['parent'])){$parent = $_GET['parent'];}
	if (isset($_GET['parent_id'])){$parent_id = $_GET['parent_id'];}
	if (isset($_GET['parent_child'])){$parent_child = $_GET['parent_child'];}
	
	if(in_array($type, $normalized)){
		$type_list = json_decode($query->queryTable("SELECT id FROM ".$table." WHERE $type = '$value'"));
		if($type_list != array()){
			$data=$query->runSQL("UPDATE $parent SET $parent_child = ".$type_list[0]->id." WHERE id = $parent_id"); 	
		}else{
			$query->runSQL("INSERT INTO ".$table." ($type) VALUES ('$value')");
			$insert_id= json_decode($query->queryTable("SELECT id FROM ".$table." WHERE $type = '$value'"));
			$data=$query->runSQL("UPDATE $parent SET ".$parent_child." = '".$insert_id[0]->id."' WHERE id = $parent_id");
		}
	}else{
		$data=$query->runSQL("UPDATE $table SET $table.$type = '$value' WHERE id = '$id'");
	}
}
else if($p == 'checkPerms')
{
	if (isset($_GET['id'])){$id = $_GET['id'];}
	if (isset($_GET['uid'])){$uid = $_GET['uid'];}
	if (isset($_GET['table'])){$table = $_GET['table'];}
	
	$group_id = json_decode($query->queryTable("SELECT group_id, owner_id FROM $table WHERE id = $id"));
	$user_pass = json_decode($query->queryTable("SELECT u_id FROM user_group WHERE g_id = ".$group_id[0]->group_id." and u_id = $uid"));
	if($user_pass[0]->u_id == $uid || $_SESSION['uid'] == 1){
		$data = 1;
	}else if ($group_id[0]->owner_id == $uid){
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
else if($p == 'getDropdownValuesPerms')
{
	if (isset($_GET['type'])){$type = $_GET['type'];}
	if (isset($_GET['table'])){$table = $_GET['table'];}
	if (isset($_GET['gids'])){$gids = $_GET['gids'];}
	$data=$query->queryTable("SELECT $type FROM $table WHERE (((group_id in ($gids)) AND (perms >= 15)) OR (owner_id = ".$_SESSION['uid'].") OR (perms >= 32))");
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
	
	//	Deleted_Samples table
	$query->runSQL("INSERT INTO ngs_deleted_samples (sample_id, samplename, lane_id, experiment_series_id, user_delete, date)
					SELECT id, samplename, lane_id, series_id, ".$_SESSION['uid'].", NOW()
					from ngs_samples
					where id in ($samples)
				   ");
	
	//	RUN IDS
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
	
	$clusteruser = json_decode($query->queryTable("SELECT clusteruser FROM users WHERE id = '".$_SESSION['uid']."'"));
	$samplenames = json_decode($query->queryTable("SELECT samplename FROM ngs_samples WHERE id in ($samples)"));
	$samplename_array = array();
	foreach($samplenames as $sn){
		array_push($samplename_array, $sn->samplename);
	}
	//	REMOVE SUCCESS FILES
	foreach ($all_run_ids as $ari){
		$outdir=json_decode($query->queryTable("SELECT outdir FROM ngs_runparams WHERE id = $ari"));
		$data = $funcs->removeAllSampleSuccessFiles($outdir[0]->outdir, $samplename_array, $clusteruser[0]->clusteruser);
	}
	
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
	$insert_query = "
	INSERT INTO ngs_deleted_runs
	(run_id, outdir, run_status, json_parameters,
	run_name, run_description, owner_id, group_id, perms,
	last_modified_user)
	SELECT id, outdir, run_status, json_parameters,
	run_name, run_description, owner_id, group_id, perms,
	last_modified_user
	FROM ngs_runparams WHERE id IN (".implode(",", $all_run_ids).")";
	$query->runSQL($insert_query);
	
	//	If sample is deleted, delete all run information
	//$query->runSQL("DELETE FROM ngs_runlist WHERE run_id IN (".implode(",", $all_run_ids).")");
	//$query->runSQL("DELETE FROM ngs_runparams WHERE id IN (".implode(",", $all_run_ids).")");
	$data = '';
}
else if ($p == 'intialRunCheck')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->queryTable("SELECT sample_id FROM ngs_fastq_files WHERE sample_id IN ($samples) AND total_reads > 0");
}
else if ($p == 'amazon_reupload')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->runSQL("
		UPDATE ngs_fastq_files
		SET aws_status = 2
		WHERE sample_id in ($samples)
		");
	$cmd = "cd ../../scripts && python aws_submit.py -c ".CONFIG." $samples  ".DOLPHIN_TOOLS_SRC_PATH." 2>&1 > ../tmp/logs/aws_uploads/".date("Y_m_d_H_i").".log &";
	$PID_COMMAND = popen( $cmd, "r" );
	pclose($PID_COMMAND);
	$data = json_encode($cmd);
}
else if ($p == "checksum_recheck")
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$cmd = "cd ../../scripts && mkdir -p ../tmp/logs/checksum_recheck && python md5sum_check.py ".BASE_PATH." $samples  2>&1 > ../tmp/logs/checksum_recheck/".date("Y_m_d_H_i").".log &";
	$PID_COMMAND = popen( $cmd, "r" );
	pclose($PID_COMMAND);
	$data = json_encode($cmd);
}
else if($p == 'encodeSampleEdit')
{
	$table_list = ['ngs_molecule','ngs_organism','ngs_source','ngs_donor','ngs_library_type','ngs_instrument_model','ngs_biosample_term','ngs_library_strategy'];
	$table_sample_link = ['molecule_id','organism_id','source_id','donor_id','library_type_id','instrument_model_id','biosample_term_id','library_strategy_id'];
	$experiment_series = ['grant'];
	$lanes = ['date_submitted','date_received'];
	$protocols = ['extraction', 'fragmentation_method'];
	$lab = ['lab'];
	$treatment = ['treatment_term_name','treatment_term_id','treatment_type','concentration','concentration_units','duration'.'duration_units'];
	$antibody_target = ['target','source','product_id','lot_id','host_organism','clonality','isotype','purifications','url'];
	if (isset($_GET['table'])){$table = $_GET['table'];}
	if (isset($_GET['field'])){$field = $_GET['field'];}
	if (isset($_GET['sample_id'])){$sample_id = $_GET['sample_id'];}
	if($table == 'ngs_samples'){
		$data = $query->runSQL("
			UPDATE encode_submissions
			SET sub_status = 'Needs Resubmission'
			WHERE sample_id = $sample_id
		");
	}else if (in_array($table,$table_list)){
		$field = $table_sample_link[array_search($table,$table_array)];
		$data = $query->runSQL("
			UPDATE encode_submissions
			SET sub_status = 'Needs Resubmission'
			WHERE sample_id in (
				SELECT id
				FROM ngs_samples
				WHERE $field = $sample_id
			)
		");
		$data = json_decode($field);
	}else if ($table == "ngs_experiment_series"){
		if(in_array($field,$experiment_series)){
			$data = $query->runSQL("
				UPDATE encode_submissions
				SET sub_status = 'Needs Resubmission'
				WHERE sample_id in (
					SELECT id
					FROM ngs_samples
					WHERE series_id = $sample_id
				)
			");
		}
	}else if ($table == "ngs_lanes"){
		if(in_array($field,$lanes)){
			$data = $query->runSQL("
				UPDATE encode_submissions
				SET sub_status = 'Needs Resubmission'
				WHERE sample_id in (
					SELECT id
					FROM ngs_samples
					WHERE lane_id = $sample_id
				)
			");
		}
	}else if ($table == "ngs_protocols"){
		if(in_array($field,$protocols)){
			$data = $query->runSQL("
				UPDATE encode_submissions
				SET sub_status = 'Needs Resubmission'
				WHERE sample_id in (
					SELECT id
					FROM ngs_samples
					WHERE protocol_id = $sample_id
				)
			");
		}
	}else if ($table == "ngs_lab"){
		if(in_array($field,$lab)){
			$data = $query->runSQL("
				UPDATE encode_submissions
				SET sub_status = 'Needs Resubmission'
				WHERE sample_id in (
					SELECT id
					FROM ngs_samples
					WHERE series_id in (
						SELECT lab_id
						FROM ngs_experiment_series
						WHERE lab_id = $sample_id
					)
				)
			");
		}
	}else if ($table == "ngs_treatment"){
		if(in_array($field,$treatment)){
			$data = $query->runSQL("
				UPDATE encode_submissions
				SET sub_status = 'Needs Resubmission'
				WHERE sample_id in (
					SELECT id
					FROM ngs_samples
					WHERE treatment_id = $sample_id
				)
			");
		}
	}else if ($table == "ngs_antibody_target"){
		if(in_array($field,$antibody_target)){
			$data = $query->runSQL("
				UPDATE encode_submissions
				SET sub_status = 'Needs Resubmission'
				WHERE sample_id in (
					SELECT id
					FROM ngs_samples
					WHERE antibody_lot_id = $sample_id
				)
			");
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
