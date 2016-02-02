-- Sql/Corp/CreateCustomsOffices.sql
-- version 20160201053356.475
CREATE TABLE "{database}"."{table_prefix}corpCustomsOffices" (
    "allowAlliance" CHAR(5) DEFAULT '',
    "allowStandings" CHAR(5) DEFAULT '',
    "itemID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "reinforceHour" VARCHAR(255) DEFAULT '',
    "solarSystemID" BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemName" CHAR(100) NOT NULL,
    "standingLevel" SMALLINT(4) UNSIGNED NOT NULL,
    "taxRateAlliance" DECIMAL(17, 2) NOT NULL,
    "taxRateCorp" DECIMAL(17, 2) NOT NULL,
    "taxRateStandingBad" DECIMAL(17, 2) NOT NULL,
    "taxRateStandingGood" DECIMAL(17, 2) NOT NULL,
    "taxRateStandingHigh" DECIMAL(17, 2) NOT NULL,
    "taxRateStandingHorrible" DECIMAL(17, 2) NOT NULL,
    "taxRateStandingNeutral" DECIMAL(17, 2) NOT NULL,
    PRIMARY KEY ("ownerID","itemID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160201053356.475')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
