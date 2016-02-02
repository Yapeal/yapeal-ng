-- Sql/Corp/CreateOutpostList.sql
-- version 20160201053947.522
CREATE TABLE "{database}"."{table_prefix}corpOutpostList" (
    "dockingCostPerShipVolume" VARCHAR(255) DEFAULT '',
    "officeRentalCost" VARCHAR(255) DEFAULT '',
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "reprocessingEfficiency" VARCHAR(255) DEFAULT '',
    "reprocessingStationTake" VARCHAR(255) DEFAULT '',
    "solarSystemID" BIGINT(20) UNSIGNED NOT NULL,
    "standingOwnerID" BIGINT(20) UNSIGNED NOT NULL,
    "stationID" BIGINT(20) UNSIGNED NOT NULL,
    "stationName" CHAR(100) NOT NULL,
    "stationTypeID" BIGINT(20) UNSIGNED NOT NULL,
    "x" VARCHAR(255) DEFAULT '',
    "y" VARCHAR(255) DEFAULT '',
    "z" VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID","stationID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160201053947.522')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
