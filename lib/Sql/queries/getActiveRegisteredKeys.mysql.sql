-- Sql/queries/getActiveRegisteredKeys.mysql.sql
-- version 20160810072026.424
-- noinspection SqlResolveForFile
-- @formatter:off
SELECT "keyID", "vCode"
 FROM "{schema}"."{tablePrefix}utilRegisteredKey"
 WHERE "active" = 1;
