-- Sql/Create/Corp/ContainerLog.sql
-- version 20161129113301.053
CREATE TABLE "{schema}"."{tablePrefix}corpContainerLog" (
    "action"           CHAR(255)                    DEFAULT '',
    "actorID"          BIGINT(20) UNSIGNED NOT NULL,
    "actorName"        CHAR(100)           NOT NULL,
    "flag"             CHAR(25)                    DEFAULT '',
    "itemID"           BIGINT(20) UNSIGNED NOT NULL,
    "itemTypeID"       BIGINT(20) UNSIGNED NOT NULL,
    "locationID"       BIGINT(20) UNSIGNED NOT NULL,
    "logTime"          DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "newConfiguration" CHAR(255)                    DEFAULT '',
    "oldConfiguration" CHAR(255)                    DEFAULT '',
    "ownerID"          BIGINT(20) UNSIGNED NOT NULL,
    "passwordType"     CHAR(25)                     DEFAULT '',
    "quantity"         BIGINT(20) UNSIGNED NOT NULL,
    "typeID"           BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "logTime")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.053');
COMMIT;
