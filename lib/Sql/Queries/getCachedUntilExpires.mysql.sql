-- Sql/Queries/getCachedUntilExpires.mysql.sql
-- version 20161202044339.070
-- @formatter:off
SELECT "expires"
 FROM "{schema}"."{tablePrefix}yapealCachedUntil"
 WHERE
 "accountKey" = %1$s
 AND "apiName" = '%2$s'
 AND "ownerID" = %3$s;
