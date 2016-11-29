-- Sql/Char/CreateMailMessages.mysql.sql
-- version 20160822175059.411
CREATE TABLE "{database}"."{tablePrefix}charMailMessages" (
    "messageID"          BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"            BIGINT(20) UNSIGNED NOT NULL,
    "senderID"           BIGINT(20) UNSIGNED NOT NULL,
    "senderName"         CHAR(100)           DEFAULT NULL,
    "senderTypeID"       BIGINT(20) UNSIGNED DEFAULT NULL,
    "sentDate"           DATETIME            NOT NULL,
    "title"              CHAR(255)           DEFAULT NULL,
    "toCharacterIDs"     TEXT,
    "toCorpOrAllianceID" BIGINT(20) UNSIGNED DEFAULT '0',
    "toListID"           TEXT,
    PRIMARY KEY ("ownerID", "messageID")
);
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20160822175059.411');
COMMIT;
