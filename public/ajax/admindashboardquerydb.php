<?php
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");

$query = new dbfuncs();

if (isset($_GET['p'])){$p = $_GET['p'];}
if (isset($_GET['type'])){$type = $_GET['type'];}
if (isset($_GET['start'])){$start = $_GET['start'];}
if (isset($_GET['end'])){$end = $_GET['end'];}

if ($p == "getMonthlyRuns")
{
    $data=$query->queryTable('
    select a.countTotal countGalaxy,a.countTotal, b.countDolphin, a.month from
    (select count(id) countTotal, DATE_FORMAT(start_time, "%Y-%m") month from galaxy_run group by month order by month) a,
    (select count(workflow_run_id) countDolphin, DATE_FORMAT(start_time, "%Y-%m") month 
    from workflow_run group by month order by month) b
    where a.month=b.month order by month
    ');
}
else if($p == "getDailyRuns")
{
   $data=$query->queryTable('
   select * from
   (select * from
   (select a.countTotal, b.countDolphin, a.day from
   (select count(id) countTotal, DATE_FORMAT(start_time, "%Y-%m-%d") day from galaxy_run group by day order by day) a,
   (select count(workflow_run_id) countDolphin, DATE_FORMAT(start_time, "%Y-%m") month 
    from workflow_run group by month order by month) b
   where a.day=b.day order by day desc) a limit 30) a order by day asc
   ');
}
else if($p == "getMonthlyJobs")
{
    $data=$query->queryTable('
    select a.countSucess, b.countFailed, a.month from
    (select count(job_id) countSucess, DATE_FORMAT(submit_time, "%Y-%m") month from jobs where result=3 group by month order by month) a,
    (select count(job_id) countFailed, DATE_FORMAT(submit_time, "%Y-%m") month from jobs where result<3 group by month order by month) b
    where a.month=b.month order by month
    ');
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
