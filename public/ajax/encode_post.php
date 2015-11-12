<?php
# POST an object to an ENCODE server

if (isset($_GET['json_name'])){$json_name = $_GET['json_name'];}
if (isset($_GET['json_passed'])){$json_passed = $_GET['json_passed'];}

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

# The URL is now the collection itself
$url = $server_start. $json_name . $server_end; // <-Replace this with appropriate server

$json = json_decode($json_passed);

#Cycle through array and post objects
foreach ($json as $json_object) {
	#POST the JSON and get back response
	$response = Requests::post($url, $headers, json_encode($json_object), $auth);
	# If the POST succeeds, the response is the new object in JSON format
	#var_dump($response->body);
	if($json_object == end($json)){
		echo $response->body;
	}else{
		echo $response->body . ",";	
	}
}

?>