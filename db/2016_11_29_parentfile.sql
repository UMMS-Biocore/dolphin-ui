ALTER TABLE `ngs_file_submissions` 
ADD COLUMN `parent_file` INT NULL DEFAULT NULL AFTER `file_type`,
ADD COLUMN `step_run` VARCHAR(100) NULL DEFAULT NULL AFTER `parent_file`,
ADD COLUMN `additional_derived_from` VARCHAR(100) NULL DEFAULT NULL AFTER `step_run`;

