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
ADD COLUMN `experiment_acc` VARCHAR(45) NULL DEFAULT NULL AFTER `library_uuid`,
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
	(`name`, `parent_name`, `link`, `owner_id`, `group_id`, `perms`, `date_created`, `date_modified`, `last_modified_user`)
	VALUES
	('DEBrowser', 'NGS Tracking', 'debrowser/index/index', '1', '1', '63', NOW(), NOW(), '1');

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

ALTER TABLE `ngs_fastq_files` 
ADD COLUMN `cron_update_date` DATETIME NULL DEFAULT NULL AFTER `last_modified_user`;

ALTER TABLE `ngs_donor` 
ADD COLUMN `life_stage` VARCHAR(45) NULL DEFAULT NULL AFTER `donor`,
ADD COLUMN `age` VARCHAR(45) NULL DEFAULT NULL AFTER `life_stage`,
ADD COLUMN `sex` VARCHAR(45) NULL DEFAULT NULL AFTER `age`;

ALTER TABLE `ngs_fastq_files` 
ADD COLUMN `file_uuid` VARCHAR(100) NULL DEFAULT NULL AFTER `aws_status`,
ADD COLUMN `file_acc` VARCHAR(100) NULL DEFAULT NULL AFTER `file_uuid`;

CREATE TABLE `encode_submissions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `sample_id` INT NULL DEFAULT NULL,
  `batch_submission` INT( 100 ) NULL DEFAULT NULL,
  `sub_status` VARCHAR(100) NULL DEFAULT NULL,
  `output_file` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

CREATE TABLE `encode_batch_submissions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `samples` VARCHAR(700) NULL DEFAULT NULL,
  `output_file` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `samples_UNIQUE` (`samples` ASC));

CREATE TABLE `ngs_flowcell` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `machine_name` VARCHAR(45) NULL DEFAULT NULL,
  `flowcell` VARCHAR(45) NULL DEFAULT NULL,
  `lane` INT NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `ngs_samples` 
ADD COLUMN `flowcell_id` INT NULL DEFAULT NULL AFTER `treatment_manufacturer_id`;

INSERT INTO `biocore`.`sidebar` (`name`, `parent_name`, `link`, `treeview`, `owner_id`, `group_id`, `perms`, `date_created`, `date_modified`, `last_modified_user`)
VALUES ('GEO Import', 'NGS Tracking', 'geoimport', '0', '1', '1', '63', '2016-10-21 16:46:04', '2016-10-21 16:46:04', '1');

-- MySQL dump 10.13  Distrib 5.5.44, for Linux (x86_64)
--
-- Host: localhost    Database: biocore
-- ------------------------------------------------------
-- Server version	5.5.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `predjob`
--

DROP TABLE IF EXISTS `predjob`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `predjob` (
  `row_names` text,
  `set` text,
  `field2` text,
  `a` double DEFAULT NULL,
  `x` double DEFAULT NULL,
  `now.str` text
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `predjob`
--

LOCK TABLES `predjob` WRITE;
/*!40000 ALTER TABLE `predjob` DISABLE KEYS */;
INSERT INTO `predjob` VALUES ('1','stepCheck','cputime',63.9946395440911,0.000000697613874886345,'2015-09-02 20:08:06'),('2','stepCheck','maxmemory',6.1908391918608,0.000000439003254594237,'2015-09-02 20:08:06'),('3','stepGetTotalReads','cputime',1045.64121580468,-0.00000210534960583967,'2015-09-02 20:08:06'),('4','stepGetTotalReads','maxmemory',9.97553449697074,0.00000018276274853818,'2015-09-02 20:08:06'),('5','stepFastQC','cputime',300.40886879394,0.0000384698869363854,'2015-09-02 20:08:06'),('6','stepFastQC','maxmemory',205.320192131142,0.00000221944670857104,'2015-09-02 20:08:06'),('7','stepSeqMappingrRNA','cputime',1197.12892773477,0.0000580063872079514,'2015-09-02 20:08:06'),('8','stepSeqMappingrRNA','maxmemory',739.311890029135,-0.00000347936758353607,'2015-09-02 20:08:06'),('9','stepSeqMappingmiRNA','cputime',1038.59859277086,0.000042690281225727,'2015-09-02 20:08:06'),('10','stepSeqMappingmiRNA','maxmemory',68.112699885953,-0.000000732753273838315,'2015-09-02 20:08:06'),('11','stepSeqMappingtRNA','cputime',1061.24140639219,0.0000413001053401492,'2015-09-02 20:08:06'),('12','stepSeqMappingtRNA','maxmemory',57.6346720909156,-0.000000348116144952529,'2015-09-02 20:08:06'),('13','stepSeqMappingsnRNA','cputime',1134.4391605665,0.0000382680381574728,'2015-09-02 20:08:06'),('14','stepSeqMappingsnRNA','maxmemory',56.2799796414185,-0.000000516515162525346,'2015-09-02 20:08:06'),('15','stepRSEM','cputime',12804.3740173764,0.00133344053382503,'2015-09-02 20:08:06'),('16','stepRSEM','maxmemory',1507.43628699634,-0.0000107933981399146,'2015-09-02 20:08:06'),('17','stepTophat2','cputime',38323.0199810108,0.0120328947013738,'2015-09-02 20:08:06'),('18','stepTophat2','maxmemory',3630.36181851955,0.000212851660824238,'2015-09-02 20:08:06'),('19','stepIGVTDFTophat','cputime',1170.86867543962,0.0000598990742649521,'2015-09-02 20:08:06'),('20','stepIGVTDFTophat','maxmemory',1071.84308315155,0.00000190093826498431,'2015-09-02 20:08:06'),('21','stepPicardTophat','cputime',308.973788787798,0.0000156412290776886,'2015-09-02 20:08:06'),('22','stepPicardTophat','maxmemory',11499.9022095066,-0.000138742870660554,'2015-09-02 20:08:06'),('23','stepQuality','cputime',7369.17595633097,-0.000267230119308174,'2015-09-02 20:08:06'),('24','stepQuality','maxmemory',15674.0279689077,-0.000626749681307221,'2015-09-02 20:08:06'),('25','stepSeqMappingercc','cputime',7872.18046355769,-0.000301110801444426,'2015-09-02 20:08:06'),('26','stepSeqMappingercc','maxmemory',180.434710785403,-0.00000687753949580064,'2015-09-02 20:08:06'),('27','stepSeqMappingrmsk','cputime',10504.1543629282,-0.000428612330921066,'2015-09-02 20:08:06'),('28','stepSeqMappingrmsk','maxmemory',1352.69742645469,-0.0000558949449406096,'2015-09-02 20:08:06');
/*!40000 ALTER TABLE `predjob` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-11-10 15:40:48
