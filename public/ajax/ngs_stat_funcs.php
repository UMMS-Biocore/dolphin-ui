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

if (isset($_GET['p'])){$p = $_GET['p'];}

if ($p == "resetService")
{
	if (isset($_GET['run_id'])){$run_id = $_GET['run_id'];}
	if (isset($_GET['s_id'])){$s_id = $_GET['s_id'];}
	if (isset($_GET['wkey'])){$wkey = $_GET['wkey'];}
	if (isset($_GET['name'])){$name = $_GET['name'];}
	if (isset($_GET['type'])){$type = $_GET['type'];}
	if (isset($_GET['clusteruser'])){$clusteruser = $_GET['clusteruser'];}

	$data = $type;
	if($type == 'hard'){
		$outdir=$query->queryAVal("
		SELECT outdir
		FROM ngs_runparams
		WHERE id = $run_id
		");
		$data = json_encode($funcs->removeSuccessFile($run_id, $outdir, $name, $clusteruser));
	}
	$query->runSQL("
	DELETE FROM jobs
	WHERE wkey = '$wkey'
	AND service_id = (
		SELECT service_id
		FROM service_run
		WHERE wkey = '$wkey'
		AND service_run_id = $s_id
		)
	");
	$query->runSQL("
	DELETE FROM service_run
	WHERE wkey = '$wkey'
	AND service_run_id = $s_id
	");
}
else if ($p == 'resetJob')
{
	if (isset($_GET['run_id'])){$run_id = $_GET['run_id'];}
	if (isset($_GET['s_id'])){$s_id = $_GET['s_id'];}
	if (isset($_GET['wkey'])){$wkey = $_GET['wkey'];}
	if (isset($_GET['name'])){$name = $_GET['name'];}
	if (isset($_GET['type'])){$type = $_GET['type'];}
	if (isset($_GET['clusteruser'])){$clusteruser = $_GET['clusteruser'];}
	
	$data = $type;
	if($type == 'hard'){
		$outdir=$query->queryAVal("
		SELECT outdir
		FROM ngs_runparams
		WHERE id = $run_id
		");
		$data = json_encode($funcs->removeSuccessFile($run_id, $outdir, $name, $clusteruser));
	}
	$query->runSQL("
	DELETE FROM jobs
	WHERE wkey = '$wkey'
	AND jobname = '$name'
	");
}
else if ($p == 'getClusterUser')
{
	$data=json_encode($query->queryAVal("
	SELECT clusteruser
	FROM users
	WHERE id = '".$_SESSION['uid']."'
	"));
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