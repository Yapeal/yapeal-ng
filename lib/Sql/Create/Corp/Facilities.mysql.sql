-- Sql/Create/Corp/Facilities.sql
-- version 20161129113301.057
CREATE TABLE "{schema}"."{tablePrefix}corpFacilities" (
    "facilityID"       BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"          BIGINT(20) UNSIGNED NOT NULL,
    "regionID"         BIGINT(20) UNSIGNED NOT NULL,
    "regionName"       CHAR(100)           NOT NULL,
    "solarSystemID"    BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemName"  CHAR(100)           NOT NULL,
    "starbaseModifier" CHAR(25) DEFAULT '',
    "tax"              DECIMAL(17, 2)      NOT NULL,
    "typeID"           BIGINT(20) UNSIGNED NOT NULL,
    "typeName"         CHAR(100)           NOT NULL,
    PRIMARY KEY ("ownerID", "facilityID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.057');
COMMIT;
