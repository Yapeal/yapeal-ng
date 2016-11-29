-- Sql/Queries/getApiLock.mysql.sql
-- version 20161129051429.899
-- @formatter:off
SELECT GET_LOCK('%1$s', 5);
