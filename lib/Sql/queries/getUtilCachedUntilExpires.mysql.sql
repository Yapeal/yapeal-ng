-- Sql/queries/getUtilCachedUntilExpires.mysql.sql
-- version 20160820103727.377
-- noinspection SqlResolveForFile
-- @formatter:off
SELECT "expires"
 FROM "{schema}"."{tablePrefix}utilCachedUntil"
 WHERE
 "accountKey" = %1$s
 AND "apiName" = '%2$s'
 AND "ownerID" = %3$s;
