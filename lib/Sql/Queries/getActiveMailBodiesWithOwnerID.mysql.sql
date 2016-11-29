-- Sql/Queries/getActiveMailBodiesWithOwnerID.mysql.sql
-- version 20161129113301.004
-- @formatter:off
SELECT "messageID"
 FROM "{schema}"."{tablePrefix}charMailMessages" AS cmm
 WHERE "ownerID" = %1$s;

