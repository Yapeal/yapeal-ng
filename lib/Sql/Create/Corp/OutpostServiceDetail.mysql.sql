-- Sql/Corp/CreateOutpostServiceDetail.sql
-- version 20160629053441.897
CREATE TABLE "{schema}"."{tablePrefix}corpOutpostServiceDetail" (
    "discountPerGoodStanding" DECIMAL(5, 2)       NOT NULL,
    "minStanding"             DECIMAL(5, 2)       NOT NULL,
    "ownerID"                 BIGINT(20) UNSIGNED NOT NULL,
    "serviceName"             CHAR(100)           NOT NULL,
    "stationID"               BIGINT(20) UNSIGNED NOT NULL,
    "surchargePerBadStanding" DECIMAL(5, 2)       NOT NULL,
    PRIMARY KEY ("ownerID", "stationID", "serviceName")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
    VALUES ('20160629053441.897')
    ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
