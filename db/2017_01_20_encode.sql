CREATE TABLE `ngs_assay_term` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `assay_term_name` VARCHAR(255) NULL DEFAULT NULL,
  `assay_term_id` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

CREATE TABLE `ngs_nucleic_acid_term` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nucleic_acid_term_name` VARCHAR(45) NULL DEFAULT NULL,
  `nucleic_acid_term_id` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

CREATE TABLE `ngs_starting_amount` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `starting_amount` VARCHAR(64) NULL DEFAULT NULL,
  `starting_amount_units` VARCHAR(45) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));


ALTER TABLE `ngs_protocols` 
ADD COLUMN `assay_term_id` INT NULL DEFAULT NULL AFTER `library_strategy_id`,
ADD COLUMN `nucleic_acid_term_id` INT NULL DEFAULT NULL AFTER `assay_term_id`,
ADD COLUMN `starting_amount_id` INT NULL DEFAULT NULL AFTER `nucleic_acid_term_id`;

ALTER TABLE `ngs_samples` 
ADD COLUMN `biosample_derived_from` VARCHAR(128) NULL DEFAULT NULL AFTER `biosample_uuid`;
