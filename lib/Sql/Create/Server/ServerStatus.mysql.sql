-- Sql/Server/CreateServerStatus.sql
-- version 20160629053443.301
CREATE TABLE "{schema}"."{tablePrefix}serverServerStatus" (
    "onlinePlayers" BIGINT(20) UNSIGNED    NOT NULL,
    "serverOpen"    ENUM ('False', 'True') NOT NULL
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20160629053443.301');
COMMIT;
