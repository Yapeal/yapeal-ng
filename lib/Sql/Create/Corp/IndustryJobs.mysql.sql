-- Sql/Create/Corp/IndustryJobs.sql
-- version 20161202044339.033
CREATE TABLE "{schema}"."{tablePrefix}corpIndustryJobs" LIKE "{schema}"."{tablePrefix}charIndustryJobs";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.033');
COMMIT;
