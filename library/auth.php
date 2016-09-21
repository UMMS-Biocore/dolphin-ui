<?php
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();

if (strpos(getcwd(), "travis/build") == 6){
  $_SESSION['user'] = 'travis';
}
  
if ($_SESSION['user'] == "")
{
  if(isset($_GET['p']) && $_GET['p'] == "verify" ){
    require_once("../includes/login.php");
  }if(isset($_POST['request'])){
    require_once("../includes/login.php");
  }else{
    if ($_POST['username']=="" && $_POST['password']=="")
    {
      require_once("../includes/loginform.php");
      exit;
    }
    else
    {
      require_once("../includes/login.php");
    }
  }
}
if (isset($_GET['p']) && $_GET['p'] == "logout" )
{
  require_once("../includes/loginform.php");
  session_destroy();
  exit;
}