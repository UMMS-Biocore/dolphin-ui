CREATE TABLE `encode_submissions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `sample_id` INT NULL DEFAULT NULL,
  `sub_status` VARCHAR(100) NULL DEFAULT NULL,
  `output_file` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));
