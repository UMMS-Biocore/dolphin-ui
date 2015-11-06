<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$query = new dbfuncs();

$pDictionary = ['getSelectedSamples', 'submitPipeline', 'getStatus', 'getRunSamples', 'grabReload', 'getReportNames', 'lanesToSamples',
				'checkMatePaired', 'getAllSampleIds', 'getLaneIdFromSample', 'getSingleSample', 'getSeriesIdFromLane', 'getAllLaneIds',
                'getGIDs', 'getSampleNames', 'getWKey', 'getFastQCBool', 'getReportList', 'getTSVFileList', 'getExperimentSeriesGroup',
                'getInfoBoxData', 'getSamplesFromName', 'getLanesWithSamples', 'changeDataGroup', 'changeDataGroupNames',
                'getLanesFromName', 'getSamplesfromExperimentSeries', 'getExperimentIdFromSample','getCustomTSV'];

$data = "";
                
$q = "";
$r = "";
$seg = "";
$search = "";
$uid = "";
$gids = "";
$perms = "";
$andPerms = "";

if (isset($_GET['p'])){$p = $_GET['p'];}
if (isset($_GET['q'])){$q = $_GET['q'];}
if (isset($_GET['r'])){$r = $_GET['r'];}
if (isset($_GET['seg'])){$seg = $_GET['seg'];}
if (isset($_GET['search'])){$search = $_GET['search'];}

if (isset($_GET['uid'])){$uid = $_GET['uid'];}
if (isset($_GET['gids'])){$gids = $_GET['gids'];}
if($uid != "" && $gids != ""){
    $perms = "WHERE (((group_id in ($gids)) AND (perms >= 15)) OR (owner_id = $uid))";
    $andPerms = "AND (((group_id in ($gids)) AND (perms >= 15)) OR (owner_id = $uid))";
}

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
                
$sampleJoin = "LEFT JOIN ngs_fastq_files
                ON ngs_samples.id = ngs_fastq_files.sample_id";
                
$laneJoin = "LEFT JOIN ngs_facility
                ON ngs_lanes.facility_id = ngs_facility.id";

$experimentSeriesJoin = "LEFT JOIN ngs_lab
                        ON ngs_experiment_series.lab_id = ngs_lab.id
                        LEFT JOIN ngs_organization
                        ON ngs_experiment_series.organization_id = ngs_organization.id";

if (isset($_GET['start'])){$start = $_GET['start'];}
if (isset($_GET['end'])){$end = $_GET['end'];}

//make the q val proper for queries
if($q == "Assay"){ $q = "library_type"; }
else { $q = strtolower($q); }

if($search != "" && !in_array($p, $pDictionary)){
	//Prepare search query
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
    
	//browse (search included)
	if($seg == "browse")
	{
		if($p == "getLanes")
		{
			$time="";
			if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_lanes.id,name, facility, total_reads, total_samples, cost, phix_requested, phix_in_lane, notes, owner_id
			FROM ngs_lanes
            $laneJoin
			WHERE ngs_lanes.id
			IN (SELECT ngs_samples.lane_id FROM ngs_samples $innerJoin WHERE $searchQuery) $andPerms $time
			");
		}
		else if($p == "getSamples")
		{
			$time="";
			if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_samples.id, name, samplename, title, source, organism, molecule, total_reads, barcode, description, avg_insert_size, read_length, concentration, time, biological_replica, technical_replica, spike_ins, adapter,
            notebook_ref, notes, genotype, library_type, biosample_type, instrument_model, treatment_manufacturer, ngs_samples.owner_id
			FROM ngs_samples
            $innerJoin
            $sampleJoin
			WHERE $searchQuery
            AND (((ngs_samples.group_id in ($gids)) AND (ngs_samples.perms >= 15)) OR (ngs_samples.owner_id = $uid))
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
		//details (search included)
		if($p == "getLanes" && $q != "")
		{
			$time="";
			if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_lanes.id,name, facility, total_reads, total_samples, cost, phix_requested, phix_in_lane, notes, owner_id
			FROM ngs_lanes
            $laneJoin
			WHERE ngs_lanes.id
			IN (SELECT ngs_samples.lane_id FROM ngs_samples $innerJoin WHERE $searchQuery)
			AND ngs_lanes.series_id = $q $andPerms $time
			");
		}
		else if($p == "getSamples" && $r != "")
		{
			$time="";
			if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_samples.id, name, samplename, title, source, organism, molecule, total_reads, barcode, description, avg_insert_size, read_length, concentration, time, biological_replica, technical_replica, spike_ins, adapter,
            notebook_ref, notes, genotype, library_type, biosample_type, instrument_model, treatment_manufacturer, ngs_samples.owner_id
			FROM ngs_samples
            $innerJoin
            $sampleJoin
			WHERE $searchQuery
			AND ngs_samples.lane_id = $r
            AND (((ngs_samples.group_id in ($gids)) AND (ngs_samples.perms >= 15)) OR (ngs_samples.owner_id = $uid))
            $time
			");
		}
		else if($p == "getSamples" && $q != "")
		{
			$time="";
			if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_samples.id, name, samplename, title, source, organism, molecule, total_reads, barcode, description, avg_insert_size, read_length, concentration, time, biological_replica, technical_replica, spike_ins, adapter,
            notebook_ref, notes, genotype, library_type, biosample_type, instrument_model, treatment_manufacturer, ngs_samples.owner_id
			FROM ngs_samples
            $innerJoin
            $sampleJoin
			WHERE $searchQuery
			AND ngs_samples.series_id = $q 
            AND (((ngs_samples.group_id in ($gids)) AND (ngs_samples.perms >= 15)) OR (ngs_samples.owner_id = $uid))
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
			IN (SELECT ngs_samples.series_id FROM ngs_samples WHERE $searchQuery) $andPerms $time
			");
		}
	}
}
else if (!in_array($p, $pDictionary))
{
	//browse (no search)
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
			SELECT ngs_lanes.id,name, facility, total_reads, total_samples, cost, phix_requested, phix_in_lane, notes, owner_id
			FROM ngs_lanes
            $laneJoin
			WHERE ngs_lanes.id
			IN (SELECT ngs_samples.lane_id FROM ngs_samples $innerJoin WHERE ngs_samples.$q = \"$r\") $andPerms $time
			");
		}
		else if($p == "getSamples")
		{
			$time="";
			if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_samples.id, name, samplename, title, source, organism, molecule, total_reads, barcode, description, avg_insert_size, read_length, concentration, time, biological_replica, technical_replica, spike_ins, adapter,
            notebook_ref, notes, genotype, library_type, biosample_type, instrument_model, treatment_manufacturer, ngs_samples.owner_id
			FROM ngs_samples
            $innerJoin
            $sampleJoin
			WHERE ngs_samples.$q = \"$r\"
            AND (((ngs_samples.group_id in ($gids)) AND (ngs_samples.perms >= 15)) OR (ngs_samples.owner_id = $uid))
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
		//details (no search)
		if($p == "getLanes" && $q != "")
		{
			$time="";
			if (isset($start)){$time="WHERE `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_lanes.id,name, facility, total_reads, total_samples, cost, phix_requested, phix_in_lane, notes, owner_id
			FROM ngs_lanes
            $laneJoin
			WHERE ngs_lanes.series_id = $q $andPerms $time
			");
		}
		else if($p == "getSamples" && $r != "")
		{
			$time="";
			if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_samples.id, name, samplename, title, source, organism, molecule, total_reads, barcode, description, avg_insert_size, read_length, concentration, time, biological_replica, technical_replica, spike_ins, adapter,
            notebook_ref, notes, genotype, library_type, biosample_type, instrument_model, treatment_manufacturer, ngs_samples.owner_id
			FROM ngs_samples
            $innerJoin
            $sampleJoin
			WHERE ngs_samples.lane_id = $r
            AND (((ngs_samples.group_id in ($gids)) AND (ngs_samples.perms >= 15)) OR (ngs_samples.owner_id = $uid))
            $time
			");
		}
		else if($p == "getSamples" && $q != "")
		{
			$time="";
			if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_samples.id, name, samplename, title, source, organism, molecule, total_reads, barcode, description, avg_insert_size, read_length, concentration, time, biological_replica, technical_replica, spike_ins, adapter,
            notebook_ref, notes, genotype, library_type, biosample_type, instrument_model, treatment_manufacturer, ngs_samples.owner_id
			FROM ngs_samples
            $innerJoin
            $sampleJoin
			WHERE ngs_samples.series_id = $q
            AND (((ngs_samples.group_id in ($gids)) AND (ngs_samples.perms >= 15)) OR (ngs_samples.owner_id = $uid))
            $time
			");
		}
		//index
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
			SELECT ngs_lanes.id,name, facility, total_reads, total_samples, cost, phix_requested, phix_in_lane, notes, owner_id
			FROM ngs_lanes
            $laneJoin
            $perms $time
			");
		}
		else if($p == "getSamples")
		{
			$time="";
			if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
			$data=$query->queryTable("
			SELECT ngs_samples.id, name, samplename, title, source, organism, molecule, total_reads, barcode, description, avg_insert_size, read_length, concentration, time, biological_replica, technical_replica, spike_ins, adapter,
            notebook_ref, notes, genotype, library_type, biosample_type, instrument_model, treatment_manufacturer, ngs_samples.owner_id
			FROM ngs_samples
            $innerJoin
            $sampleJoin
            WHERE (((ngs_samples.group_id in ($gids)) AND (ngs_samples.perms >= 15)) OR (ngs_samples.owner_id = $uid))
            $time
			");
		}
	}
}
else if ($p == "getSelectedSamples")
{

	//Prepare selected sample search query
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
	$time="";
	if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
	$data=$query->queryTable("
	SELECT ngs_samples.id, name, samplename, title, source, organism, molecule, barcode, description, avg_insert_size, read_length, concentration, time, biological_replica, technical_replica, spike_ins, adapter,
    notebook_ref, notes, genotype, library_type, biosample_type, instrument_model, treatment_manufacturer, ngs_samples.owner_id
    FROM ngs_samples
    $innerJoin
	WHERE $searchQuery $andPerms $time
	");
}
else if ($p =='getStatus')
{
	$time="";
	if (isset($start)){$time="and `date_created`>='$start' and `date_created`<='$end'";}
	$data=$query->queryTable("
	SELECT id, run_group_id, run_name, wkey, outdir, run_description, run_status
	FROM ngs_runparams
	$perms $time
	");
}
else if($p == 'getRunSamples')
{
	//Grab Variables
	if (isset($_GET['runID'])){$runID = $_GET['runID'];}

	$data=$query->queryTable("
	SELECT DISTINCT sample_id
	FROM ngs_runlist
	WHERE ngs_runlist.run_id = $runID $andPerms
	");
}
else if ($p == 'grabReload')
{
	//Grab variables
	if (isset($_GET['groupID'])){$groupID = $_GET['groupID'];}

	$data=$query->queryTable("
	SELECT outdir, json_parameters, run_name, run_description
	FROM ngs_runparams
	WHERE ngs_runparams.id = $groupID $andPerms
	");
}
else if ($p == 'getReportNames')
{
	if (isset($_GET['runid'])){$runid = $_GET['runid'];}
    if (isset($_GET['samp'])){$samp = $_GET['samp'];}
	$sampleQuery = '';
    $samples = explode(",", $samp);
    
	foreach($samples as $s){
		$sampleQuery.= 'ngs_runlist.sample_id = '+ $s;
		if($s != end($samples)){
			$sampleQuery.= ' OR ';
		}
	}

	$data=$query->queryTable("
		SELECT distinct(ngs_fastq_files.file_name), ngs_runparams.outdir
		FROM ngs_fastq_files, ngs_runparams, ngs_runlist
		WHERE ngs_runlist.sample_id = ngs_fastq_files.sample_id
		AND ngs_runparams.id = ngs_fastq_files.lane_id
			AND ngs_fastq_files.lane_id = $runid
			AND ( $sampleQuery )
            $andPerms;
	");
}
else if ($p == 'lanesToSamples')
{
	if (isset($_GET['lane'])){$lane = $_GET['lane'];}
	$data=$query->queryTable("
		SELECT id
		FROM ngs_samples
		WHERE ngs_samples.lane_id = $lane $andPerms
	");
}
else if ($p == 'getAllSampleIds')
{
	$data=$query->queryTable("
		SELECT id
		FROM ngs_samples $perms
	");
}
else if ($p == 'getAllLaneIds')
{
	$data=$query->queryTable("
		SELECT id
		FROM ngs_lanes $perms
	");
}
else if ($p == 'getLaneIdFromSample')
{
	if (isset($_GET['sample'])){$sample = $_GET['sample'];}
	$data=$query->queryTable("
		SELECT id
		FROM ngs_lanes
		where id =
				(select lane_id
				from ngs_samples
				where ngs_samples.id = $sample)
        $andPerms;
	");
}
else if ($p == 'getExperimentIdFromSample')
{
    if (isset($_GET['sample'])){$sample = $_GET['sample'];}
    $data=$query->queryTable("
		SELECT id
		FROM ngs_experiment_series
		where id =
				(select series_id
				from ngs_samples
				where ngs_samples.id = $sample);
	");
}
else if($p == 'getSingleSample')
{
	if (isset($_GET['sample'])){$sample = $_GET['sample'];}
	$data=$query->queryTable("
		SELECT id, name, samplename
		FROM ngs_samples
		where id = $sample $andPerms
	");
}
else if($p == 'getSeriesIdFromLane')
{
	if (isset($_GET['lane'])){$lane = $_GET['lane'];}
	$data=$query->queryTable("
		SELECT series_id
		FROM ngs_lanes
		where id = $lane $andPerms
	");
}
else if ($p == 'checkMatePaired')
{
	if (isset($_GET['runid'])){$runid = $_GET['runid'];}
	$data=$query->queryTable("
		SELECT json_parameters
		FROM ngs_runparams
		where id = $runid $andPerms
	"); 
}
else if ($p == 'getSampleNames')
{
    if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->queryTable("
		SELECT name, samplename
		FROM ngs_samples
		where id in ($samples) $andPerms
	");  
}
else if ($p == 'getWKey')
{
    if (isset($_GET['run_id'])){$run_id = $_GET['run_id'];}
    $data=$query->queryTable("
    SELECT wkey
    FROM ngs_runparams
    WHERE id = $run_id
    ");
}
else if ($p == 'getFastQCBool')
{
    if (isset($_GET['id'])){$id = $_GET['id'];}
    $data=$query->queryTable("
    SELECT json_parameters
    FROM ngs_runparams
    WHERE id = $id
    ");
}
else if ($p == 'getReportList')
{
    if (isset($_GET['wkey'])){$wkey = $_GET['wkey'];}
    $data=$query->queryTable("
    SELECT version, type, file
    FROM report_list
    WHERE wkey = '$wkey'
    ");
}
else if ($p == 'getTSVFileList')
{
    if (isset($_GET['wkey'])){$wkey = $_GET['wkey'];}
    $data=$query->queryTable("
    SELECT file
    FROM report_list
    WHERE wkey = '$wkey' and file like '%.tsv'
    ");
}
else if ($p == 'getInfoBoxData')
{
    if (isset($_GET['fieldname'])){$fieldname = $_GET['fieldname'];}
    $data=$query->queryTable("
    SELECT help_text
    FROM ngs_help
    WHERE field_name = '$fieldname';
    ");
}
else if($p == 'getSamplesFromName')
{
    if (isset($_GET['names'])){$names = $_GET['names'];}
    if (isset($_GET['lane'])){$lane = $_GET['lane'];}
    if (isset($_GET['experiment'])){$experiment = $_GET['experiment'];}
    $names = explode(",", $names);
    $sqlnames = "";
    foreach($names as $n){
        if($n != end($names)){
            $sqlnames.= "'".$n."',";
        }else{
            $sqlnames.= "'".$n."'";    
        }
    }
    $data=$query->queryTable("
    SELECT DISTINCT ns.id
    FROM ngs_samples ns, ngs_lanes nl, ngs_experiment_series ne 
    WHERE ns.name in ($sqlnames)
    AND ns.lane_id IN (SELECT id from ngs_lanes where name in ($lane))
    AND ns.series_id IN (SELECT id from ngs_experiment_series where experiment_name = '$experiment');
    ");
}
else if ($p == 'getLanesWithSamples')
{
    $data=$query->queryTable("
    SELECT ngs_lanes.id, ngs_lanes.owner_id
    FROM ngs_lanes
    WHERE ngs_lanes.id in (
        SELECT ngs_samples.lane_id
        FROM ngs_samples
        WHERE id in (
            SELECT ngs_fastq_files.sample_id
            FROM ngs_fastq_files
            WHERE total_reads > 0
        )
    )
    ");
}
else if ($p == 'getSamplesfromExperimentSeries')
{
    if (isset($_GET['experiment'])){$experiment = $_GET['experiment'];}
    $data=$query->queryTable("
    SELECT id
    FROM ngs_samples
    where series_id = $experiment
    ");
}
else if ($p == 'getCustomTSV')
{
	$data=$query->queryTable("
    SELECT name, file
    FROM ngs_createdtables
    where owner_id = ".$_SESSION['uid']
    );
}
else if ($p == 'changeDataGroupNames')
{
	if (isset($_GET['experiment'])){$experiment = $_GET['experiment'];}
	$owner_check=$query->queryAVal("
	SELECT owner_id
	FROM ngs_experiment_series
	WHERE id = $experiment
	");
	if($owner_check == $_SESSION['uid']){
		$data=$query->queryTable("
		SELECT id,name
		FROM groups
		WHERE owner_id = " . $_SESSION['uid'] . "
		");
	}else{
		$data=json_encode("");
	}
}
else if ($p == 'changeDataGroup')
{
	if (isset($_GET['group_id'])){$group_id = $_GET['group_id'];}
	if (isset($_GET['experiment'])){$experiment = $_GET['experiment'];}
	
	//	EXPERIMENT SERIES
	$ES_UPDATE=$query->runSQL("
	UPDATE ngs_experiment_series
	SET group_id = $group_id
	WHERE id = $experiment
	");
	//	IMPORTS
	$IMPORTS_UPDATE=$query->runSQL("
	UPDATE ngs_lanes
	SET group_id = $group_id
	WHERE series_id = $experiment
	");
	//	SAMPLES
	$ES_UPDATE=$query->runSQL("
	UPDATE ngs_samples
	SET group_id = $group_id
	WHERE series_id = $experiment
	");
	$data=json_encode('passed');
}
else if ($p == 'getExperimentSeriesGroup')
{
	if (isset($_GET['experiment'])){$experiment = $_GET['experiment'];}
	$data=json_encode($query->queryAVal("
	SELECT group_id
	FROM ngs_experiment_series
	WHERE id = $experiment
	"));
}


header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo $data;
exit;
?>
