-- Sql/Api/CreateCallList.sql
-- version 20160629012109.659
CREATE TABLE "{database}"."{table_prefix}apiCallList" (
    "description" TEXT NOT NULL,
    "groupID" BIGINT(20) UNSIGNED NOT NULL,
    "name" CHAR(100) NOT NULL,
    PRIMARY KEY ("groupID")
);
CREATE TABLE "{database}"."{table_prefix}apiCalls" (
    "accessMask" VARCHAR(255) DEFAULT '',
    "description" TEXT NOT NULL,
    "groupID" BIGINT(20) UNSIGNED NOT NULL,
    "name" CHAR(100) NOT NULL,
    "type" VARCHAR(255) DEFAULT '',
    PRIMARY KEY ("accessMask","type")
);
START TRANSACTION;
INSERT INTO "{database}"."{table_prefix}utilDatabaseVersion" ("version")
VALUES
('20160629012109.659')
ON DUPLICATE KEY UPDATE "version" = VALUES("version");
COMMIT;
