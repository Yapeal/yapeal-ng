-- Sql/Corp/CreateContactList.sql
-- version 20160201053354.790
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
    "labelMask" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "standing" DECIMAL(4,2) NOT NULL,
    PRIMARY KEY ("ownerID","contactID")
);
CREATE TABLE "{database}"."{table_prefix}corpContactList" (
    "contactID" BIGINT(20) UNSIGNED NOT NULL,
    "contactName" CHAR(100) NOT NULL,
    "contactTypeID" BIGINT(20) UNSIGNED NOT NULL,
    "labelMask" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "standing" DECIMAL(4,2) NOT NULL,
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
('20160201053354.790')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
