-- Sql/Char/CreateIndustryJobsHistory.sql
-- version 20160629053422.388
CREATE TABLE "{database}"."{table_prefix}charIndustryJobsHistory" LIKE "{database}"."{table_prefix}charIndustryJobs";
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053422.388')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
