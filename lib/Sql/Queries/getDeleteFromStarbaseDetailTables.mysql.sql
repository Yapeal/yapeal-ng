-- Sql/Queries/getDeleteFromStarbaseDetailTables.mysql.sql
-- version 20161129052023.581
-- @formatter:off
DELETE FROM "{schema}"."{tablePrefix}%1$s"
    WHERE "ownerID"='%2$s'
    AND "itemID"='%3$s';
