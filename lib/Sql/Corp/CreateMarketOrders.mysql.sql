-- Sql/Corp/CreateMarketOrders.sql
-- version 20160629053437.987
CREATE TABLE "{schema}"."{table_prefix}corpMarketOrders" LIKE "{schema}"."{table_prefix}charMarketOrders";
START TRANSACTION;
INSERT INTO "{schema}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053437.987')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
