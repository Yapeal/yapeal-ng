-- Sql/Char/CreateContactList.sql
-- version 20160629053416.231
CREATE TABLE "{database}"."{table_prefix}charAllianceContactLabels" (
    "labelID" BIGINT(20) UNSIGNED NOT NULL,
    "name" CHAR(100) NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","labelID")
);
CREATE TABLE "{database}"."{table_prefix}charAllianceContactList" (
    "contactID" BIGINT(20) UNSIGNED NOT NULL,
    "contactName" CHAR(100) NOT NULL,
    "contactTypeID" BIGINT(20) UNSIGNED NOT NULL,
    "labelMask" VARCHAR(255) DEFAULT '',
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "standing" VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID","contactID")
);
CREATE TABLE "{database}"."{table_prefix}charContactLabels" (
    "labelID" BIGINT(20) UNSIGNED NOT NULL,
    "name" CHAR(100) NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","labelID")
);
CREATE TABLE "{database}"."{table_prefix}charContactList" (
    "contactID" BIGINT(20) UNSIGNED NOT NULL,
    "contactName" CHAR(100) NOT NULL,
    "contactTypeID" BIGINT(20) UNSIGNED NOT NULL,
    "inWatchlist" VARCHAR(255) DEFAULT '',
    "labelMask" VARCHAR(255) DEFAULT '',
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "standing" VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID","contactID")
);
CREATE TABLE "{database}"."{table_prefix}charCorporateContactLabels" (
    "labelID" BIGINT(20) UNSIGNED NOT NULL,
    "name" CHAR(100) NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","labelID")
);
CREATE TABLE "{database}"."{table_prefix}charCorporateContactList" (
    "contactID" BIGINT(20) UNSIGNED NOT NULL,
    "contactName" CHAR(100) NOT NULL,
    "contactTypeID" BIGINT(20) UNSIGNED NOT NULL,
    "labelMask" VARCHAR(255) DEFAULT '',
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "standing" VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID","contactID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629053416.231')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
