-- Sql/Char/CreateSkillQueue.sql
-- version 20160629053444.663
CREATE TABLE "{database}"."{table_prefix}charSkillQueue" (
    "endSP" VARCHAR(255) DEFAULT '',
    "endTime" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "level" SMALLINT(4) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "queuePosition" VARCHAR(255) DEFAULT '',
    "startSP" VARCHAR(255) DEFAULT '',
    "startTime" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "typeID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","queuePosition")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629053444.663')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
