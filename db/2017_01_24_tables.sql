CREATE TABLE `ngs_biosample_acc` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `biosample_acc` VARCHAR(64) NULL,
  PRIMARY KEY (`id`));

CREATE TABLE `ngs_experiment_acc` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `experiment_acc` VARCHAR(64) NULL,
  PRIMARY KEY (`id`));


/*

INSERT INTO ngs_experiment_acc( ngs_experiment_acc.experiment_acc ) 
SELECT DISTINCT experiment_acc
FROM ngs_samples
WHERE experiment_acc != ''

UPDATE ngs_samples,
ngs_experiment_acc SET ngs_samples.experiment_acc = ngs_experiment_acc.id WHERE ngs_samples.experiment_acc = ngs_experiment_acc.experiment_acc

INSERT INTO ngs_biosample_acc( ngs_biosample_acc.biosample_acc ) 
SELECT DISTINCT biosample_acc
FROM ngs_samples
WHERE biosample_acc != ''

UPDATE ngs_samples,
ngs_biosample_acc SET ngs_samples.biosample_acc = ngs_biosample_acc.id WHERE ngs_samples.biosample_acc = ngs_biosample_acc.biosample_acc

*/