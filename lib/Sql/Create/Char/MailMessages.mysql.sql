-- Sql/Create/Char/MailMessages.mysql.sql
-- version 20161129113301.039
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
    VALUES ('20161129113301.039');
COMMIT;
