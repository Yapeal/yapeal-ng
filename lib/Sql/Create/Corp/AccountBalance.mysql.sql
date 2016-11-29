-- Sql/Corp/CreateAccountBalance.sql
-- version 20160629053411.529
CREATE TABLE "{schema}"."{tablePrefix}corpAccountBalance" LIKE "{schema}"."{tablePrefix}charAccountBalance";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20160629053411.529');
COMMIT;
