-- Sql/Map/CreateJumps.sql
-- version 20160629053424.141
CREATE TABLE "{schema}"."{tablePrefix}mapJumps" (
    "shipJumps"     BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("solarSystemID")
);
START TRANSACTION;
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
VALUES ('20160629053424.141')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;