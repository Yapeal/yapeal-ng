-- Sql/Create/Server/ServerStatus.sql
-- version 20161202044339.053
CREATE TABLE "{schema}"."{tablePrefix}serverServerStatus" (
    "onlinePlayers" BIGINT(20) UNSIGNED    NOT NULL,
    "serverOpen"    ENUM ('False', 'True') NOT NULL
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161202044339.053');
COMMIT;
