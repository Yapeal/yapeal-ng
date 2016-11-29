-- Sql/Queries/getActiveApis.mysql.sql
-- version 20161129035724.146
-- @formatter:off
SELECT "apiName", "interval", "sectionName"
    FROM "{schema}"."{tablePrefix}yapealEveApi"
    WHERE "active" = 1
    ORDER BY RAND();
