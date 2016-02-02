-- Sql/Corp/CreateIndustryJobs.sql
-- version 20160201053358.289
CREATE TABLE "{database}"."{table_prefix}corpIndustryJobs" (
    "activityID" BIGINT(20) UNSIGNED NOT NULL,
    "blueprintID" BIGINT(20) UNSIGNED NOT NULL,
    "blueprintLocationID" BIGINT(20) UNSIGNED NOT NULL,
    "blueprintTypeID" BIGINT(20) UNSIGNED NOT NULL,
    "blueprintTypeName" CHAR(100) NOT NULL,
    "completedCharacterID" BIGINT(20) UNSIGNED NOT NULL,
    "completedDate" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "cost" VARCHAR(255) DEFAULT '',
    "endDate" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "facilityID" BIGINT(20) UNSIGNED NOT NULL,
    "installerID" BIGINT(20) UNSIGNED NOT NULL,
    "installerName" CHAR(100) NOT NULL,
    "jobID" BIGINT(20) UNSIGNED NOT NULL,
    "licensedRuns" VARCHAR(255) DEFAULT '',
    "outputLocationID" BIGINT(20) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "pauseDate" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "probability" VARCHAR(255) DEFAULT '',
    "productTypeID" BIGINT(20) UNSIGNED NOT NULL,
    "productTypeName" CHAR(100) NOT NULL,
    "runs" VARCHAR(255) DEFAULT '',
    "solarSystemID" BIGINT(20) UNSIGNED NOT NULL,
    "solarSystemName" CHAR(100) NOT NULL,
    "startDate" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "stationID" BIGINT(20) UNSIGNED NOT NULL,
    "status" VARCHAR(255) DEFAULT '',
    "successfulRuns" VARCHAR(255) DEFAULT '',
    "teamID" BIGINT(20) UNSIGNED NOT NULL,
    "timeInSeconds" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    PRIMARY KEY ("ownerID","jobID")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160201053358.289')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
