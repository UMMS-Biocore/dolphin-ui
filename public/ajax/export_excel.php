<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");
require_once("../../includes/excel/Classes/PHPExcel.php");
//header

function num2alpha($n){
		for($r = ""; $n >= 0; $n = intval($n / 26) - 1){
			$r = chr($n%26 + 0x41) . $r;
		}
		return $r;
}

$query = new dbfuncs();

$data;
if (isset($_GET['p'])){$p = $_GET['p'];}

if($p == 'exportExcel')
{
	$user = $_SESSION['user'];
	//	Change directory and obtain empty template name
	$inputFileName = "../tmp/files/Blank_Excel_Export.xls";
	
	//	Load in the empty excel template
	$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
	$objPHPExcel->setActiveSheetIndex(3);
	
	//	Change values within the Excel file
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	
	/*
		Insert Example:
		$objPHPExcel->getActiveSheet()->setCellValue('B3', $_SESSION['user']);
	*/
	
	//	Samples and data gathering
	$sample_data = json_decode($query->queryTable(
		"SELECT ngs_samples.id, ngs_samples.series_id, ngs_samples.protocol_id, ngs_samples.lane_id,
		ngs_samples.name, ngs_samples.samplename, ngs_samples.barcode, ngs_samples.title, ngs_samples.batch_id,
		ngs_samples.description, ngs_samples.avg_insert_size, ngs_samples.read_length, ngs_samples.concentration,
		ngs_samples.time, ngs_samples.biological_replica, ngs_samples.technical_replica, ngs_samples.spike_ins,
		source, source_symbol, organism, genotype, molecule, library_type, donor, biosample_type, instrument_model,
		treatment_manufacturer, ngs_samples.adapter, ngs_samples.notebook_ref, ngs_samples.notes, ngs_lanes.name as l_name,
		ngs_protocols.name as p_name
		FROM ngs_samples
		LEFT JOIN ngs_lanes
		ON ngs_samples.lane_id = ngs_lanes.id
		LEFT JOIN ngs_protocols
		ON ngs_samples.protocol_id = ngs_protocols.id
		LEFT JOIN ngs_donor
		ON ngs_samples.donor_id = ngs_donor.id
		LEFT JOIN ngs_source
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
		ON ngs_samples.treatment_manufacturer_id = ngs_treatment_manufacturer.id
		WHERE ngs_samples.id IN (".implode(",", $samples).")
		"));
	
	$experiment_series = $sample_data[0]->series_id;
	$lane_ids = array();
	$protocol_ids = array();
	$col_number = 4;
	
	foreach($sample_data as $sd){
		
		$condition_data = json_decode($query->queryTable(
			"SELECT ngs_conds.condition, ngs_conds.cond_symbol
			FROM ngs_conds
			LEFT JOIN ngs_sample_conds
			ON ngs_conds.id = ngs_sample_conds.cond_id
			WHERE ngs_sample_conds.sample_id = ".$sd->id
			));
		
		$cond_symbols = '';
		$conditions = '';
		foreach($condition_data as $cd){
			if($cond_symbols == ''){
				$cond_symbols = $cd->cond_symbol;
			}else{
				$cond_symbols .= ','.$cd->cond_symbol;
			}
			
			if($conditions == ''){
				$conditions = $cd->condition;
			}else{
				$conditions .= ','.$cd->condition;
			}
		}
		
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$col_number, $sd->name);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$col_number, $sd->l_name);		//	Lane name
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$col_number, $sd->p_name);		//	Protocol name
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$col_number, $sd->barcode);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$col_number, $sd->title);
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$col_number, $sd->batch_id);
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$col_number, $sd->source_symbol);
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$col_number, $sd->source);
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$col_number, $sd->organism);
		$objPHPExcel->getActiveSheet()->setCellValue('J'.$col_number, $sd->biosample_type);
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$col_number, $sd->molecule);
		$objPHPExcel->getActiveSheet()->setCellValue('L'.$col_number, $sd->description);
		$objPHPExcel->getActiveSheet()->setCellValue('M'.$col_number, $sd->instrument_model);
		$objPHPExcel->getActiveSheet()->setCellValue('N'.$col_number, $sd->avg_insert_size);
		$objPHPExcel->getActiveSheet()->setCellValue('O'.$col_number, $sd->read_length);
		$objPHPExcel->getActiveSheet()->setCellValue('P'.$col_number, $sd->genotype);
		$objPHPExcel->getActiveSheet()->setCellValue('Q'.$col_number, $cond_symbols);		//	condition symbols
		$objPHPExcel->getActiveSheet()->setCellValue('R'.$col_number, $conditions);		//	conditions
		$objPHPExcel->getActiveSheet()->setCellValue('S'.$col_number, $sd->concentration);
		$objPHPExcel->getActiveSheet()->setCellValue('T'.$col_number, $sd->treatment_manufacturer);
		$objPHPExcel->getActiveSheet()->setCellValue('U'.$col_number, $sd->donor);
		$objPHPExcel->getActiveSheet()->setCellValue('V'.$col_number, $sd->time);
		$objPHPExcel->getActiveSheet()->setCellValue('W'.$col_number, $sd->biological_replica);
		$objPHPExcel->getActiveSheet()->setCellValue('X'.$col_number, $sd->technical_replica);
		$objPHPExcel->getActiveSheet()->setCellValue('Y'.$col_number, $sd->spike_ins);
		$objPHPExcel->getActiveSheet()->setCellValue('Z'.$col_number, $sd->adapter);
		$objPHPExcel->getActiveSheet()->setCellValue('AA'.$col_number, $sd->notebook_ref);
		$objPHPExcel->getActiveSheet()->setCellValue('AB'.$col_number, $sd->notes);
		
		//	Push lane_ids and protocol_ids
		
		if(!in_array($sd->lane_id, $lane_ids)){
			array_push($lane_ids, $sd->lane_id);
		}
		if(!in_array($sd->protocol_id, $protocol_ids)){
			array_push($protocol_ids, $sd->protocol_id);
		}
		
		$col_number++;
	}
	
	//	Metadata
	$objPHPExcel->setActiveSheetIndex(0);
	$experiment_data = json_decode($query->queryTable(
		"SELECT ngs_experiment_series.experiment_name, ngs_experiment_series.summary,
		ngs_experiment_series.design, ngs_experiment_series.`grant`,
		organization, lab
		FROM ngs_experiment_series
		LEFT JOIN ngs_lab
		ON ngs_experiment_series.lab_id = ngs_lab.id
		LEFT JOIN ngs_organization
		ON ngs_experiment_series.organization_id = ngs_organization.id
		WHERE ngs_experiment_series.id = $experiment_series
		"));
	
	$objPHPExcel->getActiveSheet()->setCellValue('B3', $experiment_data[0]->experiment_name);
	$objPHPExcel->getActiveSheet()->setCellValue('B4', $experiment_data[0]->summary);
	$objPHPExcel->getActiveSheet()->setCellValue('B5', $experiment_data[0]->design);
	$objPHPExcel->getActiveSheet()->setCellValue('B6', $experiment_data[0]->organization);
	$objPHPExcel->getActiveSheet()->setCellValue('B7', $experiment_data[0]->lab);
	$objPHPExcel->getActiveSheet()->setCellValue('B8', $experiment_data[0]->grant);
	
	//	Lanes
	$objPHPExcel->setActiveSheetIndex(1);
	$lane_data = json_decode($query->queryTable(
		"SELECT ngs_lanes.name, ngs_lanes.sequencing_id, ngs_lanes.cost,
		ngs_lanes.date_submitted, ngs_lanes.date_received, ngs_lanes.phix_requested,
		ngs_lanes.phix_in_lane, ngs_lanes.total_samples, ngs_lanes.resequenced,
		ngs_lanes.notes, facility
		FROM ngs_lanes
		LEFT JOIN ngs_facility
		ON ngs_lanes.facility_id = ngs_facility.id
		WHERE ngs_lanes.id IN (".implode(",",$lane_ids).")
		"));
	
	$col_number = 4;
	foreach($lane_data as $ld){
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$col_number, $ld->name);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$col_number, $ld->sequencing_id);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$col_number, $ld->facility);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$col_number, $ld->cost);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$col_number, $ld->date_submitted);
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$col_number, $ld->date_received);
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$col_number, $ld->phix_requested);
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$col_number, $ld->phix_in_lane);
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$col_number, $ld->total_samples);
		$objPHPExcel->getActiveSheet()->setCellValue('J'.$col_number, $ld->resequenced);
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$col_number, $ld->notes);
		
		$col_number++;
	}
	
	//	Protocols
	$objPHPExcel->setActiveSheetIndex(2);
	$protocol_data = json_decode($query->queryTable(
		"SELECT ngs_protocols.name, ngs_protocols.growth, ngs_protocols.treatment,
		ngs_protocols.extraction, ngs_protocols.library_construction, ngs_protocols.crosslinking_method,
		ngs_protocols.fragmentation_method, ngs_protocols.strand_specific, library_strategy
		FROM ngs_protocols
		LEFT JOIN ngs_library_strategy
		ON ngs_protocols.library_strategy_id = ngs_library_strategy.id
		WHERE ngs_protocols.id IN (".implode(",",$protocol_ids).")
		"));
	
	$col_number = 4;
	foreach($protocol_data as $pd){
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$col_number, $pd->name);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$col_number, $pd->growth);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$col_number, $pd->extraction);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$col_number, $pd->library_construction);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$col_number, $pd->crosslinking_method);
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$col_number, $pd->fragmentation_method);
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$col_number, $pd->strand_specific);
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$col_number, $pd->library_strategy);
		
		$col_number++;
	}
	
	//	Directories
	$objPHPExcel->setActiveSheetIndex(4);
	$sample_file_directories = json_decode($query->queryTable(
		"SELECT DISTINCT ngs_dirs.id, fastq_dir, backup_dir, amazon_bucket
		FROM ngs_temp_sample_files
		LEFT JOIN ngs_dirs
		ON ngs_dirs.id = ngs_temp_sample_files.dir_id
		WHERE ngs_temp_sample_files.sample_id IN (".implode(",", $samples).")
		"));

    $lane_file_directories = json_decode($query->queryTable(
		"SELECT DISTINCT ngs_dirs.id, fastq_dir, backup_dir, amazon_bucket
		FROM ngs_temp_lane_files
		LEFT JOIN ngs_dirs
		ON ngs_dirs.id = ngs_temp_lane_files.dir_id
		WHERE ngs_temp_lane_files.lane_id IN (SELECT ngs_samples.id FROM ngs_samples WHERE ngs_samples.id IN (".implode(",", $samples)."))
		"));
	
	$stored_dirs = [];
	$objPHPExcel->getActiveSheet()->setCellValue('C3', 'backup directory');
    $objPHPExcel->getActiveSheet()->setCellValue('D3', 'amazon bucket');
	$col_number = 4;
	foreach($sample_file_directories as $sfd){
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$col_number, $sfd->id);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$col_number, $sfd->fastq_dir);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$col_number, $sfd->backup_dir);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$col_number, $sfd->amazon_bucket);
		$col_number++;
	}
	foreach($lane_file_directories as $lfd){
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$col_number, $lfd->id);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$col_number, $lfd->fastq_dir);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$col_number, $lfd->backup_dir);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$col_number, $lfd->amazon_bucket);
		$col_number++;
	}
	
	
	//	Files
	$objPHPExcel->setActiveSheetIndex(5);
	$file_sample_data = json_decode($query->queryTable(
		"SELECT ngs_samples.name, ngs_temp_sample_files.file_name, ngs_temp_sample_files.dir_id
		FROM ngs_temp_sample_files
		LEFT JOIN ngs_samples
		ON ngs_samples.id = ngs_temp_sample_files.sample_id
		WHERE ngs_temp_sample_files.sample_id IN (".implode(",", $samples).")
		"));
	
	$file_lane_data = json_decode($query->queryTable(
		"SELECT ngs_lanes.name, ngs_temp_lane_files.file_name, ngs_temp_lane_files.dir_id
		FROM ngs_temp_lane_files
		LEFT JOIN ngs_lanes
		ON ngs_lanes.id = ngs_temp_lane_files.lane_id
		WHERE ngs_temp_lane_files.lane_id IN (SELECT ngs_samples.id FROM ngs_samples WHERE ngs_samples.id IN (".implode(",", $samples)."))
		"));
	
	$col_number = 4;
	foreach($file_sample_data as $fsd){
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$col_number, $fsd->name);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$col_number, $fsd->dir_id);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$col_number, $fsd->file_name);
		$col_number++;
	}
	foreach($file_lane_data as $fld){
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$col_number, $fld->name);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$col_number, $fld->dir_id);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$col_number, $fld->file_name);
		$col_number++;
	}
	
	//	Save the file to be downloaded
	$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
	$name = "/tmp/files/".$user."_".date('Y-m-d-H-i-s').".xls";
	$objWriter->save("..".$name);
	echo $name;
}
else if($p == 'checkExperimentSeries')
{
	if (isset($_GET['samples'])){$samples = $_GET['samples'];}
	
	$experiment_series = $query->queryTable(
		"SELECT distinct ngs_samples.series_id
		FROM ngs_samples
		WHERE ngs_samples.id in (".implode(",",$samples).")
		");
	
	echo $experiment_series;
}
else if($p == 'deleteExcel')
{
	if (isset($_GET['file'])){$file = $_GET['file'];}
	
	sleep(2);
	pclose(popen( "rm ..".$file, 'r'));
}
?>