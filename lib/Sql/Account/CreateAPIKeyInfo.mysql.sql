-- Sql/Account/CreateAPIKeyInfo.sql
-- version 20160627181619.973
CREATE TABLE "{schema}"."{tablePrefix}accountAPIKeyInfo" (
    "accessMask" BIGINT(20) UNSIGNED NOT NULL,
    "expires"    DATETIME            NOT NULL DEFAULT '2038-01-19 03:14:07',
    "keyID"      BIGINT(20) UNSIGNED NOT NULL,
    "type"       CHAR(11)            NOT NULL,
    PRIMARY KEY ("keyID")
);
ALTER TABLE "{schema}"."{tablePrefix}accountAPIKeyInfo"
    ADD INDEX "accountAPIKeyInfo1"  ("type");
CREATE TABLE "{schema}"."{tablePrefix}accountCharacters" (
    "allianceID"      BIGINT(20) UNSIGNED NOT NULL,
    "allianceName"    CHAR(100)           NOT NULL,
    "characterID"     BIGINT(20) UNSIGNED NOT NULL,
    "characterName"   CHAR(100)           NOT NULL,
    "corporationID"   BIGINT(20) UNSIGNED NOT NULL,
    "corporationName" CHAR(100)           NOT NULL,
    "factionID"       BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
    "factionName"     CHAR(100)           NOT NULL DEFAULT '',
    PRIMARY KEY ("characterID")
);
ALTER TABLE "{schema}"."{tablePrefix}accountCharacters"
    ADD INDEX "accountCharacters1"  ("corporationID");
CREATE TABLE "{schema}"."{tablePrefix}accountKeyBridge" (
    "characterID" BIGINT(20) UNSIGNED NOT NULL,
    "keyID"       BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("keyID", "characterID")
);
ALTER TABLE "{schema}"."{tablePrefix}accountKeyBridge"
    ADD UNIQUE INDEX "accountKeyBridge1"  ("characterID", "keyID");
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}utilDatabaseVersion" ("version")
    VALUES ('20160627181619.973')
    ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
