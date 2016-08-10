-- Sql/Api/CreateCallList.sql
-- version 20160629012109.659
CREATE TABLE "{schema}"."{table_prefix}apiCallList" (
    "description" TEXT                NOT NULL,
    "groupID"     TINYINT(2) UNSIGNED NOT NULL,
    "name"        CHAR(100)           NOT NULL,
    PRIMARY KEY ("groupID")
);
CREATE TABLE "{schema}"."{table_prefix}apiCalls" (
    "accessMask"  BIGINT(20) UNSIGNED NOT NULL,
    "description" TEXT                NOT NULL,
    "groupID"     TINYINT(2) UNSIGNED NOT NULL,
    "name"        CHAR(100)           NOT NULL,
    "type"        CHAR(11)            NOT NULL,
    PRIMARY KEY ("accessMask", "type")
);
START TRANSACTION;
INSERT INTO "{schema}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES ('20160629012109.659')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
