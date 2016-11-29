-- Sql/Queries/getActiveRegisteredKeys.mysql.sql
-- version 20161129040146.958
-- @formatter:off
SELECT "keyID", "vCode"
 FROM "{schema}"."{tablePrefix}yapealRegisteredKey"
 WHERE "active" = 1;
