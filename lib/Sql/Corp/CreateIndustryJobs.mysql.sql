-- Sql/Corp/CreateIndustryJobs.sql
-- version 20160629053421.844
CREATE TABLE "{schema}"."{tablePrefix}corpIndustryJobs" LIKE "{schema}"."{tablePrefix}charIndustryJobs";
START TRANSACTION;
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
VALUES ('20160629053421.844')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
