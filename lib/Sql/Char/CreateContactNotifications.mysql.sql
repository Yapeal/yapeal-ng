-- Sql/Char/CreateContactNotifications.sql
-- version 20160629053417.257
CREATE TABLE "{database}"."{table_prefix}charContactNotifications" (
    "messageData"    TEXT,
    "notificationID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"        BIGINT(20) UNSIGNED NOT NULL,
    "senderID"       BIGINT(20) UNSIGNED NOT NULL,
    "senderName"     CHAR(100)           NOT NULL,
    "sentDate"       DATETIME            NOT NULL,
    PRIMARY KEY ("ownerID", "notificationID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053417.257')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
