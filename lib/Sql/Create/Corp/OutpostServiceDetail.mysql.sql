-- Sql/Create/Corp/OutpostServiceDetail.sql
-- version 20161129113301.064
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
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.064');
COMMIT;
