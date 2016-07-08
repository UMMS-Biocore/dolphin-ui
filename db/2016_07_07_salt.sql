ALTER TABLE `biocore`.`amazon_credentials` 
CHANGE COLUMN `aws_access_key_id` `aws_access_key_id` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `aws_secret_access_key` `aws_secret_access_key` VARCHAR(255) NULL DEFAULT NULL ;
