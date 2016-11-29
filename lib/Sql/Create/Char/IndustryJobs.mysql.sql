-- Sql/Create/Char/IndustryJobs.sql
-- version 20161129113301.037
CREATE TABLE "{schema}"."{tablePrefix}charIndustryJobs" (
    "activityID"           TINYINT(2) UNSIGNED NOT NULL,
    "blueprintID"          BIGINT(20) UNSIGNED NOT NULL,
    "blueprintLocationID"  BIGINT(20) UNSIGNED NOT NULL,
    "blueprintTypeID"      BIGINT(20) UNSIGNED NOT NULL,
    "blueprintTypeName"    CHAR(100)           NOT NULL,
    "completedCharacterID" BIGINT(20) UNSIGNED NOT NULL,
    "completedDate"        DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "cost"                 DECIMAL(17, 2)      NOT NULL,
    "endDate"              DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "facilityID"           BIGINT(20) UNSIGNED NOT NULL,
    "installerID"          BIGINT(20) UNSIGNED NOT NULL,
    "installerName"        CHAR(100)           NOT NULL,
    "jobID"                BIGINT(20) UNSIGNED NOT NULL,
    "licensedRuns"         BIGINT(20)          NOT NULL,
    "outputLocationID"     BIGINT(20) UNSIGNED NOT NULL,
    "ownerID"              BIGINT(20) UNSIGNED NOT NULL,
    "pauseDate"            DATETIME            NOT NULL DEFAULT '1970-01-01 00:00:01',
    "probability"          CHAR(24)                     DEFAULT NULL,
    "productTypeID"        BIGINT(20) UNSIGNED NOT NULL,
    "productTypeName"      CHAR(100)           NOT NULL,
    "runs"                 BIGINT(20)          NOT NULL,
    "solarSystemID"        BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemName"      CHAR(100)           NOT NULL,
    "startDate"            DATETIME            NOT NULL,
    "stationID"            BIGINT(20) UNSIGNED NOT NULL,
    "status"               INT                 NOT NULL,
    "successfulRuns"       BIGINT(20)                   DEFAULT 0,
    "teamID"               BIGINT(20) UNSIGNED NOT NULL,
    "timeInSeconds"        BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID", "jobID")
);
-- Used altered index name(s) since they get copied to corp table during create ... like ...
ALTER TABLE "{schema}"."{tablePrefix}charIndustryJobs"
    ADD INDEX "IndustryJobs1" ("ownerID", "stationID", "jobID");
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.037');
COMMIT;
