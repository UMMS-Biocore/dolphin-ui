<?php
# PATCH an object to an ENCODE server

if (isset($_GET['json_name'])){$json_name = $_GET['json_name'];}
if (isset($_GET['json_passed'])){$json_passed = $_GET['json_passed'];}
if (isset($_GET['accession'])){$accession = $_GET['accession'];}

$accs = explode(",", $accession);

$server_start = "https://ggr-test.demo.encodedcc.org/";
//$server_start = "https://test.encodedcc.org/";
$server_end = "/";

#Uses Requests library from https://github.com/rmccue/Requests
include('../php/Requests/library/Requests.php');
Requests::register_autoloader();

# Force return from the server in JSON format
$headers = array('Content-Type' => 'application/json', 'Accept' => 'application/json');

# Authentication is always required to POST ENCODE objects
$authid = "SU45FB2Q"; // <-Replace this with your access_key
$authpw = "rae76sr5bntlz5c6"; // <-Replace this with your secret_access_key
$auth = array('auth' => array($authid, $authpw));

$json = json_decode($json_passed);
#var_dump($json);

#Cycle through array and post objects
$count = 0;
foreach ($accs as $acc) {
	if($acc != null){
		# The URL is now the collection itself
		$url = $server_start . $json_name . "/" . $acc . $server_end;
		$response = Requests::patch($url, $headers, json_encode($json[$count]), $auth);
		$count++;
		if(count($json) == $count){
			echo $response->body;
		}else{
			echo $response->body . ",";	
		}
	}
}

?>