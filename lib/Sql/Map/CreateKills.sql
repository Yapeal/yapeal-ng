-- Sql/Map/CreateKills.sql
-- version 20160629053430.383
CREATE TABLE "{database}"."{table_prefix}mapKills" (
    "factionKills" VARCHAR(255) DEFAULT '',
    "podKills" VARCHAR(255) DEFAULT '',
    "shipKills" VARCHAR(255) DEFAULT '',
    "solarSystemID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("solarSystemID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629053430.383')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
