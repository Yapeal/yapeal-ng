-- Sql/Create/Map/Sovereignty.sql
-- version 20161129113301.077
CREATE TABLE "{schema}"."{tablePrefix}mapSovereignty" (
    "allianceID"      BIGINT(20) UNSIGNED NOT NULL,
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "factionID"       BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemID"   BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemName" CHAR(100)           NOT NULL,
    PRIMARY KEY ("solarSystemID")
);
ALTER TABLE "{schema}"."{tablePrefix}mapSovereignty"
    ADD INDEX "mapSovereignty1" ("corporationID", "solarSystemID");
ALTER TABLE "{schema}"."{tablePrefix}mapSovereignty"
    ADD INDEX "mapSovereignty2" ("allianceID", "corporationID");
ALTER TABLE "{schema}"."{tablePrefix}mapSovereignty"
    ADD INDEX "mapSovereignty3" ("allianceID", "solarSystemID", "corporationID");
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.077');
COMMIT;
