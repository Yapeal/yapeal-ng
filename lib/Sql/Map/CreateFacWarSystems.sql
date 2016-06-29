-- Sql/Map/CreateFacWarSystems.sql
-- version 20160629053420.773
CREATE TABLE "{database}"."{table_prefix}mapFacWarSystems" (
    "contested" VARCHAR(255) DEFAULT '',
    "occupyingFactionID" BIGINT(20) UNSIGNED NOT NULL,
    "occupyingFactionName" CHAR(100) NOT NULL,
    "owningFactionID" BIGINT(20) UNSIGNED NOT NULL,
    "owningFactionName" CHAR(100) NOT NULL,
    "solarSystemID" BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemName" CHAR(100) NOT NULL,
    "victoryPoints" VARCHAR(255) DEFAULT '',
    "victoryPointThreshold" VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("solarSystemID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629053420.773')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
