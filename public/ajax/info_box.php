<?php
//error_reporting(E_ERROR);
error_reporting(E_ALL);
ini_set('report_errors','on');

if (!isset($_SESSION) || !is_array($_SESSION)) session_start();

//	Grab passed variables
$data = "test";
if (isset($_GET['p'])){$p = $_GET['p'];}

if($p == "experiments"){
	$data = "This table represents all of the Experiment Series you have access to viewing.
			<br><br>
			You can select all of the samples within a specific experiment series by clicking the checkbox
			to the right.
			<br><br>
			To display more information about the Experiment Series, you can click on the
			expand button to the left of this info box.
			<br><br>
			You can sort the information within this table by searching for specific key phrases in the upper right search box, or by clicking on the
			headers of the table to sort based on that columns ascending or descending order.
			<br><br>
			If you have permissions, you can change the information within the table by clicking on the cell you want to alter, altering the information
			and then pressing enter to save the change.  To cancel the alterations, simply hit the 'esc' key.";
}else if($p == "lanes"){
	$data = "This table represents all of the Imports you have access to viewing.
			<br><br>
			You can select all of the samples within a specific import by clicking on the checkbox to the right.
			<br><br>
			The 'Backup' column displays the status of the import's samples amazon backup status.  Backups will only be prompted if correct amazon credentials
			are supplies on sample imports.  The status can be 1 of 5 colors: grey means the backup is pending, red stands for an amazon checksum mismatch,
			pink means the checksum has changed from the original checksum of the files, blue means the backup has no errors but is over 2 months old,
			and green means the upload is current.
			<br><br>
			To view more information about the Imports, you can click on the expand button to the left of this info box.
			<br><br>
			You can sort the information within this table by searching for specific key phrases in the upper right search box, or by clicking on the
			headers of the table to sort based on that columns ascending or descending order.
			<br><br>
			If you have permissions, you can alter the information within a specific cell by clicking on the cell you want to alter, altering the information
			and then pressing enter to save the change.  To cancel the alterations, simply hit the 'esc' key.";
}else if ($p == "samples"){
	$data = "This table represents all of the Samples you have access to viewing.
			<br><br>
			You can select a sample by clicking on the checkbox to the right.
			<br><br>
			The 'Backup' column displays the status of the samples amazon backup status.  Backups will only be prompted if correct amazon credentials
			are supplies on sample imports.  The status can be 1 of 4 colors: grey means the backup is pending, red stands for an amazon checksum mismatch,
			pink means the checksum has changed from the original checksum of the files, blue means the backup has no errors but is over 2 months old,
			and green means the upload is current.
			<br><br>
			For more information about the samples, you can click on the expand button to the left of this info box.
			<br><br>
			You can sort the information within this table by searching for specific key phrases in the upper right search box, or by clicking on the
			headers of the table to sort based on that columns ascending or descending order.
			<br><br>
			If you have permissions, you can alter the information in a specific cell by clicking on the cell you want to alter, altering the information
			and then pressing enter to save the change.  To cancel the alterations, simply hit the 'esc' key.
			<br><br>
			If viewing this table on the selection page, these are all the samples you have selected.";
}else if($p == "run_name"){
	$data = "Give a specific name for this particular run.";
}else if($p == "run_description"){
	$data = "Give a brief description of this particular run.";
}else if($p == "genomebuild"){
	$data = "Select the specific genome build you wish to use.";
}else if($p == "spaired"){
	$data = "Select yes if your samples are mate-paired.";
}else if($p == "resume"){
	$data = "Is this a Run a new run, or a continuation of a previous run.";
}else if($p == "outdir"){
	$data = "Please specify the full path for your backup directory.
			<br><br>
			Example:
			<br>
			/share/data/umw_biocore/genome_data/mousetest";
}else if($p == "fastqc"){
	$data = "Would you like to perform FastQC?
			<br><br>
			For more information on Fastqc, you can check out their website
			<a href=\"http://www.bioinformatics.babraham.ac.uk/projects/fastqc/\" style=\"color:blue\">here.</a>
			<br><br>
			FastQC Version: 0.10.1";
}else if($p == "adapters"){
	$data = "If you would like to perform adapter removal, Expand this box and select the yes checkbox.
			<br><br>
			Removes 3' Adapter Sequences. You can enter a single sequence or multiple sequences in different lines. Reverse sequences will not be removed.
			<br><br>
			Dolphin uses Trimmomatic for adapter removal, for more information about Trimmomatic you can click
			<a href\"http://www.usadellab.org/cms/uploads/supplementary/Trimmomatic/TrimmomaticManual_V0.32.pdf\" style=\"color:blue\">here.</a>
			<br><br>
			Trimmomatic Version: 0.32";
}else if($p == "custom"){
	$data = "If you would like to define your own custom sequence sets, Expand this box and click the 'Add a Custom Sequence Set' button.
			<br><br>
			You may add more than one custom sequence set.
			<br><br>
			In order to map your reads to a custom sequence, you first must create an index file and that fasta must be in the same folder.
			Please remove all spacing from the naming of sequences within your fasta file in order for our pipeline to properly prepare quantification tables.
			<br><br>
			The index directory must include the full path and the name of the index file must only be the prefix of the fasta. Index files and Fasta files
			also need to have the same prefix.
			<br><br>
			You may include additional Bowtie parameters in commandline form as well as a discription of the index created.
			<br><br>
			You may also select whether or not you want the reads mapped to this index filtered out of your total reads.
			<br><br>
			Example Bowtie parameters:
			<br>
			-build /fasta/path prefix
			<br><br>
			Dolphin uses Bowtie2 to create your custom index file.  For more information on Bowtie2 you can click
			<a href=\"http://bowtie-bio.sourceforge.net/bowtie2/manual.shtml\" style=\"color:blue\">here.</a>
			<br><br>
			Bowtie Version: 2.1.0";
}else if($p == "pipeline"){
	$data = "If you would like to run additional pipelines, Expand this box, click the 'Add Pipeline' button, and select a pipeline in which you would like to run.
			<br><br>
			The Deduplication process for RSEM, Tophat, and ChipSeq is only possible with paired-end reads.  If selecting 'Mark Duplicates' please be sure that your read
			data is paired end
			<br><br>
			RSEM custom genome index and annotation files need to have the same prefix in order to use them.
			<br><br>
			For ChipSeq analysis, you must define Chip input definitions in a column format separated by ';'.  The first column is the name to give
			the output files.  The second column contains a comma sepatared list of library names, and the third column has a comma separated list
			of the input samples.
			<br><br>
			To perform DESEQ, you must also run an RSEM pipeline.
			<br><br>
			You may only run 1 RSEM pipeline.
			<br><br>
			To perform DiffMeth, you must also run a BisulphiteMapping pipeline.
			<br><br>
			You may only run 1 BisulphiteMapping pipeline.
			<br><br>
			Pipelines will be run in sequential order from top to bottom.
			<br><br>
			RNASeqRSEM uses
			<a href=\"http://deweylab.biostat.wisc.edu/rsem/README.html\" style=\"color:blue\">RSEM</a> and
			<a href=\"http://bowtie-bio.sourceforge.net/manual.shtml\" style=\"color:blue\">Bowtie</a>, Tophat uses
			<a href=\"https://ccb.jhu.edu/software/tophat/manual.shtml\" style=\"color:blue\">Tophat2</a> and
			<a href=\"http://bowtie-bio.sourceforge.net/bowtie2/manual.shtml\" style=\"color:blue\"> Bowtie2 </a>, DESeq uses
			<a href=\"https://bioconductor.org/packages/release/bioc/html/DESeq2.html\" style=\"color:blue\">DESeq2</a>, ChipSeq uses
			<a href=\"http://liulab.dfci.harvard.edu/MACS/README.html\" style=\"color:blue\">Macs</a>, BisulphiteMapping uses
			<a href=\"http://bmcbioinformatics.biomedcentral.com/articles/10.1186/1471-2105-10-232\" style=\"color:blue\">BSMap</a>, and DiffMeth uses
			<a href=\"https://code.google.com/p/methylkit/\" style=\"color:blue\">Methyl Kit</a>.  For more information, click on the program of interest.
			<br><br>
			RSEM Version: 1.2.26
			<br>
			Bowtie Version: 0.12.9
			<br>
			Bowtie2 Version: 2.1.0
			<br>
			Tophat2 Version:2.0.9
			<br>
			DESeq2 Version: 1.10.2
			<br>
			Macs Version: 1.4.2
			<br>
			BSMap Version: 2.9
			<br>
			MethylKit Version: 0.9.2";
}else if($p == "split"){
	$data = "If you would like to split your fastq files, Expand this box and select the yes checkbox.
			<br><br>
			You can specify the number of reads per file below.";
}else if($p == "quality"){
	$data = "If you would like to perform quality filtering, Expand this box and select the yes checkbox.
			<br><br>
			This performs a variety of useful trimming tasks for illumina paired-end and single ended data.
			<br><br>
			Dolphin uses Trimmomatic for quality filtering, for more information about Trimmomatic you can click
			<a href\"http://www.usadellab.org/cms/uploads/supplementary/Trimmomatic/TrimmomaticManual_V0.32.pdf\" style=\"color:blue\">here.</a>
			<br><br>
			Trimmomatic Version: 0.32";
}else if($p == "trim"){
	$data = "If you would like to perform trimming, Expand this box and select the yes checkbox.
			<br><br>
			This trims both the 3' and 5' ends for both single-ended or paired-end reads.
			<br><br>
			Dolphin uses Trimmomatic for trimming, for more information about Trimmomatic you can click
			<a href\"http://www.usadellab.org/cms/uploads/supplementary/Trimmomatic/TrimmomaticManual_V0.32.pdf\" style=\"color:blue\">here.</a>
			<br><br>
			Trimmomatic Version: 0.32";
}else if($p == "commonind"){
	$data = "If you would like to map your reads sequentially to common RNAs, Expand this box and select the RNAs you would like to map.
			<br><br>
			Bowtie2 map your reads sequentially to common RNAs below. Mapped reads will be removed to go to the next step. Sequential mapping occurs on RNAs from left to right.
			To change the default parameters, please use 'change parameters' box.
			<br><br>
			You can give Bowtie specific parameters by selecting the 'Change Parameters' button.
			<br><br>
			Dolphin uses <a href=\"http://bowtie-bio.sourceforge.net/bowtie2/manual.shtml\" style=\"color:blue\">Bowtie2</a> and
			<a href=\"http://bedtools.readthedocs.org/en/latest/index.html\" style=\"color:blue\">Bedtools</a> for common RNA mapping/removal.  For more
			information you can click on the links of the program.<br><br>Bowtie2 Version: 2.1.0
			<br>
			Bedtools Version: 2.17.0";
}else if($p == "barcode_sep"){
	$data = "Select yes if you wish to perform barcode separation.";
}else if($p == "Barcode Definitions"){
	$data = "Please enter the sample name and barcode for each sample.
			<br><br>
			The format for submission of samples and barcodes is mimiced below.  Each line should be it's sample name, followed by a space, and finished with the barcode.
			For multiple barcodes, simply split your samples/barcodes with new lines.
			<br><br>
			Example barcodes:
			<br><br>
			control_rep1 CCGAGT
			<br>
			control_rep2 GATTTG
			<br>
			control_rep3 TTAGAC
			<br>
			exper_rep1 AACTCG
			<br>
			exper_rep2 CTGGGC
			<br>
			exper_rep3 GGTCTA";
}else if($p == "series_name"){
	$data = "Please enter the experiment series name.
			<br>
			If adding to an already existing experiment series, please enter the exact name.";
}else if($p == "lane_name"){
	$data = "Import a specific name for your group of samples.
			<br><br>
			This is specific name can be either a lane name or something completely customizable.
			<br><br>
			If adding to an already existing Import name, please enter the exact name.";
}else if($p == "input_dir"){
	$data = "Enter the full path of the Fastq files.
			<br><br>
			For input directory please use the full path in the cluster.
			<br><br>
			You can search the cluster for this directory and the files within it by selecting the 'Search Directory' button.
			Selecting this button will prompt you with how to find your specific fastq files with a regex search.  For paired-end reads, the regex search must
			find the exact same number of reads for each regex in order to display results within the 'Input Files' tab.
			<br><br>
			Example:
			<br>
			/share/data/umw_biocore/genome_data/mousetest";
}else if($p == "input_files"){
	$data = "<b>Manual Selection:</b>
			<br><br>
			No Barcode separation:
			<br><br>
			For \"Input Parameters\" please enter 2 columns for single end or 3 columns for paired end libraries.
			<br>
			First column represents the name of the libraries.
			<br>
			Please use only letters, numbers and the _ character.
			<br>
			The second (and possible third for paired-end) column(s) is the file name of the library.
			<br>
			(Ex: your_library.fastq)
			<br>
			Each file will be added with the path you entered in \"Input Directory\" section.
			<br><br>
			Barcode separation:
			<br><br>
			For \"Input Parameters\" please enter the file names 1 column for single and 2 columns for paired end lanes.
			<br>
			(Ex: your_library.fastq)
			<br>
			Each file will be added with the path you entered in \"Input Directory\" section.
			<br><br>
			<b>Directory Selection:</b>
			<br><br>
			After searching your input directory you will be switched to the 'Directory' tab within the input files section.  A list of files should appear (both paired-end if selected)
			above an empty table with some options.  Hightlight the files you wish to add to a sample and click the 'Add Sample' button.  The 'Add All' button takes your original
			regex search and attempts to create samples based on that regex.  You can remove samples by clicking on the corresponding red 'X' or to start over completely, hit the
			'Reset' button.";
}else if($p == "permissions"){
	$data = "Who will be able to view this run?";
}else if($p == "backup_dir"){
	$data = "Enter the directory path you would like the results to be located.
			<br><br>
			Example:
			<br>
			/share/data/umw_biocore/genome_data/mousetest";
}else if($p == "amazon_bucket"){
	$data = "Please specify your Amazon bucket.
			<br><br>
			If you would like to use our amazon options, please contact an admin at biocore@umassmed.edu with your security keys to set up this service.";
}else if($p == "runparams"){
	$data = "This table displays all of your runs, their current status, and options you have available for each run.
			<br><br>
			If your run is not queued, you may click on the status button to see advanced information about the run's steps.
			<br><br>
			The options button will display your current options you have for this run.  Rerun allows you to re-run this run with the option to alter alter the run parameters.
			Resume will resume this run with no change in parameters where the run last left off.  Reset will reset the status of this run and allow the run to start fresh from the
			beginning.  Stop will stop a currently running run.  Delete will delete this run from your status page.
			<br><br>
			If the run is complete, you may look at the runs results by selecting reports, or you can plot the runs results using the plots option.";
}else if($p == "services"){
	$data = "This table shows all of the services performed within the run.
			<br><br>
			To break down a service even further into it's seperate steps, click on the 'Select Service' button on the right.";
}else if($p == "jobs"){
	$data = "This table shows all of the jobs within a specific service.
			<br><br>
			To see the standard output of the job, click on the 'Select Job' button on the right.";
}else if($p == "initial_mapping"){
	$data = "This table shows the initial mapping summary.
			<br><br>
			For each sample selected this table will show the total number of reads within the sample, and if common RNA mapping was selected,
			it will also show the reads that mapped to each common RNA.
			<br><br>
			The selection box under the table also lets you view addional information about the common RNA mapping.
			<br><br>
			Once you have selected the results to view, a button will appear where you can download the specific file, clear the selected table,
			or view a link showing the data in a specific format.";
}else if($p == "summary"){
	$data = "To view FastQC summary results, expand this box and select the results you wish to view.  The summary includes information on all of the
			samples selected within one file.";
}else if($p == "details"){
	$data = "To view detailed FastQC summary results, expand this box and select the results you wish to view.  The detailed results includes
			all of the FastQC results for the specific sample.";
}else if($p == "RSEM"){
	$data = "To view RSEM results, Expand this box and select the results file you wish to view.
			<br><br>
			Once you have selected the results to view, a button will appear where you can download the specific file, clear the selected table,
			or view a link showing the data in a specific format.";
}else if($p == "DESEQ"){
	$data = "To view DESEQ results, Expand this box and select the results file you wish to view.
			<br><br>
			Once you have selected the results to view, a button will appear where you can download the specific file, clear the selected table,
			or view a link showing the data in a specific format.";
}else if($p == "excel_import"){
	$data = "Select an excel file to upload specified data from within the cluster.
			<br>
			An example excel sheet is provided for if the data is in a single directory or multiple directories below.
			<br><br>
			The excel sheet selected must be similar to the example provided.
			<br>
			Then select the group to submit under as well as who will be able to see the data.";
}else if($p == "plot_control_panel"){
	$data = "This panel displays all the information you can manipulate in order to show various plots.
			<br><br>
			First, select a file source. If you have an external file you wish to view, select the 'Input TSV file location' and insert the files location \
			in the box that appears.
			<br><br>
			The X and Y axis manipulate the X and Y axis on the scatterplot while the pseudo count and the color axis maniuplates how the scatterplot data
			is being displayed.
			<br><br>
			By selecting columns, you manipulate the heatmaps data display as well as the barplot.
			<br><br>
			You can query specific genes by searching for genes (comma separated) and hitting the submit button.
			<br><br>
			Selected genes on the scatter plot will be displayed within the 'Selected Region' box.";
}else if($p == "bar_format"){
	$data = "Select the location of the barcodes for barcode separation.";
}else if($p == "bar_distance"){
	$data = "Select the Hamming distance for barcode separation.";
}else if($p == "perms"){
	$data = "Who will be able to view this run?";
}else if($p == "groups"){
	$data = "Which group will you submit this run under?";
}else if($p == "submission"){
	$data = "Prep this run for submission?";
}else if($p == "run_types"){
	$data = "Change the type of run you would like displayed within the table above.";
}else if($p == "picard"){
	$data = "To view Picard results, Expand this box and select the results file you wish to view.
			<br><br>
			Once you have selected the results to view, a button will appear where you can download the specific file, clear the selected table,
			or view a link showing the data in a specific format.";
}else if($p == "rseqc"){
	$data = "To view RSeQC results, Expand this box and select the results file you wish to view.
			<br><br>
			For more information on the RSeQC reports, you can view the program used, 'read-distribution.py' for RSeQC, 
			<a href=\"http://rseqc.sourceforge.net/#read-distribution-py\" style=\"color:blue\">here.</a>
			<br><br>
			Once you have selected the results to view, a button will appear where you can download the specific file, clear the selected table,
			or view a link showing the data in a specific format.";
}else if($p == "user_profile"){
	$data = "This table displays all of your user information within our system obtained from our cluster network.";
}else if($p == "amazon"){
	$data = "This table displays the amazon bucket information you have access to.  Only the owner of the bucket may alter or view the
			entirety of the information displayed here.";
}else if($p == "table_viewer"){
	$data = "This table displays all of your previously created custom tables.
			<br><br>
			View allows you to view the table you created, Plot table will send you to the plots section with the table as the tsv for plotting,
			and Delete will delete the selected table.";
}else if($p == "generated"){
	$data = "This table displays the table generated from your sample selection.";
}else if($p == "table_export"){
	$data = "By clicking on the 'plus' button, an option with various download types for this data will apear for selection.  Select the type of
			download you desire and you will be re-directed to a page with the data in that format.";
}else if($p == "selected_samples"){
	$data = "This table is the list of samples you have selected to generate into a table.  You may choose the specific run you wish to gather
			information from and also remove the sample from the list by clicking on the red 'X'.";
}else if($p == "table_create"){
	$data = "By hitting the 'plus' button, an expanded list of samples you have permissions to and have runs with data will be listed.
			By clicking on the checkboxes to the right, you can add or removal additional samples without having to go back to the sample browser.";
}else if($p == "report_section"){
	$data = "When samples are selected, all possible files you can pair will be shown in this box.  If a file is greyed-out, then not all of
			your runs contain this file to generate a table.  Only select one file you wish to join upon.";
}else if($p == "email_address"){
	$data = "This is the email address that will be alerted when a run finishes successfully";
}else if($p == "email_check"){
	$data = "Would you like to be sent an email whenever runs successfully finish?";
}else if($p == "profile_groups"){
	$data = "This table displays all of the groups that you are a part of.  All users can view all of the current users within a group.
			<br><br>
			Owners of groups can add members to their groups, accept new member requests, change the owner of a group, or delete a group.
			<br><br>
			To create a group, simply click the 'Create a New Group' button and give the name of your group to create.  To request to be added to a specific
			group, click the 'Join a Group' button and select the group you wish to be added to.";
}

if (!headers_sent()) {
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: application/json');
	echo json_encode($data);
	exit;
}else{
	echo json_encode($data);
}
?>