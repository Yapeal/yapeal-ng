-- Sql/queries/getApiLock.mysql.sql
-- version 20160810070126.703
-- @formatter:off
SELECT GET_LOCK('%1$s', 5);
