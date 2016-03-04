-- Sql/Char/CreateBlueprints.sql
-- version 20160201053351.590
CREATE TABLE "{database}"."{table_prefix}charBlueprints" (
    "flagID"             BIGINT(20) UNSIGNED NOT NULL,
    "itemID"             BIGINT(20) UNSIGNED NOT NULL,
    "locationID"         BIGINT(20) UNSIGNED NOT NULL,
    "materialEfficiency" TINYINT(3) UNSIGNED NOT NULL,
    "ownerID"            BIGINT(20) UNSIGNED NOT NULL,
    "quantity"           BIGINT(20)          NOT NULL,
    "runs"               BIGINT(20)          NOT NULL,
    "timeEfficiency"     TINYINT(3) UNSIGNED NOT NULL,
    "typeID"             BIGINT(20) UNSIGNED NOT NULL,
    "typeName"           CHAR(100)           NOT NULL,
    PRIMARY KEY ("ownerID", "itemID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160201053351.590')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
