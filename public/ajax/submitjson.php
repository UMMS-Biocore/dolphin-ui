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
if (isset($_GET['type'])){$type = $_GET['type'];} #put,patch, post
if (isset($_GET['acc'])){$acc = $_GET['acc'];} # TSTSR990993
if (isset($_GET['object'])){$object = $_GET['object'];} # file or experiments etc.
if (isset($_GET['jsonfile'])){$jsonfile = $_GET['jsonfile'];} # file

$url = $server_start . $object . "/" . $acc . $server_end;
echo "<BR>URL:<BR>";
print_r($url);
echo "<BR>"; 

$json = file_get_contents($jsonfile);

echo "<BR>json:<BR>";
print_r($json);
echo "<BR>";

if ($type == "patch"){
  $response = Requests::patch($url, $headers, $json, $auth);
}else if ($type == "put"){
  $response = Requests::put($url, $headers, $json, $auth);
}else if ($type == "post"){
  $response = Requests::post($url, $headers, $json, $auth);
}


$body = json_decode($response->body);

echo "<BR>BODY:<BR>";
print_r($body);
echo "<BR>";                             
