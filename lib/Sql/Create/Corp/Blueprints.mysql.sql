-- Sql/Create/Corp/Blueprints.sql
-- version 20161129113301.051
CREATE TABLE "{schema}"."{tablePrefix}corpBlueprints" LIKE "{schema}"."{tablePrefix}charBlueprints";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.051');
COMMIT;
