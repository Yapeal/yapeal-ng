-- Sql/Char/CreateUpcomingCalendarEvents.sql
-- version 20160629053459.556
CREATE TABLE "{schema}"."{table_prefix}charUpcomingCalendarEvents" (
    "duration"    SMALLINT(4) UNSIGNED NOT NULL,
    "eventDate"   DATETIME             NOT NULL,
    "eventID"     BIGINT(20) UNSIGNED  NOT NULL,
    "eventText"   TEXT,
    "eventTitle"  CHAR(255) DEFAULT '',
    "importance"  TINYINT(1) UNSIGNED  NOT NULL,
    "ownerID"     BIGINT(20) UNSIGNED  NOT NULL,
    "ownerName"   CHAR(100)            NOT NULL,
    "ownerTypeID" BIGINT(20) UNSIGNED  NOT NULL,
    "response"    CHAR(9)   DEFAULT '',
    PRIMARY KEY ("ownerID", "eventID")
);
START TRANSACTION;
INSERT INTO "{schema}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053459.556')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
