-- Sql/Create/Corp/AssetList.mysql.sql
-- version 20161202044339.025
CREATE TABLE "{schema}"."{tablePrefix}corpAssetList" LIKE "{schema}"."{tablePrefix}charAssetList";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.025');
COMMIT;
