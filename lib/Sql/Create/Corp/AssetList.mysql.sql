-- Sql/Corp/CreateAssetList.mysql.sql
-- version 20160811034458.522
CREATE TABLE "{schema}"."{tablePrefix}corpAssetList" LIKE "{schema}"."{tablePrefix}charAssetList";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20160811034458.522');
COMMIT;
