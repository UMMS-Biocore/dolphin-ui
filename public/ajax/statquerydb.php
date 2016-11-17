<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");

$query = new dbfuncs();

if (isset($_GET['p'])){$p = $_GET['p'];}
if (isset($_GET['type'])){$type = $_GET['type'];}
if (isset($_GET['start'])){$start = $_GET['start'];}
if (isset($_GET['end'])){$end = $_GET['end'];}

$username=$_SESSION['user'];
$userstr=" and g.username in (select u.username from user_group ug, users u where u.id=ug.u_id and ug.g_id in ( SELECT ug.g_id from user_group ug, users u where u.id=ug.u_id and u.username='$username'))";
$userstrdol=" and u.username in (select u.username from user_group ug, users u where u.id=ug.u_id and ug.g_id in ( SELECT ug.g_id from user_group ug, users u where u.id=ug.u_id and u.username='$username'))";

if($p == "getDailyRuns")
{
   $data=$query->queryTable('
   select * from
   (select * from
   (select a.countTotal, b.countDolphin, a.day from
   (select count(id) countTotal, DATE_FORMAT(start_time, "%Y-%m-%d") day from galaxy_run g where 1=1 '.$userstr.' group by day order by day) a,
   (select count(id) countDolphin, DATE_FORMAT(start_time, "%Y-%m-%d") day from galaxy_run g where dolphin=TRUE '.$userstr.' group by day order by day) b
   where a.day=b.day order by day desc) a limit 30) a order by day asc
   ');
}
else if($p == "getTopUsers")
{
   if ($type=="Dolphin"){
      $data=$query->queryTable("
      select u.name, count(distinct j.workflow_id) count
      from jobs j, users u
      where u.clusteruser=j.username
      $userstrdol
      group by j.username
      order by count desc
      limit 20
      ");
   }else{
      $data=$query->queryTable("
      select u.name, count(g.id) count
      from galaxy_run g, users u
      where u.username=g.username 
      and dolphin=false
      $userstr
      group by g.username
      order by count desc
      limit 20
      ");
   }
}
else if($p == "getTopUsersTime")
{
   $time="";
   if ($type=="Dolphin"){
      if (isset($start)){$time="and j.`start_time`>='$start' and j.`start_time`<='$end'";}
      $data=$query->queryTable("
      select u.name, count(distinct j.workflow_id) count
      from jobs j, users u
      where u.clusteruser=j.username
      $userstrdol
      $time
      group by j.username
      order by count desc
      limit 20
      ");
   }else{
      if (isset($start)){$time="and g.`start_time`>='$start' and g.`start_time`<='$end'";}
      $data=$query->queryTable("
      select u.name, count(g.id) count
      from galaxy_run g, users u
      where u.username=g.username
      $userstr
      $time
      and dolphin=false
      group by g.username
      order by count desc
      limit 20
      ");
   }
}
else if($p == "getUsersTime")
{
   $time="";
   if ($type=="Dolphin"){
      if (isset($start)){$time="and j.`start_time`>='$start' and j.`start_time`<='$end'";}
      $data=$query->queryTable("
      select u.name, u.lab, count(distinct j.wkey) count
      from users u, jobs j
      where u.clusteruser=j.username
      $userstrdol 
      $time
      group by j.username
      order by count desc
      ");
   }else{
      if (isset($start)){$time="and g.`start_time`>='$start' and g.`start_time`<='$end'";}
      $data=$query->queryTable("
      select u.name, u.lab, count(g.id) count
      from galaxy_run g, users u
      where u.username=g.username
      and dolphin=false
      $userstr
      $time 
      group by g.username
      order by count desc
      ");
   }
}
else if($p == "getLabsTime")
{
   $time="";
   if ($type=="Dolphin"){
      if (isset($start)){$time="and j.`start_time`>='$start' and j.`start_time`<='$end'";}
      $data=$query->queryTable("
      select u.lab, count(distinct j.workflow_id) count
      from users u, jobs j
      where u.clusteruser=j.username
      $userstrdol
      $time
      group by u.lab
      order by count desc
      ");
   }else{
      if (isset($start)){$time="and g.`start_time`>='$start' and g.`start_time`<='$end'";}
      $data=$query->queryTable("
      select u.lab, count(g.id) count
      from galaxy_run g, users u
      where u.username=g.username
      and dolphin=false
      $userstr
      $time
      group by u.lab
      order by count desc
      ");
   }
}
else if($p == "getToolTime")
{
    $time="";
    if ($type=="Dolphin"){$dolphin="and dolphin=true";}else{$dolphin="and dolphin=false";}
    if (isset($start)){$time="and g.`start_time`>='$start' and g.`start_time`<='$end'";}
    $data=$query->queryTable("
    select g.tool_name, count(g.id) count
    from galaxy_run g, users u
    where 1=1
    $time
    $dolphin
    $userstr
    group by g.tool_name
    order by count desc
    ");
}
else if ($p == "getServiceTime")
{
   $time="";
   if (isset($start)){$time="and j.`start_time`>='$start' and j.`start_time`<='$end'";}
   $data=$query->queryTable("
   select s.servicename, count(j.service_id) count
   from jobs j, services s
   where j.service_id=s.service_id
   $userstrdol
   $time
   group by servicename
   order by count desc
   ");
}
else if($p == "getJobTime")
{
    $userstr=" and j.username in (select u.clusteruser from user_group ug, users u where u.id=ug.u_id and ug.g_id in ( SELECT ug.g_id from user_group ug, users u where u.id=ug.u_id and u.username='$username'))";
    $time="";
    if (isset($start)){$time="and j.`submit_time`>='$start' and j.`submit_time`<='$end'";}
    $sql="
    select s.servicename, count(j.job_id) count
    from jobs j, services s
    where j.service_id=s.service_id
    $userstr 
    $time 
    group by servicename
    order by count desc
    ";
    $data=$query->queryTable($sql);
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
