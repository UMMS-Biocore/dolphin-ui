<?php
# PATCH an object to an ENCODE server
require_once("../../config/config.php");
if (isset($_GET['json_name'])){$json_name = $_GET['json_name'];}
if (isset($_GET['accession'])){$accession = $_GET['accession'];}

$server_start = ENCODE_URL;
$server_end = "/";

#Uses Requests library from https://github.com/rmccue/Requests
include('../php/Requests/library/Requests.php');
Requests::register_autoloader();

# Force return from the server in JSON format
$headers = array('Content-Type' => 'application/json', 'Accept' => 'application/json');

# Authentication is always required to POST ENCODE objects
$authid = ENCODE_ACCESS;
$authpw = ENCODE_SECRET;
$auth = array('auth' => array($authid, $authpw));

# Arguments at comand line, file to post and object
$jsonfile = $json_passed;
$object = $json_name;

# The URL is now the collection itself
$url = $server_start. $object . "/" . $accession . $server_end; // <-Replace this with appropriate server

#GET the JSON and get back response
$response = Requests::get($url, $headers, $auth);
# If the GET succeeds, the response is the new object in JSON format
echo $response->body;
?>
