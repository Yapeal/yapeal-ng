-- Sql/Corp/CreateIndustryJobs.sql
-- version 20160629053421.844
CREATE TABLE "{database}"."{table_prefix}corpIndustryJobs" LIKE "{database}"."{table_prefix}charIndustryJobs";
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053421.844')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
