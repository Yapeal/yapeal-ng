-- Sql/Char/CreateContactList.sql
-- version 20160629053416.231
CREATE TABLE "{schema}"."{tablePrefix}charAllianceContactLabels" (
    "labelID" BIGINT(20) UNSIGNED NOT NULL,
    "name"    CHAR(100)           NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "labelID")
);
CREATE TABLE "{schema}"."{tablePrefix}charAllianceContactList" (
    "contactID"     BIGINT(20) UNSIGNED NOT NULL,
    "contactName"   CHAR(100)           NOT NULL,
    "contactTypeID" BIGINT(20) UNSIGNED NOT NULL,
    "labelMask"     BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "standing"      DECIMAL(5, 2)       NOT NULL,
    PRIMARY KEY ("ownerID", "contactID")
);
CREATE TABLE "{schema}"."{tablePrefix}charContactLabels" LIKE "{schema}"."{tablePrefix}charAllianceContactLabels";
CREATE TABLE "{schema}"."{tablePrefix}charContactList" (
    "contactID"     BIGINT(20) UNSIGNED NOT NULL,
    "contactName"   CHAR(100)           NOT NULL,
    "contactTypeID" BIGINT(20) UNSIGNED NOT NULL,
    "inWatchlist"   CHAR(5)             NOT NULL,
    "labelMask"     BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"       BIGINT(20) UNSIGNED NOT NULL,
    "standing"      DECIMAL(5, 2)       NOT NULL,
    PRIMARY KEY ("ownerID", "contactID")
);
CREATE TABLE "{schema}"."{tablePrefix}charCorporateContactLabels" LIKE "{schema}"."{tablePrefix}charAllianceContactLabels";
CREATE TABLE "{schema}"."{tablePrefix}charCorporateContactList" LIKE "{schema}"."{tablePrefix}charAllianceContactList";
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
    VALUES ('20160629053416.231')
    ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
