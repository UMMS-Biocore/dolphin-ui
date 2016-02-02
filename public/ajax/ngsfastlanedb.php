<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");

$query = new dbfuncs();

$data = '';

if (isset($_GET['p'])){$p = $_GET['p'];}
if (isset($_POST['p'])){$p = $_POST['p'];}


if ($p == 'experimentSeriesCheck'){
	if (isset($_GET['name'])){$name = $_GET['name'];}
	$data=$query->queryAVal("
	SELECT id
	FROM ngs_experiment_series
	WHERE experiment_name = '$name';
	");
}else if ($p == 'laneCheck'){
	if (isset($_GET['experiment'])){$experiment = $_GET['experiment'];}
	if (isset($_GET['lane'])){$lane = $_GET['lane'];}
	$data=$query->queryAVal("
	SELECT id
	FROM ngs_lanes
	WHERE series_id = '$experiment' AND name = '$lane';
	");
}else if ($p == 'sampleCheck'){
	if (isset($_GET['experiment'])){$experiment = $_GET['experiment'];}
	if (isset($_GET['lane'])){$lane = $_GET['lane'];}
	if (isset($_GET['sample'])){$sample = $_GET['sample'];}
	$data=$query->queryAVal("
	SELECT id
	FROM ngs_samples
	WHERE series_id = '$experiment' AND lane_id = '$lane' AND name = '$sample';
	");
}else if ($p == 'directoryCheck'){
	if (isset($_GET['input'])){$input = $_GET['input'];}
	if (isset($_GET['backup'])){$backup = $_GET['backup'];}
	if (isset($_GET['amazon'])){$amazon = $_GET['amazon'];}
	$data=$query->queryAVal("
	SELECT id
	FROM ngs_dirs
	WHERE fastq_dir = '$input' AND backup_dir = '$backup' AND amazon_bucket = '$amazon';
	");
}else if ($p == 'insertExperimentSeries'){
	if (isset($_POST['name'])){$name = $_POST['name'];}
	if (isset($_POST['gids'])){$gids = $_POST['gids'];}
	if (isset($_POST['perms'])){$perms = $_POST['perms'];}
	$data=$query->runSQL("
	INSERT INTO ngs_experiment_series
		(experiment_name, summary, owner_id, group_id, perms, date_created, date_modified, last_modified_user)
		VALUES ('$name', 'please create an experiment summary', ".$_SESSION['uid'].", $gids, $perms, now(), now(), ".$_SESSION['uid'].");
	");
}else if ($p == 'insertLane'){
	if (isset($_POST['experiment'])){$experiment = $_POST['experiment'];}
	if (isset($_POST['lane'])){$lane = $_POST['lane'];}
	if (isset($_POST['gids'])){$gids = $_POST['gids'];}
	if (isset($_POST['perms'])){$perms = $_POST['perms'];}
	$data=$query->runSQL("
	INSERT INTO ngs_lanes
		(series_id, name, owner_id, group_id, perms, date_created, date_modified, last_modified_user)
		VALUES ('$experiment', '$lane', ".$_SESSION['uid'].", $gids, $perms, now(), now(), ".$_SESSION['uid'].");
	");
}else if ($p == 'insertSample'){
	if (isset($_GET['experiment'])){$experiment = $_GET['experiment'];}
	if (isset($_GET['lane'])){$lane = $_GET['lane'];}
	if (isset($_GET['sample'])){$sample = $_GET['sample'];}
	if (isset($_GET['organism'])){$organism = $_GET['organism'];}
	if (isset($_GET['barcode'])){$barcode = $_GET['barcode'];}
	if (isset($_GET['gids'])){$gids = $_GET['gids'];}
	if (isset($_GET['perms'])){$perms = $_GET['perms'];}

	$data=$query->runSQL("
	INSERT INTO ngs_samples
		(series_id, lane_id, name, samplename, title, barcode,
		owner_id, group_id, perms, date_created, date_modified, last_modified_user)
		VALUES ($experiment, $lane, '$sample', '$sample', '$sample', '$barcode',
		".$_SESSION['uid'].", $gids, $perms, now(), now(), ".$_SESSION['uid'].");
	");
	
	$sample_id = $query->queryAVal("SELECT `id` FROM ngs_samples WHERE series_id = $experiment AND lane_id = $lane and name = '$sample'");
	$org_array = explode(",", $organism);
	//	Organism
	$org_check = "SELECT `id` FROM ngs_organism WHERE `organism` = '".$org_array[0]."'";
	$org_check_result = $query->queryAVal($org_check);
	
	if($org_check_result == NULL || $org_check_result == '' || $org_check_result == '0'){
		//	Empty
		$query->runSQL("INSERT INTO `ngs_organism` (`organism`, `organism_symbol`) VALUES ('".$org_array[0]."', '".$org_array[1]."')");
		$organism_id = $query->queryAVal("SELECT `id` FROM `ngs_organism` WHERE organism = '".$org_array[0]."'");
		$query->runSQL("UPDATE `ngs_samples` SET `organism_id` = ".$organism_id." WHERE `id` = $sample_id");
	}else{
		//	Organism exists
		$organism_id = $query->queryAVal("SELECT `id` FROM `ngs_organism` WHERE organism = '".$org_array[0]."'");
		$query->runSQL("UPDATE `ngs_samples` SET `organism_id` = ".$organism_id." WHERE `id` = $sample_id");
	}
	
}else if ($p == 'insertDirectories'){
	if (isset($_POST['input'])){$input = $_POST['input'];}
	if (isset($_POST['backup'])){$backup = $_POST['backup'];}
	if (isset($_POST['amazon'])){$amazon = $_POST['amazon'];}
	if (isset($_POST['gids'])){$gids = $_POST['gids'];}
	if (isset($_POST['perms'])){$perms = $_POST['perms'];}
	$data=$query->runSQL("
	INSERT INTO ngs_dirs
		(fastq_dir, backup_dir, amazon_bucket,
		owner_id, group_id, perms, date_created, date_modified, last_modified_user)
		VALUES ('$input', '$backup', '$amazon',
		".$_SESSION['uid'].", $gids, $perms, now(), now(), ".$_SESSION['uid'].");
	");
}else if ($p == 'insertTempSample'){
	if (isset($_POST['filename'])){$filename = $_POST['filename'];}
	if (isset($_POST['sample_id'])){$sample_id = $_POST['sample_id'];}
	if (isset($_POST['input'])){$input = $_POST['input'];}
	if (isset($_POST['gids'])){$gids = $_POST['gids'];}
	if (isset($_POST['perms'])){$perms = $_POST['perms'];}
	$data=$query->runSQL("
	INSERT INTO ngs_temp_sample_files
		(file_name, sample_id, dir_id,
		owner_id, group_id, perms, date_created, date_modified, last_modified_user)
		VALUES ('$filename', $sample_id, $input,
		".$_SESSION['uid'].", $gids, $perms, now(), now(), ".$_SESSION['uid'].");
	");
}else if ($p == 'insertTempLane'){
	if (isset($_POST['file_name'])){$file_name = $_POST['file_name'];}
	if (isset($_POST['lane_id'])){$lane_id = $_POST['lane_id'];}
	if (isset($_POST['dir_id'])){$dir_id = $_POST['dir_id'];}
	if (isset($_POST['gids'])){$gids = $_POST['gids'];}
	if (isset($_POST['perms'])){$perms = $_POST['perms'];}
	$data=$query->runSQL("
	INSERT INTO ngs_temp_lane_files
		(file_name, lane_id, dir_id,
		owner_id, group_id, perms, date_created, date_modified, last_modified_user)
		VALUES ('$file_name', $lane_id, $dir_id,
		".$_SESSION['uid'].", $gids, $perms, now(), now(), ".$_SESSION['uid'].");
	");
}else if ($p == 'sendProcessData'){
	if (isset($_GET['info_array'])){$info_array = $_GET['info_array'];}
	if (isset($_GET['post'])){$post = $_GET['post'];}
	
	$_SESSION[$post] = implode(",",$info_array);
}else if ($p == 'obtainGroupFromName'){
	if (isset($_GET['name'])){$name = $_GET['name'];}
	$data = $query->queryAVal("SELECT `id` FROM `groups` WHERE name = '".$name."'");
}else if ($p == 'getClusterName'){
	$data=$query->queryTable("
	SELECT username, clusteruser
	FROM users
	WHERE username = '".$_SESSION['user']."'
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