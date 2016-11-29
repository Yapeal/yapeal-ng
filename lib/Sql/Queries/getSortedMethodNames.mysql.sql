-- Sql/Queries/getSortedMethodNames.mysql.sql
-- version 20161129113301.025
-- @formatter:off
SELECT "apiName", "sectionName"
    FROM "{schema}"."{tablePrefix}yapealEveApi"
    ORDER BY "sectionName" ASC, "apiName" ASC;
