-- Sql/Create/Char/Notifications.sql
-- version 20161129113301.042
CREATE TABLE "{schema}"."{tablePrefix}charNotifications" (
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
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.042');
COMMIT;
