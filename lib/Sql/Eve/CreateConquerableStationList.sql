-- Sql/Eve/CreateConquerableStationList.sql
-- version 20160201053354.200
CREATE TABLE "{database}"."{table_prefix}eveConquerableStationList" (
    "corporationID" BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(100) NOT NULL,
    "solarSystemID" BIGINT(20) UNSIGNED NOT NULL,
    "stationID" BIGINT(20) UNSIGNED NOT NULL,
    "stationName" CHAR(100) NOT NULL,
    "stationTypeID" BIGINT(20) UNSIGNED NOT NULL,
    "x" VARCHAR(255) DEFAULT '',
    "y" VARCHAR(255) DEFAULT '',
    "z" VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("stationID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160201053354.200')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
