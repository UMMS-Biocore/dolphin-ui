UPDATE ngs_runparams SET owner_id = 2 where id = 4;
UPDATE ngs_runlist SET owner_id = 2 where id IN (19,20);

DELETE FROM amazon_credentials WHERE id = 1;
