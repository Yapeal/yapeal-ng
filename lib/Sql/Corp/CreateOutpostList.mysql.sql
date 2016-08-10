-- Sql/Corp/CreateOutpostList.sql
-- version 20160629053441.369
CREATE TABLE "{schema}"."{table_prefix}corpOutpostList" (
    "dockingCostPerShipVolume" DECIMAL(17, 2)           NOT NULL,
    "officeRentalCost"         DECIMAL(17, 2)           NOT NULL,
    "ownerID"                  BIGINT(20) UNSIGNED      NOT NULL,
    "reprocessingEfficiency"   DECIMAL(17, 16) UNSIGNED NOT NULL,
    "reprocessingStationTake"  DECIMAL(17, 16) UNSIGNED NOT NULL,
    "solarSystemID"            BIGINT(20) UNSIGNED      NOT NULL,
    "standingOwnerID"          BIGINT(20) UNSIGNED      NOT NULL,
    "stationID"                BIGINT(20) UNSIGNED      NOT NULL,
    "stationName"              CHAR(100)                NOT NULL,
    "stationTypeID"            BIGINT(20) UNSIGNED      NOT NULL,
    "x"                        BIGINT(20)               NOT NULL,
    "y"                        BIGINT(20)               NOT NULL,
    "z"                        BIGINT(20)               NOT NULL,
    PRIMARY KEY ("ownerID", "stationID")
);
START TRANSACTION;
INSERT INTO "{schema}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053441.369')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
