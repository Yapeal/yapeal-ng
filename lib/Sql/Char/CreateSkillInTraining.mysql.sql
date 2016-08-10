-- Sql/Char/CreateSkillInTraining.sql
-- version 20160629053444.328
CREATE TABLE "{schema}"."{table_prefix}charSkillInTraining" (
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
INSERT INTO "{schema}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053444.328')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
