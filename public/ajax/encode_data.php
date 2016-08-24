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

if($p == 'getSampleDataInfo')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	$data=$query->queryTable("SELECT ngs_samples.id, ngs_samples.name, ngs_samples.samplename, ngs_samples.title, concentration,
							 read_length, biological_replica, technical_replica, spike_ins, read_length,
							 molecule, genotype, treatment_manufacturer, instrument_model, adapter,
							 time, ngs_donor.id as did, donor, life_stage, age, sex, donor_acc, donor_uuid, series_id,
							 protocol_id, lane_id, organism,
							 biosample_acc, biosample_uuid, library_acc, library_uuid, replicate_uuid,
							 experiment_acc, experiment_uuid, treatment_id, antibody_lot_id, biosample_id,
							 biosample_term_name, biosample_term_id, biosample_type
							 FROM ngs_samples
							 LEFT JOIN ngs_donor
							 ON ngs_donor.id = ngs_samples.donor_id
							 LEFT JOIN ngs_biosample_term
							 ON ngs_biosample_term.id = ngs_samples.biosample_id
							 LEFT JOIN ngs_organism
							 ON ngs_organism.id = ngs_samples.organism_id
							 LEFT JOIN ngs_molecule
							 ON ngs_molecule.id = ngs_samples.molecule_id
							 LEFT JOIN ngs_treatment_manufacturer
							 ON ngs_treatment_manufacturer.id = ngs_samples.treatment_manufacturer_id
							 LEFT JOIN ngs_instrument_model
							 ON ngs_instrument_model.id = ngs_samples.instrument_model_id
							 LEFT JOIN ngs_genotype
							 ON ngs_genotype.id = ngs_samples.genotype_id
							 WHERE ngs_samples.id IN ( $samples )");
}
else if($p == "getLaneDataInfo")
{
	if (isset($_GET['lanes'])){$lanes = $_GET['lanes'];}
	$data=$query->queryTable("SELECT ngs_lanes.id, ngs_lanes.name, sequencing_id, ngs_lanes.lane_id,
							 facility, cost, date_submitted, date_received, total_reads,
							 phix_requested, phix_in_lane, total_samples, resequenced
							 FROM ngs_lanes
							 LEFT JOIN ngs_facility
							 ON ngs_facility.id = ngs_lanes.facility_id
							 WHERE ngs_lanes.id IN ( $lanes )");
}
else if($p == 'getSeriesDataInfo')
{
	if (isset($_GET['series'])){$series = $_GET['series'];}
	$data=$query->queryTable("SELECT ngs_experiment_series.id, experiment_name, summary, design, organization, lab, ngs_experiment_series.grant
							 FROM ngs_experiment_series
							 LEFT JOIN ngs_organization
							 ON ngs_organization.id = ngs_experiment_series.organization_id
							 LEFT JOIN ngs_lab
							 ON ngs_lab.id = ngs_experiment_series.lab_id
							 WHERE ngs_experiment_series.id = $series");
}
else if ($p == 'getProtocolDataInfo')
{
	if (isset($_GET['protocols'])){$protocols = $_GET['protocols'];}
	$data=$query->queryTable("SELECT ngs_protocols.id, name, growth, treatment,
							 extraction, library_construction, crosslinking_method, fragmentation_method,
							 strand_specific, library_strategy
							 FROM ngs_protocols
							 LEFT JOIN ngs_library_strategy
							 ON ngs_protocols.library_strategy_id = ngs_library_strategy.id
							 WHERE ngs_protocols.id in ( $protocols )");
}
else if ($p == 'getTreatmentDataInfo')
{
	if (isset($_GET['treatments'])){$treatments = $_GET['treatments'];}
	$data=$query->queryTable("SELECT * FROM ngs_treatment WHERE id IN ( $treatments )");
}
else if ($p == 'getAntibodyDataInfo')
{
	if (isset($_GET['antibodies'])){$antibodies = $_GET['antibodies'];}
	$data=$query->queryTable("SELECT * FROM ngs_antibody_target WHERE id IN ( $antibodies )");
}
else if ($p == 'submitAccessionAndUuid')
{
	if (isset($_GET['item'])){$item = $_GET['item'];}
	if (isset($_GET['table'])){$table = $_GET['table'];}
	if (isset($_GET['type'])){$type = $_GET['type'];}
	if (isset($_GET['accession'])){$accession = $_GET['accession'];}
	if (isset($_GET['uuid'])){$uuid = $_GET['uuid'];}
	if($type == 'treatment' || $type == "replicate"){
		$data=$query->runSQL("UPDATE `" . $table . "` " .
							"SET `" . $type . "_uuid` = '" . $uuid . "' " .
							"WHERE id = " . $item);
	}else{
		$data=$query->runSQL("UPDATE `" . $table . "` " .
							"SET `" . $type . "_acc` = '" . $accession . "', `" . $type . "_uuid` = '" . $uuid . "' " .
							"WHERE id = " . $item);	
	}
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo $data;
exit;
?>
