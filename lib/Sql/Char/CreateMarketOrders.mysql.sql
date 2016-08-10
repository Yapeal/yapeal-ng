-- Sql/Char/CreateMarketOrders.sql
-- version 20160629053437.490
CREATE TABLE "{schema}"."{table_prefix}charMarketOrders" (
    "accountKey"   SMALLINT(5) UNSIGNED    NOT NULL,
    "bid"          TINYINT(1) UNSIGNED     NOT NULL,
    "charID"       BIGINT(20) UNSIGNED     NOT NULL,
    "duration"     SMALLINT(3) UNSIGNED    NOT NULL,
    "escrow"       DECIMAL(17, 2) UNSIGNED NOT NULL,
    "issued"       DATETIME                NOT NULL,
    "minVolume"    BIGINT(20) UNSIGNED     NOT NULL,
    "orderID"      BIGINT(20) UNSIGNED     NOT NULL,
    "orderState"   TINYINT(2) UNSIGNED     NOT NULL,
    "ownerID"      BIGINT(20) UNSIGNED     NOT NULL,
    "price"        DECIMAL(17, 2) UNSIGNED NOT NULL,
    "range"        SMALLINT(5)             NOT NULL,
    "stationID"    BIGINT(20) UNSIGNED     NOT NULL,
    "typeID"       BIGINT(20) UNSIGNED     NOT NULL,
    "volEntered"   BIGINT(20) UNSIGNED     NOT NULL,
    "volRemaining" BIGINT(20) UNSIGNED     NOT NULL,
    PRIMARY KEY ("ownerID", "orderID")
);
START TRANSACTION;
INSERT INTO "{schema}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053437.490')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
