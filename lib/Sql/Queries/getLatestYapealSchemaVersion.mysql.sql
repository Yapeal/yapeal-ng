-- Sql/Queries/getLatestYapealSchemaVersion.mysql.sql
-- version 20161202044339.076
-- @formatter:off
SELECT MAX("version") FROM "{schema}"."{tablePrefix}yapealSchemaVersion";
