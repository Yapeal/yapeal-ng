-- Sql/Char/CreateMedals.sql
-- version 20160629053438.509
CREATE TABLE "{database}"."{table_prefix}charMedals" (
    "issued" VARCHAR(255) DEFAULT '',
    "issuerID" BIGINT(20) UNSIGNED NOT NULL,
    "medalID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "reason" VARCHAR(255) DEFAULT '',
    "status" VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID","medalID")
);
CREATE TABLE "{database}"."{table_prefix}charOtherCorporations" (
    "corporationID" BIGINT(20) UNSIGNED NOT NULL,
    "description" TEXT NOT NULL,
    "issued" VARCHAR(255) DEFAULT '',
    "issuerID" BIGINT(20) UNSIGNED NOT NULL,
    "medalID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "reason" VARCHAR(255) DEFAULT '',
    "status" VARCHAR(255) DEFAULT '',
    "title" VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID","medalID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629053438.509')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
