-- Sql/Char/CreateNotifications.sql
-- version 20160629053440.639
CREATE TABLE "{database}"."{table_prefix}charNotifications" (
    "notificationID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "read" VARCHAR(255) DEFAULT '',
    "senderID" BIGINT(20) UNSIGNED NOT NULL,
    "senderName" CHAR(100) NOT NULL,
    "sentDate" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "typeID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","notificationID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629053440.639')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
