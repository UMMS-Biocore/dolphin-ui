ALTER TABLE `biocore`.`ngs_fastq_files` 
ADD COLUMN `current_checksum` VARCHAR(255) NULL DEFAULT NULL AFTER `backup_checksum`;
