-- Sql/Corp/CreateBlueprints.sql
-- version 20160629053412.885
CREATE TABLE "{schema}"."{tablePrefix}corpBlueprints" LIKE "{schema}"."{tablePrefix}charBlueprints";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20160629053412.885');
COMMIT;
