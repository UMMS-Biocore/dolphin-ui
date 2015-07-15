<?php
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");

$id = $_GET['id'];

$query = new dbfuncs();

$data=$query->queryTable('
SELECT j.jobname, jo.jobout FROM jobs j, jobsout jo
where j.wkey=jo.wkey and j.job_num=jo.jobnum and j.job_id='.$id
);


header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo json_encode($data);
exit;
