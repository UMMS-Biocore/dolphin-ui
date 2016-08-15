ALTER TABLE `users` 
ADD COLUMN `photo_loc` VARCHAR(255) NOT NULL DEFAULT '/public/img/avatar5.png' AFTER `lab`;

ALTER TABLE `ngs_experiment_series`
ADD COLUMN `organization_id` INT(11) NULL DEFAULT NULL AFTER `design`,
ADD COLUMN `lab_id` INT NULL DEFAULT NULL AFTER `organization_id`,
ADD COLUMN `grant` VARCHAR(255) NULL DEFAULT NULL AFTER `lab_id`;

ALTER TABLE `ngs_protocols`
ADD COLUMN `crosslinking_method` VARCHAR(255) NULL DEFAULT NULL AFTER `library_construction`,
ADD COLUMN `fragmentation_method` VARCHAR(255) NULL DEFAULT NULL AFTER `crosslinking_method`,
ADD COLUMN `strand_specific` VARCHAR(255) NULL DEFAULT NULL AFTER `fragmentation_method`;

ALTER TABLE `ngs_lanes`
ADD COLUMN `sequencing_id` VARCHAR(255) NULL DEFAULT NULL AFTER `name`;

ALTER TABLE `ngs_samples`
ADD COLUMN `batch_id` VARCHAR(255) NULL DEFAULT NULL AFTER `title`,
ADD COLUMN `concentration` VARCHAR(255) NULL DEFAULT NULL AFTER `read_length`,
ADD COLUMN `time` VARCHAR(255) NULL DEFAULT NULL AFTER `concentration`,
ADD COLUMN `biological_replica` VARCHAR(255) NULL DEFAULT NULL AFTER `time`,
ADD COLUMN `technical_replica` VARCHAR(255) NULL DEFAULT NULL AFTER `biological_replica`,
ADD COLUMN `spike_ins` VARCHAR(255) NULL DEFAULT NULL AFTER `technical_replica`;

DROP TABLE IF EXISTS `ngs_sample_conds`;
CREATE TABLE IF NOT EXISTS `ngs_sample_conds` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `sample_id` INT NULL DEFAULT NULL,
  `cond_id` INT NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

DROP TABLE IF EXISTS `ngs_conds`;
CREATE TABLE IF NOT EXISTS `ngs_conds` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `cond_symbol` VARCHAR(45) NULL DEFAULT NULL,
  `condition` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `ngs_samples` 
DROP COLUMN `condition`;

DROP TABLE IF EXISTS `ngs_source`;
CREATE TABLE IF NOT EXISTS `ngs_source` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `source` VARCHAR(45) NULL DEFAULT NULL,
  `source_symbol` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `ngs_samples` 
DROP COLUMN `source`,
ADD COLUMN `source_id` INT NULL DEFAULT NULL AFTER `spike_ins`;

DROP TABLE IF EXISTS `ngs_organism`;
CREATE TABLE IF NOT EXISTS `ngs_organism` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `organism` VARCHAR(100) NULL DEFAULT NULL,
  `organism_symbol` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `ngs_samples` 
DROP COLUMN `organism`,
ADD COLUMN `organism_id` INT NULL DEFAULT NULL AFTER `source_id`;

DROP TABLE IF EXISTS `ngs_genotype`;
CREATE TABLE IF NOT EXISTS `ngs_genotype` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `genotype` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `ngs_samples` 
DROP COLUMN `genotype`,
ADD COLUMN `genotype_id` INT NULL DEFAULT NULL AFTER `organism_id`;

DROP TABLE IF EXISTS `ngs_molecule`;
CREATE TABLE IF NOT EXISTS `ngs_molecule` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `molecule` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `ngs_samples` 
DROP COLUMN `molecule`,
ADD COLUMN `molecule_id` INT NULL DEFAULT NULL AFTER `genotype_id`;

DROP TABLE IF EXISTS `ngs_library_type`;
CREATE TABLE IF NOT EXISTS `ngs_library_type` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `library_type` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `ngs_samples` 
DROP COLUMN `library_type`,
ADD COLUMN `library_type_id` INT NULL DEFAULT NULL AFTER `molecule_id`;

DROP TABLE IF EXISTS `ngs_donor`;
CREATE TABLE IF NOT EXISTS `ngs_donor` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `donor` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `ngs_samples` 
ADD COLUMN `donor_id` INT NULL DEFAULT NULL AFTER `library_type_id`;

DROP TABLE IF EXISTS `ngs_biosample_type`;
CREATE TABLE IF NOT EXISTS `ngs_biosample_type` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `biosample_type` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `ngs_samples` 
ADD COLUMN `biosample_type_id` INT NULL DEFAULT NULL AFTER `donor_id`;

DROP TABLE IF EXISTS `ngs_instrument_model`;
CREATE TABLE IF NOT EXISTS `ngs_instrument_model` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `instrument_model` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `ngs_samples` 
DROP COLUMN `instrument_model`,
ADD COLUMN `instrument_model_id` INT NULL DEFAULT NULL AFTER `biosample_type_id`;

DROP TABLE IF EXISTS `ngs_treatment_manufacturer`;
CREATE TABLE IF NOT EXISTS `ngs_treatment_manufacturer` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `treatment_manufacturer` VARCHAR(255) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `ngs_samples` 
ADD COLUMN `treatment_manufacturer_id` INT NULL DEFAULT NULL AFTER `instrument_model_id`;

DROP TABLE IF EXISTS `ngs_library_strategy`;
CREATE TABLE IF NOT EXISTS `ngs_library_strategy` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `library_strategy` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `ngs_protocols` 
DROP COLUMN `library_strategy`,
ADD COLUMN `library_strategy_id` INT NULL DEFAULT NULL AFTER `strand_specific`;

DROP TABLE IF EXISTS `ngs_organization`;
CREATE TABLE IF NOT EXISTS `ngs_organization` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `organization` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

DROP TABLE IF EXISTS `ngs_lab`;
CREATE TABLE IF NOT EXISTS `ngs_lab` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `lab` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

DROP TABLE IF EXISTS `ngs_facility`;
CREATE TABLE IF NOT EXISTS `ngs_facility` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `facility` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `ngs_lanes` 
DROP COLUMN `facility`,
ADD COLUMN `facility_id` INT NULL DEFAULT NULL AFTER `lane_id`;

ALTER TABLE `ngs_source` 
CHANGE COLUMN `source` `source` VARCHAR(255) NULL DEFAULT NULL ;

ALTER TABLE `ngs_samples` 
ADD COLUMN `samplename` VARCHAR(255) NULL DEFAULT NULL AFTER `name`;

ALTER TABLE `ngs_samples` 
ADD COLUMN `target_id` INT NULL DEFAULT NULL AFTER `notebook_ref`;

UPDATE `sidebar` SET `link`='stat/status' WHERE `id`='12';

ALTER TABLE `ngs_runparams` 
ADD COLUMN `wrapper_pid` INT NULL DEFAULT NULL AFTER `run_status`,
ADD COLUMN `runworkflow_pid` INT NULL DEFAULT NULL AFTER `wrapper_pid`;

DROP TABLE IF EXISTS `ngs_wkeylist`;
CREATE TABLE IF NOT EXISTS `ngs_wkeylist` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `run_id` INT NULL DEFAULT NULL,
  `wkey` VARCHAR(30) NULL DEFAULT NULL,
  `wrapper_pid` INT NULL DEFAULT NULL,
  `workflow_pid` INT NULL DEFAULT NULL,
  `time_added` INT NULL DEFAULT NULL,  
  PRIMARY KEY (`id`));

INSERT INTO `sidebar`
	(`id`, `name`, `parent_name`, `link`, `iconname`, `owner_id`, `group_id`, `perms`, `date_created`, `date_modified`, `last_modified_user`)
	VALUES
	('14', 'Tutorial', '', 'readthedocs','fa-mortar-board', '1', '1', '63', now(), now(), '1');

DROP TABLE IF EXISTS `ngs_createdtables`;
CREATE TABLE IF NOT EXISTS `ngs_createdtables` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NULL DEFAULT NULL,
  `parameters` VARCHAR(255) NULL DEFAULT NULL,
  `file` VARCHAR(100) NULL DEFAULT NULL,
  `owner_id` INT(11) NULL DEFAULT NULL,
  `group_id` INT(11) NULL DEFAULT NULL,
  `perms` INT(11) NULL DEFAULT NULL,
  `date_created` DATETIME NULL DEFAULT NULL,
  `date_modified` DATETIME NULL DEFAULT NULL,
  `last_modified_user` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

INSERT INTO `sidebar`
	(`name`, `parent_name`, `link`, `treeview`, `owner_id`, `group_id`, `perms`, `date_created`, `date_modified`, `last_modified_user`)
	VALUES
	('Generated Tables', 'NGS Tracking', 'tablecreator/tablereports', '0', '1', '1', '63', NOW(), NOW(), '1');

DROP TABLE IF EXISTS `user_group_requests`;
CREATE TABLE IF NOT EXISTS `user_group_requests` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_request` INT(11) NULL DEFAULT NULL,
  `user_check` INT(11) NULL DEFAULT NULL,
  `group_id` VARCHAR(100) NULL DEFAULT NULL,
  `group_owner` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `users` 
ADD COLUMN `pass_hash` VARCHAR(100) NULL DEFAULT NULL AFTER `lab`,
ADD COLUMN `verification` VARCHAR(45) NULL DEFAULT NULL AFTER `pass_hash`;

DROP TABLE IF EXISTS `ngs_file_submissions`;
CREATE TABLE IF NOT EXISTS `ngs_file_submissions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `dir_id` INT NULL DEFAULT NULL,
  `run_id` INT NULL DEFAULT NULL,
  `sample_id` INT NULL DEFAULT NULL,
  `file_name` VARCHAR(100) NULL DEFAULT NULL,
  `file_type` VARCHAR(45) NULL DEFAULT NULL,
  `file_md5` VARCHAR(100) NULL DEFAULT NULL,
  `file_uuid` VARCHAR(100) NULL DEFAULT NULL,
  `file_acc` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `jobs` 
CHANGE COLUMN `wkey` `wkey` VARCHAR(41) NOT NULL ;

DROP TABLE IF EXISTS `ngs_treament`;
CREATE TABLE IF NOT EXISTS `ngs_treatment` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NULL DEFAULT NULL,
  `treatment_term_name` VARCHAR(100) NULL DEFAULT NULL,
  `treatment_term_id` VARCHAR(100) NULL DEFAULT NULL,
  `treatment_type` VARCHAR(100) NULL DEFAULT NULL,
  `concentration` VARCHAR(45) NULL DEFAULT NULL,
  `concentration_units` VARCHAR(45) NULL DEFAULT NULL,
  `duration` VARCHAR(45) NULL DEFAULT NULL,
  `duration_units` VARCHAR(45) NULL DEFAULT NULL,
  `uuid` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

DROP TABLE IF EXISTS `ngs_antibody_target`;
CREATE TABLE IF NOT EXISTS `ngs_antibody_target` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `target` VARCHAR(100) NULL DEFAULT NULL,
  `target_symbol` VARCHAR(45) NULL DEFAULT NULL,
  `source` VARCHAR(45) NULL DEFAULT NULL,
  `product_id` VARCHAR(45) NULL DEFAULT NULL,
  `lot_id` VARCHAR(45) NULL DEFAULT NULL,
  `host_organism` VARCHAR(45) NULL DEFAULT NULL,
  `clonality` VARCHAR(45) NULL DEFAULT NULL,
  `isotype` VARCHAR(45) NULL DEFAULT NULL,
  `purifications` VARCHAR(45) NULL DEFAULT NULL,
  `url` VARCHAR(255) NULL DEFAULT NULL,
  `uuid` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));
  
DROP TABLE IF EXISTS `ngs_biosample_term`;
CREATE TABLE IF NOT EXISTS `ngs_biosample_term` (
`id` INT NOT NULL,
`biosample_term_name` VARCHAR(100) NULL DEFAULT NULL,
`biosample_term_id` VARCHAR(100) NULL DEFAULT NULL,
`biosample_type` VARCHAR(100) NULL DEFAULT NULL,
PRIMARY KEY (`id`));

ALTER TABLE `ngs_donor` 
ADD COLUMN `donor_acc` VARCHAR(45) NULL DEFAULT NULL AFTER `donor`,
ADD COLUMN `donor_uuid` VARCHAR(100) NULL DEFAULT NULL AFTER `donor_acc`;

ALTER TABLE `ngs_samples` 
ADD COLUMN `biosample_acc` VARCHAR(45) NULL DEFAULT NULL AFTER `notes`,
ADD COLUMN `biosample_uuid` VARCHAR(100) NULL DEFAULT NULL AFTER `biosample_acc`,
ADD COLUMN `library_acc` VARCHAR(45) NULL DEFAULT NULL AFTER `biosample_uuid`,
ADD COLUMN `library_uuid` VARCHAR(100) NULL DEFAULT NULL AFTER `library_acc`,
ADD COLUMN `experiment_acc` VARCHAR(45) NULL DEFAULT NULL AFTER `library_uuid `,
ADD COLUMN `experiment_uuid` VARCHAR(100) NULL DEFAULT NULL AFTER `experiment_acc`,
ADD COLUMN `replicate_uuid` VARCHAR(100) NULL DEFAULT NULL AFTER `experiment_uuid`,
ADD COLUMN `treatment_id` INT NULL DEFAULT NULL AFTER `replicate_uuid`,
ADD COLUMN `antibody_lot_id` INT NULL DEFAULT NULL AFTER `treatment_id`,
ADD COLUMN `biosample_id` INT NULL DEFAULT NULL AFTER `antibody_lot_id`;

ALTER TABLE `jobs` ADD `resources` TEXT NOT NULL AFTER `jobstatus` ;

ALTER TABLE `users` 
ADD COLUMN `email_toggle` INT NULL DEFAULT 0 AFTER `email`;

DROP TABLE IF EXISTS `ngs_delete_runs`;
CREATE TABLE IF NOT EXISTS `ngs_deleted_runs` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `run_id` INT NULL DEFAULT NULL,
  `outdir` VARCHAR(100) NULL DEFAULT NULL,
  `run_status` INT NULL DEFAULT NULL,
  `json_parameters` VARCHAR(1000) NULL DEFAULT NULL,
  `run_name` VARCHAR(100) NULL DEFAULT NULL,
  `run_description` VARCHAR(255) NULL DEFAULT NULL,
  `owner_id` INT NULL DEFAULT NULL,
  `group_id` INT NULL DEFAULT NULL,
  `perms` INT NULL DEFAULT NULL,
  `last_modified_user` INT NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

INSERT INTO datafields
	(table_id, fieldname, title, type, len, owner_id, group_id, date_created, date_modified, last_modified_user)
	VALUES
	(7, 'donor', 'Donor', 'text', 128, 1, 1, NOW(), NOW(), 1),
	(7, 'target', 'Antibody Target', 'text', 128, 1, 1, NOW(), NOW(), 1),
	(7, 'time', 'Time', 'text', 128, 1, 1, NOW(), NOW(), 1),
	(7, 'biological_replica', 'Biological Rep', 'text', 128, 1, 1, NOW(), NOW(), 1),
	(7, 'technical_replica', 'Technical Rep', 'text', 128, 1, 1, NOW(), NOW(), 1);

UPDATE `datafields` SET `fieldname`='samplename' WHERE `id`='44';

INSERT INTO `sidebar`
	(`id`, `name`, `parent_name`, `link`, `owner_id`, `group_id`, `perms`, `date_created`, `date_modified`, `last_modified_user`)
	VALUES
	('15', 'DEBrowser', 'NGS Tracking', 'debrowser/index/index', '1', '1', '63', NOW(), NOW(), '1');

DROP TABLE IF EXISTS `ngs_genome`;
CREATE TABLE IF NOT EXISTS `ngs_genome` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `genome` VARCHAR(100) NULL DEFAULT NULL,
  `build` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

INSERT INTO `ngs_genome` (`id`, `genome`, `build`) VALUES ('1', 'human', 'hg19');
INSERT INTO `ngs_genome` (`id`, `genome`, `build`) VALUES ('2', 'mouse', 'mm10');
INSERT INTO `ngs_genome` (`id`, `genome`, `build`) VALUES ('3', 'hamster', 'cho-k1');
INSERT INTO `ngs_genome` (`id`, `genome`, `build`) VALUES ('4', 'rat', 'rn5');
INSERT INTO `ngs_genome` (`id`, `genome`, `build`) VALUES ('5', 'zebrafish', 'danRer7');
INSERT INTO `ngs_genome` (`id`, `genome`, `build`) VALUES ('6', 'zebrafish', 'danRer10');
INSERT INTO `ngs_genome` (`id`, `genome`, `build`) VALUES ('7', 's_cerevisiae', 'sacCer3');
INSERT INTO `ngs_genome` (`id`, `genome`, `build`) VALUES ('8', 'c_elegans', 'ce10');
INSERT INTO `ngs_genome` (`id`, `genome`, `build`) VALUES ('9', 'cow', 'bosTau7');
INSERT INTO `ngs_genome` (`id`, `genome`, `build`) VALUES ('10', 'd_melanogaster', 'dm3');
INSERT INTO `ngs_genome` (`id`, `genome`, `build`) VALUES ('11', 'mousetest', 'mm10');
INSERT INTO `ngs_genome` (`id`, `genome`, `build`) VALUES ('12', 's_pombe', 'ASM294v2');

DROP TABLE IF EXISTS `ngs_deleted_samples`;
CREATE TABLE IF NOT EXISTS `ngs_deleted_samples` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `sample_id` INT NULL DEFAULT NULL,
  `samplename` VARCHAR(100) NULL DEFAULT NULL,
  `lane_id` INT NULL DEFAULT NULL,
  `experiment_series_id` INT NULL DEFAULT NULL,
  `user_delete` INT NULL DEFAULT NULL,
  `date` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`));
  
ALTER TABLE `ngs_fastq_files` 
ADD COLUMN `aws_status` INT NULL DEFAULT NULL AFTER `backup_checksum`;

ALTER TABLE `ngs_fastq_files` 
ADD COLUMN `checksum_recheck` VARCHAR(255) NULL DEFAULT NULL AFTER `backup_checksum`,
ADD COLUMN `original_checksum` VARCHAR(255) NULL DEFAULT NULL AFTER `backup_checksum`;

ALTER TABLE `amazon_credentials` 
CHANGE COLUMN `aws_access_key_id` `aws_access_key_id` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `aws_secret_access_key` `aws_secret_access_key` VARCHAR(255) NULL DEFAULT NULL ;

