-- Sql/Create/Char/MarketOrders.sql
-- version 20161202044339.015
CREATE TABLE "{schema}"."{tablePrefix}charMarketOrders" (
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
-- Used altered index name(s) since they get copied to corp table during create ... like ...
ALTER TABLE "{schema}"."{tablePrefix}charMarketOrders"
    ADD INDEX "MarketOrders1" ("ownerID", "stationID", "orderID");
ALTER TABLE "{schema}"."{tablePrefix}charMarketOrders"
    ADD INDEX "MarketOrders2" ("ownerID", "typeID", "orderID");
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.015');
COMMIT;
