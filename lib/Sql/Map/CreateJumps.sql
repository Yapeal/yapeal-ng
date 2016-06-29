-- Sql/Map/CreateJumps.sql
-- version 20160629053424.141
CREATE TABLE "{database}"."{table_prefix}mapJumps" (
    "shipJumps" VARCHAR(255) DEFAULT '',
    "solarSystemID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("solarSystemID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629053424.141')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
