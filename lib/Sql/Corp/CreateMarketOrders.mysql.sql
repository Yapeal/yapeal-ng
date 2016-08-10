-- Sql/Corp/CreateMarketOrders.sql
-- version 20160629053437.987
CREATE TABLE "{schema}"."{tablePrefix}corpMarketOrders" LIKE "{schema}"."{tablePrefix}charMarketOrders";
START TRANSACTION;
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
VALUES ('20160629053437.987')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
