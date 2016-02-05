<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");

$query = new dbfuncs();

if (isset($_GET['p'])){$p = $_GET['p'];}


if($p == "getFileList")
{
    $data=$query->queryTable("
    SELECT d.fastq_dir, f.file_name, d.amazon_bucket 
    FROM ngs_fastq_files f, ngs_dirs d 
    where d.id=f.dir_id and (f.backup_checksum='' or isnull(f.backup_checksum))
    ");
}
else if($p == "updateHashBackup")
{
   if (isset($_GET['input'])){$input = rawurldecode($_GET['input']);}
   if (isset($_GET['dirname'])){$dirname = rawurldecode($_GET['dirname']);}
   if (isset($_GET['hashstr'])){$hashstr = rawurldecode($_GET['hashstr']);}
   print $input."<br>";
   print $dirname."<br>";
   print $hashstr."<br>";
   $data=$query->queryTable("
   SET SQL_SAFE_UPDATES = 0;
   UPDATE  ngs_fastq_files nff, 
   (SELECT nff.id FROM ngs_fastq_files nff, ngs_dirs nd
   where nff.dir_id = nd.id AND 
   nff.file_name='$input' AND 
   nd.fastq_dir='$dirname') a 
   set nff.backup_checksum='$hashstr'
   where a.id=nff.id
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
