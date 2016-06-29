-- Sql/Corp/CreateContactList.sql
-- version 20160629053416.744
CREATE TABLE "{database}"."{table_prefix}corpAllianceContactLabels" (
    "labelID" BIGINT(20) UNSIGNED NOT NULL,
    "name" CHAR(100) NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","labelID")
);
CREATE TABLE "{database}"."{table_prefix}corpAllianceContactList" (
    "contactID" BIGINT(20) UNSIGNED NOT NULL,
    "contactName" CHAR(100) NOT NULL,
    "contactTypeID" BIGINT(20) UNSIGNED NOT NULL,
    "labelMask" VARCHAR(255) DEFAULT '',
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "standing" VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID","contactID")
);
CREATE TABLE "{database}"."{table_prefix}corpContactList" (
    "contactID" BIGINT(20) UNSIGNED NOT NULL,
    "contactName" CHAR(100) NOT NULL,
    "contactTypeID" BIGINT(20) UNSIGNED NOT NULL,
    "labelMask" VARCHAR(255) DEFAULT '',
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "standing" VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID","contactID")
);
CREATE TABLE "{database}"."{table_prefix}corpCorporateContactLabels" (
    "labelID" BIGINT(20) UNSIGNED NOT NULL,
    "name" CHAR(100) NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","labelID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629053416.744')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
