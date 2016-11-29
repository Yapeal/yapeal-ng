-- Sql/Create/Char/UpcomingCalendarEvents.sql
-- version 20161129113301.046
CREATE TABLE "{schema}"."{tablePrefix}charUpcomingCalendarEvents" (
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
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.046');
COMMIT;
