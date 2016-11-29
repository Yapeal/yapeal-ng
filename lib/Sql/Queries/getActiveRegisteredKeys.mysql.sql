-- Sql/Queries/getActiveRegisteredKeys.mysql.sql
-- version 20161129113301.008
-- @formatter:off
SELECT "keyID", "vCode"
 FROM "{schema}"."{tablePrefix}yapealRegisteredKey"
 WHERE "active" = 1;
