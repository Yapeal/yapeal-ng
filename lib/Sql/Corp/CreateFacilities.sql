-- Sql/Corp/CreateFacilities.sql
-- version 20160201053357.147
CREATE TABLE "{database}"."{table_prefix}corpFacilities" (
    "facilityID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "regionID" BIGINT(20) UNSIGNED NOT NULL,
    "regionName" CHAR(100) NOT NULL,
    "solarSystemID" BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemName" CHAR(100) NOT NULL,
    "starbaseModifier" VARCHAR(255) DEFAULT '',
    "tax" DECIMAL(17, 2) NOT NULL,
    "typeID" BIGINT(20) UNSIGNED NOT NULL,
    "typeName" CHAR(100) NOT NULL,
    PRIMARY KEY ("ownerID","facilityID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160201053357.147')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
