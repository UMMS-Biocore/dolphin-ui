<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$query = new dbfuncs();

if (isset($_GET['p'])){$p = $_GET['p'];}

if ($p == 'getTableSamples')
{
	if (isset($_GET['search'])){$search = $_GET['search'];}
	$data=$query->queryTable("
    SELECT id, samplename
	FROM ngs_samples
	WHERE id IN ( $search )
    ");
}
else if ($p == 'getTableRuns')
{
	if (isset($_GET['search'])){$search = $_GET['search'];}
	$data=$query->queryTable("
    SELECT run_id, sample_id, run_name, wkey
	FROM ngs_runlist
	LEFT JOIN ngs_runparams
	ON ngs_runlist.run_id = ngs_runparams.id
	WHERE sample_id IN ( $search )
	AND wkey != 'NULL'
	AND run_name NOT LIKE '%Initial Run%'
    ");
}
else if ($p == 'getTableReportsList')
{
	if (isset($_GET['wkey'])){$wkey = $_GET['wkey'];}
	$key_count = count(explode(",",$wkey));
	if($key_count == 1){
		$data=$query->queryTable("
		SELECT file, json_parameters
		FROM report_list
		LEFT JOIN ngs_runparams
		ON report_list.wkey = ngs_runparams.wkey
		WHERE report_list.wkey = '". $wkey ."'
		");
	}else{
		$data=$query->queryTable("
		SELECT file, json_parameters
		FROM report_list
		LEFT JOIN ngs_runparams
		ON report_list.wkey = ngs_runparams.wkey
		WHERE report_list.wkey IN ( '". implode("','", explode(",",$wkey))."' )
		");
	}
}
else if ($p == 'samplesWithRuns')
{
	$run_ids_json=json_decode($query->queryTable("
		SELECT distinct ngs_runparams.`id`, ngs_runparams.`run_name` NOT LIKE '%Initial Run%' as `irc`
		FROM biocore.ngs_runparams
		LEFT JOIN report_list
		ON ngs_runparams.wkey = report_list.wkey
		WHERE ngs_runparams.wkey != 'NULL'
		"));
	$run_ids = array();
	foreach($run_ids_json as $rij){
		if($rij->irc == '1'){
			array_push($run_ids, $rij->id);
		}
	}
	$data=$query->queryTable("
		SELECT distinct sample_id
		FROM ngs_runlist
		WHERE run_id in ( " . implode(",",$run_ids) . " )
		");
}
else if ($p == 'getCreatedTables')
{
	if (isset($_GET['gids'])){$gids = $_GET['gids'];}
	$data=$query->queryTable("
		SELECT *
		FROM ngs_createdtables
		WHERE owner_id = " . $_SESSION['uid'] . "
		OR
		(group_id in ( $gids )
		AND perms > 3)
		");
}
else if ($p == 'createNewTable')
{
	if (isset($_GET['search'])){$search = $_GET['search'];}
	if (isset($_GET['name'])){$name = $_GET['name'];}
	if (isset($_GET['file'])){$file = $_GET['file'];}
	if (isset($_GET['group'])){$group = $_GET['group'];}
	if (isset($_GET['perms'])){$perms = $_GET['perms'];}
	
	$current_tables=json_decode($query->queryTable("
		SELECT *
		FROM ngs_createdtables
		WHERE owner_id = " . $_SESSION['uid']
		));
	
	$table_check = false;
	$id = '';
	foreach($current_tables as $ct){
		if ($ct->parameters == $search){
			$table_check = true;
			$id = $ct->id;
		}
	}
	
	if($table_check == false){
		$data=$query->runSQL("
		INSERT ngs_createdtables
		(`name`,`parameters`,`file`,`owner_id`,`group_id`,`perms`,`date_created`,`date_modified`,`last_modified_user`)
		VALUES
		( '$name', '$search','$file', ".$_SESSION['uid'].",$group,$perms,NOW(),NOW(), ".$_SESSION['uid'].")"
		);
	}else{
		$data=$query->runSQL("
		UPDATE ngs_createdtables
		SET name = '$name'
		WHERE id = $id
		");
	}
	$data = json_encode('true');
}
else if ($p == 'deleteTable')
{
	if (isset($_GET['id'])){$id = $_GET['id'];}
	$file=$query->queryTable("
		SELECT file FROM ngs_createdtables
		WHERE id = $id
		");
	$data=json_decode($file);
	
	$handle = popen('rm ../tmp/files/'.$data[0]->file, "r");
	pclose($handle);
	
	$data=$query->runSQL("
		DELETE FROM ngs_createdtables
		WHERE id = $id
		");
}
else if ($p == 'createTableFile')
{
	if (isset($_GET['url'])){$url = $_GET['url'];}
	$json = file_get_contents($url);
	$user = $_SESSION['user'].'_'.date('Y-m-d-H-i-s').'.json';
	
	$file = fopen('../tmp/files/'.$user, "w");
	fwrite($file,$json);
	fclose($file);
	
	if(strpos($url, "json") > -1){
		$json2 = file_get_contents($url."2");
	}else{
		$json2 = '';
	}
	$file = fopen('../tmp/files/'.$user."2", "w");
	fwrite($file,$json2);
	fclose($file);
	
	$data = json_encode($user);
}
else if ($p == 'convertToTSV')
{       
	if (isset($_GET['url'])){$url = $_GET['url'];}
	$json_data_string = substr(file_get_contents($url), 1, -1);
	$json_string_array = explode($json_data_string, "},");
	$json_data = array();
	foreach($json_string_array as $json_string_frag){
		if(end($json_string_array) == $json_string_frag){
			array_push($json_data, json_decode($json_string_frag . "}"));
		}else{
			array_push($json_data, json_decode($json_string_frag));
		}
	}
	$user = $_SESSION['user'].'_'.date('Y-m-d-H-i-s').'.tsv';
	$file = fopen('../tmp/files/'.$user, "w");
	$toggle = true;
	$json_keys = array(); 
	foreach($json_data[0] as $key => $value){
		array_push($json_keys, $key);
	}
	foreach($json_data as $jd){
		if($toggle){
			fwrite($file, implode("	", $json_keys).PHP_EOL);
			$toggle = false;
		}
		$input = "";
		foreach($json_keys as $key){
			if(end($json_keys) != $key){
				$input .= $jd->$key . "	";
			}else{  
				$input .= $jd->$key;
			}
		}
		fwrite($file, $input.PHP_EOL);
	}
	fclose($file);
	$data = json_encode($user);
}
else if ($p == 'removeTSV')
{
	if (isset($_GET['file'])){$file = $_GET['file'];}
	$open = popen('rm ../tmp/files/'.$file, "r");
	pclose($open);
	$data = json_encode('deleted');
}
else if ($p == 'getAllUsers')
{
	if (isset($_GET['table'])){$table = $_GET['table'];}
	$owner_check=$query->queryAVal("
	SELECT owner_id
	FROM ngs_createdtables
	WHERE id = $table
	");
	if($owner_check == $_SESSION['uid']){
		$data=$query->queryTable("
		SELECT id, username
		FROM users
		");
	}else{
		$data=json_encode("");
	}
}
else if ($p == 'changeDataGroupNames')
{
	if (isset($_GET['table'])){$table = $_GET['table'];}
	$owner_check=$query->queryAVal("
	SELECT owner_id
	FROM ngs_createdtables
	WHERE id = $table
	");
	if($owner_check == $_SESSION['uid']){
		$data=$query->queryTable("
		SELECT id,name
		FROM groups
		WHERE id in (
			SELECT g_id
			FROM user_group
			WHERE u_id = ".$_SESSION['uid']."
			)
		OR id = (
			SELECT group_id
			FROM ngs_createdtables
			WHERE id = $table
			)
		");
	}else{
		$data=$data=$query->queryTable("
		SELECT id,name
		FROM groups
		WHERE id = (
			SELECT group_id
			FROM ngs_createdtables
			WHERE id = $table
			)
		");
	}
}
else if ($p == 'getTablePerms')
{
	if (isset($_GET['table'])){$table = $_GET['table'];}
	$data=$query->queryAVal("
	SELECT perms
	FROM ngs_runparams
	WHERE id = $table
	");
}
else if ($p == 'changeTableData'){
	if (isset($_GET['table'])){$table = $_GET['table'];}
	if (isset($_GET['owner_id'])){$owner_id = $_GET['owner_id'];}
	if (isset($_GET['group_id'])){$group_id = $_GET['group_id'];}
	if (isset($_GET['perms'])){$perms = $_GET['perms'];}
	$data=$query->runSQL("
	UPDATE ngs_createdtables
	SET owner_id = $owner_id,
	group_id = $group_id,
	perms = $perms,
	last_modified_user = ".$_SESSION['uid']."
	WHERE id = $table
	");
}
else if ($p == 'getTableOwner')
{
	if (isset($_GET['table'])){$table = $_GET['table'];}
	$data=$query->queryAVal("
	SELECT owner_id
	FROM ngs_createdtables
	WHERE id = $table
	");
}
else if ($p == 'sendToGeneratedTable')
{
	if (isset($_GET['table_id'])){$table_id = $_GET['table_id'];}
	$data=json_decode($query->queryTable("
	SELECT parameters, file
	FROM ngs_createdtables
	WHERE id = $table_id
	"));
	$_SESSION['from_table_list'] = 'true';
	$_SESSION['table_id'] = $table_id;
	$_SESSION['table_file'] = $data[0]->file;
	$_SESSION['table_params'] = $data[0]->parameters;
	$data=json_encode($data);
}
else if ($p == 'getGeneratedTable')
{
	//	Please note that this does not check for the JSON2 file.
	//	Removal of the file name within the database will generate the JSON2 file
	if(isset($_SESSION['table_params'])){
		$array['parameters'] = $_SESSION['table_params'];
		$array['id'] = $_SESSION['table_id'];
		$array['from_table_list'] = $_SESSION['from_table_list'];
		$array['file'] = $_SESSION['table_file'];
		$data = json_encode($array);
	}else{
		$data = '';
	}
}
else if ($p == 'createCustomTable')
{
	if (isset($_GET['params'])){$params = $_GET['params'];}
	$_SESSION['from_table_list'] = 'false';
	$_SESSION['table_file'] = '';
	$_SESSION['table_params'] = $params;
	$data=json_encode($params);
}
else if  ($p == 'getJsonFromFile')
{
	if (isset($_GET['file'])){$file = $_GET['file'];}
	$data = file_get_contents("../tmp/files/$file");
}
else if ($p == 'updateTableFile')
{
	if (isset($_GET['url'])){$url = $_GET['url'];}
	if (isset($_GET['id'])){$id = $_GET['id'];}
	$json = file_get_contents($url);
	$user = $_SESSION['user'].'_'.date('Y-m-d-H-i-s').'.json';
	
	$file = fopen('../tmp/files/'.$user, "w");
	fwrite($file,$json);
	fclose($file);
	
	$json2 = file_get_contents($url."2");
	$file = fopen('../tmp/files/'.$user."2", "w");
	fwrite($file,$json2);
	fclose($file);
	
	$data=$query->runSQL("
	UPDATE ngs_createdtables
	SET file = '$user'
	WHERE id = $id
	");
	
	$_SESSION['table_file'] = $user;
}
else if ($p == "createSummaryTSV")
{
	if (isset($_GET['wkey'])){$wkey = $_GET['wkey'];}
	if (isset($_GET['headers'])){$headers = $_GET['headers'];}
	if (isset($_GET['data_array'])){$data_array = $_GET['data_array'];}

	$myDir = "../pub/$wkey/summary/";
	if (!is_dir($myDir)) {
		mkdir($myDir, 0777, true);
	}
	$file = fopen("../pub/$wkey/summary/summary.tsv", "w");
	fwrite($file,implode("\t",$headers)."\n");
	foreach($data_array as $d){
		fwrite($file,implode("\t",$d)."\n");
	}
	fclose($file);
	
	$data=$query->runSQL("
	INSERT INTO report_list
	(`wkey`, `version`, `type`, `file`)
	VALUES
	('$wkey', '1.0.0', 'summary', 'summary/summary.tsv')
	");
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
