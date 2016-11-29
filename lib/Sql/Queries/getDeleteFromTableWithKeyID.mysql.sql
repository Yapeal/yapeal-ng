-- Sql/Queries/getDeleteFromTableWithKeyID.mysql.sql
-- version 20161129113301.017
-- @formatter:off
DELETE FROM "{schema}"."{tablePrefix}%1$s" WHERE "keyID"='%2$s';
