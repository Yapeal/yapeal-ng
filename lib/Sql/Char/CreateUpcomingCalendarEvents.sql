-- Sql/Char/CreateUpcomingCalendarEvents.sql
-- version 20160201053951.565
CREATE TABLE "{database}"."{table_prefix}charUpcomingCalendarEvents" (
    "duration" VARCHAR(255) DEFAULT '',
    "eventDate" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "eventID" BIGINT(20) UNSIGNED NOT NULL,
    "eventText" VARCHAR(255) DEFAULT '',
    "eventTitle" VARCHAR(255) DEFAULT '',
    "importance" VARCHAR(255) DEFAULT '',
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerName" CHAR(100) NOT NULL,
    "ownerTypeID" BIGINT(20) UNSIGNED NOT NULL,
    "response" VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("ownerID","eventID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160201053951.565')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
