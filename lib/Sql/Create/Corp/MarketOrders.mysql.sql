-- Sql/Create/Corp/MarketOrders.sql
-- version 20161202044339.034
CREATE TABLE "{schema}"."{tablePrefix}corpMarketOrders" LIKE "{schema}"."{tablePrefix}charMarketOrders";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.034');
COMMIT;
