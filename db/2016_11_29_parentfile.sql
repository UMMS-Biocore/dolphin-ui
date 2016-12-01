ALTER TABLE `ngs_file_submissions` 
ADD COLUMN `parent_file` INT NULL DEFAULT NULL AFTER `file_type`;
