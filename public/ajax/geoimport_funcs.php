<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");

$query = new dbfuncs();

if (isset($_GET['p'])){$p = $_GET['p'];}

if ($p == 'getAccessions')
{
	if (isset($_GET['term'])){$term = $_GET['term'];}
	$cmd = "cd ../../scripts && python parse_geo.py $term";
	$COMMAND_OPEN = popen( $cmd, "r" );
	$COMMAND_READ =trim(fread($COMMAND_OPEN, 4096));
	$data = json_encode(str_replace("'", '"', $COMMAND_READ));
	pclose($COMMAND_OPEN);
	$cmd = "cd ../../scripts && rm $term*";
	$COMMAND_OPEN = popen( $cmd, "r" );
	pclose($COMMAND_OPEN);
}else if ($p == 'insertSampleGEO'){
	if (isset($_GET['experiment'])){$experiment = $_GET['experiment'];}
	if (isset($_GET['lane'])){$lane = $_GET['lane'];}
	if (isset($_GET['sample'])){$sample = $_GET['sample'];}
	if (isset($_GET['file'])){$file = $_GET['file'];}
	if (isset($_GET['barcode'])){$barcode = $_GET['barcode'];}
	if (isset($_GET['gids'])){$gids = $_GET['gids'];}
	if (isset($_GET['perms'])){$perms = $_GET['perms'];}
	$sample_check=json_decode($query->queryTable("
		SELECT id
		FROM ngs_samples
		WHERE series_id = $experiment
		AND lane_id = $lane
		AND samplename = '$sample'
		"));
	if(isset($sample_check[0]->id)){
		$data = $sample_check[0]->id;
	}else{
		$data=$query->runSQL("
		INSERT INTO ngs_samples
			(series_id, lane_id, name, samplename, title, barcode,
			owner_id, group_id, perms, date_created, date_modified, last_modified_user)
			VALUES ($experiment, $lane, '$sample', '$sample', '$file', '$barcode',
			".$_SESSION['uid'].", $gids, $perms, now(), now(), ".$_SESSION['uid'].");
		");
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