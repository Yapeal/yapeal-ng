-- Sql/Create/Api/CallList.mysql.sql
-- version 20161129113301.029
CREATE TABLE "{schema}"."{tablePrefix}apiCallList" (
    "description" TEXT                NOT NULL,
    "groupID"     TINYINT(2) UNSIGNED NOT NULL,
    "name"        CHAR(100)           NOT NULL,
    PRIMARY KEY ("groupID")
);
CREATE TABLE "{schema}"."{tablePrefix}apiCalls" (
    "accessMask"  BIGINT(20) UNSIGNED NOT NULL,
    "description" TEXT                NOT NULL,
    "groupID"     TINYINT(2) UNSIGNED NOT NULL,
    "name"        CHAR(100)           NOT NULL,
    "type"        CHAR(11)            NOT NULL,
    PRIMARY KEY ("accessMask", "type")
);
START TRANSACTION;
-- @formatter:off
INSERT INTO "{schema}"."{tablePrefix}yapealSchemaVersion" ("version")
    VALUES ('20161129113301.029');
COMMIT;
