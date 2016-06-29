-- Sql/Corp/CreateShareholders.sql
-- version 20160629053443.777
CREATE TABLE "{database}"."{table_prefix}corpCorporations" (
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "shareholderID" BIGINT(20) UNSIGNED NOT NULL,
    "shareholderName" CHAR(100) NOT NULL,
    "shares" VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID","shareholderID")
);
CREATE TABLE "{database}"."{table_prefix}corpShareholders" (
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "shareholderCorporationID" BIGINT(20) UNSIGNED NOT NULL,
    "shareholderCorporationName" CHAR(100) NOT NULL,
    "shareholderID" BIGINT(20) UNSIGNED NOT NULL,
    "shareholderName" CHAR(100) NOT NULL,
    "shares" VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID","shareholderID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629053443.777')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
