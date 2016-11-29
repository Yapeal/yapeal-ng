-- Sql/queries/getSortedMethodNames.mysql.sql
-- version 20161128084414.715
-- @formatter:off
SELECT "apiName", "sectionName"
    FROM "{schema}"."{tablePrefix}utilEveApi"
    ORDER BY "sectionName" ASC, "apiName" ASC;
