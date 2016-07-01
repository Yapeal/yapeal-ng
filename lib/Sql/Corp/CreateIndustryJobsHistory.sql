-- Sql/Corp/CreateIndustryJobsHistory.sql
-- version 20160629053422.896
CREATE TABLE "{database}"."{table_prefix}corpIndustryJobsHistory" LIKE "{database}"."{table_prefix}charIndustryJobs";
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053422.896')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
