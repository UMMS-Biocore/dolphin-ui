<?php
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");

$wkey = $_GET['wkey'];

$query = new dbfuncs();

$data=$query->queryTable('
SELECT CONCAT("id_", CAST(sr.service_run_id AS CHAR)) id, sr.service_run_id num,  s.servicename title,
 TIME_TO_SEC(timediff(sr.end_time, sr.start_time)) duration, sp.percentComplete, sr.start_time start, sr.end_time finish, sr.result result
  FROM workflow_run wr, service_run sr, services s,
(Select a.service_run_id, (jobFinished/jobCount)*100 percentComplete
FROM
(SELECT s.service_run_id, count(j.job_id) jobCount
FROM service_run s, workflow_run w, jobs j 
where j.wkey=w.wkey and j.wkey=s.wkey and j.wkey=s.wkey and w.wkey="'.$wkey.'"
group by s.service_run_id) a,
(SELECT s.service_run_id, count(j.job_id) jobFinished
FROM service_run s, workflow_run w, jobs j 
where j.wkey=w.wkey and j.wkey=s.wkey and j.wkey=s.wkey and w.wkey="'.$wkey.'"
group by s.service_run_id) b
where a.service_run_id=b.service_run_id) sp
  where sr.wkey=wr.wkey and s.service_id=sr.service_id and sp.service_run_id=sr.service_run_id 
  and wr.wkey="'.$wkey.'" order by sr.start_time;
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
