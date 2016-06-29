-- Sql/Map/CreateSovereignty.sql
-- version 20160629053447.001
CREATE TABLE "{database}"."{table_prefix}mapSovereignty" (
    "allianceID" BIGINT(20) UNSIGNED NOT NULL,
    "corporationID" BIGINT(20) UNSIGNED NOT NULL,
    "factionID" BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemID" BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemName" CHAR(100) NOT NULL,
    PRIMARY KEY ("solarSystemID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629053447.001')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
