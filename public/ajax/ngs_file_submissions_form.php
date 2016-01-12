<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');
require_once("../../config/config.php");
require_once("../../includes/dbfuncs.php");
$query = new dbfuncs();
//header

if (isset($_GET['sample_ids'])){$sample_ids = $_GET['sample_ids'];}
$sample_id_array = explode(",", $sample_ids);

foreach($sample_id_array as $sia){
	//	Grab needed variables from the database
	
	//ngs_samples (samplename)
	$ngs_samples = $query->queryAVal("
		SELECT samplename
		FROM ngs_samples
		WHERE id = $sia
		");
	//ngs_fastq_files grab (all)
	$ngs_fastq_files = json_decode($query->queryTable("
		SELECT *
		FROM ngs_fastq_files
		WHERE sample_id = $sia
		"));
	//ngs_dirs grab (all)
	//Requires $ngs_fastq_files
	$ngs_dirs = json_decode($query->queryTable("
		SELECT *
		FROM ngs_dirs
		WHERE id = " . $ngs_fastq_files[0]->dir_id
		));
	//ngs_runlist grab (run_id)
	$ngs_runlist = json_decode($query->queryTable("
		SELECT run_id
		FROM ngs_runlist
		WHERE sample_id = " . $sia . "
		AND run_id IN (
			SELECT id
			FROM ngs_runparams
			WHERE json_parameters LIKE '%submission\":\"1%'
			)
		"));
	//ngs_runparams (outdir)
	$ngs_runparams = json_decode($query->queryTable("
		SELECT outdir
		FROM ngs_runparams
		WHERE id = " . $ngs_runlist[0]->run_id
		));
	$directory = $ngs_runparams[0]->outdir;
	if(substr($directory, -1) != '/'){
		$directory = $directory . "/";
	}
	
	/*
	Encode Files needed per sample:
	
	1. Merged, untouched fast files.  Two per line if paired-end
	
	Example:
	control_rep1.1.fastq.gz,control_rep1.2.fastq.gz
	
	2. fastqc zip files, two per line if paired-end
	
	example:
	fastqc/control_rep1.1/control_rep1.1_fastqc.zip,fastqc/control_rep1.2/control_rep1.2_fastqc.zip
	
	3. seqmapping fastq
	
	example:
	seqmapping/snrna/control_rep1.1.fastq.gz,seqmapping/snrna/control_rep1.2.fastq.gz
	
	4. seqmapping bam files
	
	example
	seqmapping/snrna/control_rep1.sorted.bam
	
	5. summary tsv tables
	
		4 files per submission:
		rRNA
		miRNA
		tRNA
		snRNA
	
		give file name, not actual table
	example:
	counts/control_rep1.merge_summary.tsv
	
	6. Tophat sorted alignment
	
	example:
	tdf_Tophat/control_rep1.bam
	
	7. Bigwig for UCSC
	
	example:
	ucsc_Tophat/control_rep1.bw
	
	8. mRNA quantification step TSV
	
		4 files per submission
		iso_exp
		iso_tmp
		gene_exp
		gene_tmp
	
	example:
	rsem/control_rep1_iso_exp.tsv
	
	9. picard metrics tsv
	
	example:
	picard_Tophat/control_rep1_RNASeqMetrics.tsv
	
	10. RSeQC tsv
	
	example:
	RSeQC_RSEM/RSqQC.control_rep1.out
	*/
	
	//	1.
	$sub1_file_name = $ngs_fastq_files[0]->file_name;
	$sub1_file_type = 'fastq';
	$sub1_file_md5 = $ngs_fastq_files[0]->checksum;
	
	//	2.
	if(count(explode(",",$sub1_file_name)) > 1){
		$sub2_file_name = 'fastqc/'.$ngs_samples.'.1/'.$ngs_samples.'.1_fastqc.zip,fastqc/'.$ngs_samples.'.2/'.$ngs_samples.'.2_fastqc.zip';
		$sub2_file_md5_1 = md5_file($directory . 'fastqc/'.$ngs_samples.'.1/'.$ngs_samples.'.1_fastqc.zip');
		$sub2_file_md5_2 = md5_file($directory . 'fastqc/'.$ngs_samples.'.2/'.$ngs_samples.'.2_fastqc.zip');
		$sub2_file_md5 = $sub2_file_md5_1 . "," . $sub2_file_md5_2;
	}else{
		$sub2_file_name = 'fastqc/'.$ngs_samples.'/'.$ngs_samples.'_fastqc.zip';
		$sub2_file_md5 = md5_file($directory . 'fastqc/'.$ngs_samples.'/'.$ngs_samples.'_fastqc.zip');
	}
	$sub2_file_type = 'fastqc';
	
	//	3.
	if(count(explode(",",$sub1_file_name)) > 1){
		$sub3_file_name = 'seqmapping/snrna/'.$ngs_samples.'.1.fastq.gz,seqmapping/snrna/'.$ngs_samples.'.2.fastq.gz';
		$sub3_file_md5_1 = md5_file($directory . 'seqmapping/snrna/'.$ngs_samples.'.1.fastq.gz');
		$sub3_file_md5_2 = md5_file($directory . 'seqmapping/snrna/'.$ngs_samples.'.2.fastq.gz');
		$sub3_file_md5 = $sub2_file_md5_1 . "," . $sub2_file_md5_2;
	}else{
		$sub3_file_name = 'fastqc/'.$ngs_samples.'/'.$ngs_samples.'_fastqc.zip';
		$sub3_file_md5 = md5_file($directory . 'seqmapping/snrna/'.$ngs_samples.'.fastq.gz');
	}
	$sub3_file_type = 'fastq';
	
	//	4.
	$sub4_file_name = 'seqmapping/snrna/'.$ngs_samples.'.sorted.bam';
	$sub4_file_md5 = md5_file($directory . 'seqmapping/snrna/'.$ngs_samples.'.sorted.bam');
	$sub4_file_type = 'bam';
	
	//	5. (4 files)
	//	rRNA
	$sub5_file_name_1 = 'counts/'.$ngs_samples.'_rRNA_summary.tsv';
	$sub5_file_type_1 = 'tsv';
	//	miRNA
	$sub5_file_name_2 = 'counts/'.$ngs_samples.'_miRNA_summary.tsv';
	$sub5_file_type_2 = 'tsv';
	//	tRNA
	$sub5_file_name_3 = 'counts/'.$ngs_samples.'_tRNA_summary.tsv';
	$sub5_file_type_3 = 'tsv';
	//	snRNA
	$sub5_file_name_4 = 'counts/'.$ngs_samples.'_snRNA_summary.tsv';
	$sub5_file_type_4 = 'tsv';
	
	//	6.
	$sub6_file_name = 'tdf_Tophat/'.$ngs_samples.'.bam';
	$sub6_file_md5 = md5_file($directory . 'tdf_Tophat/'.$ngs_samples.'.bam');
	$sub6_file_type = 'bam';
	
	//	7.
	$sub7_file_name = 'ucsc_Tophat/'.$ngs_samples.'.bw';
	$sub7_file_md5 = md5_file($directory . 'ucsc_Tophat/'.$ngs_samples.'.bw');
	$sub7_file_type = 'bigwig';
	
	//	8. (4 files)
	//	iso_exp
	$sub8_file_name_1 = 'rsem/'.$ngs_samples.'_iso_exp.tsv';
	$sub8_file_type_1 = 'tsv';
	//	iso_tpm
	$sub8_file_name_2 = 'rsem/'.$ngs_samples.'_iso_tpm.tsv';
	$sub8_file_type_2 = 'tsv';
	//	gene_exp
	$sub8_file_name_3 = 'rsem/'.$ngs_samples.'_gene_exp.tsv';
	$sub8_file_type_3 = 'tsv';
	//	gene_tmp
	$sub8_file_name_4 = 'rsem/'.$ngs_samples.'_gene_tpm.tsv';
	$sub8_file_type_4 = 'tsv';
	
	//	9.
	$sub9_file_name = 'picard_Tophat/'.$ngs_samples.'_RNASeqMetrics.tsv';
	$sub9_file_md5 = md5_file($directory . 'picard_Tophat/'.$ngs_samples.'_RNASeqMetrics.tsv');
	$sub9_file_type = 'tsv';
	
	//	10.
	$sub10_file_name = 'RSeQC_Tophat/RSqQC.rsem.out.'.$ngs_samples.'.genome.out';
	$sub10_file_md5 = md5_file($directory . 'RSeQC_RSEM/RSqQC.rsem.out.'.$ngs_samples.'.genome.out');
	$sub10_file_type = 'tsv';
	
	//	Insertion into Database
	$insertion = $query->runSQL("
		INSERT INTO ngs_file_submissions
		(dir_id, run_id, sample_id, file_name, file_type, file_md5)
		VALUES
		(".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, '$sub1_file_name', '$sub1_file_type', '$sub1_file_md5'),
		(".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, '$sub2_file_name', '$sub2_file_type', '$sub2_file_md5'),
		(".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, '$sub3_file_name', '$sub3_file_type', '$sub3_file_md5'),
		(".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, '$sub4_file_name', '$sub4_file_type', '$sub4_file_md5'),
		(".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, '$sub5_file_name_1', '$sub5_file_type_1', NULL),
		(".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, '$sub5_file_name_2', '$sub5_file_type_2', NULL),
		(".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, '$sub5_file_name_3', '$sub5_file_type_3', NULL),
		(".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, '$sub5_file_name_4', '$sub5_file_type_4', NULL),
		(".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, '$sub6_file_name', '$sub6_file_type', '$sub6_file_md5'),
		(".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, '$sub7_file_name', '$sub7_file_type', '$sub7_file_md5'),
		(".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, '$sub8_file_name_1', '$sub8_file_type_1', NULL),
		(".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, '$sub8_file_name_2', '$sub8_file_type_2', NULL),
		(".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, '$sub8_file_name_3', '$sub8_file_type_3', NULL),
		(".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, '$sub8_file_name_4', '$sub8_file_type_4', NULL),
		(".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, '$sub9_file_name', '$sub9_file_type', '$sub9_file_md5'),
		(".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, '$sub10_file_name', '$sub10_file_type', '$sub10_file_md5')
		");
	
	var_dump("insertion sub1:  ".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, $sub1_file_name, $sub1_file_type, $sub1_file_md5");
	var_dump("insertion sub2:  ".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, $sub2_file_name, $sub2_file_type, $sub2_file_md5");
	var_dump("insertion sub3:  ".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, $sub3_file_name, $sub3_file_type, $sub3_file_md5");
	var_dump("insertion sub4.1:  ".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, $sub4_file_name_1, $sub4_file_type_1, NULL");
	var_dump("insertion sub4.2:  ".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, $sub4_file_name_2, $sub4_file_type_2, NULL");
	var_dump("insertion sub4.3:  ".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, $sub4_file_name_3, $sub4_file_type_3, NULL");
	var_dump("insertion sub4.4:  ".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, $sub4_file_name_4, $sub4_file_type_4, NULL");
	var_dump("insertion sub5:  ".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, $sub5_file_name, $sub5_file_type, $sub5_file_md5");
	var_dump("insertion sub6:  ".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, $sub6_file_name, $sub6_file_type, $sub6_file_md5");
	var_dump("insertion sub7:  ".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, $sub7_file_name, $sub7_file_type, $sub7_file_md5");
	var_dump("insertion sub8.1:  ".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, $sub8_file_name_1, $sub8_file_type_1, NULL");
	var_dump("insertion sub8.2:  ".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, $sub8_file_name_2, $sub8_file_type_2, NULL");
	var_dump("insertion sub8.3:  ".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, $sub8_file_name_3, $sub8_file_type_3, NULL");
	var_dump("insertion sub8.4:  ".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, $sub8_file_name_4, $sub8_file_type_4, NULL");
	var_dump("insertion sub9:  ".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, $sub9_file_name, $sub9_file_type, $sub9_file_md5");
	var_dump("insertion sub10:  ".$ngs_dirs[0]->id.", ".$ngs_runlist[0]->run_id.", $sia, $sub10_file_name, $sub10_file_type, $sub10_file_md5");
	var_dump("@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@");
}

$data = "EOF";

//footer
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo $data;
exit;
?>