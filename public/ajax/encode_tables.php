<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");
if (!isset($_SESSION) || !is_array($_SESSION)) session_start();
$query = new dbfuncs();

if (isset($_GET['p'])){$p = $_GET['p'];}
$data = '';

if($p == 'getDonors')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->queryTable("
		SELECT *
		FROM ngs_donor
		WHERE id
		IN (SELECT ngs_samples.donor_id FROM ngs_samples WHERE id IN ($samples))
		");
}
else if($p == 'getExperiments')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->queryTable("
		SELECT DISTINCT ngs_samples.id as sample_id, ngs_samples.samplename, ngs_lab.lab, ngs_experiment_series.`grant`, ngs_library_strategy.library_strategy,
		ngs_lab.id as lab_id, ngs_protocols.id as protocol_id, ngs_samples.description, ngs_samples.experiment_acc, ngs_samples.experiment_uuid,
		ngs_library_strategy.id as library_strategy_id, ngs_experiment_series.id as experiment_series_id,
		ngs_organism.organism, ngs_samples.organism_id
		FROM ngs_samples
		LEFT JOIN ngs_organism
		ON ngs_samples.organism_id = ngs_organism.id
		LEFT JOIN ngs_experiment_series
		ON ngs_experiment_series.id = ngs_samples.series_id
		LEFT JOIN ngs_lab
		ON ngs_lab.id = ngs_experiment_series.lab_id
		LEFT JOIN ngs_protocols
		ON ngs_protocols.id = ngs_samples.protocol_id
		LEFT JOIN ngs_library_strategy
		ON ngs_library_strategy.id = ngs_protocols.library_strategy_id
		WHERE ngs_samples.id in ($samples)
		");
}
else if($p == 'getTreatments')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->queryTable("
		SELECT *
		FROM ngs_treatment
		WHERE id in (
			SELECT treatment_id
			FROM ngs_samples
			WHERE id in ($samples)
		)
		");
}
else if($p == 'getBiosamples')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->queryTable("
		SELECT ngs_samples.id as sample_id, ngs_samples.samplename, ngs_donor.donor, ngs_biosample_term.biosample_term_name, ngs_biosample_term.biosample_term_id,
		ngs_biosample_term.id as biosample_id, ngs_lanes.id as lane_id, ngs_donor.id as donor_id, ngs_biosample_term.biosample_type, ngs_lanes.date_received,
		ngs_lanes.date_submitted
		FROM ngs_samples
		LEFT JOIN ngs_biosample_term
		ON ngs_samples.biosample_id = ngs_biosample_term.id
		LEFT JOIN ngs_lanes
		ON ngs_samples.lane_id = ngs_lanes.id
		LEFT JOIN ngs_donor
		ON ngs_samples.donor_id = ngs_donor.id
		WHERE ngs_samples.id in ($samples)
		");
}
else if($p == 'getLibraries')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->queryTable("
		SELECT ngs_samples.id as sample_id, ngs_samples.samplename, ngs_molecule.molecule, ngs_protocols.extraction, ngs_samples.read_length,
		ngs_molecule.id as molecule_id, ngs_protocols.id as protocol_id
		FROM ngs_samples
		LEFT JOIN ngs_molecule
		ON ngs_samples.molecule_id = ngs_molecule.id
		LEFT JOIN ngs_protocols
		ON ngs_samples.protocol_id = ngs_protocols.id
		WHERE ngs_samples.id in ($samples)
		");
}
else if($p == 'getAntibodies')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->queryTable("
		SELECT *
		FROM ngs_antibody_target
		WHERE id in (
			SELECT antibody_lot_id
			FROM ngs_samples
			WHERE id in ($samples)
		)
		");
}
else if($p == 'getReplicates')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->queryTable("
		SELECT id, samplename, biological_replica, technical_replica
		FROM ngs_samples
		where id in ($samples)
		");
}


header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo $data;
exit;
?>