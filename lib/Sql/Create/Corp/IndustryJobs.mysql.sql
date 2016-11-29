-- Sql/Create/Corp/IndustryJobs.sql
-- version 20161129113301.058
CREATE TABLE "{schema}"."{tablePrefix}corpIndustryJobs" LIKE "{schema}"."{tablePrefix}charIndustryJobs";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.058');
COMMIT;
