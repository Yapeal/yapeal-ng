-- Sql/Char/CreateContactNotifications.sql
-- version 20160201053355.119
CREATE TABLE "{database}"."{table_prefix}charContactNotifications" (
    "messageData"    TEXT                NOT NULL,
    "notificationID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"        BIGINT(20) UNSIGNED NOT NULL,
    "senderID"       BIGINT(20) UNSIGNED NOT NULL,
    "senderName"     CHAR(100)           NOT NULL,
    "sentDate"       DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    PRIMARY KEY ("ownerID", "notificationID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160201053355.119')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
