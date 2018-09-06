<?php

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");
                                        
//Metadata submission                   
include(REQUESTS.'/library/Requests.php');
Requests::register_autoloader(); 

$headers = array('Content-Type' => 'application/json', 'Accept' => 'application/json');
$server_start = ENCODE_URL;
$server_end = "/";
$auth = array('auth' => array(ENCODE_ACCESS, ENCODE_SECRET));
#$url = $server_start . $json_name . "/" . $acc . $server_end;
$acc = "ENCFF119LBW";
$url = $server_start . 'file' . "/" . $acc . $server_end;
echo "<BR>URL:<BR>";
print_r($url);
echo "<BR>"; 
$data =  array( "dataset" => "ENCSR571IUZ",
               "replicate" => "/replicates/c4834447-bcc5-4e66-9e2e-1b691feddb5e/",
                 "file_size" => 3529272,
                 "md5sum" => "a0bbd8c5ac636d18cdbf9a6d4024977a",
                 "platform" => "encode:HiSeq2000",
                 "submitted_file_name" => "rsem.out.D10_MDDC_Lps_4h.isoforms.results",
                 "lab" => "manuel-garber",
                 "award" => "U01HG007910",
                 "flowcell_details" => array ( array("machine" => "HWI-ST570") , array("flowcell" => "C5F36ACXX"), array("lane" => "6" )),
                 "output_type" => "transcript quantifications",
                 "file_format" => "tsv",
                 "assembly" => "hg19",
                 "aliases" => array ("manuel-garber:tsv_1_D10_MDDC_Lps_4h_1" ),
                 "step_run" => "manuel-garber:pipeline_1_rsem_step_run",
                 "derived_from" => array ("/files/ENCFF649ASL","/files/ENCFF550VOB/","/files/ENCFF484VOS","/files/ENCFF123SSX", "/files/ENCFF904HZM")
                 );

echo "<BR>DATA:<BR>";
print_r($data);
echo "<BR>";
$data = json_encode($data);



echo "<BR>encode:<BR>";
print_r($data);
echo "<BR>";

$response = Requests::patch($url, $headers, $data, $auth);
$body = json_decode($response->body);

echo "<BR>BODY:<BR>";
print_r($body);
echo "<BR>";                             
