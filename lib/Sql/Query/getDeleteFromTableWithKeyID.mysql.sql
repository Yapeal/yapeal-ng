-- Sql/queries/getDeleteFromTableWithKeyID.mysql.sql
-- version 20160810094904.367
-- @formatter:off
DELETE FROM "{schema}"."{tablePrefix}%1$s" WHERE "keyID"='%2$s';
