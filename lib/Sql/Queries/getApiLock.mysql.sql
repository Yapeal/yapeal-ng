-- Sql/Queries/getApiLock.mysql.sql
-- version 20161202044339.068
-- @formatter:off
SELECT GET_LOCK('%1$s', 5);
