-- Sql/Queries/getDeleteFromTableWithOwnerID.mysql.sql
-- version 20161129052158.039
-- @formatter:off
DELETE FROM "{schema}"."{tablePrefix}%1$s" WHERE "ownerID"='%2$s';
