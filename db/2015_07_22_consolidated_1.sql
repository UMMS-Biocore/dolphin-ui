ALTER TABLE `biocore`.`users` 
ADD COLUMN `photo_loc` VARCHAR(255) NOT NULL DEFAULT '/public/img/avatar5.png' AFTER `lab`;

CREATE TABLE `biocore`.`ngs_help` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `field_name` VARCHAR(45) NULL,
  `video_url` VARCHAR(255) NULL,
  `help_text` VARCHAR(5000) NULL,
  PRIMARY KEY (`id`));
  
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('1', 'experiments', 'This table represents all of the Experiment Series you have access to viewing.<br><br>For more information about the Experiment Series, you can click on the expand button to the right of this info box.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('2', 'lanes', 'This table represents all of the Imports you have access to viewing.<br><br>If enabled, you can select all of the samples within a specific import by clicking on the checkbox to the right.<br><br>For more information about the Imports, you can click on the expand button to the right of this info box.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('3', 'samples', 'This table represents all of the Samples you have access to viewing.<br><br>If enabled, you can select a sample by clicking on the checkbox to the right.<br><br>For more information about the Samples, you can click on the expand button to the right of this info box.<br><br>If viewing this table on the selection page, these are all the samples you have selected.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('4', 'run_name', 'Give a specific name for this particular run.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('5', 'description', 'Give a brief description of this particular run.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('6', 'genomebuild', 'Select the specific genome build you wish to use.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('7', 'spaired', 'Select yes if your samples are mate-paired.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('8', 'resume', 'Is this a Run a new run, or a continuation of a previous run.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('9', 'outdir', 'Please specify the full path for your backup directory.<br><br>/share/data/umw_biocore/genome_data/mousetest/mm10/barcodetest');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('10', 'fastqc', 'Would you like to perform FastQC?');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('11', 'barcodes', 'barcodes');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('12', 'adapter', 'If you would like to perform adapter removal, Expand this box and select the yes checkbox.<br><br>Removes 3` Adapter Sequences. You can enter a single sequence or multiple sequences in different lines. Reverse sequences will not be removed.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('13', 'custom', 'If you would like to define your own custom sequence sets, Expand this box and click the \`Add a Custom Sequence Set\` button.<br><br>You may add more than one custom sequence set.<br><br>In order to map your reads to a custom sequence, you first must create an index file and that fasta must be in the same folder.<br><br>The index definition must include the full path and prefix.<br><br>Example Bowtie parameters:<br>-build /fasta/path prefix');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('14', 'pipeline', 'If you would like to run additional pipelines, Expand this box and select a pipeline in which you would like to run.<br><br>To perform DESEQ, you must also run an RSEM pipeline.<br><br>You may only run 1 RSEM pipeline.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('15', 'split', 'If you would like to split your fastq files, Expand this box and select the yes checkbox.<br><br>You can specify the number of reads per file below.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('16', 'quality', 'If you would like to perform quality filtering, Expand this box and select the yes checkbox.<br><br>This performs a variety of useful trimming tasks for illumina paired-end and single ended data.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('17', 'trim', 'If you would like to perform trimming, Expand this box and select the yes checkbox.<br><br>This trims both the 3` and 5` ends.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('18', 'commonind', 'If you would like to map your reads sequentially to common RNAs, Expand this box and select the RNAs you would like to map.<br><br>Bowtie2 map your reads sequentially to common RNAs below. Mapped reads will be removed to go to the next step. To change the default parameters, please use `change parameters` box.<br><br>You can give Bowtie specific parameters by selecting the `Change Parameters` button.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('19', 'barcode_sep', 'Select yes if you wish to perform barcode separation');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('20', 'Barcode Definitions', 'Please enter for each file:<br><br>File_name barcode<br><br>Example barcodes:<br><br>control_rep1 CCGAGT<br>control_rep2 GATTTG<br>control_rep3 TTAGAC<br>exper_rep1 AACTCG<br>exper_rep2 CTGGGC<br>exper_rep3 GGTCTA');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('21', 'series_name', 'Please enter the experiment series name.<br>If adding to an already existing experiment series, please enter the exact name.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('22', 'lane_name', 'Import a specific name for your group of samples.<br><br>This is specific name can be either a lane name or something completely customizable.<br><br>If adding to an already existing Import name, please enter the exact name.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('23', 'input_dir', 'For input directory please use the full path in the cluster;<br>Ex:<br><br>/share/data/umw_biocore/genome_data/mousetest/mm10/barcodetest');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('24', 'input_files', 'No Barcode separation:<br><br>For \"Input Parameters\" please enter 2 columns for single end or 3 columns for paired end libraries.<br>First column represents the name of the libraries.<br>Please use only letters, numbers and _ character.<br> Second column is the file name of the library.<br>If you enter a full path in the \"input directory section\".<br>You can use this path in your library definitions (Ex: your_library.fastq).<br> Each file will be added with the path you entered in \"Input Directory\" section.<br><br>Barcode separation:<br><br>For \"Input Parameters\" please enter the file names 1 column for single and 2 columns for paired end lanes.<br>This path in your library definitions (Ex: your_library.fastq).<br>Each file will be added with the path you entered in \"Input Directory\" section.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('25', 'backup_dir', 'Please specify the full path for your backup directory.<br><br>/share/data/umw_biocore/genome_data/mousetest/mm10/barcodetest');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('26', 'amazon_bucket', 'Please specify your Amazon bucket.<br><br>If you would like to use our amazon options, please contact an admin at biocore@umassmed.edu with your security keys to set up this service.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('27', 'runparams', 'This table displays all of your runs, their current status, and options you have available for each run.<br><br>If your run is not queued, you may click on the status button to see advanced information about the run`s steps.<br><br>The options button will display your current options you have for this run.  Rerun allows you to re-run this run but you will be able to alter parameters.  Resume will re-run this run with no change in parameters.  Delete will delete this run from your status page.<br><br>If the run is complete, you may look at the runs results by selecting reports, or you can plot the runs results using the plots option.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('28', 'services', 'This table shows all of the services performed within the run.<br><br>To break down a service even further into it`s seperate steps, click on the `Select Service` button on the right.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('29', 'jobs', 'This table shows all of the jobs within a specific service.<br><br>To see the standard output of the job, click on the `Select Job` button on the right.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('30', 'initial_mapping', 'This table shows the initial mapping summary.<br><br>For each sample selected this table will show the total number of reads within the sample, and if common RNA mapping was selected, it will also show the reads that mapped to each common RNA.<br><br>The selection box under the table also lets you view addional information about the common RNA mapping.<br><br>Once you have selected the results to view, a button will appear where you can download the specific file, clear the selected table, or view a link showing the data in a specific format.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('31', 'summary', 'To view FastQC summary results, expand this box and select the results you wish to view.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('32', 'details', 'To view detailed FastQC summary results, expand this box and select the results you wish to view.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('33', 'RSEM', 'To view RSEM results, Expand this box and select the results file you wish to view.<br><br>Once you have selected the results to view, a button will appear where you can download the specific file, clear the selected table, or view a link showing the data in a specific format.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('34', 'DESEQ', 'To view DESEQ results, Expand this box and select the results file you wish to view.<br><br>Once you have selected the results to view, a button will appear where you can download the specific file, clear the selected table, or view a link showing the data in a specific format.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('35', 'excel_import', 'Select an excel file to upload specified data from within the cluster. <br>An example excel sheet is provided which you can download.<br>The excel sheet selected must be similar to the example provided.<br>Then select the group to submit under as well as who will be able to see the data.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('36', 'plot_control_panel', 'This panel displays all the information you can manipulate in order to show various plots.<br><br>First, select a file source.  If you have an external file you wish to view, select the `Input TSV file location` and insert the files location in the box that appears.<br><br>The X and Y axis manipulate the X and Y axis on the scatterplot while the pseudo count and the color axis maniuplates how the scatterplot data is being displayed.<br><br>By selecting columns, you manipulate the heatmaps data display as well as the barplot.<br><br>You can query specific genes by searching for genes (comma separated) and hitting the submit button.<br><br>Selected genes on the scatter plot will be displayed within the `Selected Region` box.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('37', 'bar_format', 'Select the location of the barcodes for barcode separation.');
INSERT INTO `biocore`.`ngs_help` (`id`, `field_name`, `help_text`) VALUES ('38', 'bar_distance', 'Select the Hamming distance for barcode separation.');

ALTER TABLE `biocore`.`ngs_experiment_series`
ADD COLUMN `organization_id` INT(11) NULL DEFAULT NULL AFTER `design`,
ADD COLUMN `lab_id` INT NULL DEFAULT NULL AFTER `organization_id`,
ADD COLUMN `grant` VARCHAR(255) NULL DEFAULT NULL AFTER `lab_id`;

ALTER TABLE `biocore`.`ngs_protocols`
ADD COLUMN `crosslinking_method` VARCHAR(255) NULL DEFAULT NULL AFTER `library_construction`,
ADD COLUMN `fragmentation_method` VARCHAR(255) NULL DEFAULT NULL AFTER `crosslinking_method`,
ADD COLUMN `strand_specific` VARCHAR(255) NULL DEFAULT NULL AFTER `fragmentation_method`;

ALTER TABLE `biocore`.`ngs_lanes`
ADD COLUMN `sequencing_id` VARCHAR(255) NULL DEFAULT NULL AFTER `name`;

ALTER TABLE `biocore`.`ngs_samples`
ADD COLUMN `batch_id` VARCHAR(255) NULL DEFAULT NULL AFTER `title`,
ADD COLUMN `concentration` VARCHAR(255) NULL DEFAULT NULL AFTER `read_length`,
ADD COLUMN `time` VARCHAR(255) NULL DEFAULT NULL AFTER `concentration`,
ADD COLUMN `biological_replica` VARCHAR(255) NULL DEFAULT NULL AFTER `time`,
ADD COLUMN `technical_replica` VARCHAR(255) NULL DEFAULT NULL AFTER `biological_replica`,
ADD COLUMN `spike_ins` VARCHAR(255) NULL DEFAULT NULL AFTER `technical_replica`;

CREATE TABLE `biocore`.`ngs_sample_conds` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `sample_id` INT NULL DEFAULT NULL,
  `cond_id` INT NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

CREATE TABLE `biocore`.`ngs_conds` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `cond_symbol` VARCHAR(45) NULL DEFAULT NULL,
  `condition` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `biocore`.`ngs_samples` 
DROP COLUMN `condition`;

CREATE TABLE `biocore`.`ngs_source` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `source` VARCHAR(45) NULL DEFAULT NULL,
  `source_symbol` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `biocore`.`ngs_samples` 
DROP COLUMN `source`,
ADD COLUMN `source_id` INT NULL DEFAULT NULL AFTER `spike_ins`;

CREATE TABLE `biocore`.`ngs_organism` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `organism` VARCHAR(100) NULL DEFAULT NULL,
  `organism_symbol` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `biocore`.`ngs_samples` 
DROP COLUMN `organism`,
ADD COLUMN `organism_id` INT NULL DEFAULT NULL AFTER `source_id`;

CREATE TABLE `biocore`.`ngs_genotype` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `genotype` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `biocore`.`ngs_samples` 
DROP COLUMN `genotype`,
ADD COLUMN `genotype_id` INT NULL DEFAULT NULL AFTER `organism_id`;

CREATE TABLE `biocore`.`ngs_molecule` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `molecule` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `biocore`.`ngs_samples` 
DROP COLUMN `molecule`,
ADD COLUMN `molecule_id` INT NULL DEFAULT NULL AFTER `genotype_id`;

CREATE TABLE `biocore`.`ngs_library_type` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `library_type` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `biocore`.`ngs_samples` 
DROP COLUMN `library_type`,
ADD COLUMN `library_type_id` INT NULL DEFAULT NULL AFTER `molecule_id`;

CREATE TABLE `biocore`.`ngs_donor` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `donor` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `biocore`.`ngs_samples` 
ADD COLUMN `donor_id` INT NULL DEFAULT NULL AFTER `library_type_id`;

CREATE TABLE `biocore`.`ngs_biosample_type` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `biosample_type` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `biocore`.`ngs_samples` 
ADD COLUMN `biosample_type_id` INT NULL DEFAULT NULL AFTER `donor_id`;

CREATE TABLE `biocore`.`ngs_instrument_model` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `instrument_model` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `biocore`.`ngs_samples` 
DROP COLUMN `instrument_model`,
ADD COLUMN `instrument_model_id` INT NULL DEFAULT NULL AFTER `biosample_type_id`;

CREATE TABLE `biocore`.`ngs_treatment_manufacturer` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `treatment_manufacturer` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `biocore`.`ngs_samples` 
ADD COLUMN `treatment_manufacturer_id` INT NULL DEFAULT NULL AFTER `instrument_model_id`;

CREATE TABLE `biocore`.`ngs_library_strategy` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `library_strategy` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `biocore`.`ngs_protocols` 
DROP COLUMN `library_strategy`,
ADD COLUMN `library_strategy_id` INT NULL DEFAULT NULL AFTER `strand_specific`;

CREATE TABLE `biocore`.`ngs_organization` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `organization` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

CREATE TABLE `biocore`.`ngs_lab` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `lab` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

CREATE TABLE `biocore`.`ngs_facility` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `facility` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `biocore`.`ngs_lanes` 
DROP COLUMN `facility`,
ADD COLUMN `facility_id` INT NULL DEFAULT NULL AFTER `lane_id`;

ALTER TABLE `biocore`.`ngs_source` 
CHANGE COLUMN `source` `source` VARCHAR(255) NULL DEFAULT NULL ;

ALTER TABLE `biocore`.`ngs_samples` 
ADD COLUMN `samplename` VARCHAR(255) NULL DEFAULT NULL AFTER `name`;

ALTER TABLE `biocore`.`ngs_samples` 
ADD COLUMN `target_id` INT NULL DEFAULT NULL AFTER `notebook_ref`;

UPDATE `biocore`.`sidebar` SET `link`='stat/status' WHERE `id`='12';

ALTER TABLE `biocore`.`ngs_runparams` 
ADD COLUMN `wrapper_pid` INT NULL DEFAULT NULL AFTER `run_status`,
ADD COLUMN `runworkflow_pid` INT NULL DEFAULT NULL AFTER `wrapper_pid`;
