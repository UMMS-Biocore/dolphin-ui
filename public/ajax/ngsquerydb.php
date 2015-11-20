<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$query = new dbfuncs();

$data = "";
$uid = "";
$gids = "";
$perms = "";
$andPerms = "";

if (isset($_GET['p'])){$p = $_GET['p'];}

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

if($p == 'getRunSamples')
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
else if ($p == 'profileLoad')
{
    $data=$query->queryTable("
    SELECT photo_loc
    FROM users
    WHERE username = '".$_SESSION['user']."'"
    );
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
else if ($p == 'getInfoBoxData')
{
    if (isset($_GET['fieldname'])){$fieldname = $_GET['fieldname'];}
    $data=$query->queryTable("
    SELECT help_text
    FROM ngs_help
    WHERE field_name = '$fieldname';
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
else if ($p == 'checkOutputDir')
{
	if (isset($_GET['outdir'])){$outdir = $_GET['outdir'];}
	$data=$query->queryAVal("
    SELECT outdir
    FROM ngs_runparams
    where outdir = '$outdir'
    ");
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
	$SAMPLE_UPDATE=$query->runSQL("
	UPDATE ngs_samples
	SET group_id = $group_id
	WHERE series_id = $experiment
	");
	$data=json_encode('passed');
}
else if ($p == 'getExperimentSeriesGroup')
{
	if (isset($_GET['experiment'])){$experiment = $_GET['experiment'];}
	$data=$query->queryTable("
	SELECT group_id, owner_id
	FROM ngs_experiment_series
	WHERE id = $experiment
	");
}
else if ($p == 'getGroups')
{
	$data=$query->queryTable("
	SELECT id, name
	FROM groups
	WHERE id in (
		SELECT g_id
		FROM user_group
		WHERE u_id = ".$_SESSION['uid']."
	)
	");
}
else if ($p == 'getRunPerms')
{
	if (isset($_GET['run_id'])){$run_id = $_GET['run_id'];}
	$data=$query->queryAVal("
	SELECT perms
	FROM ngs_runparams
	WHERE id = $run_id
	");
}
else if ($p == 'changeRunGroup')
{
	if (isset($_GET['run_id'])){$run_id = $_GET['run_id'];}
	if (isset($_GET['group_id'])){$group_id = $_GET['group_id'];}
	$RUNPARAM_UPDATE=$query->runSQL("
	UPDATE ngs_runparams
	SET group_id = $group_id
	WHERE id = $run_id
	");
	$data=json_encode('pass');
}
else if ($p == 'changeRunPerms')
{
	if (isset($_GET['run_id'])){$run_id = $_GET['run_id'];}
	if (isset($_GET['perms'])){$perms = $_GET['perms'];}
	$RUNPARAM_UPDATE=$query->runSQL("
	UPDATE ngs_runparams
	SET perms = $perms
	WHERE id = $run_id
	");
	$data=json_encode('pass');
}
else if ($p == 'getAllUsers')
{
	if (isset($_GET['experiment'])){$experiment = $_GET['experiment'];}
	$owner_check=$query->queryAVal("
	SELECT owner_id
	FROM ngs_experiment_series
	WHERE id = $experiment
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
else if ($p == "changeOwnerExperiment")
{
	if (isset($_GET['owner_id'])){$owner_id = $_GET['owner_id'];}
	if (isset($_GET['experiment'])){$experiment = $_GET['experiment'];}
	
	//	EXPERIMENT SERIES
	$ES_UPDATE=$query->runSQL("
	UPDATE ngs_experiment_series
	SET owner_id = $owner_id
	WHERE id = $experiment
	");
	//	IMPORTS
	$IMPORTS_UPDATE=$query->runSQL("
	UPDATE ngs_lanes
	SET owner_id = $owner_id
	WHERE series_id = $experiment
	");
	//	SAMPLES
	$SAMPLE_UPDATE=$query->runSQL("
	UPDATE ngs_samples
	SET owner_id = $owner_id
	WHERE series_id = $experiment
	");
	$data=json_encode('passed');
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo $data;
exit;
?>
