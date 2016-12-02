-- Sql/Queries/getActiveMailBodiesWithOwnerID.mysql.sql
-- version 20161202044339.062
-- @formatter:off
SELECT "messageID"
 FROM "{schema}"."{tablePrefix}charMailMessages" AS cmm
 WHERE "ownerID" = %1$s;

