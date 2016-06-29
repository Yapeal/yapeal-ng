-- Sql/Char/CreateContactNotifications.sql
-- version 20160629053417.257
CREATE TABLE "{database}"."{table_prefix}charContactNotifications" (
    "messageData" VARCHAR(255) DEFAULT '',
    "notificationID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "senderID" BIGINT(20) UNSIGNED NOT NULL,
    "senderName" CHAR(100) NOT NULL,
    "sentDate" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    PRIMARY KEY ("ownerID","notificationID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629053417.257')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
