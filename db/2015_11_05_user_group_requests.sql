CREATE TABLE `biocore`.`user_group_requests` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_request` INT(11) NULL DEFAULT NULL,
  `user_check` INT(11) NULL DEFAULT NULL,
  `group_name` VARCHAR(100) NULL DEFAULT NULL,
  `group_owner` INT(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`));
