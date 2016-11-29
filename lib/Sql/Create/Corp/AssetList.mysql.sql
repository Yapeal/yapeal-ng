-- Sql/Create/Corp/AssetList.mysql.sql
-- version 20161129113301.050
CREATE TABLE "{schema}"."{tablePrefix}corpAssetList" LIKE "{schema}"."{tablePrefix}charAssetList";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.050');
COMMIT;
