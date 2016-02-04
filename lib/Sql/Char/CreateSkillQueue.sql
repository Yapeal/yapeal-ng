-- Sql/Char/CreateSkillQueue.sql
-- version 20160201053950.463
CREATE TABLE "{database}"."{table_prefix}charSkillQueue" (
    "endSP" BIGINT(20) UNSIGNED NOT NULL,
    "endTime" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "level" SMALLINT(4) UNSIGNED NOT NULL,
    "ownerID" BIGINT(20) UNSIGNED NOT NULL,
    "queuePosition" BIGINT(20) UNSIGNED NOT NULL,
    "startSP" BIGINT(20) UNSIGNED NOT NULL,
    "startTime" DATETIME NOT NULL DEFAULT '1970-01-01 00:00:01',
    "typeID" BIGINT(20) UNSIGNED NOT NULL,
    PRIMARY KEY ("ownerID","queuePosition")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160201053950.463')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
