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
$url = $server_start . 'file' . $server_end;

echo "<BR>URL:<BR>";
print_r($url);
echo "<BR>";

$data =  array( "dataset" => "ENCSR536GUD",
                 "replicate" => "/replicates/fd789497-7b9d-486d-a376-aa423cbb6c8c/",
                 "file_size" => 1505194079,
                 "md5sum" => "030d1ad82155f9779abd08c9ef9c8bb6",
                 "platform" => "encode:HiSeq2000",
                 "submitted_file_name" => "D01_MDDC_Lps_4h.bam",
                 "lab" => "manuel-garber",
                 "award" => "U01HG007910",
                 "flowcell_details" => array ( array("machine" => "HWI-ST570") , array("flowcell" => "C5F36ACXX"), array("lane" => "2" )),
                 "output_type" => "alignments",
                 "file_format" => "bam",
                 "assembly" => "hg19",
                 "aliases" => array ("manuel-garber:bam_D01_MDDC_Lps_4h_2" ),
                 "step_run" => "manuel-garber:pipeline_1_mapping_step_run",
                 "derived_from" => array ("/files/ENCFF816SSU/","/files/ENCFF912WRB/","/files/ENCFF550VOB/","/files/ENCFF484VOS","/files/ENCFF123SSX")
                 );



echo "<BR>DATA:<BR>";
print_r($data);
echo "<BR>";   
$data = json_encode($data);
echo "<BR>encode:<BR>";
print_r($data);
echo "<BR>";

$response = Requests::post($url, $headers, $data, $auth);
$body = json_decode($response->body);

echo "<BR>BODY:<BR>";
print_r($body);
echo "<BR>";                             
