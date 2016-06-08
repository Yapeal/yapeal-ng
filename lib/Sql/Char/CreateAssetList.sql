-- Sql/Char/CreateAssetList.sql
-- version 20160608221648.885
CREATE TABLE "{database}"."{table_prefix}charAssetList" (
    "flag"        SMALLINT(5) UNSIGNED NOT NULL,
    "itemID"      BIGINT(20) UNSIGNED  NOT NULL,
    "lft"         BIGINT(20) UNSIGNED  NOT NULL,
    "locationID"  BIGINT(20) UNSIGNED  NOT NULL,
    "lvl"         TINYINT(2) UNSIGNED  NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED  NOT NULL,
    "quantity"    BIGINT(20) UNSIGNED  NOT NULL,
    "rawQuantity" BIGINT(20) DEFAULT NULL,
    "rgt"         BIGINT(20) UNSIGNED  NOT NULL,
    "singleton"   TINYINT(1)           NOT NULL,
    "typeID"      BIGINT(20) UNSIGNED  NOT NULL,
    PRIMARY KEY ("ownerID", "itemID")
);
ALTER TABLE "{database}"."{table_prefix}charAssetList" ADD INDEX "charAssetList1"  ("lft");
ALTER TABLE "{database}"."{table_prefix}charAssetList" ADD INDEX "charAssetList2"  ("locationID");
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160608221648.885')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
