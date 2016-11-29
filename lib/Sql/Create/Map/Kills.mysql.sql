-- Sql/Map/CreateKills.sql
-- version 20160629053430.383
CREATE TABLE "{schema}"."{tablePrefix}mapKills" (
    "factionKills"  BIGINT(20) UNSIGNED NOT NULL,
    "podKills"      BIGINT(20) UNSIGNED NOT NULL,
    "shipKills"     BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("solarSystemID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20160629053430.383');
COMMIT;
