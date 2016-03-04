-- Sql/Corp/CreateCustomsOffices.sql
-- version 20160201053356.475
CREATE TABLE "{database}"."{table_prefix}corpCustomsOffices" (
    "allowAlliance"           TINYINT(1) UNSIGNED      NOT NULL,
    "allowStandings"          TINYINT(1) UNSIGNED      NOT NULL,
    "itemID"                  BIGINT(20) UNSIGNED      NOT NULL,
    "ownerID"                 BIGINT(20) UNSIGNED      NOT NULL,
    "reinforceHour"           TINYINT(2) UNSIGNED      NOT NULL,
    "solarSystemID"           BIGINT(20) UNSIGNED      NOT NULL,
    "solarSystemName"         CHAR(100)                NOT NULL,
    "standingLevel"           DECIMAL(5, 2)            NOT NULL,
    "taxRateAlliance"         DECIMAL(17, 16) UNSIGNED NOT NULL,
    "taxRateCorp"             DECIMAL(17, 16) UNSIGNED NOT NULL,
    "taxRateStandingBad"      DECIMAL(17, 16) UNSIGNED NOT NULL,
    "taxRateStandingGood"     DECIMAL(17, 16) UNSIGNED NOT NULL,
    "taxRateStandingHigh"     DECIMAL(17, 16) UNSIGNED NOT NULL,
    "taxRateStandingHorrible" DECIMAL(17, 16) UNSIGNED NOT NULL,
    "taxRateStandingNeutral"  DECIMAL(17, 16) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "itemID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160201053356.475')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
