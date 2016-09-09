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

if ($p == 'getSubmissions')
{
	$data=$query->queryTable("
		SELECT *
		FROM encode_submissions
	");
}
else if ($p == 'getSamples')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->queryTable("
		SELECT ngs_samples.id AS sample_id, ngs_samples.samplename, ngs_samples.source_id,
		ngs_samples.organism_id, ngs_samples.molecule_id, source, organism, molecule,
		ngs_samples.donor_id, ngs_donor.donor, ngs_experiment_series.id,
		ngs_experiment_series.`grant`, ngs_experiment_series.lab_id, ngs_lab.lab
		FROM ngs_samples
		LEFT JOIN ngs_donor
		ON ngs_samples.donor_id = ngs_donor.id
		LEFT JOIN ngs_experiment_series
		ON ngs_samples.series_id = ngs_experiment_series.id
		LEFT JOIN ngs_lab
		ON ngs_experiment_series.lab_id = ngs_lab.id
		LEFT JOIN ngs_source
		ON ngs_samples.source_id = ngs_source.id
		LEFT JOIN ngs_organism
		ON ngs_samples.organism_id = ngs_organism.id
		LEFT JOIN ngs_molecule
		ON ngs_samples.molecule_id = ngs_molecule.id
		WHERE ngs_samples.id IN ($samples);
	");
}
else if($p == 'getDonors')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->queryTable("
		SELECT ngs_donor.id as donor_id, ngs_donor.donor,
		ngs_donor.life_stage, ngs_donor.age,
		ngs_donor.sex, ngs_donor.donor_acc, ngs_donor.donor_uuid
		FROM ngs_donor
		WHERE ngs_donor.id
		IN (
			SELECT donor_id
			FROM ngs_samples
			WHERE ngs_samples.id IN ($samples)
		)
		");
}
else if($p == 'getExperiments')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->queryTable("
		SELECT DISTINCT ngs_samples.id as sample_id, ngs_samples.samplename, ngs_protocols.id as protocol_id,
		ngs_samples.description, ngs_samples.experiment_acc, ngs_samples.experiment_uuid,
		ngs_library_strategy.id as library_strategy_id, ngs_library_strategy.library_strategy,
		ngs_samples.source_id as source_id, ngs_source.source
		FROM ngs_samples
		LEFT JOIN ngs_protocols
		ON ngs_protocols.id = ngs_samples.protocol_id
		LEFT JOIN ngs_library_strategy
		ON ngs_library_strategy.id = ngs_protocols.library_strategy_id
		LEFT JOIN ngs_source
		ON ngs_source.id = ngs_samples.source_id
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
		SELECT ngs_samples.id as sample_id, ngs_samples.samplename, ngs_biosample_term.biosample_term_name, ngs_biosample_term.biosample_term_id,
		ngs_biosample_term.id as biosample_id, ngs_lanes.id as lane_id, ngs_biosample_term.biosample_type, ngs_lanes.date_received,
		ngs_treatment.id as treatment_id, ngs_lanes.date_submitted, ngs_samples.biosample_acc, ngs_samples.biosample_uuid, ngs_treatment.name
		FROM ngs_samples
		LEFT JOIN ngs_biosample_term
		ON ngs_samples.biosample_id = ngs_biosample_term.id
		LEFT JOIN ngs_treatment
		ON ngs_samples.treatment_id = ngs_treatment.id
		LEFT JOIN ngs_lanes
		ON ngs_samples.lane_id = ngs_lanes.id
		WHERE ngs_samples.id in ($samples)
		");
}
else if($p == 'getLibraries')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->queryTable("
		SELECT ngs_samples.id as sample_id, ngs_samples.samplename, ngs_molecule.molecule, ngs_protocols.extraction, ngs_samples.read_length,
		ngs_molecule.id as molecule_id, ngs_protocols.id as protocol_id, ngs_samples.instrument_model_id as imid, instrument_model,
		ngs_samples.spike_ins, ngs_protocols.crosslinking_method, ngs_protocols.fragmentation_method, ngs_samples.library_acc,
		ngs_samples.library_uuid
		FROM ngs_samples
		LEFT JOIN ngs_molecule
		ON ngs_samples.molecule_id = ngs_molecule.id
		LEFT JOIN ngs_instrument_model
		ON ngs_samples.instrument_model_id = ngs_instrument_model.id
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
		SELECT ngs_samples.id as sample_id, samplename, biological_replica, technical_replica, ngs_antibody_target.target, ngs_antibody_target.id as antibody_id,
		ngs_samples.replicate_uuid
		FROM ngs_samples
		LEFT JOIN ngs_antibody_target
		ON ngs_samples.antibody_lot_id = ngs_antibody_target.id
		where ngs_samples.id in ($samples)
		");
}
else if ($p == 'createEncodeRow')
{
	if (isset($_GET['type'])){$type = $_GET['type'];}
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	if (isset($_GET['name'])){$name = $_GET['name'];}
	if($type == 'Treatment'){
		$table = 'ngs_treatment';
		$update = 'treatment_id';
		$rowname = 'name';
	}else{
		$table = 'ngs_antibody_target';
		$update = 'antibody_lot_id';
		$rowname = 'target';
	}
	
	$data=$query->runSQL("
		INSERT INTO $table
		($rowname)
		VALUES
		('$name')
	");
	$typeID=json_decode($query->queryTable("
		SELECT id
		FROM $table
		WHERE $rowname = '$name'
		ORDER BY id DESC
	"));
	$data=$query->runSQL("
		UPDATE ngs_samples
		SET $update = " . $typeID[0]->id . "
		WHERE id IN ($samples)
	");
}


header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo $data;
exit;
?>