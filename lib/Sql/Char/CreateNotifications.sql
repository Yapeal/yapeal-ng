-- Sql/Char/CreateNotifications.sql
-- version 20160201053946.867
CREATE TABLE "{database}"."{table_prefix}charNotifications" (
    "notificationID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"        BIGINT(20) UNSIGNED NOT NULL,
    "read"           TINYINT(1)          NOT NULL,
    "senderID"       BIGINT(20) UNSIGNED NOT NULL,
    "senderName"     CHAR(100)           NOT NULL,
    "sentDate"       DATETIME            NOT NULL,
    "typeID"         BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "notificationID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160201053946.867')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
