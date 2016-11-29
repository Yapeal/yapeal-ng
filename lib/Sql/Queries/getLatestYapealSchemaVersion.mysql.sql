-- Sql/Queries/getLatestYapealSchemaVersion.mysql.sql
-- version 20161129113301.021
-- @formatter:off
SELECT MAX("version") FROM "{schema}"."{tablePrefix}yapealSchemaVersion";
