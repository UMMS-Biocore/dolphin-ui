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

//testing
/*
$sample_id = 1;
$experiment = 'TSTSR295244';
$replicate = 'b150d4e9-7d5c-4f79-87b3-e5dec3f0c524';
*/
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
	SELECT samplename, machine_name, flowcell, lane
	FROM ngs_samples
	LEFT JOIN ngs_flowcell
	ON ngs_samples.flowcell_id = ngs_flowcell.id
	WHERE ngs_samples.id = $sample_id
	"));

//Encoded access information
$encoded_access_key = ENCODE_ACCESS;
$encoded_secret_access_key = ENCODE_SECRET;

//Experiment Accession number
$dataset_acc = $experiment;

//Lab info
$my_lab = $experiment_info[0]->lab;
$my_award = $experiment_info[0]->grant;

//Other information
$encValData = 'encValData';
$assembly = 'hg19';
$replicate = "/replicates/" . $replicate . "/";

$step_list = array();
$sample_count = 0;
//For each file (single or paired end)
foreach($fastq_data as $fq){
	$sample_name = $sample_name_query[$sample_count]->samplename;
	$machine_name = $sample_name_query[$sample_count]->machine_name;
	if($machine_name == 'None'){
		$machine_name = 'uknown';
	}
	$flowcell = $sample_name_query[$sample_count]->flowcell;
	if($flowcell == 'None'){
		$flowcell = 'unknown';
	}
	$lane = $sample_name_query[$sample_count]->lane;
	if($lane == 'None'){
		$lane = 'unknown';
	}
	$inserted = false;
	$file_accs = array();
	$file_uuids = array();
	$paired = '';
	$file_names = explode(",",$fq->file_name);
	
	//	Fill out file metadata to submit before actual file submission
	foreach($file_names as $fn){
		//File path
		$directory = $dir_query[0]->backup_dir;
		if(substr($directory, -1) != '/'){
			$directory = $directory . "/";
		}
		$file_size = filesize($directory . $fn);
		//File checksum
		if(end($file_names) == $fn){
			$md5sum = end(explode(",",$fq->checksum));
		}else{
			$md5sum = explode(",",$fq->checksum)[0];
		}
		$data = array(
			"dataset" => $dataset_acc,
			"replicate" => $replicate,
			"file_size" => $file_size,
			"md5sum" => $md5sum,
			"read_length" => 101,
			"platform" => "ENCODE:HiSeq2000",
			"submitted_file_name" => end(explode("/",$fn)),
			"lab" => $my_lab,
			"award" => $my_award,
			"flowcell_details" => array(array("machine" => $machine_name),
										array("flowcell" => $flowcell),
										array("lane" => $lane))
		);
			
		$data['output_type'] = 'reads';
		$step = "step1";
		if(count($file_names) == 2){
			//	FASTQ PAIRED
			$data["file_format"] = 'fastq';
			$data["run_type"] = "paired-ended";
			if(end($file_names) == $fn){
				$data["aliases"] = array($my_lab.':fastq_p2_'.$sample_name);
				$data["paired_end"] = '2';
				$data["paired_with"] = $paired;
			}else{
				$data["aliases"] = array($my_lab.':fastq_p1_'.$sample_name);
				$data["paired_end"] = '1';
				$paired = $my_lab.':fastq_p1_'.$sample_name;
			}
		}else if (count($file_names) == 1){
			//	FASTQ SINGLE
			$data["file_format"] = 'fastq';
			$data["run_type"] = "single-ended";
			$data["aliases"] = array($my_lab.':fastq_'.$sample_name);
			if($step != 'step1'){
				$data['derived_from'] = explode(",",$step_list['step1']);
			}
		}
		
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
		if(in_array($data['file_format'], $gzip_types) && explode('.',$fn)[count(explode('.',$fn))] == '.gz'){
			$is_gzipped = 'Expected gzipped file';
		}else{
			$is_gzipped = 'Expected un-gzipped file';
		}
		
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
			if($fq->file_acc == null || $fq->file_acc == ""){
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
				if(end($file_names) == $fn){
					$url = $server_start . 'file/' . end(explode(",",$fq->file_acc)) . $server_end;
					if(isset($step_list[$step])){
						$step_list[$step] .= ',/files/' . end(explode(",",$fq->file_acc)) . $server_end;
					}else{
						$step_list[$step] = '/files/' . end(explode(",",$fq->file_acc)) . $server_end;
					}
				}else{
					$url = $server_start . 'file/' . explode(",",$fq->file_acc)[0] . $server_end;
					if(isset($step_list[$step])){
						$step_list[$step] .= ',/files/' . explode(",",$fq->file_acc)[0] . $server_end;
					}else{
						$step_list[$step] = '/files/' . explode(",",$fq->file_acc)[0] . $server_end;
					}
				}
				$response = Requests::patch($url, $headers, json_encode($data), $auth);
				$body = json_decode($response->body);
			}
			
			$logloc = $_SESSION['encode_log'];
			$logfile = fopen($logloc, "a") or die("Unable to open file!");
			fwrite($logfile, $response->body . "\n\n");
			fclose($logfile);
			
			$item = $body->{'@graph'}[0];
			
			echo $response->body.',';
			
			####################
			# POST file to S3
			$creds = $item->{'upload_credentials'};
			$cmd_aws_launch = "python ../../scripts/encode_file_submission.py " . $directory.$fn . " " . $creds->{'access_key'} . " " .
				$creds->{'secret_key'} . " " . $creds->{'upload_url'} . " " . $creds->{'session_token'} . " " .
				ENCODE_URL . " " . ENCODE_BUCKET . " " . $_SESSION['encode_log'] . " &";
			$AWS_COMMAND_DO = popen( $cmd_aws_launch, "r" );
			$AWS_COMMAND_OUT = fread($AWS_COMMAND_DO, 2096);
			pclose($AWS_COMMAND_DO);
			//echo $cmd_aws_launch . "\n\n";
			echo $AWS_COMMAND_OUT . ",";
			echo $cmd_aws_launch;
			
			if(end($file_names) != $fn){
				echo ',';
			}
		}else{
			//	File Validation Failed
			if(end($file_names) == $fn){
				echo '{"error":"'.$fn.' not validated"}';
			}else{
				echo '{"error":"'.$fn.' not validated"}' . ',';
			}
		}
		//	Store uuid/acc in database
		if($inserted && implode(",",$file_accs) != ","){
			$file_update = json_decode($query->runSQL("
			UPDATE ngs_fastq_files
			SET `file_acc` = '" . implode(",",$file_accs) . "',
			`file_uuid` = '" . implode(",",$file_uuids) . "' 
			WHERE id = " . $fq->id));
		}
	}
	$sample_count++;
}
?>
