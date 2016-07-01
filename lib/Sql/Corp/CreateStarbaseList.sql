-- Sql/Corp/CreateStarbaseList.sql
-- version 20160629053459.024
CREATE TABLE "{database}"."{table_prefix}corpStarbaseList" (
    "itemID"          BIGINT(20) UNSIGNED NOT NULL,
    "locationID"      BIGINT(20) UNSIGNED NOT NULL,
    "moonID"          BIGINT(20) UNSIGNED NOT NULL,
    "onlineTimestamp" DATETIME            NOT NULL,
    "ownerID"         BIGINT(20) UNSIGNED NOT NULL,
    "standingOwnerID" BIGINT(20) UNSIGNED NOT NULL,
    "state"           TINYINT(2) UNSIGNED NOT NULL,
    "stateTimestamp"  DATETIME            NOT NULL,
    "typeID"          BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "itemID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053459.024')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
