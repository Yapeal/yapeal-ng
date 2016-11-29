-- Sql/Queries/getApiLock.mysql.sql
-- version 20161129113301.010
-- @formatter:off
SELECT GET_LOCK('%1$s', 5);
