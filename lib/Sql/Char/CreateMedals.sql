-- Sql/Char/CreateMedals.sql
-- version 20160201053945.336
CREATE TABLE "{database}"."{table_prefix}charMedals" (
    "issued"   DATETIME            NOT NULL,
    "issuerID" BIGINT(20) UNSIGNED NOT NULL,
    "medalID"  BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"  BIGINT(20) UNSIGNED NOT NULL,
    "reason"   TEXT,
    "status"   CHAR(8)             NOT NULL,
    PRIMARY KEY ("ownerID", "medalID")
);
CREATE TABLE "{database}"."{table_prefix}charOtherCorporations" (
    "corporationID" BIGINT(20) UNSIGNED NOT NULL,
    "description"   TEXT                NOT NULL,
    "issued"        DATETIME            NOT NULL,
    "issuerID"      BIGINT(20) UNSIGNED NOT NULL,
    "medalID"       BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "reason"        TEXT,
    "status"        CHAR(8)             NOT NULL,
    "title"         VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID", "medalID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160201053945.336')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
