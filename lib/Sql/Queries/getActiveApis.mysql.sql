-- Sql/Queries/getActiveApis.mysql.sql
-- version 20161129113301.003
-- @formatter:off
SELECT "apiName", "interval", "sectionName"
    FROM "{schema}"."{tablePrefix}yapealEveApi"
    WHERE "active" = 1
    ORDER BY RAND();
