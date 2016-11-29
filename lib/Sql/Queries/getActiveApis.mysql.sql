-- Sql/queries/getActiveApis.mysql.sql
-- version 20160809230319.496
-- @formatter:off
SELECT "apiName", "interval", "sectionName"
    FROM "{schema}"."{tablePrefix}utilEveApi"
    WHERE "active" = 1
    ORDER BY RAND();
