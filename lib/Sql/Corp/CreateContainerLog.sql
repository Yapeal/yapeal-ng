-- Sql/Corp/CreateContainerLog.sql
-- version 20160201053355.486
CREATE TABLE "{database}"."{table_prefix}corpContainerLog" (
    "action"           CHAR(24)             NOT NULL,
    "actorID"          BIGINT(20) UNSIGNED  NOT NULL,
    "actorName"        CHAR(100)            NOT NULL,
    "flag"             SMALLINT(5) UNSIGNED NOT NULL,
    "itemID"           BIGINT(20) UNSIGNED  NOT NULL,
    "itemTypeID"       BIGINT(20) UNSIGNED  NOT NULL,
    "locationID"       BIGINT(20) UNSIGNED  NOT NULL,
    "logTime"          DATETIME             NOT NULL,
    "newConfiguration" SMALLINT(4) UNSIGNED NOT NULL,
    "oldConfiguration" SMALLINT(4) UNSIGNED NOT NULL,
    "ownerID"          BIGINT(20) UNSIGNED  NOT NULL,
    "passwordType"     CHAR(12)             NOT NULL,
    "quantity"         BIGINT(20) UNSIGNED  NOT NULL,
    "typeID"           BIGINT(20) UNSIGNED  NOT NULL,
    PRIMARY KEY ("ownerID", "logTime")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160201053355.486')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
