-- Sql/Create/Char/ContactNotifications.sql
-- version 20161202044339.010
CREATE TABLE "{schema}"."{tablePrefix}charContactNotifications" (
    "messageData"    TEXT,
    "notificationID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"        BIGINT(20) UNSIGNED NOT NULL,
    "senderID"       BIGINT(20) UNSIGNED NOT NULL,
    "senderName"     CHAR(100)           NOT NULL,
    "sentDate"       DATETIME            NOT NULL,
    PRIMARY KEY ("ownerID", "notificationID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.010');
COMMIT;
