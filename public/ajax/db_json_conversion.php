<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');
require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");
$query = new dbfuncs();
//header

$execute = false;

if(isset($_GET['change'])){
	if($_GET['change'] == 'yes'){
		$execute = true;
	}
}

$cmd = " mysql -u biocore --password=biocore2013 --xml -e 'SELECT * from biocore.ngs_runparams' > ./runparams.xml";
$PID_COMMAND = popen( $cmd, "r" );
pclose($PID_COMMAND);

$runparams = json_decode($query->queryTable("
	SELECT id, json_parameters
	FROM ngs_runparams
	"));

foreach($runparams as $rp){
	$id = $rp->id;
	$json = json_decode($rp->json_parameters);
	$json_new = [];
	
	//genomebuild
	if(isset($json->genomebuild)){
		$json_new['genomebuild'] = $json->genomebuild;
	}
	
	//spaired
	if(isset($json->spaired)){
		$json_new['spaired'] = $json->spaired;
	}
	
	//resume
	if(isset($json->resume)){
		$json_new['resume'] = $json->resume;
	}
	
	//fastqc
	if(isset($json->fastqc)){
		$json_new['fastqc'] = $json->fastqc;
	}
	
	//barcodes
	if(isset($json->barcodes)){
		$json_new['barcodes'] = $json->barcodes;
	}
	
	//submission
	$json_new['submission'] = "0";
	
	//adapter
	if(isset($json->adapter)){
		$json_new['adapters'] = $json->adapter;
	}
	if(isset($json->adapters)){
		$json_new['adapters'] = $json->adapters;
	}
	
	//quality
	if(isset($json->quality)){
		if($json->quality == 'none'){
			$json_new['quality'] = $json->quality;
		}else{
			$quality = [];
			$quality_old = explode(":", $json->quality);
			$quality['windowSize'] = $quality_old[0];
			$quality['requiredQuality'] = $quality_old[1];
			$quality['leading'] = $quality_old[2];
			$quality['trailing'] = $quality_old[3];
			$quality['minlen'] = $quality_old[4];
			$json_new['quality'] = [$quality];
		}
	}
	//trim
	if(isset($json->trim)){
		if($json->trim == 'none'){
			$json_new['trim'] = $json->trim;
		}else{
			$trim = [];
			$trim_old = explode(":", $json->trim);
			$trim['5len1'] = $trim_old[0];
			$trim['3len1'] = $trim_old[1];
			if(count($trim_old) > 2){
				$trim['5len2'] = $trim_old[2];
				$trim['3len2'] = $trim_old[3];
				$trim['trimpaired'] = $json->trimpaired;
			}
			$json_new['trim'] = [$trim];
		}
	}
	
	//split
	if(isset($json->split)){
		$json_new['split'] = $json->split;
	}
	
	//commonind
	if(isset($json->commonind)){
		$json_new['commonind'] = $json->commonind;
		if(isset($json->advparams)){
			$json_new['advparams'] = $json->advparams;
		}
	}
	
	//custominds
	if(isset($json->custom)){
		$total_custom = [];
		foreach($json->custom as $c){
			$custom = [];
			$custom_old = explode(":", $c);
			$custom['FullPath'] = $custom_old[0];
			$custom['IndexPrefix'] = $custom_old[1];
			$custom['BowtieParams'] = $custom_old[2];
			$custom['Description'] = $custom_old[3];
			$custom['Filter Out'] = $custom_old[4];
			array_push($total_custom, $custom);
		}
		$json_new['custominds'] = $total_custom;
	}
	
	//pipeline
	if(isset($json->pipeline)){
		$total_pipeline = [];
		foreach($json->pipeline as $p){
			$pipe = [];
			$pipe_old = explode(":", $p);
			if($pipe_old[0] == "RNASeqRSEM"){
				$pipe['Type'] = $pipe_old[0];
				$pipe['Params'] = $pipe_old[1];
				$pipe['IGVTDF'] = $pipe_old[2];
				$pipe['BAM2BW'] = $pipe_old[3];
				if($pipe_old[4] == null){
					$pipe['ExtFactor'] = '0';
				}else{
					$pipe['ExtFactor'] = $pipe_old[4];
				}
				if($pipe_old[5] == null){
					$pipe['RSeQC'] = 'no';
				}else{
					$pipe['RSeQC'] = $pipe_old[5];
				}
				array_push($total_pipeline, $pipe);
			}else if ($pipe_old[0] == "Tophat"){
				$pipe['Type'] = $pipe_old[0];
				$pipe['Params'] = $pipe_old[1];
				$pipe['IGVTDF'] = $pipe_old[2];
				$pipe['BAM2BW'] = $pipe_old[3];
				if($pipe_old[4] == null){
					$pipe['ExtFactor'] = '0';
				}else{
					$pipe['ExtFactor'] = $pipe_old[4];
				}
				if($pipe_old[5] == null){
					$pipe['RSeQC'] = 'no';
				}else{
					$pipe['RSeQC'] = $pipe_old[5];
				}
				if($pipe_old[6] == null){
					$pipe['CollectRnaSeqMetrics'] = 'no';
				}else{
					$pipe['CollectRnaSeqMetrics'] = $pipe_old[6];
				}
				if($pipe_old[7] == null){
					$pipe['CollectMultipleMetrics'] = 'no';
				}else{
					$pipe['CollectMultipleMetrics'] = $pipe_old[7];
				}
				if($pipe_old[8] == null){
					$pipe['MarkDuplicates'] = 'no';
				}else{
					$pipe['MarkDuplicates'] = $pipe_old[8];
				}
				array_push($total_pipeline, $pipe);
			}else if ($pipe_old[0] == "ChipSeq"){
				$pipe['Type'] = $pipe_old[0];
				$pipe['ChipInput'] = $pipe_old[1];
				$pipe['MultiMapper'] = $pipe_old[2];
				$pipe['TagSize'] = $pipe_old[3];
				$pipe['BandWith'] = $pipe_old[4];
				$pipe['EffectiveGenome'] = $pipe_old[5];
				$pipe['IGVTDF'] = $pipe_old[6];
				$pipe['BAM2BW'] = $pipe_old[7];
				if($pipe_old[8] == null){
					$pipe['ExtFactor'] = '0';
				}else{
					$pipe['ExtFactor'] = $pipe_old[8];
				}
				if($pipe_old[9] == null){
					$pipe['CollectMultipleMetrics'] = 'no';
				}else{
					$pipe['CollectMultipleMetrics'] = $pipe_old[9];
				}
				if($pipe_old[10] == null){
					$pipe['MarkDuplicates'] = 'no';
				}else{
					$pipe['MarkDuplicates'] = $pipe_old[10];
				}
				array_push($total_pipeline, $pipe);
			}else if ($pipe_old[0] == "DESeq"){
				$pipe['Type'] = $pipe_old[0];
				$pipe['Name'] = "";
				$pipe['Conditions'] = $pipe_old[2];
				$pipe['Columns'] = $pipe_old[1];
				$pipe['FitType'] = $pipe_old[3];
				$pipe['HeatMap'] = $pipe_old[4];
				$pipe['padj'] = $pipe_old[5];
				$pipe['foldChange'] = $pipe_old[6];
				$pipe['DataType'] = $pipe_old[7];
				array_push($total_pipeline, $pipe);
			}else if ($pipe_old[0] == "BisulphiteMapping"){
				$pipe['Type'] = $pipe_old[0];
				if($pipe_old[1] == 1){
					$pipe['BSMapStep'] = 'yes';
				}else{
					$pipe['BSMapStep'] = 'no';
				}
				if($pipe_old[2] != ""){
					$pipe['BisulphiteType'] = 'RRBS';
					$pipe['Digestion'] = $pipe_old[2];
				}else{
					$pipe['BisulphiteType'] = 'WGBS';
					$pipe['Digestion'] = $pipe_old[2];
				}
				$pipe['BSMapParams'] = $pipe_old[3];
				$pipe['IGVTDF'] = 'no';
				$pipe['BAM2BW'] = 'no';
				$pipe['ExtFactor'] = '0';
				$pipe['CollectMultipleMetrics'] = 'no';
				$pipe['MarkDuplicates'] = 'no';
				if($pipe_old[4] == 1){
					$pipe['MCallStep'] = 'yes';
				}else{
					$pipe['MCallStep'] = 'no';
				}
				$pipe['Conditions'] = $pipe_old[6];
				$pipe['Columns'] = $pipe_old[5];
				$pipe['MCallParams'] = $pipe_old[7];
				if($pipe_old[8] == 1){
					$pipe['MCompStep'] = 'yes';
				}else{
					$pipe['MCompStep'] = 'no';
				}
				$pipe['MCompParams'] = $pipe_old[9];
				array_push($total_pipeline, $pipe);
			}
		}
		$json_new['pipeline'] = $total_pipeline;
	}
	echo json_encode($id);
	echo '<br>';
	echo json_encode($json);
	echo '<br><br>';
	echo json_encode($json_new);
	echo '<br>';
	
	if($execute && strpos(json_encode($json_new), "null") === false && strpos(json_encode($json_new), '"pipeline":[]') === false){
		$query->runSQL("
		UPDATE ngs_runparams
		SET json_parameters = '" . json_encode($json_new) . "'
		WHERE id = $id
		");
		echo '<br>Params Changed!<br><br>';
	}else{
		echo '<br>Params Ignored due to null<br><br>';
	}
}

$data = "EOF";

//footer
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo $data;
exit;
?>
