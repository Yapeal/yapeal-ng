-- Sql/Corp/CreateMarketOrders.sql
-- version 20160629053437.987
CREATE TABLE "{schema}"."{tablePrefix}corpMarketOrders" LIKE "{schema}"."{tablePrefix}charMarketOrders";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20160629053437.987');
COMMIT;
