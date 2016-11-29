-- Sql/Queries/getDeleteFromTableWithKeyID.mysql.sql
-- version 20161129052131.167
-- @formatter:off
DELETE FROM "{schema}"."{tablePrefix}%1$s" WHERE "keyID"='%2$s';
