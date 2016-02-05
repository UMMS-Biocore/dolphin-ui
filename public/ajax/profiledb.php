<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$query = new dbfuncs();

if (isset($_GET['p'])){$p = $_GET['p'];}

if ($p == 'alterAccessKey')
{
    if (isset($_GET['id'])){$id = $_GET['id'];}
    if (isset($_GET['a_key'])){$a_key = $_GET['a_key'];}
    $data=$query->runSQL("
	UPDATE amazon_credentials
    SET aws_access_key_id = '".$a_key."'
    WHERE id = $id
    ");
}
else if ($p == 'newAmazonKeys')
{
	if (isset($_GET['a_key'])){$a_key = $_GET['a_key'];}
	if (isset($_GET['s_key'])){$s_key = $_GET['s_key'];}
    $data=$query->runSQL("
	INSERT INTO amazon_credentials
    (`aws_access_key_id`, `aws_secret_access_key`)
	VALUES
	('$a_key', '$s_key')
    ");
}
else if ($p == 'alterSecretKey')
{
    if (isset($_GET['id'])){$id = $_GET['id'];}
    if (isset($_GET['s_key'])){$s_key = $_GET['s_key'];}
    $data=$query->runSQL("
	UPDATE amazon_credentials
    SET aws_secret_access_key = '".$s_key."'
    WHERE id = $id
    ");
}
else if($p == 'updateProfile')
{
    if (isset($_GET['img'])){$img = $_GET['img'];}
    $data=$query->runSQL("
	UPDATE users
    SET photo_loc = '".$img."'
    WHERE username = '".$_SESSION['user']."'
    ");
}
else if ($p == 'checkAmazonPermissions')
{
    if (isset($_GET['a_id'])){$a_id = $_GET['a_id'];}
    $data=$query->queryTable("
    SELECT id FROM groups WHERE owner_id = ".$_SESSION['uid']." AND id IN(
    SELECT group_id FROM group_amazon WHERE amazon_id = (
    SELECT DISTINCT id FROM amazon_credentials where id = $a_id));
    ");
}
else if ($p == 'viewAmazonGroupCreation')
{
	$data=$query->queryTable("
	SELECT id, name
	FROM groups
	WHERE owner_id = ".$_SESSION['uid']."
	AND id NOT IN (
		SELECT groups.id
		FROM amazon_credentials
		LEFT JOIN group_amazon
		ON amazon_credentials.id = group_amazon.amazon_id
		LEFT JOIN groups
		ON groups.id = group_amazon.group_id
		LEFT JOIN user_group
		ON user_group.g_id = groups.id
		WHERE user_group.u_id = ".$_SESSION['uid']."
	)
	");
}
else if ($p == 'obtainAmazonKeys')
{
    $data=$query->queryTable("
    SELECT amazon_credentials.id, name, aws_access_key_id, aws_secret_access_key
	FROM amazon_credentials
	LEFT JOIN group_amazon
	ON amazon_credentials.id = group_amazon.amazon_id
	LEFT JOIN groups
	ON groups.id = group_amazon.group_id
	LEFT JOIN user_group
	ON user_group.g_id = groups.id
	WHERE user_group.u_id = ".$_SESSION['uid']."
    ");
}
else if ($p == 'createAWSKeys')
{
	if (isset($_GET['group'])){$group = $_GET['group'];}
	if (isset($_GET['a_key'])){$a_key = $_GET['a_key'];}
	if (isset($_GET['s_key'])){$s_key = $_GET['s_key'];}
	$data=$query->runSQL("
	INSERT INTO amazon_credentials
	(`aws_access_key_id`, `aws_secret_access_key`)
	VALUES
	('$a_key', '$s_key')
	");
	$aws_id=$query->queryAVal("
	SELECT id
	FROM amazon_credentials
	WHERE aws_access_key_id = '$a_key'
	AND aws_secret_access_key = '$s_key'
	");
	$data=$query->runSQL("
	INSERT INTO group_amazon
	(`group_id`, `amazon_id`)
	VALUES
	($group, $aws_id)
	");
}
else if ($p == 'profileLoad')
{
    $data=$query->queryTable("
    SELECT photo_loc
    FROM users
    WHERE username = '".$_SESSION['user']."'"
    );
}
else if ($p == 'obtainGroups')
{
	$data=$query->queryTable("
    SELECT groups.id, name, groups.date_created, groups.owner_id, user_group.u_id
    FROM groups
	LEFT JOIN user_group
	ON user_group.g_id = groups.id
    WHERE u_id = ".$_SESSION['uid']
    );
}
else if ($p == 'obtainProfileInfo')
{
	$data=$query->queryTable("
    SELECT *
    FROM users
    WHERE username = '".$_SESSION['user']."'"
    );
}
else if ($p == 'newGroupProcess')
{
	if (isset($_GET['newGroup'])){$newGroup = $_GET['newGroup'];}
	$groupCheck = $query->queryAVal("
	SELECT id
	FROM groups
	WHERE name = '" . $newGroup . "'
	");
	if($groupCheck > 0){
		$data = json_encode('A group with the same name already exists');
	}else{
		$groupsInsert=$query->runSQL("
		INSERT INTO groups
		( `name`, `owner_id`, `group_id`, `perms`, `date_created`, `date_modified`, `last_modified_user` )
		VALUES
		( '".$newGroup."', ".$_SESSION['uid'].", 1, 15, NOW(), NOW(), ".$_SESSION['uid']." )
		");
		$groupsQuery=$query->queryAVal("
		SELECT id
		FROM groups
		WHERE name = '".$newGroup."'
		");
		$user_group=$query->runSQL("
		INSERT INTO user_group
		( `u_id`, `g_id`, `owner_id`, `group_id`, `perms`, `date_created`, `date_modified`, `last_modified_user` )
		VALUES
		( ".$_SESSION['uid'].", ".$groupsQuery.", 1, 1, 15, NOW(), NOW(), ".$_SESSION['uid']." )
		");
		$data = json_encode('Your group has been created');
	}
}
else if ($p == 'joinGroupList')
{
	$data=$query->queryTable("
	SELECT id, name
	FROM groups
	WHERE id NOT IN (
		SELECT g_id
		FROM user_group
		WHERE u_id = " . $_SESSION['uid'] . "
	)
	AND id NOT IN (
		SELECT group_id
		FROM user_group_requests
		WHERE user_request = ".$_SESSION['uid']."
	)
	");
}
else if ($p == 'sendJoinGroupRequest')
{
	if (isset($_GET['group_id'])){$group_id = $_GET['group_id'];}
	$group_owner=$query->queryAVal("
	SELECT owner_id
	FROM groups
	WHERE id = '$group_id'
	");
	$data=json_decode($query->runSQL("
	INSERT INTO user_group_requests
	( `user_request`, `user_check`, `group_id`, `group_owner` )
	VALUES
	( ".$_SESSION['uid'].", 0, $group_id, $group_owner )
	"));
}
else if ($p == 'viewGroupMembers')
{
	if (isset($_GET['group'])){$group = $_GET['group'];}
	$data=$query->queryTable("
	SELECT id, username
	FROM users
	WHERE id in (
		SELECT u_id
		FROM user_group
		WHERE g_id = $group
	)
	");
}
else if ($p == 'deleteGroup')
{
	if (isset($_GET['group_id'])){$group_id = $_GET['group_id'];}
	$delete=$query->runSQL("
	DELETE FROM groups
	WHERE id = $group_id
	");
	$delete=$query->runSQL("
	DELETE FROM user_group
	WHERE group_id = $group_id
	");
	$data=json_encode('pass');
}
else if ($p == 'getGroupMemberAdd')
{
	if (isset($_GET['group_id'])){$group_id = $_GET['group_id'];}
	$data=$query->queryTable("
	SELECT id, username
	FROM users
	WHERE id in (
		SELECT user_request
		FROM user_group_requests
		WHERE group_id = $group_id
		AND group_owner = ".$_SESSION['uid']."
	)
	");
}
else if ($p == 'addGroupMember')
{
	if (isset($_GET['group_id'])){$group_id = $_GET['group_id'];}
	if (isset($_GET['user_id'])){$user_id = $_GET['user_id'];}
	
	$delete_possible_request=$query->runSQL("
	DELETE FROM user_group_requests
	WHERE group_id = $group_id
	AND user_request = $user_id
	");
	$data=$query->runSQL("
	INSERT INTO user_group
	(`u_id`, `g_id`, `owner_id`, `group_id`, `perms`, `date_created`, `date_modified`, `last_modified_user`)
	VALUES
	($user_id, $group_id, 1, 1, 15, NOW(), NOW(), ".$_SESSION['uid'].")
	");
}
else if ($p == 'getMemberAdd')
{
       if (isset($_GET['group_id'])){$group_id = $_GET['group_id'];}
       $data=$query->queryTable("
       SELECT id, username
       FROM users
       WHERE id NOT IN (
               SELECT u_id
               FROM user_group
               WHERE g_id = $group_id
       )
       ");
}
else if ($p == 'transferOwner')
{
       if (isset($_GET['group_id'])){$group_id = $_GET['group_id'];}
       if (isset($_GET['user_id'])){$user_id = $_GET['user_id'];}
       $new_owner=$query->runSQL("
       UPDATE groups
       SET owner_id = $user_id
       WHERE id = $group_id
       ");
	   $pending_user=$query->runSQL("
		DELETE FROM user_group_requests
		WHERE group_id = $group_id
		AND user_request = $user_id
		");
       $data=json_encode('pass');
}
else if ($p == 'getUID')
{
    $data=json_encode($_SESSION['uid']);
}



if (!headers_sent()) {
   header('Cache-Control: no-cache, must-revalidate');
   header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
   header('Content-type: application/json');
   echo $data;
   exit;
}else{
   echo $data;
}
?>
