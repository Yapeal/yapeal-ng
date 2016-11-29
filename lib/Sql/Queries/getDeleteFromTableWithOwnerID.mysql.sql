-- Sql/Queries/getDeleteFromTableWithOwnerID.mysql.sql
-- version 20161129113301.018
-- @formatter:off
DELETE FROM "{schema}"."{tablePrefix}%1$s" WHERE "ownerID"='%2$s';
