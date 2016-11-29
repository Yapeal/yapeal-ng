-- Sql/Create/Corp/OutpostList.sql
-- version 20161129113301.063
CREATE TABLE "{schema}"."{tablePrefix}corpOutpostList" (
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
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.063');
COMMIT;
