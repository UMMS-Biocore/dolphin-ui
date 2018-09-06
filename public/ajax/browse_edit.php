<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");
require_once("../api/funcs.php");
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$query = new dbfuncs();
$funcs = new funcs();

$p = '';
$normalized = ['facility', 'organism', 'molecule', 'lab', 'organization', 'genotype', 'library_type', 'source',
				'instrument_model', 'treatment_manufacturer', 'library_strategy', 'donor', 'biosample_term_name'];

if (isset($_GET['p'])){$p = $_GET['p'];}
if (isset($_POST['p'])){$p = $_POST['p'];}

if($p == 'insertFromCombobox')
{
	if (isset($_POST['type'])){$type = $_POST['type'];}
	if (isset($_POST['value'])){$value = $_POST['value'];}


	if($type != '' && $value != ''){
		$data=$query->runSQL("INSERT INTO ngs_" . $type . " ($type) VALUES ('$value')");
	}
}

if($p == 'postInsertDatabase')
{
	if (isset($_POST['type'])){$type = $_POST['type'];}
	if (isset($_POST['table'])){$table = $_POST['table'];}
	if (isset($_POST['value'])){$value = $_POST['value'];}
	if (isset($_POST['sample_ids'])){$sample_ids = $_POST['sample_ids'];}
	if($type != '' && $table != '' && $value != '' && $sample_ids != ''){
		$data=$query->runSQL("UPDATE $table SET $type = \"$value\"  WHERE id IN ($sample_ids)");
	}
}

if($p == 'getDirectoryInfoForSample')
{
	if (isset($_GET['sample_id'])){$sample_id = $_GET['sample_id'];}

    // getSampleFileLocation
    $file_loc = $query->queryTable("select ngs_temp_sample_files.id, ngs_dirs.id as dir_id, file_name, fastq_dir, backup_dir, amazon_bucket from ngs_temp_sample_files left join ngs_dirs on ngs_temp_sample_files.dir_id = ngs_dirs.id where sample_id = $sample_id");
	$files = json_decode($file_loc, true);

	// getInputSampleDirectories
	$sample_dirs = $query->queryTable("select ngs_dirs.fastq_dir, ngs_dirs.id from ngs_dirs where id in (select dir_id from ngs_temp_sample_files where sample_id = $sample_id)");
	$dir_array = json_decode($sample_dirs, true);

    // getSampleFastqFileLocation
    $fastq_locs = $query->queryTable("select ngs_fastq_files.id, ngs_dirs.id as dir_id, file_name, fastq_dir, backup_dir, amazon_bucket from ngs_fastq_files left join ngs_dirs on ngs_fastq_files.dir_id = ngs_dirs.id where sample_id = $sample_id");
	$fastq_files = json_decode($fastq_locs, true);
    
		$html = '';

		foreach ($dir_array as $da){

			$html .= '		<table class="table table-hover table-striped table-condensed">';
			$html.='			<thead><tr><th>Input File(s) Directory:</th></tr></thead>
								<tbody>';
			$html.='				<tr><td onclick="editBox( '.$_SESSION['uid'].', '. $da['id'].', \'fastq_dir\', \'ngs_dirs\', this)">'.$da['fastq_dir'].'</td></tr>
								</tbody>';
			$html.='			<thead><tr><th>Input File(s):</th></tr></thead>
								<tbody>';
			foreach ($files as $f){
				if($f['dir_id'] == $da['id']){
					$html.='<tr><td onclick="editBox( '.$_SESSION['uid'].', '. $f['id'].', \'file_name\', \'ngs_temp_sample_files\', this)">'.$f['file_name'].'</td></tr>';	
				}
			}
			$html .= '			</tbody>
							</table>';
		}

		if($fastq_files != null){
			$html .= '		<table class="table table-hover table-striped table-condensed">';
			$html.='			<thead><tr><th>Processed File(s) Directory:</th></tr></thead>
								<tbody>';
			$html.='				<tr><td onclick="editBox( '.$_SESSION['uid'].', '. $fastq_files[0]['dir_id'].', \'backup_dir\', \'ngs_dirs\', this)">'.$fastq_files[0]['backup_dir'].'</td></tr>
								</tbody>';
			$html.='			<thead><tr><th>Processed File(s):</th></tr></thead>
								<tbody>';
			foreach ($fastq_files as $ff){
					$html.='		<tr><td onclick="editBox( '.$_SESSION['uid'].', '. $ff['id'].', \'file_name\', \'ngs_fastq_files\', this)">'.$ff['file_name'].'</td></tr>';
			}
			$html.='			<thead><tr><th>Amazon Backup:</th></tr></thead>
								<tbody>';
			$html.='				<tr><td onclick="editBox( '.$_SESSION['uid'].', '. $fastq_files[0]['dir_id'].', \'amazon_bucket\', \'ngs_dirs\', this)">'.$fastq_files[0]['amazon_bucket'].'</td></tr>
								</tbody>';
			$html .= '		</table>';
		}

		
		// return the entire html
		$data = $html;
}

if($p == 'getRunsForSample')
{
	if (isset($_GET['sample_id'])){$sample_id = $_GET['sample_id'];}
        $data=$query->queryTable("SELECT nr.id, nr.run_name
                FROM ngs_runparams nr, ngs_runlist nl
                WHERE nr.id = nl.run_id and nl.sample_id = $sample_id");
}

if($p == 'getTablesForSample')
{
	if (isset($_GET['runs'])){$runs = $_GET['runs'];}

	$r_list = json_decode($runs);

	$query_str = 'SELECT id, name
				FROM ngs_createdtables
				WHERE parameters LIKE ';

	$id_list = [];
	foreach($r_list as $rl){
		$id_list[] = "'%;".$rl->id."%' ";
	}
	$query_str.= implode('OR parameters LIKE ', $id_list);

	$data=$query->queryTable($query_str);
}

if($p == 'getExperimentDetailsSearch')
{
	if (isset($_GET['experiment_id'])){$experiment_id = $_GET['experiment_id'];}
	$data=$query->queryTable("SELECT summary, design, experiment_name, groups.name, perms.perms_name FROM `ngs_experiment_series` LEFT JOIN `groups` ON ngs_experiment_series.group_id = groups.id LEFT JOIN `perms` ON ngs_experiment_series.perms = perms.value WHERE ngs_experiment_series.id = $experiment_id");
}

if($p == 'getImportDetailsSearch')
{
	if (isset($_GET['import_id'])){$import_id = $_GET['import_id'];}
	$data=$query->queryTable("
		SELECT ngs_lanes.name as import_name, ngs_experiment_series.experiment_name, ngs_facility.facility, ngs_lanes.date_submitted, ngs_lanes.date_received, ngs_lanes.total_samples, ngs_lanes.resequenced, groups.name as group_name, perms.perms_name, ngs_lanes.lane_id, ngs_lanes.series_id
		FROM `ngs_lanes`
		LEFT JOIN `groups`
		ON ngs_lanes.group_id = groups.id
		LEFT JOIN `perms`
		ON ngs_lanes.perms = perms.value
		LEFT JOIN `ngs_facility`
		ON ngs_lanes.facility_id = ngs_facility.id
		LEFT JOIN `ngs_experiment_series`
		ON ngs_lanes.series_id = ngs_experiment_series.id
		WHERE ngs_lanes.id = $import_id
		");
}


if($p == 'getFilteredSampleData')
{
	if (isset($_GET['experiment_or_import'])){$experiment_or_import = $_GET['experiment_or_import'];}
	if($experiment_or_import == "experiment"){
		$where_variable = "ngs_samples.series_id";
	}
	if($experiment_or_import == "import"){
		$where_variable = "ngs_samples.lane_id";
	}
	if (isset($_GET['id'])){$id = $_GET['id'];}

	$left_join_str = "";

	$fields_to_left_join = ["source", "organism", "molecule", "genotype", "library_type",
	    "biosample_type", "instrument_model", "treatment_manufacturer"];
	              
	foreach( $fields_to_left_join as $field_join){
	    $left_join_str .= "LEFT JOIN `ngs_$field_join`
			ON ngs_samples." . $field_join . "_id = ngs_" . $field_join . ".id
			";
	}



	$amazon_str = "AND ngs_fastq_files.dir_id = (SELECT ngs_dirs.id FROM ngs_dirs WHERE ngs_fastq_files.dir_id = ngs_dirs.id AND (ngs_dirs.amazon_bucket LIKE '%s3://%'))";

	$sampleBackup = "CASE
						WHEN (SELECT COUNT(*) FROM ngs_fastq_files WHERE aws_status = 2 AND ngs_samples.id = ngs_fastq_files.sample_id) > 0 THEN '<button class=\"btn btn-warning\" disabled>'
						WHEN (SELECT COUNT(*) FROM ngs_fastq_files WHERE checksum != original_checksum AND (original_checksum != '' AND original_checksum IS NOT NULL) AND ngs_samples.id = ngs_fastq_files.sample_id) > 0 THEN '<button class=\"btn btn-flickr\" disabled>'
						WHEN (SELECT COUNT(*) FROM ngs_fastq_files WHERE checksum != backup_checksum AND (backup_checksum != '' AND backup_checksum IS NOT NULL) AND ngs_samples.id = ngs_fastq_files.sample_id $amazon_str) > 0 THEN '<button class=\"btn btn-danger\" disabled>'
						WHEN (SELECT COUNT(*) FROM ngs_fastq_files WHERE (backup_checksum = '' OR backup_checksum IS NULL) AND ngs_samples.id = ngs_fastq_files.sample_id $amazon_str) > 0 THEN '<button class=\"btn btn-secondary\" disabled>'
						WHEN (SELECT COUNT(*) FROM ngs_fastq_files WHERE date_modified < DATE_SUB(now(), INTERVAL 2 MONTH) AND ngs_samples.id = ngs_fastq_files.sample_id $amazon_str) > 0 THEN '<button class=\"btn btn-primary\" disabled>'
						WHEN (SELECT COUNT(*) FROM ngs_fastq_files WHERE ngs_samples.id = ngs_fastq_files.sample_id $amazon_str) = 0 THEN ''
						ELSE '<button class=\"btn btn-success\" disabled>'
					END AS backup";



	$data=$query->queryTable("
		SELECT ngs_samples.id, ngs_samples.samplename, ngs_samples.title, ngs_source.source, ngs_organism.organism, ngs_molecule.molecule, $sampleBackup, barcode, description, avg_insert_size, read_length, concentration, time, biological_replica, technical_replica, spike_ins, adapter, notebook_ref, notes, ngs_genotype.genotype, ngs_library_type.library_type, ngs_biosample_type.biosample_type, ngs_instrument_model.instrument_model, ngs_treatment_manufacturer.treatment_manufacturer
		FROM `ngs_samples`
		LEFT JOIN `ngs_source`
		ON ngs_samples.source_id = ngs_source.id
		LEFT JOIN `ngs_organism`
		ON ngs_samples.organism_id = ngs_organism.id
		LEFT JOIN `ngs_molecule`
		ON ngs_samples.molecule_id = ngs_molecule.id
		LEFT JOIN `ngs_genotype`
		ON ngs_samples.genotype_id = ngs_genotype.id
		LEFT JOIN `ngs_library_type`
		ON ngs_samples.library_type_id = ngs_library_type.id
		LEFT JOIN `ngs_biosample_type`
		ON ngs_samples.biosample_type_id = ngs_biosample_type.id
		LEFT JOIN `ngs_instrument_model`
		ON ngs_samples.instrument_model_id = ngs_instrument_model.id
		LEFT JOIN `ngs_treatment_manufacturer`
		ON ngs_samples.treatment_manufacturer_id = ngs_treatment_manufacturer.id
		WHERE " . $where_variable . " = $id
		");
}

if($p == 'getFilteredImportData')
{
	if (isset($_GET['experiment_id'])){$experiment_id = $_GET['experiment_id'];}
	$data=$query->queryTable("
		SELECT ngs_lanes.id, ngs_lanes.name as import_name, ngs_facility.facility, ngs_lanes.total_reads, ngs_lanes.total_samples,
			ngs_lanes.cost, ngs_lanes.phix_requested, ngs_lanes.phix_in_lane, ngs_lanes.notes
		FROM `ngs_lanes`
		LEFT JOIN `groups`
		ON ngs_lanes.group_id = groups.id
		LEFT JOIN `perms`
		ON ngs_lanes.perms = perms.value
		LEFT JOIN `ngs_facility`
		ON ngs_lanes.facility_id = ngs_facility.id
		LEFT JOIN `ngs_experiment_series`
		ON ngs_lanes.series_id = ngs_experiment_series.id
		WHERE ngs_lanes.series_id = $experiment_id
		");
}

if($p == 'getSampleDetailsSearch')
{
	if (isset($_GET['sample_id'])){$sample_id = $_GET['sample_id'];}
	$data=$query->queryTable("
		SELECT ngs_experiment_series.experiment_name, ngs_lanes.name as import_name, ngs_protocols.name as protocol_name, ngs_samples.samplename, ngs_samples.barcode, ngs_samples.title, ngs_source.source, ngs_organism.organism, ngs_molecule.molecule, ngs_instrument_model.instrument_model, ngs_samples.avg_insert_size, ngs_samples.read_length, ngs_genotype.genotype, ngs_library_type.library_type, ngs_samples.notes, groups.name as group_name, perms.perms_name, ngs_donor.donor, ngs_samples.time, ngs_samples.biological_replica, ngs_samples.technical_replica, ngs_samples.lane_id, ngs_dirs.fastq_dir, ngs_dirs.backup_dir, ngs_fastq_files.file_name, ngs_dirs.amazon_bucket, GROUP_CONCAT(ngs_temp_sample_files.file_name separator '<br/>') AS file_names
		FROM `ngs_samples`
		LEFT JOIN `ngs_experiment_series`
		ON ngs_samples.series_id = ngs_experiment_series.id
		LEFT JOIN `ngs_lanes`
		ON ngs_samples.lane_id = ngs_lanes.id
		LEFT JOIN `ngs_protocols`
		ON ngs_samples.protocol_id = ngs_protocols.id
		LEFT JOIN `ngs_source`
		ON ngs_samples.source_id = ngs_source.id
		LEFT JOIN `ngs_organism`
		ON ngs_samples.organism_id = ngs_organism.id
		LEFT JOIN `ngs_molecule`
		ON ngs_samples.molecule_id = ngs_molecule.id
		LEFT JOIN `ngs_instrument_model`
		ON ngs_samples.instrument_model_id = ngs_instrument_model.id
		LEFT JOIN `ngs_genotype`
		ON ngs_samples.genotype_id = ngs_genotype.id
		LEFT JOIN `ngs_library_type`
		ON ngs_samples.library_type_id = ngs_library_type.id
		LEFT JOIN `groups`
		ON ngs_samples.group_id = groups.id
		LEFT JOIN `perms`
		ON ngs_samples.perms = perms.value
		LEFT JOIN `ngs_donor`
		ON ngs_samples.donor_id = ngs_donor.id
		LEFT JOIN `ngs_fastq_files`
		ON ngs_samples.id = ngs_fastq_files.sample_id
		LEFT JOIN `ngs_dirs`
		ON ngs_dirs.id = ngs_fastq_files.dir_id
		LEFT JOIN `ngs_temp_sample_files`
		ON ngs_temp_sample_files.sample_id = ngs_samples.id
		WHERE ngs_samples.id = $sample_id
		");
}

if($p == 'insertDatabase')
{
	if (isset($_GET['type'])){$type = $_GET['type'];}
	if (isset($_GET['table'])){$table = $_GET['table'];}
	if (isset($_GET['value'])){$value = $_GET['value'];}
	if (isset($_GET['parent'])){$parent = $_GET['parent'];}
	if (isset($_GET['parent_id'])){$parent_id = $_GET['parent_id'];}
	if (isset($_GET['parent_child'])){$parent_child = $_GET['parent_child'];}
	if($value == ''){
		$data=$query->runSQL("UPDATE $parent SET ".$parent_child." = NULL WHERE id = '$parent_id'");
	}else{
		$query->runSQL("INSERT INTO ".$table." ($type) VALUES ('$value')");
		$insert_id= json_decode($query->queryTable("SELECT id FROM ".$table." WHERE $type = '$value'"));
		$data=$query->runSQL("UPDATE $parent SET ".$parent_child." = '".$insert_id[0]->id."' WHERE id = '$parent_id'");
	}
}
if($p == 'insertDatabaseMulti')
{
	if (isset($_GET['type'])){$type = $_GET['type'];}
	if (isset($_GET['table'])){$table = $_GET['table'];}
	if (isset($_GET['value'])){$value = $_GET['value'];}
	if (isset($_GET['parent'])){$parent = $_GET['parent'];}
	if (isset($_GET['parent_id'])){$parent_id = $_GET['parent_id'];}
	if (isset($_GET['parent_child'])){$parent_child = $_GET['parent_child'];}
	if($value == ''){
		$data=$query->runSQL("UPDATE $parent SET ".$parent_child." = NULL WHERE id in ($parent_id)");
	}else{
		$query->runSQL("INSERT INTO ".$table." ($type) VALUES ('$value')");
		$insert_id= json_decode($query->queryTable("SELECT id FROM ".$table." WHERE $type = '$value'"));
		$data=$query->runSQL("UPDATE $parent SET ".$parent_child." = '".$insert_id[0]->id."' WHERE id in ($parent_id)");
	}
}
if($p == 'updateDatabase')
{
	if (isset($_GET['id'])){$id = $_GET['id'];}
	if (isset($_GET['type'])){$type = $_GET['type'];}
	if (isset($_GET['table'])){$table = $_GET['table'];}
	if (isset($_GET['value'])){$value = $_GET['value'];}
	if (isset($_GET['parent'])){$parent = $_GET['parent'];}
	if (isset($_GET['parent_id'])){$parent_id = $_GET['parent_id'];}
	if (isset($_GET['parent_child'])){$parent_child = $_GET['parent_child'];}
	if(in_array($type, $normalized)){
		$type_list = json_decode($query->queryTable("SELECT id FROM ngs_".$type." WHERE $type = \"$value\""));
		if($type_list != array()){
			$data=$query->runSQL("UPDATE $table SET ".$type."_id = ".$type_list[0]->id." WHERE id = '$id'");
		}else if ($value == ''){
			$data=$query->runSQL("UPDATE $table SET ".$type."_id = NULL WHERE id = '$id'");
		}else{
			$query->runSQL("INSERT INTO ngs_".$type." ($type) VALUES ('$value')");
			$insert_id= json_decode($query->queryTable("SELECT id FROM ngs_".$type." WHERE $type = '$value'"));
			$data=$query->runSQL("UPDATE $table SET ".$type."_id = '".$insert_id[0]->id."' WHERE id = '$id'");
		}
	}else{
		if ($value == ''){
			$data=$query->runSQL("UPDATE $table SET $table.$type = NULL WHERE id = '$id'");
		}else{
			$data=$query->runSQL("UPDATE $table SET $table.$type = '$value' WHERE id = '$id'");
		}
	}
}
if($p == 'updateDatabaseEncode')
{

	if (isset($_GET['id'])){$id = $_GET['id'];}
	if (isset($_GET['type'])){$type = $_GET['type'];}
	if (isset($_GET['table'])){$table = $_GET['table'];}
	if (isset($_GET['value'])){$value = $_GET['value'];}
	if (isset($_GET['parent'])){$parent = $_GET['parent'];}
	if (isset($_GET['parent_id'])){$parent_id = $_GET['parent_id'];}
	if (isset($_GET['parent_child'])){$parent_child = $_GET['parent_child'];}

	if(in_array($type, $normalized)){
		$type_list = json_decode($query->queryTable("SELECT id FROM ".$table." WHERE $type = '$value'"));
		if($type_list != array()){
			$data=$query->runSQL("UPDATE $parent SET $parent_child = ".$type_list[0]->id." WHERE id = $parent_id");
		}else if ($value == ''){
			$data=$query->runSQL("UPDATE $parent SET ".$parent_child." = NULL WHERE id = $parent_id");
		}else{
			$query->runSQL("INSERT INTO ".$table." ($type) VALUES ('$value')");
			$insert_id= json_decode($query->queryTable("SELECT id FROM ".$table." WHERE $type = '$value'"));
			$data=$query->runSQL("UPDATE $parent SET ".$parent_child." = '".$insert_id[0]->id."' WHERE id = $parent_id");
		}
	}else{
		if ($value == ''){
			$data=$query->runSQL("UPDATE $table SET $table.$type = NULL WHERE id = '$id'");
		}else{
			$data=$query->runSQL("UPDATE $table SET $table.$type = '$value' WHERE id = '$id'");
		}
	}
}
if($p == 'updateDatabaseMultiEncode')
{

	if (isset($_GET['id'])){$id = $_GET['id'];}
	if (isset($_GET['type'])){$type = $_GET['type'];}
	if (isset($_GET['table'])){$table = $_GET['table'];}
	if (isset($_GET['value'])){$value = $_GET['value'];}
	if (isset($_GET['parent'])){$parent = $_GET['parent'];}
	if (isset($_GET['parent_child'])){$parent_child = $_GET['parent_child'];}

	if(in_array($type, $normalized)){
		$type_list = json_decode($query->queryTable("SELECT id FROM ".$table." WHERE $type = '$value'"));
		if($type_list != array()){
			$data=$query->runSQL("UPDATE $parent SET $parent_child = ".$type_list[0]->id." WHERE id in ($id)");
		}else if ($value == ''){
			$data=$query->runSQL("UPDATE $parent SET ".$parent_child." = NULL WHERE id in ($id)");
		}else{
			$query->runSQL("INSERT INTO ".$table." ($type) VALUES ('$value')");
			$insert_id= json_decode($query->queryTable("SELECT id FROM ".$table." WHERE $type = '$value'"));
			$data=$query->runSQL("UPDATE $parent SET ".$parent_child." = '".$insert_id[0]->id."' WHERE id in ($id)");
		}
	}else{
		if ($value == ''){
			$data=$query->runSQL("UPDATE $table SET $table.$type = NULL WHERE id in ($id)");
		}else{
			$data=$query->runSQL("UPDATE $table SET $table.$type = '$value' WHERE id in ($id)");
		}
	}
}
else if($p == 'checkPerms')
{
	if (isset($_GET['id'])){$id = $_GET['id'];}
	if (isset($_GET['uid'])){$uid = $_GET['uid'];}
	if (isset($_GET['table'])){$table = $_GET['table'];}

	$group_id = json_decode($query->queryTable("SELECT group_id, owner_id FROM $table WHERE id = $id"));
	$user_pass = json_decode($query->queryTable("SELECT u_id FROM user_group WHERE g_id = ".$group_id[0]->group_id." and u_id = $uid"));
	if($user_pass[0]->u_id == $uid || $_SESSION['uid'] == 1){
		$data = 1;
	}else if ($group_id[0]->owner_id == $uid){
		$data = 1;
	}else{
		$data = 0;
	}
}
else if($p == 'getDropdownValues')
{
	if (isset($_GET['type'])){$type = $_GET['type'];}
	$data=$query->queryTable("SELECT $type FROM ngs_".$type);
}
else if($p == 'getDropdownValuesWithID')
{
	if (isset($_GET['fields'])){$fields = $_GET['fields'];}
	$data_arr = [];
	$field_arr = explode("&field&", $fields);
	foreach($field_arr as $field){
		$data_arr[$field] = $query->queryTable("SELECT id, $field FROM ngs_".$field);
	}
	$data = json_encode($data_arr);
}
else if($p == 'getDirectDropdownValues')
{
	if (isset($_GET['fields'])){$fields = $_GET['fields'];}
	$data_arr = [];
	$field_arr = explode("&field&", $fields);
	foreach($field_arr as $field){
		$data_arr[$field] = $query->queryTable("SELECT $field FROM `ngs_samples` GROUP BY $field");
	}
	$data = json_encode($data_arr);
}
else if($p == 'getDropdownValuesPerms')
{
	if (isset($_GET['type'])){$type = $_GET['type'];}
	if (isset($_GET['table'])){$table = $_GET['table'];}
	if (isset($_GET['gids'])){$gids = $_GET['gids'];}
	$data=$query->queryTable("SELECT $type FROM $table WHERE (((group_id in ($gids)) AND (perms >= 15)) OR (owner_id = ".$_SESSION['uid'].") OR (perms >= 32))");
}
else if ($p == 'getExperimentPermissions')
{
	if (isset($_GET['experiments'])){$experiments = $_GET['experiments'];}
	$data=$query->queryTable("SELECT id FROM ngs_experiment_series WHERE id IN ($experiments) AND (owner_id = ".$_SESSION['uid']." OR 1 = ".$_SESSION['uid'].")");
}
else if($p == 'getLanePermissions')
{
	if (isset($_GET['lanes'])){$lanes = $_GET['lanes'];}
	$data=$query->queryTable("SELECT id FROM ngs_lanes WHERE id IN ($lanes) AND (owner_id = ".$_SESSION['uid']." OR 1 = ".$_SESSION['uid'].")");
}
else if($p == 'getSamplePermissions')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->queryTable("SELECT id FROM ngs_samples WHERE id IN ($samples) AND (owner_id = ".$_SESSION['uid']." OR 1 = ".$_SESSION['uid'].")");
}
else if($p == 'deleteSelected')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	if (isset($_GET['lanes'])){$lanes = $_GET['lanes'];}
	if (isset($_GET['experiments'])){$experiments = $_GET['experiments'];}

	//	Deleted_Samples table
	$query->runSQL("INSERT INTO ngs_deleted_samples (sample_id, samplename, lane_id, experiment_series_id, user_delete, date)
					SELECT id, samplename, lane_id, series_id, ".$_SESSION['uid'].", NOW()
					from ngs_samples
					where id in ($samples)
				   ");

	//	RUN IDS
	$sample_run_ids=json_decode($query->queryTable("SELECT DISTINCT run_id FROM ngs_runlist WHERE sample_id IN ($samples)"));
	$lane_run_ids=json_decode($query->queryTable("SELECT DISTINCT run_id FROM ngs_runlist WHERE sample_id IN (SELECT id from ngs_samples WHERE lane_id in ($lanes))"));

	$all_run_ids = array();
	foreach($sample_run_ids as $sri){
		if(!in_array($sri->run_id, $all_run_ids)){
			array_push($all_run_ids, $sri->run_id);
		}
	}
	foreach($lane_run_ids as $lri){
		if(!in_array($lri->run_id, $all_run_ids)){
			array_push($all_run_ids, $lri->run_id);
		}
	}

	$clusteruser = json_decode($query->queryTable("SELECT clusteruser FROM users WHERE id = '".$_SESSION['uid']."'"));
	$samplenames = json_decode($query->queryTable("SELECT samplename FROM ngs_samples WHERE id in ($samples)"));
	$samplename_array = array();
	foreach($samplenames as $sn){
		array_push($samplename_array, $sn->samplename);
	}
	//	REMOVE SUCCESS FILES
	foreach ($all_run_ids as $ari){
		$outdir=json_decode($query->queryTable("SELECT outdir FROM ngs_runparams WHERE id = $ari"));
		$data = $funcs->removeAllSampleSuccessFiles($outdir[0]->outdir, $samplename_array, $clusteruser[0]->clusteruser);
	}

	//	GATHER DIRS
	$dirs=json_decode($query->queryTable("SELECT id FROM ngs_dirs WHERE id IN (SELECT dir_id FROM ngs_fastq_files WHERE sample_id IN ( $samples ))"));

	//	EXPERIMENT SERIES
	if ($experiments != ""){
		$query->runSQL("DELETE FROM ngs_experiment_series WHERE id IN ($experiments)");
	}

	//	LANES
	$query->runSQL("DELETE FROM ngs_temp_lane_files WHERE lane_id IN ($lanes)");
	$query->runSQL("DELETE FROM ngs_fastq_files WHERE lane_id IN ($lanes)");
	$query->runSQL("DELETE FROM ngs_temp_sample_files WHERE sample_id IN (SELECT id FROM ngs_samples WHERE lane_id IN ($lanes)");
	$query->runSQL("DELETE FROM ngs_sample_conds WHERE sample_id IN (SELECT id FROM ngs_samples WHERE lane_id IN ($lanes))");
	$query->runSQL("DELETE FROM ngs_samples WHERE lane_id IN ($lanes)");
	$query->runSQL("DELETE FROM ngs_lanes WHERE id IN ($lanes)");

	//	SAMPLES
	$query->runSQL("DELETE FROM ngs_temp_sample_files WHERE sample_id IN ($samples)");
	$query->runSQL("DELETE FROM ngs_sample_conds WHERE sample_id IN ($samples)");
	$query->runSQL("DELETE FROM ngs_fastq_files WHERE sample_id IN ($samples)");
	$query->runSQL("DELETE FROM ngs_samples WHERE id IN ($samples)");

	//	REMOVE DIRS
	foreach($dirs as $d){
		$dir_return=json_decode($query->queryTable("SELECT sample_id FROM ngs_fastq_files WHERE dir_id = ".$d->id));
		if(sizeof($dir_return) == 0){
			$query->runSQL("DELETE FROM ngs_dirs WHERE id = ".$d->id);
		}
	}

	//	OBTAIN WKEY INFORMATION FOR REPORT_LIST REMOVAL //
	$wkeys = array();
	$wkeys_json = json_decode($query->queryTable("SELECT wkey FROM ngs_runparams WHERE id IN (".implode(",", $all_run_ids).")"));
	foreach($wkeys_json as $wj){
		if(!in_array($wj->wkey, $wkeys) && $wj->wkey != NULL && $wj->wkey != ''){
			array_push($wkeys, $wj->wkey);
		}
	}

	//	USE WKEY FOR REPORT_LIST REMOVAL	//
	foreach($wkeys as $w){
		$query->runSQL("DELETE FROM report_list WHERE wkey = '$w'");
		$query->runSQL("DELETE FROM ngs_wkeylist WHERE wkey = '$w'");
	}

	//	OBTAIN PID IF RUNNING AND REMOVE	//
	//	Check to make sure this is nessicary	//

	$workflow_pids = json_decode($query->queryTable("SELECT runworkflow_pid FROM ngs_runparams WHERE run_id IN (".implode(",", $all_run_ids).")"));
	$wrapper_pids = json_decode($query->queryTable("SELECT wrapper_pid FROM ngs_runparams WHERE run_id IN (".implode(",", $all_run_ids).")"));

	foreach($workflow_pids as $wp){
		$cmd = "ps -ef | grep '[".substr($wp->runworkflow_pid, 0, 1)."]".substr($wp->runworkflow_pid, 1)."'";
		$pid_check = pclose(popen( $cmd, "r" ) );
		if($pid_check > 0 && $pid_check != NULL){
			pclose(popen( "kill -9 ".$wp->runworkflow_pid, "r" ) );
		}
	}
	foreach($wrapper_pids as $wp){
		$cmd = "ps -ef | grep '[".substr($wp->wrapper_pid, 0, 1)."]".substr($wp->wrapper_pid, 1)."'";
		$pid_check = pclose(popen( $cmd, "r" ) );
		if($pid_check > 0 && $pid_check != NULL){
			pclose(popen( "kill -9 ".$wp->wrapper_pid, "r" ) );
		}
	}

	//	RUNS
	$insert_query = "
	INSERT INTO ngs_deleted_runs
	(run_id, outdir, run_status, json_parameters,
	run_name, run_description, owner_id, group_id, perms,
	last_modified_user)
	SELECT id, outdir, run_status, json_parameters,
	run_name, run_description, owner_id, group_id, perms,
	last_modified_user
	FROM ngs_runparams WHERE id IN (".implode(",", $all_run_ids).")";
	$query->runSQL($insert_query);

	//	If sample is deleted, delete all run information
	//$query->runSQL("DELETE FROM ngs_runlist WHERE run_id IN (".implode(",", $all_run_ids).")");
	//$query->runSQL("DELETE FROM ngs_runparams WHERE id IN (".implode(",", $all_run_ids).")");
	$data = '';
}
else if ($p == 'intialRunCheck')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->queryTable("SELECT sample_id FROM ngs_fastq_files WHERE sample_id IN ($samples) AND total_reads > 0");
}
else if ($p == 'amazon_reupload')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->runSQL("
		UPDATE ngs_fastq_files
		SET aws_status = 2
		WHERE sample_id in ($samples)
		");
	$cmd = "cd ../../scripts && python aws_submit.py -c ".CONFIG." $samples  ".DOLPHIN_TOOLS_SRC_PATH." 2>&1 > ../tmp/logs/aws_uploads/".date("Y_m_d_H_i").".log &";
	$PID_COMMAND = popen( $cmd, "r" );
	pclose($PID_COMMAND);
	$data = json_encode($cmd);
}
else if ($p == "checksum_recheck")
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$cmd = "cd ../../scripts && mkdir -p ../tmp/logs/checksum_recheck && python md5sum_check.py ".BASE_PATH." $samples  2>&1 > ../tmp/logs/checksum_recheck/".date("Y_m_d_H_i").".log &";
	$PID_COMMAND = popen( $cmd, "r" );
	pclose($PID_COMMAND);
	$data = json_encode($cmd);
}
else if($p == 'encodeSampleEdit')
{
	$table_list = ['ngs_molecule','ngs_organism','ngs_source','ngs_donor','ngs_library_type','ngs_instrument_model','ngs_biosample_term','ngs_library_strategy'];
	$table_sample_link = ['molecule_id','organism_id','source_id','donor_id','library_type_id','instrument_model_id','biosample_term_id','library_strategy_id'];
	$experiment_series = ['grant'];
	$lanes = ['date_submitted','date_received'];
	$protocols = ['extraction', 'fragmentation_method'];
	$lab = ['lab'];
	$treatment = ['treatment_term_name','treatment_term_id','treatment_type','concentration','concentration_units','duration'.'duration_units'];
	$antibody_target = ['target','source','product_id','lot_id','host_organism','clonality','isotype','purifications','url'];
	if (isset($_GET['table'])){$table = $_GET['table'];}
	if (isset($_GET['field'])){$field = $_GET['field'];}
	if (isset($_GET['id'])){$id = $_GET['id'];}
	if (isset($_GET['sample_id'])){$sample_id = $_GET['sample_id'];}
	if($table == 'ngs_samples'){
		$data = $query->runSQL("
			UPDATE encode_submissions
			SET sub_status = '2'
			WHERE sample_id in ($id)
		");
	}else if (in_array($table,$table_list)){
		$field = $table_sample_link[array_search($table,$table_array)];
		$data = $query->runSQL("
			UPDATE encode_submissions
			SET sub_status = '2'
			WHERE sample_id in (
				SELECT id
				FROM ngs_samples
				WHERE id in ($sample_id)
			)
		");
		$data = json_decode($field);
	}else if ($table == "ngs_experiment_series"){
		if(in_array($field,$experiment_series)){
			$data = $query->runSQL("
				UPDATE encode_submissions
				SET sub_status = '2'
				WHERE sample_id in (
					SELECT id
					FROM ngs_samples
					WHERE series_id in ($id)
				)
			");
		}
	}else if ($table == "ngs_lanes"){
		if(in_array($field,$lanes)){
			$data = $query->runSQL("
				UPDATE encode_submissions
				SET sub_status = '2'
				WHERE sample_id in (
					SELECT id
					FROM ngs_samples
					WHERE lane_id in ($id)
				)
			");
		}
	}else if ($table == "ngs_protocols"){
		if(in_array($field,$protocols)){
			$data = $query->runSQL("
				UPDATE encode_submissions
				SET sub_status = '2'
				WHERE sample_id in (
					SELECT id
					FROM ngs_samples
					WHERE protocol_id in ($id)
				)
			");
		}
	}else if ($table == "ngs_lab"){
		if(in_array($field,$lab)){
			$data = $query->runSQL("
				UPDATE encode_submissions
				SET sub_status = '2'
				WHERE sample_id in (
					SELECT id
					FROM ngs_samples
					WHERE series_id in (
						SELECT lab_id
						FROM ngs_experiment_series
						WHERE lab_id in ($id)
					)
				)
			");
		}
	}else if ($table == "ngs_treatment"){
		if(in_array($field,$treatment)){
			$data = $query->runSQL("
				UPDATE encode_submissions
				SET sub_status = '2'
				WHERE sample_id in (
					SELECT id
					FROM ngs_samples
					WHERE treatment_id in ($id)
				)
			");
		}
	}else if ($table == "ngs_antibody_target"){
		if(in_array($field,$antibody_target)){
			$data = $query->runSQL("
				UPDATE encode_submissions
				SET sub_status = '2'
				WHERE sample_id in (
					SELECT id
					FROM ngs_samples
					WHERE antibody_lot_id in ($id)
				)
			");
		}
	}
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
