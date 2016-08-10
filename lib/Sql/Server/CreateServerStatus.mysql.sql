-- Sql/Server/CreateServerStatus.sql
-- version 20160629053443.301
CREATE TABLE "{schema}"."{table_prefix}serverServerStatus" (
    "onlinePlayers" BIGINT(20) UNSIGNED    NOT NULL,
    "serverOpen"    ENUM ('False', 'True') NOT NULL
);
START TRANSACTION;
INSERT INTO "{schema}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629053443.301')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
