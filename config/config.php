<?php
/** Configuration Variables **/
/** If DOLPHIN_PARAMS_SECTION environment variable set into any parameter section in config.ini file 
The configuration parameters will be read from that section**/

$param_section = "Docker";
if (!empty($_SERVER["HTTP_HOST"])){
   $http_host=$_SERVER["HTTP_HOST"];
   # CHANGE HERE ACCORDING TO YOUR ENVIRONMENT
   if ( preg_match("/biocore/", $http_host) )
   {
      $param_section="Biocore";
   }
   else if  ( preg_match("/galaxy/", $http_host) )
   {
      $param_section="Galaxy";
   }
   else if  ( preg_match("/localhost/", $http_host) )
   {
      $param_section="Localhost";
   }
   else if  ( preg_match("/dolphin.umassmed.edu/", $http_host) )
   {
      $param_section="DolphinDev";
   }
   ###########################################
}
 
$ini = parse_ini_file("config.ini", true);

$ini_array = $ini[$param_section];

define ('DEVELOPMENT_ENVIRONMENT',true);

date_default_timezone_set($ini_array['TIMEZONE']);

define('DB_NAME', $ini_array['DB_NAME']);
define('DB_USER', $ini_array['DB_USER']);
define('DB_PASSWORD', $ini_array['DB_PASSWORD']);
define('DB_HOST', $ini_array['DB_HOST']);
define('DOLPHIN_TOOLS_SRC_PATH', $ini_array['DOLPHIN_TOOLS_SRC_PATH']);
define('REMOTE_HOST', $ini_array['REMOTE_HOST']);
define('JOB_STATUS', $ini_array['JOB_STATUS']);
define('PYTHON', $ini_array['PYTHON']);
define('CONFIG', $ini_array['CONFIG']);
define('LDAP_SERVER', $ini_array['LDAP_SERVER']);
define('DN_STRING', $ini_array['DN_STRING']);
define('BIND_USER', $ini_array['BIND_USER']);
define('BIND_PASS', $ini_array['BIND_PASS']);
define('SCHEDULAR', $ini_array['SCHEDULAR']);

if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
if (isset($_SESSION['user']))
{
  define('USERNAME', $_SESSION['user']);
  define('UID', $_SESSION['uid']);
}

define('BASE_PATH', $ini_array['BASE_PATH']);
define('API_PATH', $ini_array['API_PATH']);

define('ENCODE_URL', $ini_array['ENCODE_URL']);
define('ENCODE_BUCKET', $ini_array['ENCODE_BUCKET']);
define('ENCODE_ACCESS', $ini_array['ENCODE_ACCESS']);
define('ENCODE_SECRET', $ini_array['ENCODE_SECRET']);
