-- Sql/Map/CreateSovereignty.sql
-- version 20160629053447.001
CREATE TABLE "{schema}"."{tablePrefix}mapSovereignty" (
    "allianceID"      BIGINT(20) UNSIGNED NOT NULL,
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "factionID"       BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemID"   BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemName" CHAR(100)           NOT NULL,
    PRIMARY KEY ("solarSystemID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
    VALUES ('20160629053447.001')
    ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
