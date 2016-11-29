-- Sql/Corp/CreateIndustryJobs.sql
-- version 20160629053421.844
CREATE TABLE "{schema}"."{tablePrefix}corpIndustryJobs" LIKE "{schema}"."{tablePrefix}charIndustryJobs";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20160629053421.844');
COMMIT;
