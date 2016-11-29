-- Sql/Queries/getActiveMailBodiesWithOwnerID.mysql.sql
-- version 20161129051318.720
-- @formatter:off
SELECT "messageID"
 FROM "{schema}"."{tablePrefix}charMailMessages" AS cmm
 WHERE "ownerID" = %1$s;

