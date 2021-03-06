-- Sql/Create/Map/FacWarSystems.sql
-- version 20161202044339.049
CREATE TABLE "{schema}"."{tablePrefix}mapFacWarSystems" (
    "contested"             ENUM ('False', 'True') NOT NULL,
    "occupyingFactionID"    BIGINT(20) UNSIGNED    NOT NULL,
    "occupyingFactionName"  CHAR(100)              NOT NULL,
    "owningFactionID"       BIGINT(20) UNSIGNED    NOT NULL,
    "owningFactionName"     CHAR(100)              NOT NULL,
    "solarSystemID"         BIGINT(20) UNSIGNED    NOT NULL,
    "solarSystemName"       CHAR(100)              NOT NULL,
    "victoryPoints"         BIGINT(20) UNSIGNED    NOT NULL,
    "victoryPointThreshold" BIGINT(20) UNSIGNED    NOT NULL,
    PRIMARY KEY ("solarSystemID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.049');
COMMIT;
