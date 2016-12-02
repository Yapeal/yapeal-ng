-- Sql/Create/Yapeal/CachedUntil.mysql.sql
-- version 20161202044339.054
CREATE TABLE "{schema}"."{tablePrefix}yapealCachedUntil" (
    "accountKey"  SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
    "apiName"     CHAR(32)             NOT NULL,
    "expires"     DATETIME             NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED  NOT NULL,
    "sectionName" CHAR(8)              NOT NULL,
    PRIMARY KEY ("apiName", "ownerID", "accountKey")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.054');
COMMIT;
