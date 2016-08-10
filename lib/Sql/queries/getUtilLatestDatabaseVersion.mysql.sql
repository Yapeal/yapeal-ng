-- Sql/queries/getUtilLatestDatabaseVersion.mysql.sql
-- version 20160810092200.911
-- noinspection SqlResolveForFile
-- @formatter:off
SELECT MAX("version") FROM "{schema}"."{tablePrefix}utilDatabaseVersion";
