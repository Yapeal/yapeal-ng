-- Sql/queries/getActiveMailBodiesWithOwnerID.mysql.sql
-- version 20160810061739.421
-- noinspection SqlResolveForFile
-- @formatter:off
SELECT "messageID"
 FROM "{schema}"."{tablePrefix}charMailMessages" AS cmm
 WHERE "ownerID" = %1$s;

