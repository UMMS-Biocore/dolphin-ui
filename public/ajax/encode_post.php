<?php
# POST an object to an ENCODE server
require_once("../../config/config.php");
if (isset($_GET['json_name'])){$json_name = $_GET['json_name'];}
if (isset($_GET['json_passed'])){$json_passed = $_GET['json_passed'];}

$server_start = ENCODE_URL;
$server_end = "/";

#Uses Requests library from https://github.com/rmccue/Requests
include(REQUESTS.'/library/Requests.php');
Requests::register_autoloader();

# Force return from the server in JSON format
$headers = array('Content-Type' => 'application/json', 'Accept' => 'application/json');

# Authentication is always required to POST ENCODE objects
$authid = ENCODE_ACCESS;
$authpw = ENCODE_SECRET;
$auth = array('auth' => array($authid, $authpw));

# The URL is now the collection itself
$url = $server_start. $json_name . $server_end; // <-Replace this with appropriate server

$json = $json_passed;

#Cycle through array and post objects
foreach ($json as $json_object) {
	if(isset($json_object['concentration'])){
		$json_object['concentration'] = intval($json_object['concentration']);
	}
	if(isset($json_object['duration'])){
		$json_object['duration'] = intval($json_object['duration']);
	}
	if(isset($json_object['biological_replicate_number'])){
		$json_object['biological_replicate_number'] = intval($json_object['biological_replicate_number']);
	}
	if(isset($json_object['technical_replicate_number'])){
		$json_object['technical_replicate_number'] = intval($json_object['technical_replicate_number']);
	}
	#POST the JSON and get back response
	$response = Requests::post($url, $headers, json_encode($json_object), $auth);
	# If the POST succeeds, the response is the new object in JSON format
	if($json_object == end($json)){
		echo $response->body;
	}else{
		echo $response->body . ",";	
	}
}
?>
