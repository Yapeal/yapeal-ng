-- Sql/Corp/CreateOutpostServiceDetail.sql
-- version 20160629053441.897
CREATE TABLE "{database}"."{table_prefix}corpOutpostServiceDetail" (
    "discountPerGoodStanding" VARCHAR(255) DEFAULT '',
    "minStanding" VARCHAR(255) DEFAULT '',
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "serviceName" CHAR(100) NOT NULL,
    "stationID" BIGINT(20) UNSIGNED NOT NULL,
    "surchargePerBadStanding" VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID","stationID","serviceName")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629053441.897')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
