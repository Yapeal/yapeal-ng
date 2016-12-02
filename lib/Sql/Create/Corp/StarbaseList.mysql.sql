-- Sql/Create/Corp/StarbaseList.sql
-- version 20161202044339.042
CREATE TABLE "{schema}"."{tablePrefix}corpStarbaseList" (
    "itemID"          BIGINT(20) UNSIGNED NOT NULL,
    "locationID"      BIGINT(20) UNSIGNED NOT NULL,
    "moonID"          BIGINT(20) UNSIGNED NOT NULL,
    "onlineTimestamp" DATETIME            NOT NULL,
    "ownerID"         BIGINT(20) UNSIGNED NOT NULL,
    "standingOwnerID" BIGINT(20) UNSIGNED NOT NULL,
    "state"           TINYINT(2) UNSIGNED NOT NULL,
    "stateTimestamp"  DATETIME            NOT NULL,
    "typeID"          BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "itemID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.042');
COMMIT;
