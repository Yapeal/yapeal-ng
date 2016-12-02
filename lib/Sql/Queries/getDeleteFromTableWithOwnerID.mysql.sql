-- Sql/Queries/getDeleteFromTableWithOwnerID.mysql.sql
-- version 20161202044339.074
-- @formatter:off
DELETE FROM "{schema}"."{tablePrefix}%1$s" WHERE "ownerID"='%2$s';
