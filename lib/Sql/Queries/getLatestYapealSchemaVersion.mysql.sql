-- Sql/Queries/getLatestYapealSchemaVersion.mysql.sql
-- version 20161129044019.672
-- @formatter:off
SELECT MAX("version") FROM "{schema}"."{tablePrefix}yapealSchemaVersion";
