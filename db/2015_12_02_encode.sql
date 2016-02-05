CREATE TABLE `biocore`.`ngs_file_submissions` (
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

CREATE TABLE `biocore`.`ngs_treatment` (
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

CREATE TABLE `biocore`.`ngs_antibody_target` (
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
  
CREATE TABLE `biocore`.`ngs_biosample_term` (
`id` INT NOT NULL,
`biosample_term_name` VARCHAR(100) NULL DEFAULT NULL,
`biosample_term_id` VARCHAR(100) NULL DEFAULT NULL,
`biosample_type` VARCHAR(100) NULL DEFAULT NULL,
PRIMARY KEY (`id`));

ALTER TABLE `biocore`.`ngs_donor` 
ADD COLUMN `donor_acc` VARCHAR(45) NULL DEFAULT NULL AFTER `donor`,
ADD COLUMN `donor_uuid` VARCHAR(100) NULL DEFAULT NULL AFTER `donot_acc`;

ALTER TABLE `biocore`.`ngs_samples` 
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
