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
else if ($p == 'obtainAmazonKeys')
{
    $data=$query->queryTable("
    SELECT * FROM amazon_credentials WHERE id IN(
        SELECT amazon_id FROM group_amazon WHERE id IN(
            SELECT id FROM groups WHERE id IN(
                SELECT g_id FROM user_group WHERE u_id = ".$_SESSION['uid'].")))
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
		( '".$newGroup."', 1, 1, 15, NOW(), NOW(), ".$_SESSION['uid']." )
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
	SELECT name
	FROM groups
	WHERE id NOT IN (
		SELECT g_id
		FROM user_group
		WHERE u_id = " . $_SESSION['uid'] . "
	)
	AND name NOT IN (
		SELECT group_name
		FROM user_group_requests
		WHERE user_request = ".$_SESSION['uid']."
	)
	");
}
else if ($p == 'sendJoinGroupRequest')
{
	if (isset($_GET['group'])){$group = $_GET['group'];}
	$group_owner=$query->queryAVal("
	SELECT owner_id
	FROM groups
	WHERE name = '$group'
	");
	$data=json_decode($query->runSQL("
	INSERT INTO user_group_requests
	( `user_request`, `user_check`, `group_name`, `group_owner` )
	VALUES
	( ".$_SESSION['uid'].", 0, '$group', $group_owner )
	"));
}
else if ($p == 'viewGroupMembers')
{
	if (isset($_GET['group'])){$group = $_GET['group'];}
	$data=$query->queryTable("
	SELECT username
	FROM users
	WHERE id in (
		SELECT u_id
		FROM user_group
		WHERE g_id = $group
	)
	");
}


header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo $data;
exit;
?>
