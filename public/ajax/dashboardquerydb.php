<?php
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");

$query = new dbfuncs();

$username=$_SESSION['user'];
if (isset($_GET['p'])){$p = $_GET['p'];}
if (isset($_GET['type'])){$type = $_GET['type'];}
if (isset($_GET['start'])){$start = $_GET['start'];}
if (isset($_GET['end'])){$end = $_GET['end'];}

#For individual user
#$userstr=" and username='$username'"; 
#For the groups the user belong to
$userstr=" and username in (select u.username from user_group ug, users u where u.id=ug.u_id and ug.g_id in ( SELECT ug.g_id from user_group ug, users u where u.id=ug.u_id and u.username='$username'))";

if ($p == "getMonthlyRuns")
{

    $data=$query->queryTable('
    select (a.countTotal-b.countDolphin) countGalaxy,a.countTotal, b.countDolphin, a.month from
    (select count(id) countTotal, DATE_FORMAT(start_time, "%Y-%m") month from galaxy_run where 1=1 '.$userstr.' group by month order by month) a,
    (select count(id) countDolphin, DATE_FORMAT(start_time, "%Y-%m") month from galaxy_run where dolphin=TRUE '.$userstr.' group by month order by month) b
    where a.month=b.month order by month
    ');
}
else if($p == "getDailyRuns")
{
   $data=$query->queryTable('
   select * from
   (select * from
   (select a.countTotal, b.countDolphin, a.day from
   (select count(id) countTotal, DATE_FORMAT(start_time, "%Y-%m-%d") day from galaxy_run where 1=1 '.$userstr.' group by day order by day) a,
   (select count(id) countDolphin, DATE_FORMAT(start_time, "%Y-%m-%d") day from galaxy_run where dolphin=TRUE '.$userstr.' group by day order by day) b
   where a.day=b.day order by day desc) a limit 30) a order by day asc
   ');
}
else if($p == "getMonthlyJobs")
{
    $userstr=" and username in (select u.clusteruser from user_group ug, users u where u.id=ug.u_id and ug.g_id in ( SELECT ug.g_id from user_group ug, users u where u.id=ug.u_id and u.username='$username'))";


    $data=$query->queryTable('
    select a.countSucess, b.countFailed, a.month from
    (select count(job_id) countSucess, DATE_FORMAT(submit_time, "%Y-%m") month from jobs where result=3 '.$userstr.' group by month order by month) a,
    (select count(job_id) countFailed, DATE_FORMAT(submit_time, "%Y-%m") month from jobs where result<3 '.$userstr.' group by month order by month) b
    where a.month=b.month order by month
    ');
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo $data;
exit;

?>
