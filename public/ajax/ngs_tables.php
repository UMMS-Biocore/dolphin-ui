<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$query = new dbfuncs();

//	Declair variables
$data = "";
$q = "";
$r = "";
$seg = "";
$search = "";
$uid = "";
$gids = "";
$perms = "";
$andPerms = "";
$run_type = "";

//	Grab passed variables
if (isset($_GET['p'])){$p = $_GET['p'];}
if (isset($_GET['q'])){$q = $_GET['q'];}
if (isset($_GET['r'])){$r = $_GET['r'];}
if (isset($_GET['seg'])){$seg = $_GET['seg'];}
if (isset($_GET['search'])){$search = $_GET['search'];}
if (isset($_GET['uid'])){$uid = $_GET['uid'];}
if (isset($_GET['gids'])){$gids = $_GET['gids'];}
//	Create permissions statements
if($uid != "" && $gids != "" && $_SESSION['uid'] != "1"){
    $perms = "WHERE (((group_id in ($gids)) AND (perms >= 15)) OR (owner_id = $uid) OR (perms >= 32))";
    $andPerms = "AND (((group_id in ($gids)) AND (perms >= 15)) OR (owner_id = $uid) OR (perms >= 32))";
}
//	inner join for samples table
$innerJoin = "LEFT JOIN ngs_source
                ON ngs_samples.source_id = ngs_source.id
                LEFT JOIN ngs_organism
                ON ngs_samples.organism_id = ngs_organism.id
                LEFT JOIN ngs_molecule
                ON ngs_samples.molecule_id = ngs_molecule.id
                LEFT JOIN ngs_genotype
                ON ngs_samples.genotype_id = ngs_genotype.id
                LEFT JOIN ngs_library_type
                ON ngs_samples.library_type_id = ngs_library_type.id
                LEFT JOIN ngs_biosample_type
                on ngs_samples.biosample_type_id = ngs_biosample_type.id
                LEFT JOIN ngs_instrument_model
                ON ngs_samples.instrument_model_id = ngs_instrument_model.id
                LEFT JOIN ngs_treatment_manufacturer
                ON ngs_samples.treatment_manufacturer_id = ngs_treatment_manufacturer.id";
$amazon_str = "AND ngs_fastq_files.dir_id = (SELECT ngs_dirs.id FROM ngs_dirs WHERE ngs_fastq_files.dir_id = ngs_dirs.id AND (ngs_dirs.amazon_bucket LIKE '%s3://%'))";
//	inner join for specific samples tables
$sampleJoin = "LEFT JOIN ngs_fastq_files
                ON ngs_samples.id = ngs_fastq_files.sample_id";
$sampleBackup = "CASE
					WHEN (SELECT COUNT(*) FROM ngs_fastq_files WHERE aws_status = 2 AND ngs_samples.id = ngs_fastq_files.sample_id) > 0 THEN '<td><button class=\"btn btn-warning\" disabled></td>'
					WHEN (SELECT COUNT(*) FROM ngs_fastq_files WHERE checksum != original_checksum AND (original_checksum != '' AND original_checksum IS NOT NULL) AND ngs_samples.id = ngs_fastq_files.sample_id) > 0 THEN '<td><button class=\"btn btn-flickr\" disabled></td>'
					WHEN (SELECT COUNT(*) FROM ngs_fastq_files WHERE checksum != backup_checksum AND (backup_checksum != '' AND backup_checksum IS NOT NULL) AND ngs_samples.id = ngs_fastq_files.sample_id $amazon_str) > 0 THEN '<td><button class=\"btn btn-danger\" disabled></td>'
					WHEN (SELECT COUNT(*) FROM ngs_fastq_files WHERE (backup_checksum = '' OR backup_checksum IS NULL) AND ngs_samples.id = ngs_fastq_files.sample_id $amazon_str) > 0 THEN '<td><button class=\"btn\" disabled></td>'
					WHEN (SELECT COUNT(*) FROM ngs_fastq_files WHERE date_modified < DATE_SUB(now(), INTERVAL 2 MONTH) AND ngs_samples.id = ngs_fastq_files.sample_id $amazon_str) > 0 THEN '<td><button class=\"btn btn-primary\" disabled></td>'
					WHEN (SELECT COUNT(*) FROM ngs_fastq_files WHERE ngs_samples.id = ngs_fastq_files.sample_id $amazon_str) = 0 THEN '<td></td>'
					ELSE '<td><button class=\"btn btn-success\" disabled></td>'
				END AS backup";
//	inner join for lane tables
$laneJoin = "LEFT JOIN ngs_facility
                ON ngs_lanes.facility_id = ngs_facility.id";
$laneBackup = "CASE
					WHEN (SELECT COUNT(*) FROM ngs_fastq_files WHERE aws_status = 2 AND ngs_lanes.id = ngs_fastq_files.lane_id) > 0 THEN '<td><button class=\"btn btn-warning\" disabled></td>'
					WHEN (SELECT COUNT(*) FROM ngs_fastq_files WHERE checksum != original_checksum AND (original_checksum != '' AND original_checksum IS NOT NULL) AND ngs_lanes.id = ngs_fastq_files.lane_id) > 0 THEN '<td><button class=\"btn btn-flickr\" disabled></td>'
					WHEN (SELECT COUNT(*) FROM ngs_fastq_files WHERE checksum != backup_checksum AND (backup_checksum != '' AND backup_checksum IS NOT NULL) AND ngs_lanes.id = ngs_fastq_files.lane_id $amazon_str) > 0 THEN '<td><button class=\"btn btn-danger\" disabled></td>'
					WHEN (SELECT COUNT(*) FROM ngs_fastq_files WHERE (backup_checksum = '' OR backup_checksum IS NULL) AND ngs_lanes.id = ngs_fastq_files.lane_id $amazon_str) > 0 THEN '<td><button class=\"btn\" disabled></td>'
					WHEN (SELECT COUNT(*) FROM ngs_fastq_files WHERE date_modified < DATE_SUB(now(), INTERVAL 2 MONTH) AND ngs_lanes.id = ngs_fastq_files.lane_id $amazon_str) > 0 THEN '<td><button class=\"btn btn-primary\" disabled></td>'
					WHEN (SELECT COUNT(*) FROM ngs_fastq_files WHERE ngs_lanes.id = ngs_fastq_files.lane_id $amazon_str) = 0 THEN '<td></td>'
					ELSE '<td><button class=\"btn btn-success\" disabled></td>'
				END AS backup";
//	inner join for experiment tables
$experimentSeriesJoin = "LEFT JOIN ngs_lab
                        ON ngs_experiment_series.lab_id = ngs_lab.id
                        LEFT JOIN ngs_organization
                        ON ngs_experiment_series.organization_id = ngs_organization.id";
//	grab date variables if passed
if (isset($_GET['start'])){$start = $_GET['start'];}
if (isset($_GET['end'])){$end = $_GET['end'];}

//	make the q val proper for queries (accordian box)
if($q == "Assay"){ $q = "library_type"; }
else { $q = strtolower($q); }

if ($p == 'getStatus')	//	Status tables
{
	$run_type = "";
	if($_SESSION['run_type'] == 1){
		$run_type = "WHERE ((run_name = 'Fastlane Initial Run') OR (run_name = 'Import Initial Run')) ";
		if($uid != "" && $gids != "" && $_SESSION['uid'] != "1"){
			$run_type .= "AND (((ngs_runparams.group_id in ($gids)) AND (ngs_runparams.perms >= 15)) OR (ngs_runparams.owner_id = $uid) OR (ngs_runparams.perms >= 32))";
		}
	}else if ($_SESSION['run_type'] == 2){
		$run_type = "WHERE ((run_name != 'Fastlane Initial Run') AND (run_name != 'Import Initial Run')) ";
		if($uid != "" && $gids != "" && $_SESSION['uid'] != "1"){
			$run_type .= "AND (((ngs_runparams.group_id in ($gids)) AND (ngs_runparams.perms >= 15)) OR (ngs_runparams.owner_id = $uid) OR (ngs_runparams.perms >= 32))";
		}
	}else{
		if($uid != "" && $gids != "" && $_SESSION['uid'] != "1"){
			$run_type = "WHERE (((ngs_runparams.group_id in ($gids)) AND (ngs_runparams.perms >= 15)) OR (ngs_runparams.owner_id = $uid) OR (ngs_runparams.perms >= 32))";
		}
	}
	$time="";
	if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
	$data=$query->queryTable("
	SELECT ngs_runparams.*, username
	FROM ngs_runparams
	JOIN users
	ON ngs_runparams.owner_id = users.id
	$run_type $time
	");
}
else if ($p == "getSelectedSamples")	//	Selected samples table
{
	//	Prepare selected sample search query
	$searchQuery = "";
	$splitIndex = ['id','lane_id'];
	$typeCount = 0;
	if (substr($search, 0, 1) == "$"){
		//only lanes selected
		$search = substr($search, 1, strlen($search));
		$splt = explode(",", $search);
		foreach ($splt as $x){
			$searchQuery .= "ngs_samples.$splitIndex[1] = $x";
			if($x != end($splt)){
				$searchQuery .= " OR ";
			}
		}
	}
	else if(substr($search, strlen($search) - 1, strlen($search)) == "$"){
		//only samples selected
		$search = substr($search, 0, strlen($search) - 1);
		$splt = explode(",", $search);
		foreach ($splt as $x){
			$searchQuery .= "ngs_samples.$splitIndex[0] = $x";
			if($x != end($splt)){
				$searchQuery .= " OR ";
			}
		}
	}
	else{
		$splt = explode("$", $search);
		foreach ($splt as $s){
			$secondSplt = explode(",", $s);
			foreach ($secondSplt as $x){
				$searchQuery .= "ngs_samples.$splitIndex[$typeCount] = $x";
				if($x != end($secondSplt)){
					$searchQuery .= " OR ";
				}
			}
			if($s != end($splt)){
					$searchQuery .= " OR ";
			}
			$typeCount = $typeCount + 1;
		}
	}
	
	$sample_perms = "";
	if ($uid != "1"){
		$sample_perms = "AND (((ngs_samples.group_id in ($gids)) AND (ngs_samples.perms >= 15)) OR (ngs_samples.owner_id = $uid))";
	}
	
	$time="";
	if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
	$data=$query->queryTable("
	SELECT ngs_samples.id, ngs_samples.series_id, ngs_samples.lane_id, name, samplename, title, source, organism, molecule, total_reads, barcode, description, avg_insert_size, read_length, concentration, time, biological_replica, technical_replica, spike_ins, adapter,
            notebook_ref, notes, genotype, library_type, biosample_type, instrument_model, treatment_manufacturer, ngs_samples.owner_id,
    $sampleBackup
	FROM ngs_samples
    $innerJoin
	$sampleJoin
	WHERE $searchQuery 
	$sample_perms
	$time
	");
}
else if($search != "")	//	If there is a search term(s) (experiments, lanes, samples)
{
	//	Prepare search query
	//	Merges multiple search terms for SQL query
	$searchQuery = "";
	$splt = explode("$", $search);
	foreach ($splt as $s){
		$queryArray = explode("=", $s);
        if(sizeof($queryArray) == 2){
            $spltTable = $queryArray[0];
            $spltValue = $queryArray[1];
            $searchQuery .= "$spltTable = \"$spltValue\"";
            if($s != end($splt)){
                $searchQuery .= " AND ";
            }
        }
	}
	//	browse (search included)
	if($seg == "browse")
	{
		if($p == "getLanes")
		{
			$time="";
			if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_lanes.id, series_id, name, facility, total_reads, total_samples, cost, phix_requested, phix_in_lane, notes, owner_id,
			$laneBackup
			FROM ngs_lanes
            $laneJoin
			WHERE ngs_lanes.id
			IN (SELECT ngs_samples.lane_id FROM ngs_samples $innerJoin WHERE $searchQuery) $andPerms $time
			");
		}
		else if($p == "getSamples")
		{
			$sample_perms = "";
			$time="";
			if ($uid != "1"){
				$sample_perms = "AND (((ngs_samples.group_id in ($gids)) AND (ngs_samples.perms >= 15)) OR (ngs_samples.owner_id = $uid))";
			}
			if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_samples.id, ngs_samples.series_id, ngs_samples.lane_id, name, samplename, title, source, organism, molecule, total_reads, barcode, description, avg_insert_size, read_length, concentration, time, biological_replica, technical_replica, spike_ins, adapter,
            notebook_ref, notes, genotype, library_type, biosample_type, instrument_model, treatment_manufacturer, ngs_samples.owner_id,
			$sampleBackup
			FROM ngs_samples
            $innerJoin
            $sampleJoin
			WHERE $searchQuery
            $sample_perms
            $time
			");
		}
		else if($p == "getExperimentSeries")
		{
			$time="";
			if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_experiment_series.id, experiment_name, summary, design, lab, organization, `grant`
			FROM ngs_experiment_series
            $experimentSeriesJoin
			WHERE ngs_experiment_series.id
			IN (SELECT ngs_samples.series_id FROM ngs_samples $innerJoin WHERE $searchQuery) $andPerms $time
			");
		}
	}
	else
	{
		//	details (search included)
		if($p == "getLanes" && $q != "")
		{
			$time="";
			if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_lanes.id, ngs_lanes.series_id, name, facility, total_reads, total_samples, cost, phix_requested, phix_in_lane, notes, owner_id,
			$laneBackup
			FROM ngs_lanes
            $laneJoin
			WHERE ngs_lanes.id
			IN (SELECT ngs_samples.lane_id FROM ngs_samples $innerJoin WHERE $searchQuery)
			AND ngs_lanes.series_id = $q $andPerms $time
			");
		}
		else if($p == "getSamples" && $r != "")
		{
			$sample_perms = "";
			$time="";
			if ($uid != "1"){
				$sample_perms = "AND (((ngs_samples.group_id in ($gids)) AND (ngs_samples.perms >= 15)) OR (ngs_samples.owner_id = $uid))";
			}
			if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_samples.id, ngs_samples.series_id, ngs_samples.lane_id,name, samplename, title, source, organism, molecule, total_reads, barcode, description, avg_insert_size, read_length, concentration, time, biological_replica, technical_replica, spike_ins, adapter,
            notebook_ref, notes, genotype, library_type, biosample_type, instrument_model, treatment_manufacturer, ngs_samples.owner_id,
			$sampleBackup
			FROM ngs_samples
            $innerJoin
            $sampleJoin
			WHERE $searchQuery
			AND ngs_samples.lane_id = $r
            $sample_perms
            $time
			");
		}
		else if($p == "getSamples" && $q != "")
		{
			$sample_perms = "";
			$time="";
			if ($uid != "1"){
				$sample_perms = "AND (((ngs_samples.group_id in ($gids)) AND (ngs_samples.perms >= 15)) OR (ngs_samples.owner_id = $uid))";
			}
			if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_samples.id, ngs_samples.series_id, ngs_samples.lane_id, name, samplename, title, source, organism, molecule, total_reads, barcode, description, avg_insert_size, read_length, concentration, time, biological_replica, technical_replica, spike_ins, adapter,
            notebook_ref, notes, genotype, library_type, biosample_type, instrument_model, treatment_manufacturer, ngs_samples.owner_id,
			$sampleBackup
			FROM ngs_samples
            $innerJoin
            $sampleJoin
			WHERE $searchQuery
			AND ngs_samples.series_id = $q 
            $sample_perms
            $time
			");
		}
		else if($p == "getExperimentSeries")
		{
			$time="";
			if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_experiment_series.id, experiment_name, summary, design, lab, organization, `grant`
			FROM ngs_experiment_series
            $experimentSeriesJoin
			WHERE ngs_experiment_series.id
			IN (SELECT ngs_samples.series_id FROM ngs_samples $innerJoin WHERE $searchQuery) $andPerms $time
			");
		}
	}
}
else	//	if there isn't a search term (experiments, lanes, samples)
{
	//	browse (no search)
	if($seg == "browse")
	{
		if($p == "getExperimentSeries")
		{
			$time="";
			if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_experiment_series.id, experiment_name, summary, design, lab, organization, `grant`
			FROM ngs_experiment_series
            $experimentSeriesJoin
			WHERE ngs_experiment_series.id
			IN (SELECT ngs_samples.series_id FROM ngs_samples $innerJoin WHERE ngs_samples.$q = \"$r\") $andPerms $time
			");
		}
		else if($p == "getLanes")
		{
			$time="";
			if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_lanes.id, ngs_lanes.series_id, name, facility, total_reads, total_samples, cost, phix_requested, phix_in_lane, notes, owner_id,
			$laneBackup
			FROM ngs_lanes
            $laneJoin
			WHERE ngs_lanes.id
			IN (SELECT ngs_samples.lane_id FROM ngs_samples $innerJoin WHERE ngs_samples.$q = \"$r\") $andPerms $time
			");
		}
		else if($p == "getSamples")
		{
			$sample_perms = "";
			$time="";
			if ($uid != "1"){
				$sample_perms = "AND (((ngs_samples.group_id in ($gids)) AND (ngs_samples.perms >= 15)) OR (ngs_samples.owner_id = $uid))";
			}
			if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_samples.id, ngs_samples.series_id, ngs_samples.lane_id,name, samplename, title, source, organism, molecule, total_reads, barcode, description, avg_insert_size, read_length, concentration, time, biological_replica, technical_replica, spike_ins, adapter,
            notebook_ref, notes, genotype, library_type, biosample_type, instrument_model, treatment_manufacturer, ngs_samples.owner_id,
			$sampleBackup
			FROM ngs_samples
            $innerJoin
            $sampleJoin
			WHERE ngs_samples.$q = \"$r\"
            $sample_perms
            $time
			");
		}
		else if($p == "getProtocols")
		{
			$time="";
			if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT id, name, growth, treatment
			FROM ngs_protocols
			WHERE ngs_samples.$q = \"$r\" $andPerms $time
			");
		}
	}
	else
	{
		//	details (no search)
		if($p == "getLanes" && $q != "")
		{
			$time="";
			if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_lanes.id, ngs_lanes.series_id, name, facility, total_reads, total_samples, cost, phix_requested, phix_in_lane, notes, owner_id,
			$laneBackup
			FROM ngs_lanes
            $laneJoin
			WHERE ngs_lanes.series_id = $q $andPerms $time
			");
		}
		else if($p == "getSamples" && $r != "")
		{
			$sample_perms = "";
			$time="";
			if ($uid != "1"){
				$sample_perms = "AND (((ngs_samples.group_id in ($gids)) AND (ngs_samples.perms >= 15)) OR (ngs_samples.owner_id = $uid))";
			}
			if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_samples.id, ngs_samples.series_id, ngs_samples.lane_id, name, samplename, title, source, organism, molecule, total_reads, barcode, description, avg_insert_size, read_length, concentration, time, biological_replica, technical_replica, spike_ins, adapter,
            notebook_ref, notes, genotype, library_type, biosample_type, instrument_model, treatment_manufacturer, ngs_samples.owner_id,
			$sampleBackup
			FROM ngs_samples
            $innerJoin
            $sampleJoin
			WHERE ngs_samples.lane_id = $r
            $sample_perms
            $time
			");
		}
		else if($p == "getSamples" && $q != "")
		{
			$sample_perms = "";
			$time="";
			if ($uid != "1"){
				$sample_perms = "AND (((ngs_samples.group_id in ($gids)) AND (ngs_samples.perms >= 15)) OR (ngs_samples.owner_id = $uid))";
			}
			if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_samples.id, ngs_samples.series_id, ngs_samples.lane_id, name, samplename, title, source, organism, molecule, total_reads, barcode, description, avg_insert_size, read_length, concentration, time, biological_replica, technical_replica, spike_ins, adapter,
            notebook_ref, notes, genotype, library_type, biosample_type, instrument_model, treatment_manufacturer, ngs_samples.owner_id,
			$sampleBackup
			FROM ngs_samples
            $innerJoin
            $sampleJoin
			WHERE ngs_samples.series_id = $q
            $sample_perms
            $time
			");
		}
		//	index
		else if($p == "getExperimentSeries")
		{
			$time="";
			if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_experiment_series.id, experiment_name, summary, design, lab, organization, `grant`
			FROM ngs_experiment_series
            $experimentSeriesJoin
            $perms $time
			");
		}
		else if($p == "getProtocols")
		{
			$time="";
			if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT id, name, growth, treatment
			FROM ngs_protocols $perms $time
			");
		}
		else if($p == "getLanes")
		{
			$time="";
			if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_lanes.id, ngs_lanes.series_id, name, facility, total_reads, total_samples, cost, phix_requested, phix_in_lane, notes, owner_id,
			$laneBackup
			FROM ngs_lanes
            $laneJoin
            $perms $time
			");
		}
		else if($p == "getSamples")
		{
			$sample_perms = "";
			$time="";
			if ($uid != "1"){
				$sample_perms = "WHERE (((ngs_samples.group_id in ($gids)) AND (ngs_samples.perms >= 15)) OR (ngs_samples.owner_id = $uid))";
			}
			if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_samples.id, ngs_samples.series_id, ngs_samples.lane_id, name, samplename, title, source, organism, molecule, total_reads, barcode, description, avg_insert_size, read_length, concentration, time, biological_replica, technical_replica, spike_ins, adapter,
            notebook_ref, notes, genotype, library_type, biosample_type, instrument_model, treatment_manufacturer, ngs_samples.owner_id,backup_checksum,
			$sampleBackup
			FROM ngs_samples
            $innerJoin
            $sampleJoin
            $sample_perms
            $time
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
