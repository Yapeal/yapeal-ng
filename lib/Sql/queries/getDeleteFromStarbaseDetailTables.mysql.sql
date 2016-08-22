-- Sql/queries/getDeleteFromStarbaseDetailTables.mysql.sql
-- version 20160822094545.028
-- @formatter:off
DELETE FROM "{schema}"."{tablePrefix}%1$s"
    WHERE "ownerID"='%2$s'
    AND "itemID"='%3$s';
