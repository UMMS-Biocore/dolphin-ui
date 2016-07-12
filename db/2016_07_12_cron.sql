ALTER TABLE `ngs_fastq_files` 
ADD COLUMN `cron_update_date` DATETIME NULL DEFAULT NULL AFTER `last_modified_user`;
