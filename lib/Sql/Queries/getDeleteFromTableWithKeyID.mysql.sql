-- Sql/Queries/getDeleteFromTableWithKeyID.mysql.sql
-- version 20161202044339.073
-- @formatter:off
DELETE FROM "{schema}"."{tablePrefix}%1$s" WHERE "keyID"='%2$s';
