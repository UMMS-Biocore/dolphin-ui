CREATE TABLE `biocore`.`encode_batch_submissions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `samples` VARCHAR(700) NULL DEFAULT NULL,
  `output_file` VARCHAR(100) NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `samples_UNIQUE` (`samples` ASC));

ALTER TABLE  `encode_submissions` CHANGE  `status_file`  `batch_submission` INT( 100 ) NULL DEFAULT NULL ;
