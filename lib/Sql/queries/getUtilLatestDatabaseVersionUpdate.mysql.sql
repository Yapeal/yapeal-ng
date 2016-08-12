-- Sql/queries/getUtilLatestDatabaseVersionUpdate.mysql.sql
-- version 20160812011133.550
-- noinspection SqlResolveForFile
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
 VALUES (?)
 ON DUPLICATE KEY UPDATE "version" = VALUES("version");
