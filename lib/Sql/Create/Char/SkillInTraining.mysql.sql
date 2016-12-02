-- Sql/Create/Char/SkillInTraining.sql
-- version 20161202044339.019
CREATE TABLE "{schema}"."{tablePrefix}charSkillInTraining" (
    "currentTQTime"         DATETIME                      DEFAULT NULL,
    "offset"                TINYINT(2)           NOT NULL,
    "ownerID"               BIGINT(20) UNSIGNED  NOT NULL,
    "skillInTraining"       BIGINT(20) UNSIGNED  NOT NULL,
    "trainingDestinationSP" BIGINT(20) UNSIGNED  NOT NULL,
    "trainingEndTime"       DATETIME             NOT NULL DEFAULT '1970-01-01 00:00:01',
    "trainingStartSP"       BIGINT(20) UNSIGNED  NOT NULL,
    "trainingStartTime"     DATETIME             NOT NULL DEFAULT '1970-01-01 00:00:01',
    "trainingToLevel"       SMALLINT(4) UNSIGNED NOT NULL,
    "trainingTypeID"        BIGINT(20) UNSIGNED  NOT NULL,
    PRIMARY KEY ("ownerID")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.019');
COMMIT;
