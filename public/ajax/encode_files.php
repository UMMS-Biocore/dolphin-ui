<?php
require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");

//Metadata submission
include(REQUESTS.'/library/Requests.php');
Requests::register_autoloader();

$query = new dbfuncs();
if (isset($_GET['sample_id'])){$sample_id = $_GET['sample_id'];}
if (isset($_GET['experiment'])){$experiment = $_GET['experiment'];}
if (isset($_GET['replicate'])){$replicate = $_GET['replicate'];}
if(!isset($_SESSION['encode_log'])){
	$_SESSION['encode_log'] = "../../tmp/encode/".$_SESSION['user']."_".date('Y-m-d-H-i-s').".log";
}

$logloc = $_SESSION['encode_log'];
$logfile = fopen($logloc, "a") or die("Unable to open file!");
fwrite($logfile,"File Submission\n###########################################\n\n");
fclose($logfile);

function baselineJSON($dataset_acc, $replicate, $snq, $sub, $my_lab, $my_award, $filename, $platform, $dir_query, $efn){
	$sample_name=$snq->samplename;
	$machine_name=$snq->machine_name;
	if($machine_name == 'None' || $machine_name == NULL){
		$machine_name = 'unknown';
	}
	$flowcell = $snq->flowcell;
	if($flowcell == 'None' || $flowcell == NULL){
		$flowcell = 'unknown';
	}
	$lane = $snq->lane;
	if($lane == 'None'){
		$lane = 'unknown';
	}
	$read_length = $snq->read_length;
	if($read_length == 'None' || $read_length == NULL){
		$read_length = 'unknown';
	}
	$paired = '';
	if($sub->parent_file == 0){
		$directory = $dir_query[0]->backup_dir;
		if(substr($directory, -1) != '/'){
			$directory = $directory . "/";
		}
		$file_size = filesize($directory . $filename);
		$md5sum = md5_file($directory . $filename);
	}else if($sub->file_type == 'fastq'){
		$directory = $sub->outdir;
		if(substr($directory, -1) == '/'){
			$directory = substr($directory, 0, -1);
		}
		$filename = $filename . ".gz";
		$file_size = filesize($directory . $efn . ".gz");
		if($sub->file_md5 != NULL && $sub != ''){
			$md5sum = $sub->file_md5;
		}else{
			$md5sum = md5_file($directory . $efn . ".gz");
		}
	}else if($sub->file_type == 'peaks-bed'){	
		$directory = $sub->outdir;
		if(substr($directory, -1) == '/'){
			$directory = substr($directory, 0, -1);
		}
		$file_size = filesize($directory . $sub->file_name . ".gz");
		if($sub->file_md5 != NULL && $sub != ''){
			$md5sum = $sub->file_md5;
		}else{
			$md5sum = md5_file($directory . $sub->file_name . ".gz");
		}
	}else{
		$directory = $sub->outdir;
		if(substr($directory, -1) == '/'){
			$directory = substr($directory, 0, -1);
		}
		$file_size = filesize($directory . $sub->file_name);
		if($sub->file_md5 != NULL && $sub != ''){
			$md5sum = $sub->file_md5;
		}else{
			$md5sum = md5_file($directory . $sub->file_name);
		}
	}
	$data = array(
		"dataset" => $dataset_acc,
		"replicate" => $replicate,
		"file_size" => $file_size,
		"md5sum" => $md5sum,
		"platform" => $platform,
		"submitted_file_name" => $filename,
		"lab" => $my_lab,
		"award" => $my_award,
		"flowcell_details" => array(array("machine" => $machine_name),
									array("flowcell" => $flowcell),
									array("lane" => $lane))
		
	);
	if($sub->file_type == 'fastq'){
		$data['output_type'] = 'reads';
		$data["read_length"] = (int)$read_length;
	}else if($sub->file_type == 'bam'){
		$data['output_type'] = 'alignments';
	}else if($sub->file_type == 'bigWig'){
		$data['output_type'] = 'signal of all reads';
	}else if($sub->file_type == 'tdf'){
		if(strpos($sub->file_name,".genes.results") !== false){
			$data['output_type'] = 'gene quantifications';
		}else{
			$data['output_type'] = 'transcript quantifications';
		}
	}else if($sub->file_type == 'peaks-bed'){
		$data['output_type'] = 'peaks';
	}
	return $data;
}

function fastqJSON($data, $sub, $my_lab, $sample_name, $run_type, $file, $file_names, $step_list){
	$data["file_format"] = 'fastq';
	$data["run_type"] = $run_type;
	if($sub->step_run != NULL && $sub->step_run != ''){
		$data['step_run'] = $sub->step_run;
	}
	//	INITIAL FASTQ
	if($sub->parent_file == 0){
		if($run_type == "paired-ended"){
			//	FASTQ PAIRED
			if(end($file_names) == $file){
				$data["aliases"] = array($my_lab.':init_fastq_p2_'.$sample_name.'_'.$sub->parent_file);
				$data["paired_end"] = '2';
				$data["paired_with"] = $my_lab.':init_fastq_p1_'.$sample_name.'_'.$sub->parent_file;
			}else{
				$data["aliases"] = array($my_lab.':init_fastq_p1_'.$sample_name.'_'.$sub->parent_file);
				$data["paired_end"] = '1';
			}
		}else if (count($file_names) == 1){
			//	FASTQ SINGLE
			$data["aliases"] = array($my_lab.':init_fastq_'.$sample_name.'_'.$sub->parent_file);
		}
	}else{
		if($run_type == "paired-ended"){
			//	FASTQ PAIRED
			if(end($file_names) == $file){
				$data["aliases"] = array($my_lab.':fastq_p2_'.$sample_name.'_'.$sub->parent_file);
				$data["paired_end"] = '2';
				$data["paired_with"] = $my_lab.':fastq_p1_'.$sample_name.'_'.$sub->parent_file;
			}else{
				$data["aliases"] = array($my_lab.':fastq_p1_'.$sample_name.'_'.$sub->parent_file);
				$data["paired_end"] = '1';
			}
			if($sub->parent_file != 0 && isset($step_list[$sub->parent_file - 1])){
				$data['derived_from'] = explode(",",$step_list[$sub->parent_file - 1]);
				if($sub->additional_derived_from != "" && isset($sub->additional_derived_from)){
					$data['derived_from'] = array_merge($data['derived_from'], explode(",", $sub->additional_derived_from));
				}
			}
		}else{
			//	FASTQ SINGLE
			$data["aliases"] = array($my_lab.':fastq_'.$sample_name.'_a'.$sub->parent_file);
			if($sub->parent_file != 0 && isset($step_list[$sub->parent_file - 1])){
				$data['derived_from'] = explode(",",$step_list[$sub->parent_file - 1]);
				if($sub->additional_derived_from != "" && isset($sub->additional_derived_from)){
					$data['derived_from'] = array_merge($data['derived_from'], explode(",", $sub->additional_derived_from));
				}
			}
		}
	}
	return $data;
}

function bamJSON($data, $sub, $my_lab, $sample_name, $run_type, $step_list, $genome){
	//	BAM
	$data["file_format"] = 'bam';
	$data['assembly'] = $genome;
	$data["aliases"] = array($my_lab.':bam_'.$sample_name.'_'.$sub->parent_file);
	if($sub->step_run != NULL && $sub->step_run != ''){
		$data['step_run'] = $sub->step_run;
	}
	if($sub->parent_file != 0 && isset($step_list[$sub->parent_file - 1])){
		$data['derived_from'] = explode(",",$step_list[$sub->parent_file - 1]);
		if($sub->additional_derived_from != "" && isset($sub->additional_derived_from)){
			$data['derived_from'] = array_merge($data['derived_from'], explode(",", $sub->additional_derived_from));
		}
	}
	return $data;
}

function tdfJSON($data, $sub, $my_lab, $sample_name, $run_type, $step_list, $tdfcount, $genome){
	//	TDF/TSV
	$data["file_format"] = 'tsv';
	$data['assembly'] = $genome;
	$data["aliases"] = array($my_lab.':tdf_'.$tdfcount.'_'.$sample_name.'_'.$sub->parent_file);
	if($sub->step_run != NULL && $sub->step_run != ''){
		$data['step_run'] = $sub->step_run;
	}
	if($sub->parent_file != 0 && isset($step_list[$sub->parent_file - 1])){
		$data['derived_from'] = explode(",",$step_list[$sub->parent_file - 1]);
		if($sub->additional_derived_from != "" && isset($sub->additional_derived_from)){
			$data['derived_from'] = array_merge($data['derived_from'], explode(",", $sub->additional_derived_from));
		}
	}
	return $data;
}

function bigwigJSON($data, $sub, $my_lab, $sample_name, $run_type, $step_list, $genome){
	//	BIGWIG
	$data["file_format"] = 'bigWig';
	$data['assembly'] = $genome;
	$data["aliases"] = array($my_lab.':bigwig_'.$sample_name.'_'.$sub->parent_file);
	if($sub->step_run != NULL && $sub->step_run != ''){
		$data['step_run'] = $sub->step_run;
	}
	if($sub->parent_file != 0 && isset($step_list[$sub->parent_file - 1])){
		$data['derived_from'] = explode(",",$step_list[$sub->parent_file - 1]);
		if($sub->additional_derived_from != "" && isset($sub->additional_derived_from)){
			$data['derived_from'] = array_merge($data['derived_from'], explode(",", $sub->additional_derived_from));
		}
	}
	return $data;
}

function bedJSON($data, $sub, $my_lab, $sample_name, $run_type, $step_list, $genome){
	//	BIGWIG
	$data["file_format"] = 'bed';
	$data['file_format_type'] = 'narrowPeak';
	$data['assembly'] = $genome;
	$data["aliases"] = array($my_lab.':bed_'.$sample_name.'_'.$sub->parent_file);
	if($sub->step_run != NULL && $sub->step_run != ''){
		$data['step_run'] = $sub->step_run;
	}
	if($sub->parent_file != 0 && isset($step_list[$sub->parent_file - 1])){
		$data['derived_from'] = explode(",",$step_list[$sub->parent_file - 1]);
		if($sub->additional_derived_from != "" && isset($sub->additional_derived_from)){
			$data['derived_from'] = array_merge($data['derived_from'], explode(",", $sub->additional_derived_from));
		}
	}
	return $data;
}

// ****************  MAIN FUNCTION  *********************

//Obtain database variables
$experiment_info = json_decode($query->queryTable("
	SELECT `lab`, `grant`
	FROM ngs_experiment_series
	LEFT JOIN ngs_lab
	ON ngs_lab.id = ngs_experiment_series.lab_id
	WHERE ngs_experiment_series.id =
	(SELECT series_id FROM ngs_samples WHERE id = $sample_id)"));
$fastq_data = json_decode($query->queryTable("
	SELECT *
	FROM ngs_fastq_files
	WHERE sample_id = $sample_id
	"));
$dir_query=json_decode($query->queryTable("
	SELECT fastq_dir, backup_dir, amazon_bucket
	FROM ngs_dirs
	WHERE id = " . $fastq_data[0]->dir_id
	));
$sample_name_query = json_decode($query->queryTable("
	SELECT samplename, machine_name, flowcell, lane, read_length, organism_symbol, instrument_model, replicate_uuid,
	ngs_experiment_acc.experiment_acc, replicate_uuid
	FROM ngs_samples
	LEFT JOIN ngs_flowcell
	ON ngs_samples.flowcell_id = ngs_flowcell.id
	LEFT JOIN ngs_organism
	ON ngs_samples.organism_id = ngs_organism.id
	LEFT JOIN ngs_instrument_model
	ON ngs_samples.instrument_model_id = ngs_instrument_model.id
	LEFT JOIN ngs_experiment_acc
	ON ngs_samples.experiment_acc = ngs_experiment_acc.id
	WHERE ngs_samples.id = $sample_id
	"));
$file_sub = json_decode($query->queryTable("
	SELECT ngs_file_submissions.id, file_name, file_type, parent_file, step_run, additional_derived_from, outdir, file_acc, file_uuid, file_md5
	FROM ngs_file_submissions
	LEFT JOIN ngs_runparams
	ON ngs_file_submissions.run_id = ngs_runparams.id
	WHERE sample_id = $sample_id
	"));

//Encoded access information
$encoded_access_key = ENCODE_ACCESS;
$encoded_secret_access_key = ENCODE_SECRET;

//Lab info
$my_lab = $experiment_info[0]->lab;
$my_award = $experiment_info[0]->grant;

$step = 0;
$tdfcount = 0;
$step_list = array();
$run_type = "";

//For each file
echo '{"status":"start"}';
foreach($sample_name_query as $snq){
	$dataset_acc = $snq->experiment_acc;
	$encValData = 'encValData';
	$assembly = $snq->organism_symbol;
	$replicate = "/replicates/" . $snq->replicate_uuid . "/";
	$md5_sums = array();
	foreach($file_sub as $sub){
		$file_accs = array();
		$file_uuids = array();
		$file_names = array();
		$extended_file_names = array();
		$fnc = 0;
		if($sub->parent_file == 0){
			$file_names = explode(",",$sub->file_name);
			echo $filenames;
			if(count($file_names) == 2){
				$run_type = 'paired-ended';
			}else{
				$run_type = 'single-ended';
			}
		}else{
			if($run_type == 'paired-ended' && $sub->file_type == 'fastq'){
				$pre_files = explode(",",$sub->file_name);
				foreach($pre_files as $pf){
					array_push($file_names, end(explode("/",$pf)));
					array_push($extended_file_names, $pf);
				}
			}else{
				array_push($file_names, end(explode("/",$sub->file_name)));
			}
		}
		foreach($file_names as $fn){
			$inserted = false;
			if($sub->parent_file == 0){
				$submissionfile = $dir_query[0]->backup_dir . "/" . $fn;
			}else if($sub->parent_file != 0 && $sub->file_type == "fastq"){
				$submissionfile = $sub->outdir . "/" . $extended_file_names[$fnc] . ".gz";
			}else if($sub->parent_file != 0 && $sub->file_type == "peaks-bed"){
				$submissionfile = $sub->outdir . "/" . $sub->file_name . ".gz";
			}else{
				$submissionfile = $sub->outdir . "/" . $sub->file_name;
			}
			//	Future: Grab 'platform'
			$data = baselineJSON($dataset_acc, $replicate, $snq, $sub, $my_lab, $my_award, $fn, $snq->instrument_model, $dir_query, $extended_file_names[$fnc]);
			$fnc++;
			if($sub->file_type == 'fastq'){
				$data = fastqJSON($data, $sub, $my_lab, $snq->samplename, $run_type, $fn, $file_names, $step_list);
			}else if($sub->file_type == 'bam'){
				$data = bamJSON($data, $sub, $my_lab, $snq->samplename, $run_type, $step_list, $snq->organism_symbol);
			}else if($sub->file_type == 'tdf'){
				$data = tdfJSON($data, $sub, $my_lab, $snq->samplename, $run_type, $step_list, $tdfcount, $snq->organism_symbol);
				$tdfcount++;
			}else if($sub->file_type == 'bigWig'){
				$data = bigwigJSON($data, $sub, $my_lab, $snq->samplename, $run_type, $step_list, $snq->organism_symbol);
			}else if($sub->file_type == 'peaks-bed'){
				$data = bedJSON($data, $sub, $my_lab, $snq->samplename, $run_type, $step_list, $snq->organism_symbol);
			}
			array_push($md5_sums, $data["md5sum"]);
			$gzip_types = array(
				"CEL",
				"bam",
				"bed",
				"csfasta",
				"csqual",
				"fasta",
				"fastq",
				"gff",
				"gtf",
				"tar",
				"sam",
				"wig"
			);
			
			// Validation data
			$chromInfo = '-chromInfo='.$encValData.'/'.$assembly.'/chrom.sizes';
			$validate_map = array(
				'fasta' =>  array(null => array('-type=fasta')),
				'fastq' =>  array(null => array('-type=fastq')),
				'bam' => array(null => array('-type=bam', $chromInfo)),
				'bigWig' => array(null => array('-type=bigWig', $chromInfo)),
				'bed' => array('bed3' => array('-type=bed3', $chromInfo),
					'bed6' => array('-type=bed6+', $chromInfo),
					'bedLogR' => array('-type=bed9+1', $chromInfo, '-as='.$encValData.'/as/bedLogR.as'),
					'bedMethyl' => array('-type=bed9+2', $chromInfo, '-as='.$encValData.'/as/bedMethyl.as'),
					'broadPeak' => array('-type=bed6+3', $chromInfo, '-as='.$encValData.'/as/broadPeak.as'),
					'gappedPeak' => array('-type=bed12+3', $chromInfo, '-as='.$encValData.'/as/gappedPeak.as'),
					'narrowPeak' => array('-type=bed6+4', $chromInfo, '-as='.$encValData.'/as/narrowPeak.as'),
					'bedRnaElements' => array('-type=bed6+3', $chromInfo, '-as='.$encValData.'/as/bedRnaElements.as'),
					'bedExonScore' => array('-type=bed6+3', $chromInfo, '-as='.$encValData.'/as/bedExonScore.as'),
					'bedRrbs' => array('-type=bed9+2', $chromInfo, '-as='.$encValData.'/as/bedRrbs.as'),
					'enhancerAssay' => array('-type=bed9+1', $chromInfo, '-as='.$encValData.'/as/enhancerAssay.as'),
					'modPepMap' => array('-type=bed9+7', $chromInfo, '-as='.$encValData.'/as/modPepMap.as'),
					'pepMap' => array('-type=bed9+7', $chromInfo, '-as='.$encValData.'/as/pepMap.as'),
					'openChromCombinedPeaks' => array('-type=bed9+12', $chromInfo, '-as'.$encValData.'s/as/openChromCombinedPeaks.as'),
					'peptideMapping' => array('-type=bed6+4', $chromInfo, '-as='.$encValData.'/as/peptideMapping.as'),
					'shortFrags' => array('-type=bed6+21', $chromInfo, '-as='.$encValData.'/as/shortFrags.as')
					),
				'bigBed' => array('bed3' => array('-type=bed3', $chromInfo),
					'bed6' => array('-type=bigBed6+', $chromInfo),
					'bedLogR' => array('-type=bigBed9+1', $chromInfo, '-as='.$encValData.'/as/bedLogR.as'),
					'bedMethyl' => array('-type=bigBed9+2', $chromInfo, '-as='.$encValData.'/as/bedMethyl.as'),
					'broadPeak' => array('-type=bigBed6+3', $chromInfo, '-as='.$encValData.'/as/broadPeak.as'),
					'gappedPeak' => array('-type=bigBed12+3', $chromInfo, '-as='.$encValData.'/as/gappedPeak.as'),
					'narrowPeak' => array('-type=bigBed6+4', $chromInfo, '-as='.$encValData.'/as/narrowPeak.as'),
					'bedRnaElements' => array('-type=bed6+3', $chromInfo, '-as='.$encValData.'/as/bedRnaElements.as'),
					'bedExonScore' => array('-type=bigBed6+3', $chromInfo, '-as='.$encValData.'/as/bedExonScore.as'),
					'bedRrbs' => array('-type=bigBed9+2', $chromInfo, '-as='.$encValData.'/as/bedRrbs.as'),
					'enhancerAssay' => array('-type=bigBed9+1', $chromInfo, '-as='.$encValData.'/as/enhancerAssay.as'),
					'modPepMap' => array('-type=bigBed9+7', $chromInfo, '-as='.$encValData.'/as/modPepMap.as'),
					'pepMap' => array('-type=bigBed9+7', $chromInfo, '-as='.$encValData.'/as/pepMap.as'),
					'openChromCombinedPeaks' => array('-type=bigBed9+12', $chromInfo, '-as='.$encValData.'/as/openChromCombinedPeaks.as'),
					'peptideMapping' => array('-type=bigBed6+4', $chromInfo, '-as='.$encValData.'/as/peptideMapping.as'),
					'shortFrags' => array('-type=bigBed6+21', $chromInfo, '-as='.$encValData.'/as/shortFrags.as')
					),
				'rcc' => array(null => array('-type=rcc')),
				'idat' => array(null => array('-type=idat')),
				'bedpe' => array(null => array('-type=bed3+', $chromInfo)),
				'bedpe' => array('mango' => array('-type=bed3+', $chromInfo)),
				'gtf' => array(null => array(null)),
				'tar' => array(null => array(null)),
				'tsv' => array(null => array(null)),
				'csv' => array(null => array(null)),
				'2bit' => array(null => array(null)),
				'csfasta' => array(null => array('-type=csfasta')),
				'csqual' => array(null => array('-type=csqual')),
				'CEL' => array(null => array(null)),
				'sam' => array(null => array(null)),
				'wig' => array(null => array(null)),
				'hdf5' => array(null => array(null)),
				'gff' => array(null => array(null))
			);
			/*
			$validate_args = $validate_map[$data['file_format']][null];
			$cmd = ENCODE_VALIDATE."/validateFiles " . $validate_args[0] . " " . $directory . $fn;
			$VALIDATE = popen( $cmd, "r" );
			$VALIDATE_READ =fread($VALIDATE, 2096);
			pclose($VALIDATE);
			*/
			$VALIDATE_READ = "Error count 0\n";
			if($VALIDATE_READ == "Error count 0\n"){
				//	File Validation Passed
				$headers = array('Content-Type' => 'application/json', 'Accept' => 'application/json');
				$server_start = ENCODE_URL;
				$server_end = "/";
				$auth = array('auth' => array($encoded_access_key, $encoded_secret_access_key));
				//	Set up file links for next step submission
				$extra_tests = false;
				if(count($file_names) == 2){
					if($fn == $file_names[0] && explode(",",$sub->file_acc)[0] != ""){
						$extra_tests = false;
					}else if ($fn == $file_names[1] && explode(",",$sub->file_acc)[1] != ""){
						$extra_tests = false;
					}else{
						$extra_tests = true;
					}
				}
				if($sub->file_acc == NULL || $sub->file_acc == "" || $extra_tests){
					$inputType = "POST";
					$url = $server_start . 'file' . $server_end;
					$response = Requests::post($url, $headers, json_encode($data), $auth);
					$body = json_decode($response->body);
					$inserted = true;
					array_push($file_accs, $body->{'@graph'}[0]->{'accession'});
					array_push($file_uuids, $body->{'@graph'}[0]->{'uuid'});
					if(end($file_names) == $fn){
						if(isset($step_list[$step])){
							$step_list[$step] .= ',/files/' . end($file_accs) . $server_end;
						}else{
							$step_list[$step] = '/files/' . end($file_accs) . $server_end;
						}
					}else{
						if(isset($step_list[$step])){
							$step_list[$step] .= ',/files/' . $file_accs[0] . $server_end;
						}else{
							$step_list[$step] = '/files/' . $file_accs[0] . $server_end;
						}
					}
				}else{
					$inputType = "PATCH";
					if(end($file_names) == $fn){
						$url = $server_start . 'file/' . end(explode(",",$sub->file_acc)) . $server_end;
						if(isset($step_list[$step])){
							$step_list[$step] .= ',/files/' . end(explode(",",$sub->file_acc)) . $server_end;
						}else{
							$step_list[$step] = '/files/' . end(explode(",",$sub->file_acc)) . $server_end;
						}
					}else{
						$url = $server_start . 'file/' . explode(",",$sub->file_acc)[0] . $server_end;
						if(isset($step_list[$step])){
							$step_list[$step] .= ',/files/' . explode(",",$sub->file_acc)[0] . $server_end;
						}else{
							$step_list[$step] = '/files/' . explode(",",$sub->file_acc)[0] . $server_end;
						}
					}
					$response = Requests::patch($url, $headers, json_encode($data), $auth);
					$body = json_decode($response->body);
				}
				
				$logloc = $_SESSION['encode_log'];
				$logfile = fopen($logloc, "a") or die("Unable to open file!");
				fwrite($logfile, $inputType."\n".$response->body . "\n\n");
				fclose($logfile);
				
				$item = $body->{'@graph'}[0];
				
				echo ','.$response->body;
				
				###################
				# POST file to S3 #
				###################
                                $inserted=true;
				if($inserted){
					$creds = $item->{'upload_credentials'};
					$cmd_aws_launch = "python ../../scripts/encode_file_submission.py " . $submissionfile . " " . $creds->{'access_key'} . " " .
						$creds->{'secret_key'} . " " . $creds->{'upload_url'} . " " . $creds->{'session_token'} . " " .
						ENCODE_URL . " " . ENCODE_BUCKET . " " . $_SESSION['encode_log'] . " &";
					$AWS_COMMAND_DO = popen( $cmd_aws_launch, "r" );
					$AWS_COMMAND_OUT = fread($AWS_COMMAND_DO, 2096);
					pclose($AWS_COMMAND_DO);
					echo $cmd_aws_launch . "\n\n";
					echo ','.$AWS_COMMAND_OUT;
					echo ','.$cmd_aws_launch;
				}
			}else{
				//	File Validation Failed
				if(end($file_names) == $fn){
					echo ',{"error":"'.$fn.' not validated"}';
				}else{
					echo ',{"error":"'.$fn.' not validated"}' . ',';
				}
			}
		}
		//	Store uuid/acc in database
		if($inserted && end($file_names) == $fn){
			$file_update = json_decode($query->runSQL("
			UPDATE ngs_file_submissions
			SET `file_md5` = '" . implode(",",$md5_sums) . "', 
			`file_acc` = '" . implode(",",$file_accs) . "',
			`file_uuid` = '" . implode(",",$file_uuids) . "' 
			WHERE id = " . $sub->id));
		}
		$step++;
	}
	echo ',{"status":"end"}';
}
?>
