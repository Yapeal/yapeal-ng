-- Sql/Char/CreateNotifications.sql
-- version 20160629053440.639
CREATE TABLE "{schema}"."{table_prefix}charNotifications" (
    "notificationID" BIGINT(20) UNSIGNED  NOT NULL,
    "ownerID"        BIGINT(20) UNSIGNED  NOT NULL,
    "read"           TINYINT(1) UNSIGNED  NOT NULL,
    "senderID"       BIGINT(20) UNSIGNED  NOT NULL,
    "senderName"     CHAR(100)            NOT NULL,
    "sentDate"       DATETIME             NOT NULL,
    "typeID"         SMALLINT(5) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "notificationID")
);
START TRANSACTION;
INSERT INTO "{schema}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053440.639')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
