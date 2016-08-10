-- Sql/Corp/CreateFacilities.sql
-- version 20160629053420.041
CREATE TABLE "{schema}"."{table_prefix}corpFacilities" (
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
INSERT INTO "{schema}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053420.041')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
