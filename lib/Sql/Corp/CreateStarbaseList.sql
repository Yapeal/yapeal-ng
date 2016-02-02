-- Sql/Corp/CreateStarbaseList.sql
-- version 20160201053951.128
CREATE TABLE "{database}"."{table_prefix}corpStarbaseList" (
    "itemID" BIGINT(20) UNSIGNED NOT NULL,
    "locationID" BIGINT(20) UNSIGNED NOT NULL,
    "moonID" BIGINT(20) UNSIGNED NOT NULL,
    "onlineTimestamp" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "standingOwnerID" BIGINT(20) UNSIGNED NOT NULL,
    "state" VARCHAR(255) DEFAULT '',
    "stateTimestamp" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "typeID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","itemID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160201053951.128')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
