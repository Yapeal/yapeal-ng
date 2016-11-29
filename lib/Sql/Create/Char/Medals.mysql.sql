-- Sql/Create/Char/Medals.sql
-- version 20161129113301.041
CREATE TABLE "{schema}"."{tablePrefix}charMedals" (
    "issued"   DATETIME            NOT NULL,
    "issuerID" BIGINT(20) UNSIGNED NOT NULL,
    "medalID"  BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "reason"   TEXT,
    "status"   CHAR(8)             NOT NULL,
    PRIMARY KEY ("ownerID", "medalID")
);
CREATE TABLE "{schema}"."{tablePrefix}charOtherCorporations" (
    "corporationID" BIGINT(20) UNSIGNED NOT NULL,
    "description"   TEXT                NOT NULL,
    "issued"        DATETIME            NOT NULL,
    "issuerID"      BIGINT(20) UNSIGNED NOT NULL,
    "medalID"       BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "reason"        TEXT,
    "status"        CHAR(8)             NOT NULL,
    "title"         CHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID", "medalID", "corporationID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.041');
COMMIT;
