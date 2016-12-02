-- Sql/Queries/getActiveRegisteredKeys.mysql.sql
-- version 20161202044339.066
-- @formatter:off
SELECT "keyID", "vCode"
 FROM "{schema}"."{tablePrefix}yapealRegisteredKey"
 WHERE "active" = 1;
