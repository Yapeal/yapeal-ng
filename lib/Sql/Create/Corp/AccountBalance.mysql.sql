-- Sql/Create/Corp/AccountBalance.sql
-- version 20161202044339.024
CREATE TABLE "{schema}"."{tablePrefix}corpAccountBalance" LIKE "{schema}"."{tablePrefix}charAccountBalance";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.024');
COMMIT;
