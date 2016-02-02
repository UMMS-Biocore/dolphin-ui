<?php
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");

$query = new dbfuncs();

$id = $_GET['id'];

$data=$query->queryTable('
Select CONCAT("id_", CAST(j.job_id AS CHAR)) id, j.job_id num, jobname title,
j.job_num, TIME_TO_SEC(timediff(j.end_time, j.start_time)) duration, 
 j.result result, submit_time submit, j.start_time start, j.end_time finish from 
jobs j, service_run sr where sr.wkey=j.wkey and sr.service_id=j.service_id and j.jobstatus=1 and sr.service_run_id='.$id.'
order by start;
');

if (!headers_sent()) {
   header('Cache-Control: no-cache, must-revalidate');
   header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
   header('Content-type: application/json');
   echo $data;
   exit;
}else{
   echo $data;
}
