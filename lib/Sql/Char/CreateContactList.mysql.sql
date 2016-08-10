-- Sql/Char/CreateContactList.sql
-- version 20160629053416.231
CREATE TABLE "{schema}"."{table_prefix}charAllianceContactLabels" (
    "labelID" BIGINT(20) UNSIGNED NOT NULL,
    "name"    CHAR(100)           NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "labelID")
);
CREATE TABLE "{schema}"."{table_prefix}charAllianceContactList" (
    "contactID"     BIGINT(20) UNSIGNED NOT NULL,
    "contactName"   CHAR(100)           NOT NULL,
    "contactTypeID" BIGINT(20) UNSIGNED NOT NULL,
    "labelMask"     BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "standing"      DECIMAL(5, 2)       NOT NULL,
    PRIMARY KEY ("ownerID", "contactID")
);
CREATE TABLE "{schema}"."{table_prefix}charContactLabels" LIKE "{schema}"."{table_prefix}charAllianceContactLabels";
CREATE TABLE "{schema}"."{table_prefix}charContactList" (
    "contactID"     BIGINT(20) UNSIGNED NOT NULL,
    "contactName"   CHAR(100)           NOT NULL,
    "contactTypeID" BIGINT(20) UNSIGNED NOT NULL,
    "inWatchlist"   CHAR(5)             NOT NULL,
    "labelMask"     BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "standing"      DECIMAL(5, 2)       NOT NULL,
    PRIMARY KEY ("ownerID", "contactID")
);
CREATE TABLE "{schema}"."{table_prefix}charCorporateContactLabels" LIKE "{schema}"."{table_prefix}charAllianceContactLabels";
CREATE TABLE "{schema}"."{table_prefix}charCorporateContactList" LIKE "{schema}"."{table_prefix}charAllianceContactList";
START TRANSACTION;
INSERT INTO "{schema}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053416.231')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
