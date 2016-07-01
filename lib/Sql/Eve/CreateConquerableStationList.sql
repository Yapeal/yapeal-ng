-- Sql/Eve/CreateConquerableStationList.sql
-- version 20160629053415.526
CREATE TABLE "{database}"."{table_prefix}eveConquerableStationList" (
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(100)           NOT NULL,
    "solarSystemID"   BIGINT(20) UNSIGNED NOT NULL,
    "stationID"       BIGINT(20) UNSIGNED NOT NULL,
    "stationName"     CHAR(100)           NOT NULL,
    "stationTypeID"   BIGINT(20) UNSIGNED NOT NULL,
    "x"               BIGINT(20)          NOT NULL,
    "y"               BIGINT(20)          NOT NULL,
    "z"               BIGINT(20)          NOT NULL,
    PRIMARY KEY ("stationID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053415.526')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
