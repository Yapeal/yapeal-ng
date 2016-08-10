-- Sql/Util/CreateCachedUntil.sql
-- version 20160131212500.001
CREATE TABLE "{schema}"."{tablePrefix}utilCachedUntil" (
    "accountKey"  SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
    "apiName"     CHAR(32)             NOT NULL,
    "expires"     DATETIME             NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED  NOT NULL,
    "sectionName" CHAR(8)              NOT NULL,
    PRIMARY KEY ("apiName", "ownerID", "accountKey")
);
