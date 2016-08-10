-- Sql/queries/getDeleteFromTableWithOwnerID.mysql.sql
-- version 20160810095203.424
-- noinspection SqlResolveForFile
-- @formatter:off
DELETE FROM "{schema}"."{tablePrefix}%1$s" WHERE "ownerID"='%2$s';
