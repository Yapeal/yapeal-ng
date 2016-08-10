-- Sql/Char/CreateMedals.sql
-- version 20160629053438.509
CREATE TABLE "{schema}"."{table_prefix}charMedals" (
    "issued"   DATETIME            NOT NULL,
    "issuerID" BIGINT(20) UNSIGNED NOT NULL,
    "medalID"  BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "reason"   TEXT,
    "status"   CHAR(8)             NOT NULL,
    PRIMARY KEY ("ownerID", "medalID")
);
CREATE TABLE "{schema}"."{table_prefix}charOtherCorporations" (
    "corporationID" BIGINT(20) UNSIGNED NOT NULL,
    "description"   TEXT                NOT NULL,
    "issued"        DATETIME            NOT NULL,
    "issuerID"      BIGINT(20) UNSIGNED NOT NULL,
    "medalID"       BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "reason"        TEXT,
    "status"        CHAR(8)             NOT NULL,
    "title"         CHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID", "medalID")
);
START TRANSACTION;
INSERT INTO "{schema}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053438.509')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
