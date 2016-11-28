-- Sql/queries/getSortedEveApis.mysql.sql
-- version 20160809230319.496
-- @formatter:off
SELECT "apiName", "sectionName"
    FROM "{schema}"."{tablePrefix}utilEveApi"
    ORDER BY "sectionName" ASC, "apiName" ASC;
