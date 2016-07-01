-- Sql/Corp/CreateMarketOrders.sql
-- version 20160629053437.987
CREATE TABLE "{database}"."{table_prefix}corpMarketOrders" LIKE "{database}"."{table_prefix}charMarketOrders";
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053437.987')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
