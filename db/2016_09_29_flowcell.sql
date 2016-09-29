CREATE TABLE `ngs_flowcell` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `machine_name` VARCHAR(45) NULL DEFAULT NULL,
  `flowcell` VARCHAR(45) NULL DEFAULT NULL,
  `lane` INT NULL DEFAULT NULL,
  PRIMARY KEY (`id`));

ALTER TABLE `ngs_samples` 
ADD COLUMN `flowcell_id` INT NULL DEFAULT NULL AFTER `treatment_manufacturer_id`;
