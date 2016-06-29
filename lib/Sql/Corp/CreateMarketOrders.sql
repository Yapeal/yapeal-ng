-- Sql/Corp/CreateMarketOrders.sql
-- version 20160629053437.987
CREATE TABLE "{database}"."{table_prefix}corpMarketOrders" (
    "accountKey" VARCHAR(255) DEFAULT '',
    "bid" VARCHAR(255) DEFAULT '',
    "charID" BIGINT(20) UNSIGNED NOT NULL,
    "duration" VARCHAR(255) DEFAULT '',
    "escrow" VARCHAR(255) DEFAULT '',
    "issued" VARCHAR(255) DEFAULT '',
    "minVolume" VARCHAR(255) DEFAULT '',
    "orderID" BIGINT(20) UNSIGNED NOT NULL,
    "orderState" VARCHAR(255) DEFAULT '',
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "price" VARCHAR(255) DEFAULT '',
    "range" VARCHAR(255) DEFAULT '',
    "stationID" BIGINT(20) UNSIGNED NOT NULL,
    "typeID" BIGINT(20) UNSIGNED NOT NULL,
    "volEntered" VARCHAR(255) DEFAULT '',
    "volRemaining" VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID","orderID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629053437.987')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
