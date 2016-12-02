-- Sql/Create/Corp/Blueprints.sql
-- version 20161202044339.026
CREATE TABLE "{schema}"."{tablePrefix}corpBlueprints" LIKE "{schema}"."{tablePrefix}charBlueprints";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.026');
COMMIT;
