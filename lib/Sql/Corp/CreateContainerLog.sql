-- Sql/Corp/CreateContainerLog.sql
-- version 20160629053417.748
CREATE TABLE "{database}"."{table_prefix}corpContainerLog" (
    "action" VARCHAR(255) DEFAULT '',
    "actorID" BIGINT(20) UNSIGNED NOT NULL,
    "actorName" CHAR(100) NOT NULL,
    "flag" VARCHAR(255) DEFAULT '',
    "itemID" BIGINT(20) UNSIGNED NOT NULL,
    "itemTypeID" BIGINT(20) UNSIGNED NOT NULL,
    "locationID" BIGINT(20) UNSIGNED NOT NULL,
    "logTime" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "newConfiguration" VARCHAR(255) DEFAULT '',
    "oldConfiguration" VARCHAR(255) DEFAULT '',
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "passwordType" VARCHAR(255) DEFAULT '',
    "quantity" VARCHAR(255) DEFAULT '',
    "typeID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","logTime")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629053417.748')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
