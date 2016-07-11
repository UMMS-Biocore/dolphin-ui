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
     SELECT d.fastq_dir, f.file_name, d.amazon_bucket, ac.aws_access_key_id ak, ac.aws_secret_access_key sk
     FROM ngs_fastq_files f, ngs_dirs d, group_amazon ga, amazon_credentials ac
     where d.id=f.dir_id and d.amazon_bucket!='' and
     d.amazon_bucket != 'none' and ga.group_id=d.group_id and ac.id=ga.amazon_id and
     (f.backup_checksum='' or isnull(f.backup_checksum) or DATE(f.date_modified) <= DATE(NOW() - INTERVAL 2 MONTH))
    ");
}
else if($p == "updateHashBackup")
{
   if (isset($_GET['input'])){$input = rawurldecode($_GET['input']);}
   if (isset($_GET['dirname'])){$dirname = rawurldecode($_GET['dirname']);}
   if (isset($_GET['hashstr'])){$hashstr = rawurldecode($_GET['hashstr']);}
   $data=$query->runSQL("
   UPDATE  ngs_fastq_files nff, 
   (SELECT nff.id FROM ngs_fastq_files nff, ngs_dirs nd
   where nff.dir_id = nd.id AND 
   nff.file_name='$input' AND 
   nd.fastq_dir='$dirname') a 
   set nff.backup_checksum='$hashstr',
   date_modified=NOW()
   where a.id=nff.id
   ");
}
else if ($p == "getSelectedFileList")
{
    if (isset($_GET['samples'])){$samples = rawurldecode($_GET['samples']);}
    $data=$query->queryTable("
    SELECT d.backup_dir, f.file_name
    FROM ngs_fastq_files f, ngs_dirs d 
    where d.id=f.dir_id and f.sample_id in ($samples)
    ");
}
else if ($p == "updateRecheckChecksum")
{
    if (isset($_GET['input'])){$input = rawurldecode($_GET['input']);}
    if (isset($_GET['dirname'])){$dirname = rawurldecode($_GET['dirname']);}
    if (isset($_GET['hashstr'])){$hashstr = rawurldecode($_GET['hashstr']);}
    $data=$query->queryTable("
    UPDATE  ngs_fastq_files nff, 
    (SELECT nff.id FROM ngs_fastq_files nff, ngs_dirs nd
    where nff.dir_id = nd.id AND 
    nff.file_name='$input' AND 
    nd.backup_dir='$dirname') a 
    set nff.checksum_recheck='$hashstr'
    where a.id=nff.id
    ");
    $data=$query->queryTable("
    SELECT nff.id FROM ngs_fastq_files nff, ngs_dirs nd
    where nff.dir_id = nd.id AND 
    nff.file_name='$input' AND 
    nd.backup_dir='$dirname'
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
